@extends('system')

@section('title', 'Inventory Reports')

@section('content')
    <div class="reports-nav mb-4"
        style="display:flex; gap:10px; border-bottom:1px solid #444; padding-bottom:15px; margin-bottom: 20px;">
        <a href="{{ route('reports.index') }}"
            class="btn {{ Route::is('reports.index') ? 'btn-primary' : 'btn-secondary' }}">Activity Log</a>
        <a href="{{ route('reports.bookings') }}"
            class="btn {{ Route::is('reports.bookings') ? 'btn-primary' : 'btn-secondary' }}">Bookings Report</a>
        <a href="{{ route('reports.inventory') }}"
            class="btn {{ Route::is('reports.inventory') ? 'btn-primary' : 'btn-secondary' }}">Inventory Report</a>
    </div>

    <div class="report-header mb-4">
        <h1 style="color:var(--brand-red);">Inventory Reports</h1>
        <div class="report-actions">
            <form action="{{ route('reports.inventory') }}" method="GET" class="filter-form d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label small">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div>
                    <label class="form-label small">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div>
                    <label class="form-label small">Stock Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="low" @selected(request('status') == 'low')>Low Stock</option>
                        <option value="out" @selected(request('status') == 'out')>Out of Stock</option>
                        <option value="good" @selected(request('status') == 'good')>Good Stock</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>

                <a href="{{ route('reports.inventory', array_merge(request()->all(), ['export' => 'pdf'])) }}"
                    class="btn btn-secondary">Export PDF</a>
            </form>
        </div>
    </div>

    <!-- Current Stock Section -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Current Stock Status</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordless">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Current Stock</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total Value</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                <td>5 (Default)</td>
                                <td>
                                    @if($item->quantity <= 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($item->quantity <= 5)
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">Good</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No inventory items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                {{ $inventory->appends(['tab' => 'stock'])->links() }}
            </div>
        </div>
    </div>

    <!-- Movements Section -->
    <!-- Showing recent movements (Stock In and Stock Out mixed if possible, or just Stock Out/In separate tables for clarity? Plan said 'Movements'. Mixing them is better for 'Tracking'. -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Inventory Movements (Stock In / Out)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total Price</th>
                            <th>User / Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $mov)
                            <tr>
                                <td>{{ $mov['date'] }}</td>
                                <td>
                                    @if($mov['type'] == 'Service Usage')
                                        <a href="{{ route('services.edit', $mov['ref_id']) }}">Service #{{ $mov['ref_code'] }}</a>
                                    @elseif($mov['type'] == 'Restock')
                                        Stock In #{{ $mov['ref_id'] }}
                                    @else
                                        {{ $mov['ref_id'] }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $mov['type'] == 'Restock' ? 'success' : 'warning' }}">
                                        {{ $mov['type'] }}
                                    </span>
                                </td>
                                <td>{{ $mov['item_name'] }}</td>
                                <td>{{ $mov['quantity'] }}</td>
                                <td class="text-end">{{ number_format($mov['unit_price'], 2) }}</td>
                                <td class="text-end">{{ number_format($mov['total_price'], 2) }}</td>
                                <td>{{ $mov['user'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No movements found in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2 text-muted small">
                * Showing most recent movements first.
            </div>
        </div>
    </div>
@endsection