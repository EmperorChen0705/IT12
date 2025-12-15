<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'System - TITLE')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/system.css') }}?v={{ time() }}" rel="stylesheet" />
    <link href="{{ asset('css/pages.css') }}?v={{ time() }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    @yield('head')
    <style>
        .glass-card {
            background: #1a1a1a !important;
        }

        .search-bar {
            background: linear-gradient(135deg, #2a2a2a, #1a1a1a) !important;
        }

        .search-bar:focus-within {
            background: linear-gradient(135deg, #2a2a2a, #1a1a1a) !important;
        }

        .search-input {
            color: #ef3535 !important;
        }

        .search-input::placeholder {
            color: #ef3535 !important;
            opacity: .5 !important;
        }

        /* All buttons - red background */
        .btn,
        .btn-primary,
        .btn-secondary,
        .btn-edit,
        .btn-delete,
        .btn-light,
        button[type="submit"],
        button[type="button"],
        .btn-add-record {
            background: #ef3535 !important;
            color: #ffffff !important;
            border: 1px solid #d32f2f !important;
        }

        .btn:hover,
        .btn-primary:hover,
        .btn-secondary:hover,
        .btn-edit:hover,
        .btn-delete:hover,
        .btn-light:hover,
        button[type="submit"]:hover,
        button[type="button"]:hover,
        .btn-add-record:hover {
            background: #d32f2f !important;
        }

        /* Modal and form styling */
        .modal-content,
        .app-modal-content {
            background: #1a1a1a !important;
            color: #ef3535 !important;
        }

        .modal-content h2,
        .modal-content h3,
        .modal-content h4,
        .app-modal-content h2,
        .app-modal-content h3,
        .app-modal-content h4,
        .modal-content label,
        .app-modal-content label,
        .glass-card h2,
        .glass-card h3,
        .glass-card h4,
        .app-modal-header,
        .section-heading {
            color: #ef3535 !important;
        }

        /* Table borders white */
        .table,
        .table th,
        .table td {
            border-color: #ffffff !important;
        }

        /* Table row backgrounds - light gradient gray */
        .table tbody tr {
            background: linear-gradient(135deg, #c0c0c0, #a0a0a0) !important;
            transition: background 0.2s ease !important;
        }

        .table tbody tr:nth-child(even) {
            background: linear-gradient(135deg, #b0b0b0, #909090) !important;
            transition: background 0.2s ease !important;
        }

        /* Table row hover effect */
        .table tbody tr:hover {
            background: linear-gradient(135deg, #e0e0e0, #c0c0c0) !important;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
        }

        /* Table header background - light gradient gray */
        .table thead th {
            background: linear-gradient(135deg, #d0d0d0, #b0b0b0) !important;
            color: #000000 !important;
        }

        .line-items-block {
            background: #1a1a1a !important;
        }

        /* Dropdown menu styling */
        .dropdown-menu {
            background: #1a1a1a !important;
            border: 1px solid #555555 !important;
        }

        .dropdown-menu a,
        .dropdown-menu button {
            background: #ef3535 !important;
            color: #ffffff !important;
            border: 1px solid #d32f2f !important;
        }

        .dropdown-menu a:hover,
        .dropdown-menu button:hover {
            background: #d32f2f !important;
        }

        /* Quick Actions styling */
        .qa-grid,
        .quick-actions-grid {
            background: #1a1a1a !important;
        }

        .panel {
            background: #1a1a1a !important;
        }

        .qa-btn {
            background: #ef3535 !important;
            color: #ffffff !important;
            border: 1px solid #d32f2f !important;
        }

        .qa-btn:hover {
            background: #d32f2f !important;
        }

        .qa-btn i {
            color: #ffffff !important;
        }

        /* Dashboard metric cards */
        .dm-card {
            background: #1a1a1a !important;
        }

        .dm-card .dm-label,
        .dm-card .dm-value,
        .dm-card .dm-sub {
            color: #ffffff !important;
        }

        /* Dashboard chart panels */
        .panel,
        .dash-panel {
            background: #1a1a1a !important;
        }

        .panel h3,
        .panel h4,
        .panel-head h3,
        .dash-panel h3,
        .panel p,
        .panel ul,
        .panel li,
        .panel span,
        .panel canvas,
        .panel svg text,
        .panel tspan {
            color: #ffffff !important;
        }

        /* Chart SVG text elements */
        svg text,
        svg tspan {
            fill: #ffffff !important;
        }

        /* Main content area */
        .main-content {
            background: #1a1a1a !important;
        }

        /* Force gray background on all containers */
        .content-wrapper,
        .page-wrapper,
        .dashboard-content,
        .content-area,
        section,
        main {
            background: #1a1a1a !important;
        }
    </style>
    <script src="{{ asset('js/system.js') }}" defer></script>
</head>

<body class="{{ session('first_login') ? 'fade-in' : '' }}" style="background: #1a1a1a !important;">
    @php
        session()->forget('first_login');
        $user = Auth::user();

        // Eager load employee relationship if user is an employee
        if ($user->role === 'employee') {
            $user->load('employee');
        }

        // Determine profile picture path
        if ($user->name === 'Admin') {
            $profilePicture = 'images/admin-profile.png';
        } elseif ($user->role === 'employee' && $user->employee && $user->employee->profile_picture) {
            // Employee with uploaded profile picture
            $profilePicture = 'storage/' . $user->employee->profile_picture;
        } else {
            // Default profile picture
            $profilePicture = 'images/admin-profile.png';
        }
    @endphp

    <div class="sidebar" id="sidebar" style="background: #000000 !important;">
        <div class="sidebar-logo">
            <img src="{{ asset('images/app-logo.png') }}" alt="Logo">
        </div>
        <center>
            <ul>
                @if($user->canAccessAdmin())
                    <li><a href="{{ route('system') }}" class="nav-link"><i class="bi bi-activity"></i> Dashboard</a></li>
                @endif
                <li><a href="{{ route('inventory.index') }}" class="nav-link"><i class="bi bi-inboxes-fill"></i>
                        Inventory</a></li>
                <li><a href="{{ route('stock_in.index') }}" class="nav-link"><i class="bi bi-dropbox"></i> Stock-In</a>
                </li>
                <li><a href="{{ route('bookings.index') }}" class="nav-link"><i class="bi bi-person-lines-fill"></i>
                        Bookings</a></li>
                <li><a href="{{ route('services.index') }}" class="nav-link"><i class="bi bi-wrench"></i> Service</a>
                </li>
                <li><a href="{{ route('suppliers.index') }}" class="nav-link"><i class="bi bi-person-fill-down"></i>
                        Suppliers</a></li>

                @if($user->role === 'admin')
                    <li><a href="{{ route('reports.index') }}" class="nav-link"><i class="bi bi-list-columns"></i>
                            Reports</a></li>
                @endif
                @if($user->canAccessAdmin())
                    <li><a href="{{ route('employees.index') }}" class="nav-link"><i class="bi bi-people-fill"></i>
                            Employees</a></li>
                    @if($user->role === 'admin')
                        <li><a href="{{ route('managers.index') }}" class="nav-link"><i class="bi bi-shield-lock"></i>
                                Managers</a></li>
                    @endif
                    <li><a href="{{ route('backups.index') }}" class="nav-link"><i class="bi bi-database"></i>
                            Backups</a></li>
                @endif
            </ul>
        </center>
    </div>

    <div class="header">
        <button class="toggle-btn" type="button" data-toggle="sidebar">☰</button>
        <h1>SubWfour Inventory System</h1>

        <div class="user-profile" id="userProfile">
            <span>
                Welcome, {{ $user->name }}!
                @if(\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_manager') && $user->isElevated())
                    <span class="badge bg-warning ms-2" style="font-size: 0.7rem;">
                        <i class="bi bi-shield-check"></i> Elevated
                    </span>
                @endif
            </span>
            <div class="profile-picture" id="profileTrigger"
                style="width: 42px !important; height: 42px !important; overflow: hidden !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                <img src="{{ asset($profilePicture) }}" alt="Profile Picture"
                    style="width: 100% !important; height: 100% !important; object-fit: cover !important; max-width: 42px !important; max-height: 42px !important;">
            </div>

            <div class="dropdown-menu hidden" id="dropdownMenu" data-dropdown-menu>
                <button class="dropdown-item" data-action="view-profile">View Profile</button>
                @if($user->canAccessAdmin())
                    <a href="{{ route('employees.index') }}" class="dropdown-item">View Employees</a>
                    @if($user->role === 'admin')
                        <button class="dropdown-item" data-action="register-employee">Register Employee</button>
                    @endif
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn dropdown-item" data-action="logout">Log-Out</button>
                </form>
            </div>

            <div class="modal hidden" id="viewProfileModal" data-modal>
                <div class="modal-content">
                    <h2>Profile</h2>
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
                    <button class="close-btn" data-close>Close</button>
                </div>
            </div>

            @if($user->canAccessAdmin())
                <div class="modal hidden" id="createEmployeeModal" data-modal>
                    <div class="modal-content" style="width: 850px; max-width: 95%;">
                        <h2 style="margin-bottom:14px;">Register Employee</h2>

                        @if($errors->any() && url()->current() === route('system'))
                            <div class="alert alert-danger mb-2">
                                <ul class="m-0 ps-3" style="font-size:.7rem;">
                                    @foreach($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(session('success') && url()->current() === route('system'))
                            <div class="alert alert-success mb-2">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data"
                            id="employeeCreateForm">
                            @csrf

                            <div style="display:flex; gap:30px; align-items:flex-start;">
                                <div style="flex:1;">
                                    <h4 class="section-heading" style="margin:0 0 12px;">Account</h4>
                                    <div class="form-group" style="margin-bottom:10px;">
                                        <label>Name</label>
                                        <input name="name" class="form-input" required value="{{ old('name') }}">
                                    </div>
                                    <div class="form-group" style="margin-bottom:10px;">
                                        <label>Email</label>
                                        <input name="email" type="email" class="form-input" required
                                            value="{{ old('email') }}">
                                    </div>
                                    <div class="form-row" style="margin-bottom:0;">
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input name="password" type="password" class="form-input" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Confirm</label>
                                            <input name="password_confirmation" type="password" class="form-input" required>
                                        </div>
                                    </div>
                                </div>

                                <div style="flex:1;">
                                    <h4 class="section-heading" style="margin:0 0 12px;">Information</h4>
                                    <div class="form-row" style="margin-bottom:10px;">
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input name="first_name" class="form-input" required
                                                value="{{ old('first_name') }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input name="last_name" class="form-input" required
                                                value="{{ old('last_name') }}">
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-bottom:10px;">
                                        <label>Address</label>
                                        <input name="address" class="form-input" required value="{{ old('address') }}">
                                    </div>
                                    <div class="form-row" style="margin-bottom:10px;">
                                        <div class="form-group">
                                            <label>Contact #</label>
                                            <input name="contact_number" class="form-input" required
                                                value="{{ old('contact_number') }}">
                                        </div>
                                        <div class="form-group">
                                            <label>SSS #</label>
                                            <input name="sss_number" class="form-input" required
                                                value="{{ old('sss_number') }}">
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label>Profile Picture</label>
                                        <input type="file" name="profile_picture" accept="image/*" class="form-input"
                                            id="createProfileInput">
                                        <div id="createProfilePreview" style="margin-top:6px; display:none;">
                                            <img src="" alt="Preview"
                                                style="height:40px;width:40px;border-radius:6px;object-fit:cover;border:1px solid var(--gray-300);">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top:20px; padding-top:15px; border-top:1px solid var(--gray-300);">
                                <h4 class="section-heading" style="margin:0 0 10px; font-size: 0.9rem;">Roles</h4>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div style="display:flex; gap:20px;">
                                        <label style="display:flex; align-items:center; gap:6px; cursor:not-allowed;">
                                            <input type="checkbox" checked disabled style="accent-color:var(--primary);">
                                            <span style="font-size:0.85rem;">Technician</span>
                                            <input type="hidden" name="role" value="employee">
                                        </label>
                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                            <input type="checkbox" name="is_manager" value="1"
                                                style="accent-color:var(--primary);">
                                            <span style="font-size:0.85rem;">Manager</span>
                                        </label>
                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                            <input type="checkbox" name="is_inventory_officer" value="1"
                                                style="accent-color:var(--primary);">
                                            <span style="font-size:0.85rem;">Inventory Officer</span>
                                        </label>
                                    </div>
                                    <div style="display:flex; gap:10px;">
                                        <button type="button" class="btn-secondary" data-close>Cancel</button>
                                        <button type="submit" class="btn-primary">Create</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="main-content" style="background: #1a1a1a !important;">
        @yield('content')
    </div>

    <div class="footer" style="background: #1a1a1a !important;">
        <p style="color: #ef3535 !important;">&copy; 2025 SubWFour. All rights reserved.</p>
    </div>

    {{-- Bootstrap JavaScript for modals --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>

</html>