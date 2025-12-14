<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (!auth()->user()->canAccessAdmin()) {
            return redirect()->route('bookings.index');
        }

        // Metrics
        $totalBookings = Booking::count();
        $pendingBookingsCount = Booking::where('status', 'pending')->count();
        $activeServicesCount = Service::whereIn('status', [Service::STATUS_IN_PROGRESS ?? 'in_progress'])->count();

        // Lists
        $todaysSchedule = Booking::where('status', '!=', 'rejected')
            ->whereDate('preferred_date', today())
            ->orderBy('preferred_time')
            ->limit(10)
            ->get();

        $ongoingServices = Service::with(['booking', 'items'])
            ->whereIn('status', [Service::STATUS_IN_PROGRESS ?? 'in_progress'])
            ->orderBy('started_at')
            ->limit(10)
            ->get();

        $lowStockItems = Item::where('quantity', '<', 5)
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        // Charts Data (Keep existing logic)
        $dailyBookings = Booking::select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as c'))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->map(fn($r) => ['date' => $r->d, 'count' => $r->c]);

        $monthlyServices = Service::select(
            DB::raw("DATE_FORMAT(created_at,'%Y-%m') as m"),
            DB::raw('COUNT(*) as c')
        )
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('m')
            ->orderBy('m')
            ->get()
            ->map(fn($r) => ['month' => $r->m, 'count' => $r->c]);

        return view('dashboard.index', compact(
            'totalBookings',
            'pendingBookingsCount',
            'activeServicesCount',
            'todaysSchedule',
            'ongoingServices',
            'lowStockItems',
            'dailyBookings',
            'monthlyServices'
        ));
    }
}