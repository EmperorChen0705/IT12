@extends('system')

@section('title', 'Booking Reports')

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

    <div class="report-header">
        <h1>Booking Reports</h1>
        <div class="report-actions">
            <!-- Filter form could go here matching ReportsController logic -->
            <form action="{{ route('reports.bookings') }}" method="GET" class="filter-form">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected(request('status') == 'pending')>Pending</option>
                    <option value="confirmed" @selected(request('status') == 'confirmed')>Confirmed</option>
                    <option value="completed" @selected(request('status') == 'completed')>Completed</option>
                    <option value="rejected" @selected(request('status') == 'rejected')>Rejected</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>

                <a href="{{ route('reports.bookings', array_merge(request()->all(), ['export' => 'pdf'])) }}"
                    class="btn btn-secondary">Export PDF</a>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table report-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Details</th>
                    <th>Vehicle / Service</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Assigned Technician</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td class="fw-bold">{{ $booking->booking_id }}</td>
                        <td>
                            <div>{{ $booking->customer_name }}</div>
                            <small class="text-muted">{{ $booking->contact_number }}</small>
                            @if($booking->email)
                                <br><small class="text-muted">{{ $booking->email }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="badge bg-info mb-1">{{ $booking->service_type }}</div>
                            @if($booking->vehicle_make || $booking->vehicle_model)
                                <div class="small fw-bold">{{ $booking->vehicle_make }} {{ $booking->vehicle_model }}</div>
                            @endif
                            @if($booking->plate_number)
                                <div class="small text-muted mb-1">Plate: {{ $booking->plate_number }}</div>
                            @endif
                            @if($booking->notes)
                                <div class="small fst-italic text-muted">Notes:
                                    {{ \Illuminate\Support\Str::limit($booking->notes, 50) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div><small>Start:</small>
                                {{ $booking->service?->started_at?->format('Y-m-d H:i') ?? $booking->preferred_date }}</div>
                            <div><small>Exp. End:</small> {{ $booking->preferred_date }}</div>
                            <!-- Simple logic: Preferred Date is expected end or start? Usually Start. We'll use Preferred as Expected for now if no other data -->
                            @if($booking->service?->completed_at)
                                <div class="text-success"><small>Actual End:</small>
                                    {{ $booking->service->completed_at->format('Y-m-d H:i') }}</div>
                            @endif
                        </td>
                        <td>
                            <span
                                class="badge bg-{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td>
                            @php
                                // Derive Technician: The active log user? Or undefined?
                                // ActivityLog logic will be handled in Controller or Model helper, but here assuming we pass it or it's on Service model if we added it?
                                // Plan said derive from ActivityLog. We can't easily query ActivityLog in view loop (N+1).
                                // Ideally Controller attaches this info. 
                                // listing 'Assigned Technician' 
                            @endphp
                            {{ $booking->technician_name ?? 'Not Assigned' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No bookings found for the selected criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
@endsection