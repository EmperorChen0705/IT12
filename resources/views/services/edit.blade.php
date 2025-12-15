@extends('system')

@section('title','Edit Service - SubWfour ')

@section('head')
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
@endsection

@section('content')
<h2 class="text-accent">EDIT SERVICE</h2>

<div class="glass-card" style="max-width:1100px;margin:0 auto;">

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

    <form action="{{ route('services.update',$service) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label>Reference</label>
                <input class="form-input" value="{{ $service->reference_code }}" disabled>
            </div>
            <div class="form-group">
                <label>Booking</label>
                <input class="form-input" value="#{{ $service->booking_id }}" disabled>
            </div>
            <div class="form-group">
                <label>Status</label>
                <input class="form-input" value="{{ ucfirst(str_replace('_',' ',$service->status)) }}" disabled>
            </div>
            
            @if(auth()->user()->canAccessAdmin())
                <div class="form-group" style="flex: 1 0 100%; margin-top: 10px; padding: 15px; border: 1px solid var(--accent-color); border-radius: 12px; background: rgba(239, 53, 53, 0.05);">
                    <label style="color:var(--accent-color); font-weight: 600; margin-bottom: 10px; display: block;">Payment Status (Admin Only)</label>
                    
                    @if($service->status === \App\Models\Service::STATUS_COMPLETED)
                        <div style="display: flex; gap: 20px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="payment_status" value="None" 
                                    @checked($service->booking->payment_status === 'None')
                                    style="accent-color: var(--accent-color); transform: scale(1.2);">
                                <span style="color: #fff;">None</span>
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="payment_status" value="Partial" 
                                    @checked($service->booking->payment_status === 'Partial')
                                    style="accent-color: var(--accent-color); transform: scale(1.2);">
                                <span style="color: #fff;">Partial</span>
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="payment_status" value="Full" 
                                    @checked($service->booking->payment_status === 'Full')
                                    style="accent-color: var(--accent-color); transform: scale(1.2);">
                                <span style="color: #fff;">Full</span>
                            </label>
                        </div>
                    @else
                        <div style="display: flex; gap: 20px; opacity: 0.6;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: not-allowed;">
                                <input type="radio" checked disabled style="accent-color: var(--gray-500);">
                                <span style="color: var(--gray-500);">{{ $service->booking->payment_status ?? 'None' }}</span>
                            </label>
                            <span style="font-size: 0.8rem; color: var(--gray-500); align-self: center;">
                                (Editable only after Check-Out)
                            </span>
                        </div>
                    @endif
                </div>
            @endif

            <div class="form-group">
                <input name="labor_fee" type="number" step="0.01" min="0"
                       class="form-input"
                       value="{{ old('labor_fee',$service->labor_fee) }}"
                       @if($service->status==='completed') disabled @endif>
            </div>
            <div class="form-group">
                <label>Expected End</label>
                <input name="expected_end_date" type="datetime-local"
                       class="form-input"
                       value="{{ old('expected_end_date', $service->expected_end_date?->format('Y-m-d\TH:i')) }}"
                       @if($service->status==='completed') disabled @endif>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex:1 0 100%;">
                <label>Notes</label>
                <input name="notes" class="form-input"
                       value="{{ old('notes',$service->notes) }}"
                       @if($service->status==='completed') disabled @endif>
            </div>
        </div>

        <h3 style="font-size:.72rem;letter-spacing:1px;text-transform:uppercase;margin:18px 0 8px;">Items</h3>
        <div class="table-responsive">
            <table class="table compact" id="editLineItemsTable">
                <thead>
                <tr>
                    <th style="width:40%;">Item</th>
                    <th style="width:10%;" class="text-end">Qty</th>
                    <th style="width:15%;" class="text-end">Unit Price</th>
                    <th style="width:15%;" class="text-end">Line Total</th>
                    <th style="width:8%;"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($service->items as $it)
                    <tr class="li-row">
                        <td>
                            <select name="items[][item_id]" class="form-input item-select"
                                    required @if($service->status==='completed') disabled @endif>
                                <option value="">-- select --</option>
                                @foreach(\App\Models\Item::orderBy('name')
                                    ->get(['item_id','name','unit_price','quantity']) as $inv)
                                    <option value="{{ $inv->item_id }}"
                                        data-price="{{ $inv->unit_price ?? 0 }}"
                                        data-stock="{{ $inv->quantity }}"
                                        @selected($inv->item_id == $it->item_id)>
                                        {{ $inv->name }} (Stock: {{ $inv->quantity }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="items[][quantity]"
                                   class="form-input qty-input text-end"
                                   min="1" value="{{ $it->quantity }}"
                                   @if($service->status==='completed') disabled @endif></td>
                        <td><input type="number" name="items[][unit_price]"
                                   class="form-input price-input text-end"
                                   step="0.01" min="0" value="{{ $it->unit_price }}"
                                   @if($service->status==='completed') disabled @endif></td>
                        <td class="line-total-cell text-end">{{ number_format($it->line_total,2) }}</td>
                        <td>
                            @if($service->status!=='completed')
                                <button type="button" class="btn btn-delete btn-sm remove-line">
                                    <i class="bi bi-x"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="3" class="text-end" style="font-weight:600;">Subtotal</td>
                    <td class="text-end"><span id="subtotalDisplay">{{ number_format($service->subtotal,2) }}</span></td>
                    <td></td>
                </tr>
                </tfoot>
            </table>
        </div>

        @if($service->status!=='completed')
            <button type="button" class="btn btn-secondary btn-sm" id="addLineItem" style="margin-top:8px;">
                <i class="bi bi-plus-lg"></i> Add Item
            </button>
        @endif

        <div class="button-row" style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end;">
            <a href="{{ route('services.index') }}" class="btn-secondary">Back</a>
            @if($service->status!=='completed')
                <button type="submit" class="btn-primary">Update Service</button>
            @endif
        </div>
    </form>

    <div class="button-row" style="margin-top:10px;display:flex;gap:10px;justify-content:flex-end;">
        @if($service->status === \App\Models\Service::STATUS_PENDING)
            <form action="{{ route('services.status',$service) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="{{ \App\Models\Service::STATUS_IN_PROGRESS }}">
                <button type="submit" class="btn btn-secondary">Check-In</button>
            </form>
        @endif
        @if(in_array($service->status, [\App\Models\Service::STATUS_IN_PROGRESS, \App\Models\Service::STATUS_PENDING]))
            <form action="{{ route('services.status',$service) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="{{ \App\Models\Service::STATUS_COMPLETED }}">
                <button type="submit" class="btn btn-primary" @if($service->items->isEmpty()) disabled @endif>Check-Out</button>
            </form>
        @endif
    </div>
</div>

@if($service->status!=='completed')
<template id="lineItemTemplate">
    <tr class="li-row">
        <td>
            <select name="items[][item_id]" class="form-input item-select" required>
                <option value="">-- select --</option>
                @foreach(\App\Models\Item::orderBy('name')
                    ->get(['item_id','name','unit_price','quantity']) as $inv)
                    <option value="{{ $inv->item_id }}"
                        data-price="{{ $inv->unit_price ?? 0 }}"
                        data-stock="{{ $inv->quantity }}">
                        {{ $inv->name }} (Stock: {{ $inv->quantity }})
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[][quantity]" class="form-input qty-input text-end" min="1" value="1" required></td>
        <td><input type="number" name="items[][unit_price]" class="form-input price-input text-end" step="0.01" min="0"></td>
        <td class="line-total-cell text-end">0.00</td>
        <td><button type="button" class="btn btn-delete btn-sm remove-line"><i class="bi bi-x"></i></button></td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.querySelector('#editLineItemsTable tbody');
    const tmpl = document.getElementById('lineItemTemplate');
    const addBtn = document.getElementById('addLineItem');
    const subtotalDisplay = document.getElementById('subtotalDisplay');

    if (addBtn) addBtn.addEventListener('click', () => {
        const row = tmpl.content.firstElementChild.cloneNode(true);
        tbody.appendChild(row);
        bindRow(row);
        updateTotals();
    });

    tbody.querySelectorAll('.li-row').forEach(r => bindRow(r));

    function bindRow(row) {
        const sel = row.querySelector('.item-select');
        const qty = row.querySelector('.qty-input');
        const price = row.querySelector('.price-input');
        const remove = row.querySelector('.remove-line');

        if (sel) {
            sel.addEventListener('change', () => {
                if (!price.value) {
                    price.value = parseFloat(sel.selectedOptions[0].dataset.price || 0).toFixed(2);
                }
                const stock = parseInt(sel.selectedOptions[0].dataset.stock || '0', 10);
                qty.max = stock;
                if (parseInt(qty.value || '1', 10) > stock) qty.value = stock;
                updateTotals();
            });
        }
        [qty, price].forEach(inp => inp && inp.addEventListener('input', updateTotals));
        if (remove) {
            remove.addEventListener('click', () => {
                row.remove();
                updateTotals();
            });
        }
    }

    function updateTotals() {
        let subtotal = 0;
        tbody.querySelectorAll('.li-row').forEach(tr => {
            const q = parseFloat(tr.querySelector('.qty-input')?.value || 0);
            const p = parseFloat(tr.querySelector('.price-input')?.value || 0);
            const lt = q * p;
            const cell = tr.querySelector('.line-total-cell');
            if (cell) cell.textContent = lt.toFixed(2);
            subtotal += lt;
        });
        subtotalDisplay.textContent = subtotal.toFixed(2);
    }
});
</script>
@endif

@endsection