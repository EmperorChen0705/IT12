@extends('system')

@section('title', 'Item History - ' . $item->name)

@section('head')
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h2 class="text-accent" style="margin:0;">ITEM HISTORY: <span style="color:var(--white);">{{ $item->name }}</span></h2>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Inventory
        </a>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th class="text-end">Quantity</th>
                        <th>User / Removed By</th>
                        <th>Reference / Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log['date']->format('M d, Y h:i A') }}</td>
                            <td>
                                @if($log['type'] === 'in')
                                    <span class="badge" style="background:var(--green-500);color:white;padding:2px 8px;border-radius:4px;">STOCK IN</span>
                                @else
                                    <span class="badge" style="background:var(--red-500);color:white;padding:2px 8px;border-radius:4px;">STOCK OUT</span>
                                @endif
                            </td>
                            <td class="text-end" style="color: {{ $log['type'] === 'in' ? 'var(--green-400)' : 'var(--red-400)' }}; font-weight:bold;">
                                {{ $log['qty'] > 0 ? '+' : '' }}{{ $log['qty'] }}
                            </td>
                            <td>{{ $log['user'] }}</td>
                            <td>{{ $log['ref'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-row text-center">No history found for this item.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
