<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ActivityLog;
use App\Models\StockOut;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->canAccessAdmin() && !auth()->user()->is_inventory_officer) {
            abort(403, 'Unauthorized access to Inventory.');
        }

        $search = $request->input('search');
        $categoryFilter = $request->input('category_filter');

        if ($request->input('view') === 'archived') {
            $query = Item::onlyTrashed()->with('category');
        } else {
            $query = Item::with('category');
        }

        if ($categoryFilter) {
            $query->where('itemctgry_id', $categoryFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $items = $query->orderBy('name')->paginate(10);
        $categories = ItemCategory::orderBy('name')->get();

        return view('inventory.index', compact('items', 'categories'));
    }

    public function store(Request $request)
    {
        // Smart Check: Is this item in the archives?
        $existing = Item::withTrashed()->where('name', $request->name)->first();
        if ($existing && $existing->trashed()) {
            return back()
                ->withInput()
                ->withErrors(['name' => "This item is currently in the ARCHIVES (deleted). Please go to Archives and restore it."]);
        }

        $data = $request->validate([
            'itemctgry_id' => ['required', 'exists:item_categories,itemctgry_id'],
            'name' => ['required', 'string', 'max:150', 'unique:items,name'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
        ]);

        $nextId = $this->nextItemId();

        $item = Item::create([
            'item_id' => $nextId,
            'itemctgry_id' => $data['itemctgry_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'unit_price' => $data['unit_price'],
            'unit' => $data['unit'] ?? null,
            'active' => true,
        ]);

        ActivityLog::record(
            'item.created',
            $item,
            'Item added: ' . $item->name,
            ['name' => $item->name, 'quantity' => $item->quantity]
        );

        return redirect()->route('inventory.index')->with('success', 'Item added successfully!');
    }

    public function edit($item_id)
    {
        $item = Item::with('category')->findOrFail($item_id);
        $categories = ItemCategory::orderBy('name')->get();

        return view('inventory.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $item_id)
    {
        $item = Item::findOrFail($item_id);

        $data = $request->validate([
            'itemctgry_id' => ['required', 'exists:item_categories,itemctgry_id'],
            'name' => ['required', 'string', 'max:150', 'unique:items,name,' . $item->item_id . ',item_id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

        $item->update([
            'itemctgry_id' => $data['itemctgry_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'quantity' => $data['quantity'] ?? $item->quantity,
            'unit_price' => $data['unit_price'],
            'unit' => $data['unit'] ?? null,
            'active' => isset($data['active']) ? (bool) $data['active'] : $item->active,
        ]);

        ActivityLog::record(
            'item.updated',
            $item,
            'Item updated: ' . $item->name,
            ['name' => $item->name, 'quantity' => $item->quantity]
        );

        return redirect()->route('inventory.index')->with('success', 'Item updated successfully!');
    }

    public function destroy($item_id)
    {
        $item = Item::findOrFail($item_id);

        // Safety: Cannot delete if stock > 0
        if ($item->quantity > 0) {
            return back()->withErrors(['error' => 'Cannot delete item with existing stock (Qty: ' . $item->quantity . '). Please empty stock first.']);
        }

        $name = $item->name;
        $item->delete();

        ActivityLog::record(
            'item.deleted',
            null,
            'Item deleted: ' . $name,
            ['name' => $name]
        );

        return redirect()->route('inventory.index')->with('success', 'Item deleted successfully!');
    }

    public function restore($item_id)
    {
        $item = Item::withTrashed()->findOrFail($item_id);
        $item->restore();

        ActivityLog::record(
            'item.restored',
            $item,
            'Item restored: ' . $item->name,
            ['name' => $item->name]
        );

        return back()->with('success', 'Item restored successfully.');
    }

    public function history($item_id)
    {
        $item = Item::withTrashed()->findOrFail($item_id);

        // Fetch Stock Ins
        $ins = \App\Models\StockIn::where('item_id', $item_id)
            ->with('supplier')
            ->get()
            ->map(fn($r) => [
                'type' => 'in',
                'date' => $r->created_at,
                'qty' => $r->quantity,
                'user' => $r->supplier->name ?? 'Unknown Supplier',
                'ref' => 'Stock In',
            ]);

        // Fetch Stock Outs (via Services or Direct StockOut if implemented)
        // Assuming StockOut model records direct usage
        $outs = StockOut::where('item_id', $item_id)
            ->with('user')
            ->get()
            ->map(fn($r) => [
                'type' => 'out',
                'date' => $r->created_at,
                'qty' => -$r->quantity, // Negative for calculation
                'user' => $r->user->name ?? 'Unknown',
                'ref' => $r->remarks ?? 'Stock Out',
            ]);

        // Combine and sort chronologically (oldest first) to calculate running balance
        $allLogs = $ins->concat($outs)->sortBy('date')->values();

        // Calculate remaining stock after each transaction
        $runningBalance = 0;
        $logsWithRemaining = $allLogs->map(function ($log) use (&$runningBalance) {
            $runningBalance += $log['qty'];
            $log['remaining'] = $runningBalance;
            return $log;
        });

        // Sort by date descending for display (newest first)
        $logs = $logsWithRemaining->sortByDesc('date');

        return view('inventory.history', compact('item', 'logs'));
    }

    private function nextItemId(): string
    {
        $last = Item::withTrashed()->orderBy('item_id', 'desc')->first();
        $n = $last ? (int) preg_replace('/\D/', '', $last->item_id) : 0;
        return 'ITM' . str_pad($n + 1, 4, '0', STR_PAD_LEFT);
    }
}