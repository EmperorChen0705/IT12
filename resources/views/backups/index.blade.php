@extends('system')

@section('title', 'Database Backups')

@section('content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="color: #ef3535;">Database Backups</h2>
            <form action="{{ route('backups.create') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Backup Now
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4" style="background: #2a2a2a; border: 1px solid #3a3a3a;">
            <div class="card-body">
                <h5 class="card-title" style="color: #ef3535;">Storage Information</h5>
                <p class="mb-1" style="color: #ccc;">
                    <strong>Total Backups:</strong> {{ count($backups) }}
                </p>
                <p class="mb-1" style="color: #ccc;">
                    <strong>Total Storage Used:</strong> {{ $totalSizeFormatted }}
                </p>
                <p class="mb-0" style="color: #ccc;">
                    <strong>Retention Period:</strong> {{ config('backup.retention_days') }} days
                </p>
            </div>
        </div>

        @if(count($backups) > 0)
            <div class="card" style="background: #2a2a2a; border: 1px solid #3a3a3a;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th style="color: #ef3535;">Filename</th>
                                    <th style="color: #ef3535;">Created</th>
                                    <th style="color: #ef3535;">Age</th>
                                    <th style="color: #ef3535;">Size</th>
                                    <th style="color: #ef3535;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($backups as $backup)
                                    <tr>
                                        <td style="color: #ccc;">
                                            <i class="bi bi-file-earmark-zip"></i>
                                            {{ $backup['filename'] }}
                                        </td>
                                        <td style="color: #ccc;">
                                            {{ $backup['created_at']->format('M d, Y H:i:s') }}
                                        </td>
                                        <td style="color: #ccc;">
                                            {{ $backup['age'] }}
                                        </td>
                                        <td style="color: #ccc;">
                                            {{ $backup['size_formatted'] }}
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('backups.download', $backup['filename']) }}"
                                                    class="btn btn-sm btn-success" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                    data-bs-target="#restoreModal{{ $loop->index }}" title="Restore">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $loop->index }}" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Restore Modal -->
                                            <div class="modal fade" id="restoreModal{{ $loop->index }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content" style="background: #2a2a2a; color: #ccc;">
                                                        <div class="modal-header" style="border-bottom: 1px solid #3a3a3a;">
                                                            <h5 class="modal-title" style="color: #ef3535;">Confirm Restore</h5>
                                                            <button type="button" class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>Warning:</strong> This will replace your current database
                                                                with the backup.</p>
                                                            <p>Backup: <strong>{{ $backup['filename'] }}</strong></p>
                                                            <p>Created:
                                                                <strong>{{ $backup['created_at']->format('M d, Y H:i:s') }}</strong>
                                                            </p>
                                                            <p class="text-danger mb-0">This action cannot be undone!</p>
                                                        </div>
                                                        <div class="modal-footer" style="border-top: 1px solid #3a3a3a;">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <form action="{{ route('backups.restore', $backup['filename']) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning">Restore
                                                                    Database</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal{{ $loop->index }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content" style="background: #2a2a2a; color: #ccc;">
                                                        <div class="modal-header" style="border-bottom: 1px solid #3a3a3a;">
                                                            <h5 class="modal-title" style="color: #ef3535;">Confirm Delete</h5>
                                                            <button type="button" class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this backup?</p>
                                                            <p>Backup: <strong>{{ $backup['filename'] }}</strong></p>
                                                            <p class="text-warning mb-0">This action cannot be undone!</p>
                                                        </div>
                                                        <div class="modal-footer" style="border-top: 1px solid #3a3a3a;">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <form action="{{ route('backups.destroy', $backup['filename']) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Delete Backup</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle"></i> No backups found. Click "Create Backup Now" to create your first backup.
            </div>
        @endif

        <div class="card mt-4" style="background: #2a2a2a; border: 1px solid #3a3a3a;">
            <div class="card-body">
                <h5 class="card-title" style="color: #ef3535;">Backup Information</h5>
                <ul style="color: #ccc;">
                    <li>Backups are created automatically every day at 2:00 AM</li>
                    <li>Backups older than {{ config('backup.retention_days') }} days are automatically deleted</li>
                    <li>You can create manual backups anytime using the "Create Backup Now" button</li>
                    <li>Backups are stored in: <code style="color: #ef3535;">{{ config('backup.path') }}</code></li>
                    <li>Download backups to save them externally or upload to cloud storage</li>
                </ul>
            </div>
        </div>
    </div>
@endsection