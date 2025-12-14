<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canAccessAdmin()) {
                abort(403, 'Unauthorized access.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        try {
            // Activity Log Query
            $query = ActivityLog::with('user')->orderBy('occurred_at', 'desc');

            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $userId = $request->input('user_id');
            $event = $request->input('event_type');
            $search = $request->input('search');

            if ($dateFrom)
                $query->whereDate('occurred_at', '>=', $dateFrom);
            if ($dateTo)
                $query->whereDate('occurred_at', '<=', $dateTo);
            if ($userId)
                $query->where('user_id', $userId);
            if ($event)
                $query->where('event_type', $event);
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('subject_id', 'like', "%{$search}%");
                });
            }

            $logs = $query->paginate(20)->withQueryString();
            $users = \App\Models\User::orderBy('name')->get();
            $eventTypes = ActivityLog::distinct()->pluck('event_type')->filter()->values();

            // Metrics (Simple implementation)
            $appointmentsThisMonth = Booking::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
            $servicesCompletedMonth = Service::whereYear('completed_at', now()->year)->whereMonth('completed_at', now()->month)->where('status', Service::STATUS_COMPLETED)->count();
            $itemsAddedMonth = Item::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();

            // Avg Appointments per day (this month)
            $daysInMonth = now()->day;
            $avgAppointmentsPerDay = $daysInMonth > 0 ? round($appointmentsThisMonth / $daysInMonth, 1) : 0;

            // Top Items Used (this month) via Service
            $topItems = DB::table('service_items')
                ->join('services', 'service_items.service_id', '=', 'services.id')
                ->whereYear('services.completed_at', now()->year)
                ->whereMonth('services.completed_at', now()->month)
                ->select('service_items.item_id', DB::raw('sum(service_items.quantity) as uses'))
                ->groupBy('service_items.item_id')
                ->orderByDesc('uses')
                ->limit(5)
                ->get();


            return view('reports.index', compact(
                'logs',
                'users',
                'eventTypes',
                'dateFrom',
                'dateTo',
                'userId',
                'event',
                'search',
                'appointmentsThisMonth',
                'servicesCompletedMonth',
                'itemsAddedMonth',
                'avgAppointmentsPerDay',
                'topItems'
            ))->render();
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getTraceAsString());
        }
    }

    public function bookings(Request $request)
    {
        $query = Booking::with(['service.technician', 'service.items']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('preferred_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('preferred_date', '<=', $request->date_to);
        }

        // CSV Export
        if ($request->input('export') === 'csv') {
            return $this->exportBookingsCsv($query->get());
        }

        $bookings = $query->latest('booking_id')->paginate(20)->appends($request->query());

        // Derive Technician from relation
        foreach ($bookings as $b) {
            if ($b->service && $b->service->technician) {
                $b->technician_name = $b->service->technician->first_name . ' ' . $b->service->technician->last_name;
            } else {
                $b->technician_name = 'Not Assigned';
            }
        }

        return view('reports.bookings', compact('bookings'));
    }

    public function inventory(Request $request)
    {
        // 1. Current Stock Query
        $stockQuery = Item::query();
        if ($request->input('status') === 'low') {
            $stockQuery->where('quantity', '<=', 5)->where('quantity', '>', 0);
        } elseif ($request->input('status') === 'out') {
            $stockQuery->where('quantity', '<=', 0);
        } elseif ($request->input('status') === 'good') {
            $stockQuery->where('quantity', '>', 5);
        }

        // Export Logic handling
        if ($request->input('export') === 'csv') {
            return $this->exportInventoryCsv($stockQuery->get(), $this->getRecentMovements($request, 1000));
        }

        $inventory = $stockQuery->orderBy('name')->paginate(20, ['*'], 'stock_page')->appends($request->query());

        // 2. Movements (StockIn + StockOut)
        // We'll manually merge the last N records for display
        $movements = $this->getRecentMovements($request);

        return view('reports.inventory', compact('inventory', 'movements'));

    }

    private function getRecentMovements(Request $request, $limit = 50)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Stock Outs
        $outQuery = StockOut::with(['item', 'user'])
            ->select('stockout_id as id', 'stockout_date as date', 'quantity', 'item_id', 'user_id', 'reference_id', 'reference_type', DB::raw("'out' as direction"));

        if ($dateFrom)
            $outQuery->where('stockout_date', '>=', $dateFrom);
        if ($dateTo)
            $outQuery->where('stockout_date', '<=', $dateTo);

        $outs = $outQuery->latest('stockout_date')->limit($limit)->get();

        // Stock Ins
        $inQuery = StockIn::with(['item', 'supplier'])
            ->select('stockin_id as id', 'stockin_date as date', 'quantity', 'price', 'total_price', 'item_id', 'supplier_id', DB::raw("'in' as direction"));

        if ($dateFrom)
            $inQuery->where('stockin_date', '>=', $dateFrom);
        if ($dateTo)
            $inQuery->where('stockin_date', '<=', $dateTo);

        $ins = $inQuery->latest('stockin_date')->limit($limit)->get();

        // Merge and Sort
        $merged = collect();

        foreach ($outs as $out) {
            // Check if it's service usage
            $type = 'Manual Out'; // Default
            $refCode = 'N/A';
            if ($out->reference_type === Service::class) {
                $type = 'Service Usage';
                // Try to find service ref code - N+1 optimization: simplified for now or eager load if possible.
                // Since it's limited report, individual query or cache might be okay.
                // For report, let's just use ID or fetch service lightly.
                $service = Service::find($out->reference_id);
                $refCode = $service ? $service->reference_code : $out->reference_id;
            }

            $merged->push([
                'date' => $out->date, // cast?
                'timestamp' => Carbon::parse($out->date)->timestamp,
                'type' => $type,
                'ref_id' => $out->reference_id ?? $out->id,
                'ref_code' => $refCode,
                'item_name' => $out->item->name ?? 'Unknown Item',
                'quantity' => $out->quantity,
                'user' => $out->user->name ?? 'Unknown',
                'description' => 'Stock Out',
                // Pricing for Out: Reference Item Unit Price at that time?
                // Or current Item unit price?
                // User requirement: "Items used with their price".
                // StockOut doesn't store price. Use Item's current price as fallback.
                'unit_price' => $out->item->unit_price ?? 0,
                'total_price' => ($out->item->unit_price ?? 0) * $out->quantity,
            ]);
        }

        foreach ($ins as $in) {
            $merged->push([
                'date' => $in->date,
                'timestamp' => Carbon::parse($in->date)->timestamp,
                'type' => 'Restock',
                'ref_id' => $in->id,
                'ref_code' => $in->id,
                'item_name' => $in->item->name ?? 'Unknown Item',
                'quantity' => $in->quantity,
                'user' => $in->supplier->name ?? 'Supplier', // Or user who encoded? StockIn doesn't have user_id, it has supplier_id.
                'description' => 'Stock In',
                'unit_price' => $in->price,
                'total_price' => $in->total_price,
            ]);
        }

        return $merged->sortByDesc('timestamp')->take($limit);
    }

    private function exportBookingsCsv($bookings)
    {
        $filename = 'booking_report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->streamDownload(function () use ($bookings) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Booking ID', 'Customer Name', 'Contact', 'Email', 'Service Type', 'Status', 'Preferred Date', 'Notes', 'Assigned Technician']);

            foreach ($bookings as $b) {
                $tech = 'Not Assigned';
                if ($b->service && $b->service->technician) {
                    $tech = $b->service->technician->first_name . ' ' . $b->service->technician->last_name;
                }
                fputcsv($out, [
                    $b->booking_id,
                    $b->customer_name,
                    $b->contact_number,
                    $b->email,
                    $b->service_type,
                    $b->status,
                    $b->preferred_date,
                    $b->notes,
                    $tech
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }

    private function exportInventoryCsv($inventory, $movements)
    {
        $filename = 'inventory_report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->streamDownload(function () use ($inventory, $movements) {
            $out = fopen('php://output', 'w');

            // Section 1: Stock
            fputcsv($out, ['--- CURRENT STOCK STATUS ---']);
            fputcsv($out, ['Item ID', 'Name', 'Quantity', 'Unit Price', 'Total Value', 'Status']);
            foreach ($inventory as $item) {
                $status = $item->quantity <= 0 ? 'Out of Stock' : ($item->quantity <= 5 ? 'Low Stock' : 'Good');
                fputcsv($out, [
                    $item->item_id,
                    $item->name,
                    $item->quantity,
                    $item->unit_price,
                    $item->quantity * $item->unit_price,
                    $status
                ]);
            }

            fputcsv($out, []); // Spacer
            fputcsv($out, ['--- RECENT MOVEMENTS ---']);
            fputcsv($out, ['Date', 'Type', 'Reference', 'Item', 'Quantity', 'Unit Price', 'Total Price', 'User/Supplier']);
            foreach ($movements as $mov) {
                fputcsv($out, [
                    $mov['date'],
                    $mov['type'],
                    $mov['ref_code'],
                    $mov['item_name'],
                    $mov['quantity'],
                    $mov['unit_price'],
                    $mov['total_price'],
                    $mov['user']
                ]);
            }

            fclose($out);
        }, $filename, $headers);
    }
}