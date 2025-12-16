@extends('system')

@section('title', 'Bookings - SubWfour')

@section('head')
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
@endsection

@section('content')
    <h2 class="text-accent">BOOKINGS</h2>

    <div class="page-actions mb-3" style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:10px;">
        <button type="button" class="btn btn-primary btn-add-record"
            onclick="document.getElementById('createBookingModal').classList.remove('hidden');requestAnimationFrame(()=>document.getElementById('createBookingModal').classList.add('show'));">
            <i class="bi bi-plus-lg"></i> New Booking
        </button>
        <a href="{{ route('booking.portal') }}" class="btn btn-secondary" target="_blank" rel="noopener">
            <i class="bi bi-globe"></i> Booking Portal
        </a>
    </div>

    <div class="glass-card glass-card-wide">

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

        <div class="toolbar-top d-flex flex-wrap align-items-end gap-3 mb-3">
            <div class="search-bar-wrapper" style="flex:1 1 360px;">
                <form method="GET" action="{{ route('bookings.index') }}" class="search-bar" autocomplete="off">
                    <span class="search-icon"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ $search }}" class="search-input"
                        placeholder="Search booking ID, customer, email, service...">
                    @if($search)
                        <button type="button" class="search-clear"
                            onclick="window.location='{{ route('bookings.index', array_filter(['status' => $status])) }}'">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    @endif
                    <button class="btn btn-primary btn-search-main">Search</button>
                </form>
                <div class="search-meta">
                    @php $total = $bookings->total(); @endphp
                    <span class="result-count">
                        {{ $total }} {{ \Illuminate\Support\Str::plural('result', $total) }}
                        @if($search) for "<strong>{{ e($search) }}</strong>" @endif
                    </span>
                    @if($search || $status)
                        <span class="active-filter-chip"><i class="bi bi-funnel"></i> Filter active</span>
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ route('bookings.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                @if($search)
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif

                <div class="d-flex gap-1 align-items-center">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="form-control form-control-sm form-input" style="width:130px;" placeholder="Start">
                    <span style="color:#666;">-</span>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="form-control form-control-sm form-input" style="width:130px;" placeholder="End">
                </div>

                <select name="status" class="form-select form-input" style="min-width:140px;">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                    <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                    <option value="appointed" @selected($status === 'appointed')>Appointed</option>
                </select>
                <button class="btn btn-secondary" style="white-space:nowrap;">Filter</button>
                @if($status || $startDate || $endDate)
                    <a href="{{ route('bookings.index', array_filter(['search' => $search])) }}" class="btn btn-light">Clear</a>
                @endif

                <!-- Helper Quick Links -->
                <a href="{{ route('bookings.index', ['start_date' => \Carbon\Carbon::tomorrow()->toDateString(), 'status' => 'approved']) }}"
                    class="btn btn-sm btn-outline-primary" style="border:1px solid #444;">
                    Upcoming (Tomorrow+)
                </a>
            </form>
        </div>

        <div class="table-responsive" style="margin-top:4px;">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width:100px;">Date</th>
                        <th style="width:90px;">Time</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Service Type</th>
                        <th>Technician</th>
                        <th style="text-align:center;">Status</th>
                        <th style="width:180px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        @php
                            // Preload related service if available (avoid N+1 if controller eager loads)
                            $srv = $b->service ?? null;
                            $badgeColor = match ($b->status) {
                                'pending' => 'var(--yellow-500)',
                                'approved' => 'var(--yellow-500)',
                                'rejected' => 'var(--red-500)',
                                'completed' => 'var(--blue-500)',
                                'appointed' => 'var(--green-500)',
                                default => 'var(--gray-500)',
                            };

                            // Service status badge color
                            $serviceBadgeColor = $srv ? match ($srv->status) {
                                'pending' => 'var(--gray-500)',
                                'in_progress' => 'var(--blue-500)',
                                'completed' => 'var(--green-500)',
                                default => 'var(--gray-500)',
                            } : null;

                            // ... (Keep existing Receipt payload logic if needed, omitted here for brevity of replacement block, but we must ensure we don't break it. 
                            // The original code calculated $receiptPayload huge block. I should preserve it or regenerate it.
                            // To be safe, I will re-include the $receiptPayload logic to avoid breaking the "Receipt" button.)

                            $receiptPayload = [
                                'booking_id' => $b->booking_id,
                                'customer_name' => $b->customer_name,
                                'email' => $b->email,
                                'contact_number' => $b->contact_number,
                                'service_type' => $b->service_type,
                                'preferred_date' => $b->preferred_date,
                                'preferred_time' => $b->preferred_time,
                                'status' => $b->status,
                                'service' => $srv ? [
                                    'reference_code' => $srv->reference_code,
                                    'status' => $srv->status,
                                    'labor_fee' => $srv->labor_fee,
                                    'subtotal' => $srv->subtotal,
                                    'total' => $srv->total,
                                    'created_at' => optional($srv->created_at)->toDateTimeString(),
                                    'started_at' => optional($srv->started_at)->toDateTimeString(),
                                    'completed_at' => optional($srv->completed_at)->toDateTimeString(),
                                    'items' => $srv->items->map(fn($si) => [
                                        'name' => optional($si->item)->name,
                                        'quantity' => $si->quantity,
                                        'unit_price' => $si->unit_price,
                                        'line_total' => $si->line_total,
                                    ]),
                                ] : null
                            ];
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($b->preferred_date)->format('M d, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($b->preferred_time)->format('h:i A') }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $b->customer_name }}</div>
                            </td>
                            <td>{{ $b->vehicle_model ?? '—' }}</td>
                            <td>{{ $b->service_type }}</td>
                            <td>
                                @if($srv && $srv->technician)
                                    <span style="color:var(--text-accent);">{{ $srv->technician->first_name }}</span>
                                @else
                                    <span class="text-muted" style="font-size:0.8rem;">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span
                                    style="background:{{ $badgeColor }}22;color:{{ $badgeColor }};padding:2px 8px;border-radius:12px;font-size:.6rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">
                                    {{ $b->status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <!-- Actions Block... -->
                                <div class="d-flex gap-2 justify-content-end">
                                    @if($b->status === 'appointed')
                                        <button type="button" class="btn btn-receipt btn-sm" data-receipt='@json($receiptPayload)'
                                            title="View Receipt">
                                            <i class="bi bi-receipt-cutoff"></i>
                                        </button>
                                    @elseif($b->status === 'completed')
                                        <form method="POST" action="{{ route('bookings.appoint', $b->booking_id) }}">
                                            @csrf
                                            <button class="btn btn-appoint btn-sm" title="Appoint">
                                                <i class="bi bi-calendar-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(!$srv && in_array($b->status, ['pending', 'approved']))
                                        <a href="{{ route('services.index', ['booking_id' => $b->booking_id, 'action' => 'create']) }}"
                                            class="btn btn-primary btn-sm" title="Create Service">
                                            <i class="bi bi-tools"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-row text-center">No bookings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $bookings->appends([
        'search' => $search,
        'status' => $status,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ])->links() }}
        </div>
        <!-- Removed duplicate table responsive block to fix structure -->


            <!-- Receipt Modal -->
            <div class="modal hidden" id="bookingReceiptModal" data-modal>
                <div class="modal-content" style="max-width:800px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <h2 style="margin:0;font-size:1rem;">Booking Receipt</h2>
                    </div>
                    <div id="receiptBody" style="font-size:.72rem;line-height:1.4;max-height:70vh;overflow:auto;"></div>
                    <div style="text-align:right;margin-top:12px;">
                        <button type="button" class="btn-secondary" data-close>Close</button>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const modalId = 'bookingReceiptModal';
                    const bodyEl = document.getElementById('receiptBody');

                    function openModal() {
                        window._systemUI?.openModalById(modalId);
                    }

                    function formatMoney(v) {
                        if (v === null || v === undefined || v === '') return '0.00';
                        return parseFloat(v).toFixed(2);
                    }

                    document.addEventListener('click', e => {
                        const btn = e.target.closest('.btn-receipt');
                        if (!btn) return;
                        try {
                            const data = JSON.parse(btn.getAttribute('data-receipt'));
                            renderReceipt(data);
                            openModal();
                        } catch (err) {
                            console.error('Receipt parse error', err);
                            alert('Cannot open receipt.');
                        }
                    });

                    function renderReceipt(data) {
                        let html = '';
                        html += `<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
                                                    <div>
                                                        <div style="font-weight:600;font-size:.8rem;">Booking #${escapeHtml(data.booking_id ?? '')}</div>
                                                        <div>${escapeHtml(data.customer_name ?? '')}</div>
                                                        <div>${escapeHtml(data.email ?? '—')}</div>
                                                        <div>${escapeHtml(data.contact_number ?? '')}</div>
                                                    </div>
                                                    <div>
                                                        <div><strong>Status:</strong> ${escapeHtml(data.status ?? '')}</div>
                                                        <div><strong>Date:</strong> ${escapeHtml(data.preferred_date ?? '')}</div>
                                                        <div><strong>Time:</strong> ${escapeHtml(data.preferred_time ?? '')}</div>
                                                        <div><strong>Service Type:</strong> ${escapeHtml(data.service_type ?? '')}</div>
                                                    </div>
                                                </div>`;

                        if (data.service) {
                            html += `<hr style="border:0;border-top:1px solid var(--gray-700);margin:8px 0;">
                                                    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                                                        <div>
                                                            <div><strong>Service Ref:</strong> ${escapeHtml(data.service.reference_code ?? '')}</div>
                                                            <div><strong>Service Status:</strong> ${escapeHtml(data.service.status ?? '')}</div>
                                                            <div><strong>Started:</strong> ${escapeHtml(data.service.started_at ?? '—')}</div>
                                                            <div><strong>Completed:</strong> ${escapeHtml(data.service.completed_at ?? '—')}</div>
                                                        </div>
                                                        <div>
                                                            <div><strong>Labor Fee:</strong> ${formatMoney(data.service.labor_fee)}</div>
                                                            <div><strong>Subtotal:</strong> ${formatMoney(data.service.subtotal)}</div>
                                                            <div><strong>Total:</strong> ${formatMoney(data.service.total)}</div>
                                                        </div>
                                                    </div>`;

                            if (data.service.items && data.service.items.length) {
                                html += `<div style="margin-top:10px;">
                                                            <h3 style="margin:0 0 6px;font-size:.7rem;letter-spacing:1px;text-transform:uppercase;">Items</h3>
                                                            <table style="width:100%;border-collapse:collapse;font-size:.68rem;">
                                                                <thead>
                                                                    <tr style="text-align:left;background:var(--gray-800);">
                                                                        <th style="padding:6px;">Name</th>
                                                                        <th style="padding:6px;">Qty</th>
                                                                        <th style="padding:6px;text-align:right;">Unit</th>
                                                                        <th style="padding:6px;text-align:right;">Line Total</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>`;
                                data.service.items.forEach(it => {
                                    html += `<tr>
                                                                <td style="padding:4px 6px;">${escapeHtml(it.name ?? '')}</td>
                                                                <td style="padding:4px 6px;">${it.quantity}</td>
                                                                <td style="padding:4px 6px;text-align:right;">${formatMoney(it.unit_price)}</td>
                                                                <td style="padding:4px 6px;text-align:right;">${formatMoney(it.line_total)}</td>
                                                            </tr>`;
                                });
                                html += `</tbody></table></div>`;
                            }
                        } else {
                            html += `<div style="margin-top:10px;font-style:italic;color:var(--gray-500);">No service linked.</div>`;
                        }

                        bodyEl.innerHTML = html;
                    }

                    function escapeHtml(str) {
                        return ('' + (str ?? '')).replace(/[&<>"']/g, s => ({
                            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                        }[s]));
                    }
                });
            </script>
            <!-- Create Booking Modal (Admin) -->
            <div class="modal hidden" id="createBookingModal" data-modal>
                <div class="modal-content"
                    style="width: 100%; max-width:1000px; background-color:#222; border:1px solid #333; color:white; border-radius:12px; padding:30px;">
                    <h2
                        style="text-align:center; color:#ef4444; margin-bottom:20px; font-weight:500; font-size:1.5rem; letter-spacing:0.5px;">
                        Booking Request</h2>

                    <form action="{{ route('booking.portal.store') }}" method="POST" id="adminBookingForm">
                        @csrf
                        <input type="hidden" name="is_admin_booking" value="1">

                        <div style="display:flex; flex-wrap:wrap; gap:30px;">
                            <!-- LEFT COLUMN: Customer & Vehicle -->
                            <div style="flex:1; min-width:300px;">
                                <h4
                                    style="color:#ef4444; margin-bottom:15px; font-size:0.9rem; text-transform:uppercase; border-bottom:1px solid #444; padding-bottom:5px;">
                                    Customer & Vehicle</h4>

                                <!-- Customer Info -->
                                <div class="form-row" style="margin-bottom:15px;">
                                    <div class="form-group" style="flex:1;">
                                        <label
                                            style="color:#ef4444; font-size:0.75rem; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:8px;">Full
                                            Name *</label>
                                        <input name="customer_name" class="form-input" required
                                            style="background:white; color:black; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%;">
                                    </div>
                                </div>

                                <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
                                    <div class="form-group" style="flex:1;">
                                        <label
                                            style="color:#ef4444; font-size:0.75rem; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:8px;">Contact
                                            Number *</label>
                                        <input name="contact_number" class="form-input" required
                                            style="background:white; color:black; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%;">
                                    </div>
                                    <div class="form-group" style="flex:1;">
                                        <label
                                            style="color:#ef4444; font-size:0.75rem; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:8px;">Channel
                                            *</label>
                                        <div style="position:relative;">
                                            <select name="channel" class="form-input" required
                                                style="background:white; color:black; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%; -webkit-appearance:none; appearance:none;">
                                                <option value="walk-in">Walk-In</option>
                                                <option value="phone">Phone Call</option>
                                                <option value="facebook">Facebook / Messenger</option>
                                            </select>
                                            <span
                                                style="position:absolute; right:15px; top:50%; transform:translateY(-50%); color:black; pointer-events:none;"><i
                                                    class="bi bi-chevron-down"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle Info -->
                                <div class="form-row" style="margin-bottom:15px;">
                                    <label
                                        style="color:#fff; font-size:0.75rem; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:8px; display:block;">Vehicle
                                        Details (Optional)</label>
                                    <div style="display:flex; gap:10px;">
                                        <div class="form-group" style="flex:1;">
                                            <input name="vehicle_make" placeholder="Make" class="form-input"
                                                style="background:white; color:black; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%;">
                                        </div>
                                        <div class="form-group" style="flex:1;">
                                            <input name="vehicle_model" placeholder="Model" class="form-input"
                                                style="background:white; color:black; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%;">
                                        </div>
                                        <div class="form-group" style="flex:1;">
                                            <input name="plate_number" placeholder="Plate" class="form-input"
                                                style="background:white; color:black; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT COLUMN: Service & Schedule -->
                            <div style="flex:1; min-width:300px;">
                                <h4
                                    style="color:#ef4444; margin-bottom:15px; font-size:0.9rem; text-transform:uppercase; border-bottom:1px solid #444; padding-bottom:5px;">
                                    Service & Schedule</h4>

                                <div class="form-row" style="margin-bottom:15px;">
                                    <div class="form-group" style="flex:1;">
                                        <label
                                            style="color:#ef4444; font-size:0.75rem; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:8px;">Service
                                            Type *</label>
                                        <div style="position:relative;">
                                            <select name="service_type" class="form-input" required
                                                style="background:white; color:#ef4444; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%; -webkit-appearance:none; appearance:none;">
                                                <option value="" disabled selected>-- select service --</option>
                                                @foreach($serviceTypes ?? [] as $st)
                                                    <option value="{{ $st->name }}">{{ $st->name }}</option>
                                                @endforeach
                                            </select>
                                            <span
                                                style="position:absolute; right:15px; top:50%; transform:translateY(-50%); color:#ef4444; pointer-events:none;"><i
                                                    class="bi bi-chevron-down"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
                                    <div class="form-group" style="flex:1;">
                                        <label
                                            style="color:#ef4444; font-size:0.75rem; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:8px;">Preferred
                                            Date *</label>
                                        <div style="position:relative;">
                                            <input name="preferred_date" type="date" class="form-input" required
                                                min="{{ date('Y-m-d') }}"
                                                style="background:white; color:#ef4444; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%;">
                                        </div>
                                    </div>
                                    <div class="form-group" style="flex:1;">
                                        <label
                                            style="color:#ef4444; font-size:0.75rem; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:8px;">Preferred
                                            Time *</label>
                                        <div style="position:relative;">
                                            <select name="preferred_time" class="form-input" required
                                                style="background:white; color:#ef4444; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%; -webkit-appearance:none; appearance:none;">
                                                <option value="" disabled selected>-- : -- --</option>
                                                <option value="08:00">08:00 AM</option>
                                                <option value="09:00">09:00 AM</option>
                                                <option value="10:00">10:00 AM</option>
                                                <option value="11:00">11:00 AM</option>
                                                <option value="13:00">01:00 PM</option>
                                                <option value="14:00">02:00 PM</option>
                                                <option value="15:00">03:00 PM</option>
                                                <option value="16:00">04:00 PM</option>
                                            </select>
                                            <span
                                                style="position:absolute; right:15px; top:50%; transform:translateY(-50%); color:black; pointer-events:none;"><i
                                                    class="bi bi-clock"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row" style="margin-bottom:0;">
                                    <div class="form-group" style="flex:1;">
                                        <label
                                            style="color:#ef4444; font-size:0.75rem; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:8px;">Additional
                                            Notes</label>
                                        <textarea name="notes" class="form-input" rows="3"
                                            style="background:white; color:black; border:none; border-radius:8px; padding:10px; font-size:0.9rem; width:100%; resize:vertical;"></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="button-row"
                            style="display:flex; gap:10px; justify-content:flex-end; margin-top:25px; border-top:1px solid #333; padding-top:20px;">
                            <button type="button" data-close
                                style="background:#444; color:white; border:none; border-radius:6px; padding:10px 24px; font-weight:600; cursor:pointer;">
                                Cancel
                            </button>
                            <button type="submit" id="submitBookingBtn"
                                style="background:#dc2626; color:white; border:none; border-radius:6px; padding:10px 24px; font-weight:600; cursor:pointer;">
                                Submit Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                // Prevent double submission on admin booking form
                (function () {
                    const form = document.getElementById('adminBookingForm');
                    const submitBtn = document.getElementById('submitBookingBtn');
                    let isSubmitting = false;

                    if (form && submitBtn) {
                        form.addEventListener('submit', function (e) {
                            if (isSubmitting) {
                                e.preventDefault();
                                return;
                            }

                            isSubmitting = true;
                            submitBtn.disabled = true;
                            submitBtn.textContent = 'Submitting...';
                        });
                    }
                })();
            </script>

@endsection