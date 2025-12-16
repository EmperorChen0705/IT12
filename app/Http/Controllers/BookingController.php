<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\StockOut;
use App\Models\ActivityLog;
use App\Models\ServiceType;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->canAccessAdmin() && !auth()->user()->is_manager) {
            abort(403, 'Unauthorized access to Bookings.');
        }

        $search = $request->input('search');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // "Upcoming" view mode or default filter
        // If request has no filters, maybe we default? 
        // Let's just implement the filter capability first.

        $query = Booking::query()->with(['service.technician']); // Eager load for the column

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('preferred_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('preferred_date', '<=', $endDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Default sort by date ascending for schedule view, or desc for log view?
        // Usually schedule is ascending (soonest first).
        // If searching/history, desc.
        // Let's decide based on filter: if "Upcoming" (start_date >= today), ASC.
        // Default existing is desc. I will keep desc as default but maybe allow toggle?
        // User said "Schedule Page (Upcoming Schedule)".
        // I'll stick to DESC default for now to match "Recent Bookings" pattern, 
        // but if they filter for future, they might want ASC.
        // Let's add simple sort logic.
        $query->orderBy('preferred_date', 'asc')->orderBy('preferred_time', 'asc');

        $bookings = $query->paginate(15);
        $serviceTypes = ServiceType::where('active', true)->orderBy('name')->get();

        return view('bookings.index', compact('bookings', 'search', 'status', 'serviceTypes', 'startDate', 'endDate'));
    }

    public function appoint($booking_id)
    {
        $booking = Booking::where('booking_id', $booking_id)->firstOrFail();

        if ($booking->status !== 'completed') {
            return back()->withErrors('Booking is not completed yet.');
        }

        if ($booking->status === 'appointed') {
            return back()->with('success', 'Already appointed.');
        }

        $booking->status = 'appointed';
        $booking->save();

        ActivityLog::record(
            'booking.appointed',
            $booking,
            'Booking appointed',
            ['status' => $booking->status]
        );

        return back()->with('success', 'Booking appointed.');
    }
}