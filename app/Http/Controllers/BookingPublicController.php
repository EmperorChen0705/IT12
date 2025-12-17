<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\ServiceType;

class BookingPublicController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::where('active', true)
            ->orderBy('name')
            ->pluck('name');

        return view('booking_portal.index', compact('serviceTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'vehicle_make' => ['nullable', 'string', 'max:50'],
            'vehicle_model' => ['nullable', 'string', 'max:50'],
            'plate_number' => ['nullable', 'string', 'max:20'],
            'contact_number' => ['required', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:150'],
            'service_type' => ['required', 'string', 'max:120'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'channel' => ['nullable', 'string', 'max:50'],
            'is_admin_booking' => ['nullable', 'boolean'],
        ]);

        // Time validation: if date is today, time must be in the future
        if ($data['preferred_date'] === now()->toDateString()) {
            $selectedTime = \Carbon\Carbon::parse($data['preferred_time']);
            if ($selectedTime->format('H:i') <= now()->format('H:i')) {
                return back()->withErrors([
                    'preferred_time' => 'For today\'s booking, please select a time later than now (' . now()->format('h:i A') . ').'
                ])->withInput();
            }
        }

        // 5-Booking Limit Check
        $count = Booking::whereDate('preferred_date', $data['preferred_date'])
            ->where('status', '!=', 'rejected')
            ->count();

        if ($count >= 5) {
            return back()->withErrors(['preferred_date' => 'We are fully booked for this date (Max 5 bookings/day). Please choose another date.'])->withInput();
        }

        // Set default channel if not provided (e.g. from public form)
        if (empty($data['channel'])) {
            $data['channel'] = 'web';
        }

        Booking::create($data);

        if ($request->has('is_admin_booking') && $request->is_admin_booking) {
            return redirect()
                ->route('bookings.index')
                ->with('success', 'Booking created successfully.');
        }

        return redirect()
            ->route('booking.portal')
            ->with('success', 'Booking submitted. We will contact you soon.');
    }
}