@extends('system')

@section('title', 'Elevation History - SubWfour')

@section('head')
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
@endsection

@section('content')
    <h2 class="text-accent">ELEVATION HISTORY</h2>

    <div class="glass-card glass-card-wide">
        <div class="mb-3">
            <a href="{{ route('managers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Managers
            </a>
        </div>

        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Event</th>
                            <th>Description</th>
                            <th>Performed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>
                                    <small>{{ $log->occurred_at->format('M d, Y') }}</small><br>
                                    <small class="text-muted">{{ $log->occurred_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @php
                                        $eventBadges = [
                                            'manager.designated' => ['bg-success', 'person-check'],
                                            'manager.undesignated' => ['bg-secondary', 'person-dash'],
                                            'manager.elevation_granted' => ['bg-primary', 'shield-plus'],
                                            'manager.elevation_quick_toggle' => ['bg-info', 'lightning'],
                                            'manager.elevation_revoked' => ['bg-warning', 'shield-minus'],
                                            'manager.elevation_revoked_all' => ['bg-danger', 'shield-x'],
                                        ];
                                        $badge = $eventBadges[$log->event_type] ?? ['bg-secondary', 'circle'];
                                    @endphp
                                    <span class="badge {{ $badge[0] }}">
                                        <i class="bi bi-{{ $badge[1] }}"></i>
                                        {{ str_replace('manager.', '', $log->event_type) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $log->description }}
                                    @if($log->meta && isset($log->meta['hours']))
                                        <br><small class="text-muted">Duration: {{ $log->meta['hours'] }} hours</small>
                                    @endif
                                    @if($log->meta && isset($log->meta['count']))
                                        <br><small class="text-muted">Affected: {{ $log->meta['count'] }} manager(s)</small>
                                    @endif
                                </td>
                                <td>
                                    @if($log->user)
                                        {{ $log->user->name }}
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No elevation history found.
            </div>
        @endif
    </div>
@endsection