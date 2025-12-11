@extends('system')

@section('title', 'Payments - SubWfour')

@section('head')
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
@endsection

@section('content')
    <h2 class="text-accent">PAYMENTS</h2>

    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="m-0 ps-3" style="font-size:.7rem;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="page-actions" style="display:flex;gap:10px;margin-bottom:10px;">
        <a href="{{ route('payments.create') }}" class="btn btn-primary"
            style="flex:1;display:flex;justify-content:center;align-items:center;">
            <i class="bi bi-plus-lg"></i> New Payment
        </a>
    </div>

    <div class="glass-card glass-card-wide">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Paid At</th>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th class="text-end">Amount</th>
                        <th>Receipt</th>
                        <th>Entered By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr>
                            <td>{{ $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : $p->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $p->booking_id }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $p->customer_name }}</div>
                                <div style="font-size:.6rem;color:var(--gray-500);">{{ $p->email }}</div>
                                <div style="font-size:.6rem;color:var(--gray-500);">{{ $p->contact_number }}</div>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $p->method)) }}</td>
                            <td class="text-end">₱{{ number_format($p->amount, 2) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ $p->reference ?? '' }}</span>
                                    <a href="{{ route('payments.receipt', $p->id) }}" class="btn btn-sm btn-light"
                                        title="Print Receipt" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                            <td>{{ $p->user?->name ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-row text-center">No payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="mt-2">{{ $payments->links() }}</div>
        @endif
    </div>
@endsection