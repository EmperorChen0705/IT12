@extends('system')

@section('title', 'Dashboard - SubWfour')

@section('head')
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
    <script src="{{ asset('js/dashboard.js') }}" defer></script>
@endsection

@section('content')
    <h2 class="text-accent">ADMIN DASHBOARD</h2>

    {{-- Elevation Status Banner for Elevated Managers --}}
    @if(\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_manager') && auth()->user()->isElevated())
        <div class="alert alert-warning mb-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-shield-fill-exclamation fs-3 me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="mb-1">
                        <i class="bi bi-shield-check"></i> Elevated Access Active
                    </h5>
                    <p class="mb-0">
                        You have temporary admin access until
                        <strong class="countdown" data-expires="{{ auth()->user()->elevated_until->toIso8601String() }}">
                            {{ auth()->user()->elevated_until->format('M d, Y h:i A') }}
                        </strong>
                    </p>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Access will automatically expire - save your work frequently
                    </small>
                </div>
            </div>
        </div>
    @endif

    <div class="dashboard-grid" id="dashboardRoot" data-daily-bookings='@json($dailyBookings)'
        data-monthly-services='@json($monthlyServices)'>

        <!-- ROW 1: New Metrics (2x2 Grid) -->
        <div class="dash-metrics" style="grid-template-columns: repeat(2, 1fr); gap: 20px;">
            <!-- Row 1, Col 1 -->
            <div class="dm-card">
                <div class="dm-label">Total Bookings</div>
                <div class="dm-value">{{ number_format($totalBookings) }}</div>
                <div class="dm-sub"><span class="dot dot-blue"></span>All time recordings</div>
            </div>

            <!-- Row 1, Col 2 -->
            <div class="dm-card">
                <div class="dm-label">Pending Bookings</div>
                <div class="dm-value">{{ number_format($pendingBookingsCount) }}</div>
                <div class="dm-sub"><span class="dot dot-amber"></span>Awaiting action</div>
            </div>

            <!-- Row 2, Col 1 -->
            <div class="dm-card">
                <div class="dm-label">Active Services (Load)</div>
                <div class="dm-value">{{ $activeServicesCount }} / 10</div>
                <div class="dm-sub">
                    <span class="dot {{ $activeServicesCount >= 10 ? 'dot-red' : 'dot-green' }}"></span>
                    Shop Capacity
                </div>
            </div>

            <!-- Row 2, Col 2 -->
            <div class="dm-card wide">
                <div class="dm-label">Low Stock Alerts</div>
                <div class="dm-value">{{ $lowStockItems->count() }}</div>
                <div class="dm-sub">
                    <span class="dot {{ $lowStockItems->count() > 0 ? 'dot-red' : 'dot-green' }}"></span>
                    Items below threshold
                </div>
            </div>
        </div>

        <!-- ROW 2: Charts (Preserved) -->
        <div class="charts-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="panel panel-chart">
                <div class="panel-head">
                    <h3>Daily Bookings (7 Days)</h3>
                    <div class="panel-actions">
                        <button class="btn btn-small-black" data-reload-bookings>Reload</button>
                    </div>
                </div>
                <canvas id="dailyBookingsChart" height="140"></canvas>
            </div>

            <div class="panel panel-chart">
                <div class="panel-head">
                    <h3>Monthly Services (6 Months)</h3>
                    <div class="panel-actions">
                        <button class="btn btn-small-black" data-reload-services>Reload</button>
                    </div>
                </div>
                <canvas id="monthlyServicesChart" height="140"></canvas>
            </div>
        </div>

        <!-- ROW 3: Operating Lists -->
        <div class="stats-row" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">

            <!-- LIST 1: Today's Schedule -->
            <div class="panel panel-list">
                <div class="panel-head">
                    <h3>Today's Schedule</h3>
                </div>
                <div class="list-body">
                    @forelse($todaysSchedule as $bk)
                        <div class="list-row">
                            <span class="lr-id">{{ \Carbon\Carbon::parse($bk->preferred_time)->format('h:i A') }}</span>
                            <div class="lr-details" style="flex:1; margin-left:10px;">
                                <div style="font-weight:600;">{{ $bk->customer_name }}</div>
                                <div style="font-size:0.8rem; opacity:0.7;">
                                    {{ $bk->service_type }}
                                    @if($bk->service && $bk->service->technician)
                                        <span style="color:var(--text-accent); margin-left:6px;">
                                            <i class="bi bi-tools"></i> {{ $bk->service->technician->first_name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <span class="lr-status badge-{{ $bk->status }}">{{ ucfirst($bk->status) }}</span>
                        </div>
                    @empty
                        <div class="empty-alt">No bookings for today.</div>
                    @endforelse
                </div>
            </div>

            <!-- LIST 2: Ongoing Services -->
            <div class="panel panel-list">
                <div class="panel-head">
                    <h3>Ongoing Services</h3>
                </div>
                <div class="list-body">
                    @forelse($ongoingServices as $svc)
                        <div class="list-row">
                            <span class="lr-id">#{{ $svc->booking_id }}</span>
                            <div class="lr-details" style="flex:1; margin-left:10px;">
                                <div style="font-weight:600;">{{ $svc->booking->customer_name ?? 'Unknown' }}</div>
                                <div style="font-size:0.8rem; opacity:0.7;">
                                    @if($svc->technician)
                                        <span style="color:var(--text-accent); margin-right:6px;">
                                            <i class="bi bi-tools"></i> {{ $svc->technician->first_name }}
                                        </span>
                                    @endif
                                    Started {{ $svc->started_at ? $svc->started_at->diffForHumans() : 'Recently' }}
                                </div>
                            </div>
                            <span class="lr-val">{{ $svc->status }}</span>
                        </div>
                    @empty
                        <div class="empty-alt">No active services.</div>
                    @endforelse
                </div>
            </div>

            <!-- LIST 3: Low Stock Alerts -->
            <div class="panel panel-list">
                <div class="panel-head">
                    <h3>Low Stock Alerts</h3>
                </div>
                <div class="list-body">
                    @forelse($lowStockItems as $item)
                        <div class="list-row">
                            <span class="lr-id">#{{ $item->item_id }}</span>
                            <div class="lr-details" style="flex:1; margin-left:10px;">
                                <div style="font-weight:600;">{{ $item->name }}</div>
                                <div style="font-size:0.8rem; color: var(--red-400);">Qty: {{ $item->quantity }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-alt">Inventory healthy.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ROW 4: Quick Actions (Preserved) -->
        <div class="dash-bottom">
            <div class="panel">
                <div class="panel-head">
                    <h3>Quick Actions</h3>
                </div>
                <div class="quick-actions-grid">
                    <a href="{{ route('bookings.index') }}" class="qa-btn">
                        <i class="bi bi-person-lines-fill"></i>Bookings
                    </a>
                    <a href="{{ route('services.index') }}" class="qa-btn">
                        <i class="bi bi-wrench"></i>Services
                    </a>
                    <a href="{{ route('inventory.index') }}" class="qa-btn">
                        <i class="bi bi-inboxes-fill"></i>Inventory
                    </a>
                    <a href="{{ route('stock_in.index') }}" class="qa-btn">
                        <i class="bi bi-dropbox"></i>Stock-In
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="qa-btn">
                        <i class="bi bi-person-fill-down"></i>Suppliers
                    </a>
                    <a href="{{ route('reports.index') }}" class="qa-btn">
                        <i class="bi bi-list-columns"></i>Reports
                    </a>
                    <a href="{{ route('stock_out.index') }}" class="qa-btn">
                        <i class="bi bi-box-arrow-up"></i>Stock-Out
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Repsonsiveness Style -->
        <style>
            @media (max-width: 900px) {
                .dash-metrics {
                    grid-template-columns: 1fr !important;
                }

                .charts-row,
                .stats-row {
                    grid-template-columns: 1fr !important;
                }
            }

            .badge-pending {
                color: var(--amber-400);
                font-size: 0.8rem;
            }

            .badge-approved {
                color: var(--blue-400);
                font-size: 0.8rem;
            }

            .badge-completed {
                color: var(--green-400);
                font-size: 0.8rem;
            }
        </style>
    </div>

    <script>
        // Countdown timer for elevation expiration
        document.addEventListener('DOMContentLoaded', function () {
            function updateCountdowns() {
                document.querySelectorAll('.countdown').forEach(el => {
                    const expiresAt = new Date(el.dataset.expires);
                    const now = new Date();
                    const diff = expiresAt - now;

                    if (diff <= 0) {
                        el.textContent = 'Expired';
                        location.reload();
                    } else {
                        const hours = Math.floor(diff / 3600000);
                        const minutes = Math.floor((diff % 3600000) / 60000);
                        el.textContent = `${hours}h ${minutes}m remaining`;
                    }
                });
            }

            if (document.querySelectorAll('.countdown').length > 0) {
                updateCountdowns();
                setInterval(updateCountdowns, 60000); // Update every minute
            }
        });
    </script>
@endsection