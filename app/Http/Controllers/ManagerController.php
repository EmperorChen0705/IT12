<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Only admins can manage manager elevations.');
            }
            return $next($request);
        });
    }

    /**
     * Display list of managers and their elevation status
     */
    public function index()
    {
        $managers = User::where('is_manager', true)
            ->with(['employee', 'elevatedBy'])
            ->orderBy('name')
            ->get();

        $employees = User::where('role', 'employee')
            ->where('is_manager', false)
            ->with('employee')
            ->orderBy('name')
            ->get();

        return view('managers.index', compact('managers', 'employees'));
    }

    /**
     * Designate an employee as a manager
     */
    public function designate(User $user)
    {
        if ($user->role !== 'employee') {
            return back()->with('error', 'Only employees can be designated as managers.');
        }

        $user->update(['is_manager' => true]);

        ActivityLog::record(
            'manager.designated',
            $user,
            "Employee {$user->name} designated as manager",
            ['manager_id' => $user->id]
        );

        return back()->with('success', "{$user->name} has been designated as a manager.");
    }

    /**
     * Remove manager designation
     */
    public function undesignate(User $user)
    {
        // Revoke any active elevation first
        if ($user->isElevated()) {
            $user->revokeElevation();
        }

        $user->update(['is_manager' => false]);

        ActivityLog::record(
            'manager.undesignated',
            $user,
            "Manager designation removed from {$user->name}",
            ['manager_id' => $user->id]
        );

        return back()->with('success', "Manager designation removed from {$user->name}.");
    }

    /**
     * Grant elevation with custom duration
     */
    public function grantElevation(Request $request, User $user)
    {
        $validated = $request->validate([
            'hours' => 'required|integer|min:1|max:24',
        ]);

        if (!$user->is_manager) {
            return back()->with('error', 'User must be designated as a manager first.');
        }

        $user->grantElevation($validated['hours'], auth()->id());

        ActivityLog::record(
            'manager.elevation_granted',
            $user,
            "Admin elevation granted to {$user->name} for {$validated['hours']} hours",
            [
                'manager_id' => $user->id,
                'hours' => $validated['hours'],
                'expires_at' => $user->elevated_until,
            ]
        );

        return back()->with('success', "Admin access granted to {$user->name} for {$validated['hours']} hours.");
    }

    /**
     * Quick toggle elevation with preset durations
     */
    public function quickToggle(Request $request, User $user)
    {
        $validated = $request->validate([
            'duration' => 'required|in:2,4,8,24,indefinite',
        ]);

        if (!$user->is_manager) {
            return back()->with('error', 'User must be designated as a manager first.');
        }

        // Handle indefinite as 168 hours (7 days) - safety failsafe
        $hours = $validated['duration'] === 'indefinite' ? 168 : (int) $validated['duration'];
        $user->grantElevation($hours, auth()->id());

        $durationText = $validated['duration'] === 'indefinite'
            ? 'indefinite (7 days max)'
            : "{$hours} hours";

        ActivityLog::record(
            'manager.elevation_quick_toggle',
            $user,
            "Quick toggle: Admin elevation granted to {$user->name} for {$durationText}",
            [
                'manager_id' => $user->id,
                'hours' => $hours,
                'duration_type' => $validated['duration'],
                'expires_at' => $user->elevated_until,
                'method' => 'quick_toggle',
            ]
        );

        return back()->with('success', "Admin access granted to {$user->name} for {$durationText}.");
    }

    /**
     * Revoke elevation for a specific manager
     */
    public function revokeElevation(User $user)
    {
        if (!$user->isElevated()) {
            return back()->with('error', "{$user->name} does not have active elevation.");
        }

        $user->revokeElevation();

        ActivityLog::record(
            'manager.elevation_revoked',
            $user,
            "Admin elevation revoked for {$user->name}",
            ['manager_id' => $user->id]
        );

        return back()->with('success', "Admin access revoked for {$user->name}.");
    }

    /**
     * Revoke all active elevations (I'm back button)
     */
    public function revokeAll()
    {
        $elevatedManagers = User::where('is_manager', true)
            ->whereNotNull('elevated_until')
            ->where('elevated_until', '>', now())
            ->get();

        $count = $elevatedManagers->count();

        if ($count === 0) {
            return back()->with('info', 'No active elevations to revoke.');
        }

        foreach ($elevatedManagers as $manager) {
            $manager->revokeElevation();
        }

        ActivityLog::record(
            'manager.elevation_revoked_all',
            null,
            "All active elevations revoked ({$count} managers)",
            [
                'count' => $count,
                'manager_ids' => $elevatedManagers->pluck('id')->toArray(),
            ]
        );

        return back()->with('success', "Admin access revoked for {$count} manager(s).");
    }

    /**
     * View elevation history
     */
    public function history()
    {
        $logs = ActivityLog::whereIn('event_type', [
            'manager.designated',
            'manager.undesignated',
            'manager.elevation_granted',
            'manager.elevation_quick_toggle',
            'manager.elevation_revoked',
            'manager.elevation_revoked_all',
        ])
            ->with('user')
            ->orderByDesc('occurred_at')
            ->paginate(50);

        return view('managers.history', compact('logs'));
    }
}
