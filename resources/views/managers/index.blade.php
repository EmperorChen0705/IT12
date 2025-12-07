@extends('system')

@section('title', 'Manager Access Control - SubWfour')

@section('head')
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
@endsection

@section('content')
    <h2 class="text-accent">MANAGER ACCESS CONTROL</h2>

    <div class="glass-card glass-card-wide">

        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info mb-3">{{ session('info') }}</div>
        @endif

        {{-- Current Managers Section --}}
        <h4 class="text-accent mb-3">
            <i class="bi bi-people"></i> Designated Managers
        </h4>

        @if($managers->count() > 0)
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Elevation Expires</th>
                            <th style="width:250px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($managers as $manager)
                            <tr>
                                <td>
                                    <strong>{{ $manager->name }}</strong>
                                    <span class="badge bg-primary ms-2">Manager</span>
                                </td>
                                <td>{{ $manager->email }}</td>
                                <td>
                                    @if($manager->isElevated())
                                        <span class="badge bg-warning">
                                            <i class="bi bi-shield-fill-check"></i> Elevated
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Not Elevated</span>
                                    @endif
                                </td>
                                <td>
                                    @if($manager->isElevated())
                                        <span class="countdown" data-expires="{{ $manager->elevated_until->toIso8601String() }}">
                                            {{ $manager->elevated_until->diffForHumans() }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if($manager->isElevated())
                                            <form action="{{ route('managers.revoke', $manager) }}" method="POST"
                                                onsubmit="return confirm('Revoke admin access for {{ $manager->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Revoke Elevation">
                                                    <i class="bi bi-x-circle"></i> Revoke
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#grantModal{{ $manager->id }}" title="Grant Elevation">
                                                <i class="bi bi-shield-plus"></i> Grant Access
                                            </button>
                                        @endif

                                        <form action="{{ route('managers.undesignate', $manager) }}" method="POST"
                                            onsubmit="return confirm('Remove manager designation from {{ $manager->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-secondary" title="Remove Manager">
                                                <i class="bi bi-person-dash"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Grant Elevation Modal --}}
                            <div class="modal fade" id="grantModal{{ $manager->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Grant Admin Access to {{ $manager->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('managers.elevate', $manager) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="hours{{ $manager->id }}" class="form-label">Duration (hours)</label>
                                                    <select name="hours" id="hours{{ $manager->id }}" class="form-select" required>
                                                        <option value="1">1 Hour</option>
                                                        <option value="2">2 Hours</option>
                                                        <option value="4" selected>4 Hours</option>
                                                        <option value="8">8 Hours</option>
                                                        <option value="12">12 Hours</option>
                                                        <option value="24">24 Hours</option>
                                                    </select>
                                                </div>
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle"></i>
                                                    {{ $manager->name }} will have full admin access for the selected duration.
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Grant Access</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle"></i> No managers designated yet. Designate employees as managers below.
            </div>
        @endif

        <hr class="my-4">

        {{-- Available Employees Section --}}
        <h4 class="text-accent mb-3">
            <i class="bi bi-person-plus"></i> Designate New Managers
        </h4>

        @if($employees->count() > 0)
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th style="width:150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td><strong>{{ $employee->name }}</strong></td>
                                <td>{{ $employee->email }}</td>
                                <td>
                                    <form action="{{ route('managers.designate', $employee) }}" method="POST"
                                        onsubmit="return confirm('Designate {{ $employee->name }} as a manager?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Designate as Manager">
                                            <i class="bi bi-person-check"></i> Designate
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-secondary">
                <i class="bi bi-check-circle"></i> All employees have been designated as managers.
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('managers.history') }}" class="btn btn-outline-primary">
                <i class="bi bi-clock-history"></i> View Elevation History
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Update countdown timers
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
                setInterval(updateCountdowns, 60000);
            }
        });
    </script>
@endsection