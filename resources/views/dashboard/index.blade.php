@extends('system')

@section('title', 'Dashboard - SubWfour')

@section('head')
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
    <script src="{{ asset('js/dashboard.js') }}" defer></script>
@endsection

@section('content')
    @php
        $topServiceNameMonth = isset($topServices) && count($topServices) ? $topServices[0]->name : null;
        $topServiceCountMonth = isset($topServices) && count($topServices) ? $topServices[0]->count : null;

        $profitMarginPercent = isset($profitMarginMonth) ? $profitMarginMonth * 100 : 0;

        $revenueMonthSafe = $revenueMonth ?? ($totalRevenueMonth ?? 0);
        $profitMonthSafe = $profitMonth ?? 0;
        $cogsMonthSafe = $cogsMonth ?? 0;

        $customersCurrentMonth = $customersCurrentMonth ?? 0;
        $customersPrevMonth = $customersPrevMonth ?? 0;
        $customerGrowthRate = $customerGrowthRate ?? null; 
    @endphp

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

    {{-- Quick Toggle Widget for Admin (Owner) --}}
    @if(auth()->user()->role === 'admin')
        @include('managers.quick_toggle')
    @endif

    <div class="dashboard-grid" id="dashboardRoot" data-daily-bookings='@json($dailyBookings)'
        data-monthly-services='@json($monthlyServices)' data-top-sales-items='@json($topSalesItems ?? [])'
        data-top-service-types='@json($topServices ?? [])'>
        <!-- Metrics Row -->
        <div class="dash-metrics">
            <div class="dm-card">
                <div class="dm-label">Total Sales (Month)</div>
                <div class="dm-value">₱{{ number_format($revenueMonthSafe, 2) }}</div>
                <div class="dm-sub"><span class="dot dot-green"></span>Completed Services</div>
            </div>

            <div class="dm-card">
                <div class="dm-label">Profit (Month)</div>
                <div class="dm-value">₱{{ number_format($profitMonthSafe, 2) }}</div>
                <div class="dm-sub">
                    <span class="dot dot-purple"></span>
                    Margin: {{ $revenueMonthSafe > 0 ? number_format($profitMarginPercent, 1) . '%' : '—' }}
                </div>
            </div>

            <div class="dm-card">
                <div class="dm-label">COS (Month)</div>
                <div class="dm-value">₱{{ number_format($cogsMonthSafe, 2) }}</div>
                <div class="dm-sub">
                    <span class="dot dot-amber"></span>
                    Cost of Items Used
                </div>
            </div>

            <div class="dm-card">
                <div class="dm-label">Top Service (Month)</div>
                <div class="dm-value" style="font-size:1.0rem;">
                    {{ $topServiceNameMonth ?? '—' }}
                </div>
                <div class="dm-sub">
                    <span class="dot dot-cyan"></span>
                    {{ $topServiceCountMonth ? $topServiceCountMonth . ' jobs' : 'No data' }}
                </div>
            </div>

            <div class="dm-card">
                <div class="dm-label">Customer Growth</div>
                <div class="dm-value">{{ $customersCurrentMonth }}</div>
                <div class="dm-sub">
                    <span class="dot dot-amber"></span>
                    Prev: {{ $customersPrevMonth }}
                    |
                    @if(!is_null($customerGrowthRate))
                        {{ ($customerGrowthRate >= 0 ? '+' : '') . number_format($customerGrowthRate, 1) }}%
                    @else
                        —
                    @endif
                </div>
            </div>

            <div class="dm-card">
                <div class="dm-label">Low Stock (&lt;5)</div>
                <div class="dm-value">{{ $lowStockCount ?? 0 }}</div>
                <div class="dm-sub">
                    <span class="dot dot-red"></span>
                    {{ ($lowStockCount ?? 0) > 0 ? ($lowStockCount . ' items need reorder') : 'All good' }}
                </div>
            </div>

            <div class="dm-card wide">
                <div class="dm-label">Inventory Value (Est.)</div>
                <div class="dm-value">₱{{ number_format($inventoryValue ?? 0, 2) }}</div>
                <div class="dm-sub"><span class="dot dot-silver"></span>Total (qty * price)</div>
            </div>

            <div class="dm-card wide">
                <div class="dm-label">Shop Load (Capacity)</div>
                <div class="dm-value">{{ $currentLoad ?? 0 }}/10</div>
                <div class="dm-sub">
                    <span class="dot {{ ($currentLoad ?? 0) >= 10 ? 'dot-red' : 'dot-blue' }}"></span>
                    Active Services (Max 10)
                </div>
            </div>
        </div>

        <!-- Charts and Side Panels -->
        <div class="dash-main-grid">
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

            <!-- Upcoming Schedule -->
            <div class="panel panel-list">
                <div class="panel-head">
                    <h3>Upcoming Schedule</h3>
                </div>
                <div class="list-body">
                    @forelse($upcomingBookings ?? [] as $ub)
                        <div class="list-row">
                            <span class="lr-id" style="width:50px;">{{ \Carbon\Carbon::parse($ub->preferred_date)->format('M d') }}</span>
                            <div class="lr-details" style="flex:1; margin-left:10px; display:flex; flex-direction:column; justify-content:center;">
                                <div style="font-weight:600; font-size:0.85rem; color:var(--white);">{{ $ub->customer_name }}</div>
                                <div style="font-size:0.75rem; opacity:0.7;">{{ $ub->service_type }} @ {{ $ub->preferred_time }}</div>
                            </div>
                            <span class="lr-val" style="font-size:0.7rem;">{{ strtoupper($ub->channel ?? 'web') }}</span>
                        </div>
                    @empty
                        <div class="empty-alt">No upcoming bookings.</div>
                    @endforelse
                </div>
            </div>

            <div class="panel panel-list">
                <div class="panel-head">
                    <h3>Top Items Used (Qty)</h3>
                </div>
                <div class="list-body">
                    @forelse($topItems as $ti)
                        <div class="list-row">
                            <span class="lr-id">#{{ $ti->item_id }}</span>
                            <div class="lr-bar">
                                @php
                                    $max = max($topItems->pluck('uses')->toArray() ?: [1]);
                                    $pct = $max ? ($ti->uses / $max) * 100 : 0;
                                @endphp
                                <span class="bar">
                                    <span class="fill" style="width:{{ $pct }}%;"></span>
                                </span>
                            </div>
                            <span class="lr-val">{{ $ti->uses }}</span>
                        </div>
                    @empty
                        <div class="empty-alt">No usage data yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="panel panel-list">
                <div class="panel-head">
                    <h3>Top Sales Items (Revenue)</h3>
                </div>
                <div class="list-body">
                    @php
                        $topSalesItems = $topSalesItems ?? collect();
                        $maxRev = $topSalesItems->count() ? $topSalesItems->max('revenue') : 1;
                    @endphp
                    @forelse($topSalesItems as $row)
                        <div class="list-row">
                            <span class="lr-id">#{{ $row->item_id }}</span>
                            <div class="lr-bar">
                                @php
                                    $pct = $maxRev ? ($row->revenue / $maxRev) * 100 : 0;
                                @endphp
                                <span class="bar">
                                    <span class="fill fill-gold" style="width:{{ $pct }}%;"></span>
                                </span>
                            </div>
                            <span class="lr-val">₱{{ number_format($row->revenue, 2) }}</span>
                        </div>
                    @empty
                        <div class="empty-alt">No sales yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="panel panel-list">
                <div class="panel-head">
                    <h3>Top Service Types</h3>
                </div>
                <div class="list-body">
                    @php
                        $topServices = $topServices ?? collect();
                        $maxSvc = $topServices->count() ? $topServices->max('count') : 1;
                    @endphp
                    @forelse($topServices as $svc)
                        @php
                            $pct = $maxSvc ? ($svc->count / $maxSvc) * 100 : 0;
                        @endphp
                        <div class="list-row">
                            <span class="lr-id">{{ $svc->name }}</span>
                            <div class="lr-bar">
                                <span class="bar">
                                    <span class="fill fill-cyan" style="width:{{ $pct }}%;"></span>
                                </span>
                            </div>
                            <span class="lr-val">{{ $svc->count }}</span>
                        </div>
                    @empty
                        <div class="empty-alt">No service data.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Bottom Panels -->
        <div class="dash-bottom">
            <div class="panel">
                <div class="panel-head">
                    <h3>Quick Actions</h3>
                </div>
                <div class="quick-actions-grid">

                    <a href="{{ route('bookings.index') }}" class="qa-btn">
                        <i class="bi bi-person-lines-fill"></i>
                        Bookings
                    </a>
                    <a href="{{ route('services.index') }}" class="qa-btn">
                        <i class="bi bi-wrench"></i>
                        Services
                    </a>
                    <a href="{{ route('inventory.index') }}" class="qa-btn">
                        <i class="bi bi-inboxes-fill"></i>
                        Inventory
                    </a>
                    <a href="{{ route('stock_in.index') }}" class="qa-btn">
                        <i class="bi bi-dropbox"></i>
                        Stock-In
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="qa-btn">
                        <i class="bi bi-person-fill-down"></i>
                        Suppliers
                    </a>
                    <a href="{{ route('reports.index') }}" class="qa-btn">
                        <i class="bi bi-list-columns"></i>
                        Reports
                    </a>
                    <a href="{{ route('stock_out.index') }}" class="qa-btn">
                        <i class="bi bi-box-arrow-up"></i>
                        Stock-Out
                    </a>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h3>System Notes</h3>
                </div>
                <div class="notes">
                    <p>Metrics use completed services and stock movement to estimate revenue, cost, and margin.</p>
                    <ul class="note-list">
                        <li>Sales = sum of completed service totals (month).</li>
                        <li>COS = qty used * avg stock-in cost (fallback unit price).</li>
                        <li>Profit Margin recalculates per page load.</li>
                        <li>Growth compares bookings to prior month.</li>
                    </ul>
                </div>
            </div>
        </div>
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