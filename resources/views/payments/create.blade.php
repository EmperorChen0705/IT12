@extends('system')

@section('title', 'New Payment - TITLE')

@section('head')
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
@endsection

@section('content')
    <h2 class="text-accent">NEW PAYMENT</h2>

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="m-0 ps-3" style="font-size:.7rem;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="glass-card" style="max-width:1100px;margin:0 auto;">
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <div class="form-row" style="margin-bottom:8px;">
                <div class="form-group" style="flex:1 0 100%;">
                    <label>Select Customer (from recent bookings)</label>
                    <select id="customerSelect" class="form-input">
                        <option value="">-- choose customer --</option>
                        @foreach(($bookingsList ?? []) as $bk)
                            @php
                                $svc = $bk->service;
                                $paid = $svc?->payments?->sum('amount') ?? 0;
                                $tot = $svc->total ?? 0;
                                $bal = $tot - $paid;
                                $status = $svc->status ?? null;
                            @endphp
                            <option value="{{ $bk->booking_id }}" data-booking-id="{{ $bk->booking_id }}"
                                data-name="{{ $bk->customer_name }}" data-email="{{ $bk->email }}"
                                data-contact="{{ $bk->contact_number }}" data-service-status="{{ $status }}"
                                data-service-total="{{ $tot }}" data-service-paid="{{ $paid }}"
                                data-service-balance="{{ $bal }}">
                                {{ $bk->customer_name }} — #{{ $bk->booking_id }}
                                @if($status) ({{ ucfirst(str_replace('_', ' ', $status)) }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group" style="flex:0 0 220px;">
                    <label>Booking</label>
                    <input name="booking_id" class="form-input" value="{{ old('booking_id', $booking->booking_id ?? '') }}"
                        required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Service Ref</label>
                    <input class="form-input" value="{{ $service->reference_code ?? '—' }}" disabled>
                </div>
                <div class="form-group" style="flex:0 0 180px;">
                    <label>Status</label>
                    <input class="form-input" value="{{ $service ? ucfirst(str_replace('_', ' ', $service->status)) : '—' }}"
                        disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:1;">
                    <label>Customer Name</label>
                    <input name="customer_name" class="form-input"
                        value="{{ old('customer_name', $booking->customer_name ?? '') }}" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Email</label>
                    <input name="email" type="email" class="form-input" value="{{ old('email', $booking->email ?? '') }}"
                        required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Contact Number</label>
                    <input name="contact_number" class="form-input"
                        value="{{ old('contact_number', $booking->contact_number ?? '') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:0 0 220px;">
                    <label>Method</label>
                    <select name="method" class="form-input" required>
                        @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'gcash' => 'Gcash', 'installment' => 'Installment'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('method') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex:0 0 220px;">
                    <label>Amount</label>
                    <input name="amount" type="number" step="0.01" min="0.01" class="form-input" value="{{ old('amount') }}"
                        required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Reference</label>
                    <input name="reference" class="form-input" value="{{ old('reference') }}" placeholder="Txn/Ref #">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:1;">
                    <label>Notes</label>
                    <input name="notes" class="form-input" value="{{ old('notes') }}">
                </div>
            </div>

            <div class="table-responsive" style="margin-top:10px;">
                <table class="table compact">
                    <tbody>
                        <tr>
                            <td style="width:200px;font-weight:600;">Service Total</td>
                            <td class="text-end">₱{{ number_format($service->total ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">Paid</td>
                            <td class="text-end">₱{{ number_format($paidTotal ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">Balance</td>
                            <td class="text-end">₱{{ number_format(max($balance ?? 0, 0), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="button-row" style="margin-top:14px;display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('payments.index') }}" class="btn-secondary">Back</a>
                <button type="submit" class="btn-primary" id="savePaymentBtn">Save Payment</button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sel = document.getElementById('customerSelect');
            if (!sel) return;

            const bookingIdIn = document.querySelector('input[name="booking_id"]');
            const nameIn = document.querySelector('input[name="customer_name"]');
            const emailIn = document.querySelector('input[name="email"]');
            const contactIn = document.querySelector('input[name="contact_number"]');
            const saveBtn = document.getElementById('savePaymentBtn');

            const totalCell = document.querySelector('table.table tbody tr:nth-child(1) td.text-end');
            const paidCell = document.querySelector('table.table tbody tr:nth-child(2) td.text-end');
            const balanceCell = document.querySelector('table.table tbody tr:nth-child(3) td.text-end');

            function updateTotals(tot, paid, bal) {
                if (totalCell) totalCell.textContent = `₱${Number(tot || 0).toFixed(2)}`;
                if (paidCell) paidCell.textContent = `₱${Number(paid || 0).toFixed(2)}`;
                if (balanceCell) balanceCell.textContent = `₱${Number(Math.max(bal || 0, 0)).toFixed(2)}`;
            }

            function updateSaveDisabled(status) {
                if (!saveBtn) return;
                saveBtn.disabled = (status && status.toLowerCase() !== 'completed');
            }

            sel.addEventListener('change', () => {
                const opt = sel.selectedOptions[0];
                if (!opt) return;
                const bk = opt.dataset.bookingId || '';
                const nm = opt.dataset.name || '';
                const em = opt.dataset.email || '';
                const cn = opt.dataset.contact || '';
                const st = opt.dataset.serviceStatus || '';
                const tot = parseFloat(opt.dataset.serviceTotal || '0');
                const pd = parseFloat(opt.dataset.servicePaid || '0');
                const bl = parseFloat(opt.dataset.serviceBalance || '0');

                if (bookingIdIn) bookingIdIn.value = bk;
                if (nameIn) nameIn.value = nm;
                if (emailIn) emailIn.value = em;
                if (contactIn) contactIn.value = cn;

                updateTotals(tot, pd, bl);
                updateSaveDisabled(st);
            });
        });
    </script>
@endsection