<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Booking;
use App\Models\ActivityLog;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $method = $request->input('method');

        $query = Payment::query()->with('user');
        if ($method)
            $query->where('method', $method);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderByDesc('paid_at')->orderByDesc('id')->paginate(20);

        return view('payments.index', compact('payments', 'search', 'method'));
    }

    public function create(Request $request)
    {
        $bookingId = $request->input('booking_id');
        $booking = null;
        $service = null;
        $paidTotal = 0;
        $balance = 0;
        if ($bookingId) {
            $booking = Booking::where('booking_id', $bookingId)->first();
            if ($booking) {
                $service = Service::where('booking_id', $booking->booking_id)->with('payments')->first();
                if ($service) {
                    $paidTotal = $service->payments()->sum('amount');
                    $balance = ($service->total ?? 0) - $paidTotal;
                }
            }
        }
        $bookingsList = Booking::with(['service.payments'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('payments.create', compact('booking', 'service', 'paidTotal', 'balance', 'bookingsList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,booking_id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'contact_number' => ['required', 'string', 'max:60'],
            'method' => ['required', 'in:cash,bank_transfer,gcash,installment'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $service = Service::where('booking_id', $data['booking_id'])->first();
        if (!$service) {
            return back()->withErrors('No service found for this booking.');
        }
        if ($service->status !== Service::STATUS_COMPLETED) {
            return back()->withErrors('Payment can only be recorded after check-out.');
        }

        $paid = $service->payments()->sum('amount');
        $due = ($service->total ?? 0) - $paid;
        if ($due < 0)
            $due = 0;
        if ($data['amount'] > $due && $service->total !== null) {
            return back()->withErrors('Payment exceeds remaining balance.')->withInput();
        }

        $payment = Payment::create([
            'service_id' => $service->id,
            'booking_id' => $service->booking_id,
            'user_id' => auth()->id(),
            'customer_name' => $data['customer_name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'method' => $data['method'],
            'amount' => $data['amount'],
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'paid_at' => now(),
        ]);

        ActivityLog::record('payment.recorded', $service, 'Payment recorded', [
            'service_id' => $service->id,
            'booking_id' => $service->booking_id,
            'amount' => $payment->amount,
            'method' => $payment->method,
        ]);

        return redirect()->route('payments.index')->with('success', 'Payment added.');
    }

    public function receipt(Payment $payment)
    {
        $payment->load('service.items.item');
        return view('payments.receipt', compact('payment'));
    }
}