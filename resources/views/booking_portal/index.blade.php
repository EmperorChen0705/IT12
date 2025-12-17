@extends('booking_layout')

@section('title', 'Booking Portal - SubWfour')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <style>
        /* Override all button colors to red */
        .btn,
        .btn-primary,
        .btn-secondary,
        .cta-btn,
        button[type="submit"],
        button[type="button"] {
            background: #ef3535 !important;
            color: #ffffff !important;
            border: 1px solid #d32f2f !important;
        }

        .btn:hover,
        .btn-primary:hover,
        .btn-secondary:hover,
        .cta-btn:hover,
        button[type="submit"]:hover,
        button[type="button"]:hover {
            background: #d32f2f !important;
        }

        /* Navigation links - red text only */
        .mini-link {
            background: transparent !important;
            color: #ef3535 !important;
            border: none !important;
        }
    </style>
    <script src="{{ asset('js/portal.js') }}" defer></script>
@endsection

@section('content')
    <div class="portal-shell">

        <!-- Top Minimal Nav -->
        <header class="portal-topbar">
            <div class="topbar-inner">
                <div class="brand">
                    <img src="{{ asset('images/app-logo.png') }}" alt="Title" class="brand-mark">
                </div>
                <nav class="mini-nav">
                    <a href="#services" class="mini-link">Services</a>
                    <a href="#process" class="mini-link">Process</a>
                    <a href="#why" class="mini-link">Why Us</a>
                    <a href="#contact" class="mini-link">Contact</a>
                    <button id="openBookingFormBtn" type="button" class="btn btn-primary top-cta">
                        <i class="bi bi-calendar-plus"></i> Book Now
                    </button>
                </nav>
            </div>
        </header>

        <!-- HERO -->
        <section class="portal-hero expanded-hero" id="hero">
            <div class="hero-grid">
                <div class="hero-main">
                    <img src="{{ asset('images/app-logo.png') }}" alt="Full Logo" class="hero-logo">
                    <h1 class="portal-title">Premium Car Audio & Installation</h1>
                    <p class="portal-subtitle hero-sub">
                        Precision speaker upgrades, subwoofer fabrication, full custom audio system design & tuning.
                        Trusted installation workflow from booking to final demonstration.
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success hero-alert">{{ session('success') }}</div>
                    @endif
                    @if($errors->any() && old('_from') === 'createBooking')
                        <div class="alert alert-danger hero-alert">
                            <ul class="m-0 ps-3" style="font-size:.7rem;">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="hero-actions">
                        <button type="button" id="openBookingFormBtnHero" class="btn btn-primary hero-book-btn">
                            <i class="bi bi-calendar-plus"></i> Book Now
                        </button>
                        <a href="#process" class="btn btn-secondary hero-secondary">
                            <i class="bi bi-arrow-down-circle"></i> How It Works
                        </a>
                    </div>

                    <div class="trust-metrics">
                        <div class="metric">
                            <span class="metric-value">LIFETIME</span>
                            <span class="metric-label">WARRANTY SUPPORT</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">FLEXIBLE</span>
                            <span class="metric-label">BOOKING OPTIONS</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">TRANSPARENT</span>
                            <span class="metric-label">PRICING POLICY</span>
                        </div>
                    </div>
                </div>
                <div class="hero-side">
                    <div class="side-card glass-tile" style="background: #1a1a1a !important;">
                        <h3 class="side-heading">Audio Precision</h3>
                        <p class="side-desc">
                            Every installation is calibrated for clarity, depth, and balancing staging. From entry
                            upgrades to full engineered builds-done right the first time.
                        </p>
                        <ul class="side-list">
                            <li><i class="bi bi-check-circle-fill"></i> Custom Enclosures</li>
                            <li><i class="bi bi-check-circle-fill"></i> DSP Tuning & Setup</li>
                            <li><i class="bi bi-check-circle-fill"></i> Noise Dampening</li>
                            <li><i class="bi bi-check-circle-fill"></i> OEM Integration</li>
                        </ul>
                        <div class="hero-carousel" aria-label="Showcase Images">
                            <div class="carousel-track">
                                <div class="carousel-slide"><img src="{{ asset('images/Sample1.jpg') }}" alt="Sample 1">
                                </div>
                                <div class="carousel-slide"><img src="{{ asset('images/Sample2.jpg') }}" alt="Sample 2">
                                </div>
                                <div class="carousel-slide"><img src="{{ asset('images/Sample3.jpg') }}" alt="Sample 3">
                                </div>
                                <div class="carousel-slide"><img src="{{ asset('images/Sample4.jpg') }}" alt="Sample 4">
                                </div>
                                <div class="carousel-slide"><img src="{{ asset('images/Sample5.jpg') }}" alt="Sample 5">
                                </div>
                                <div class="carousel-slide"><img src="{{ asset('images/Sample1.jpg') }}" alt="Sample 1">
                                </div>
                                <div class="carousel-slide"><img src="{{ asset('images/Sample2.jpg') }}" alt="Sample 2">
                                </div>
                                <div class="carousel-slide"><img src="{{ asset('images/Sample3.jpg') }}" alt="Sample 3">
                                </div>
                                <div class="carousel-slide"><img src="{{ asset('images/Sample4.jpg') }}" alt="Sample 4">
                                </div>
                                <div class="carousel-slide"><img src="{{ asset('images/Sample5.jpg') }}" alt="Sample 5">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- INLINE BOOKING FORM -->
        <div id="bookingFormWrapper" class="booking-form-wrapper" aria-hidden="true"
            data-auto-open="{{ ($errors->any() && old('_from') === 'createBooking') ? '1' : '0' }}">
            <form id="bookingInlineForm" action="{{ route('booking.portal.store') }}" method="POST"
                class="booking-form-panel" novalidate>
                @csrf
                <input type="hidden" name="_from" value="createBooking">

                <h2 class="booking-form-title">Booking Request</h2>

                <div id="formErrorSummary" class="portal-alert error" style="display:none;font-size:.65rem;"></div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input name="customer_name" class="form-input" required value="{{ old('customer_name') }}">
                        <div class="field-error" data-error-for="customer_name"></div>
                    </div>
                </div>

                <div class="form-row uniform">
                    <div class="form-group" style="margin-right:15px;">
                        <label>Car Make (Optional)</label>
                        <input name="vehicle_make" class="form-input" value="{{ old('vehicle_make') }}">
                    </div>
                    <div class="form-group" style="margin-right:15px;">
                        <label>Car Model (Optional)</label>
                        <input name="vehicle_model" class="form-input" value="{{ old('vehicle_model') }}">
                    </div>
                    <div class="form-group">
                        <label>Plate Number (Optional)</label>
                        <input name="plate_number" class="form-input" value="{{ old('plate_number') }}">
                    </div>
                </div>

                <div class="form-row uniform">
                    <div class="form-group" style="margin-right:25px;">
                        <label>Contact Number *</label>
                        <input name="contact_number" class="form-input" required value="{{ old('contact_number') }}">
                        <div class="field-error" data-error-for="contact_number"></div>
                    </div>
                    <div class="form-group">
                        <label>Service Type *</label>
                        <select name="service_type" class="form-input" required>
                            <option value="">-- select service --</option>
                            @foreach(($serviceTypes ?? []) as $st)
                                <option value="{{ $st }}" @selected(old('service_type') == $st)>{{ $st }}</option>
                            @endforeach
                            <option value="Other" @selected(old('service_type') === 'Other')>Other</option>
                        </select>
                        <div class="field-error" data-error-for="service_type"></div>
                    </div>
                </div>

                <div class="form-row uniform">
                    <div class="form-group" style="margin-right:25px;">
                        <label>Preferred Date *</label>
                        <input type="date" name="preferred_date" class="form-input" required
                            min="{{ now()->format('Y-m-d') }}" value="{{ old('preferred_date', now()->format('Y-m-d')) }}">
                        <div class="field-error" data-error-for="preferred_date"></div>
                    </div>
                    <div class="form-group">
                        <label>Preferred Time *</label>
                        <input type="time" name="preferred_time" class="form-input" required
                            value="{{ old('preferred_time') }}">
                        <div class="field-error" data-error-for="preferred_time"></div>
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>Additional Notes (Optional)</label>
                        <textarea name="notes" rows="3" class="form-input"
                            style="resize:vertical;">{{ old('notes') }}</textarea>
                        <div class="field-error" data-error-for="notes"></div>
                    </div>
                </div>

                <div class="note" style="margin-top:6px;">
                    We’ll review availability & confirm via your preferred contact method.
                </div>

                <div class="button-row" style="margin-top:18px;display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" id="cancelBookingFormBtn" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" id="bookingSubmitBtn">Submit Booking</button>
                </div>
            </form>
        </div>

        <!-- SERVICES -->
        <section class="section-block" id="services">
            <div class="section-head">
                <h2 class="section-title">Core Services</h2>
                <p class="section-sub">
                    "To Hear is To Believe"
                </p>
            </div>
            <div class="card-grid services-grid">
                <div class="svc-card" style="background: #1a1a1a !important;">
                    <div class="svc-icon gradient"><i class="bi bi-speaker"></i></div>
                    <h3>CONSULTATION & DESIGN</h3>
                    <p>Expert guidance to design your perfect audio system tailored to your vehicle and preferences.</p>
                </div>
                <div class="svc-card" style="background: #1a1a1a !important;">
                    <div class="svc-icon gradient"><i class="bi bi-boombox"></i></div>
                    <h3>CUSTOMIZATION & UPGRADES</h3>
                    <p>Transform your existing system with premium components and custom enclosures.</p>
                </div>
                <div class="svc-card" style="background: #1a1a1a !important;">
                    <div class="svc-icon gradient"><i class="bi bi-diagram-3"></i></div>
                    <h3>INSTALLATION</h3>
                    <p>Professional installation ensuring optimal performance and seamless integration.</p>
                </div>
                <div class="svc-card" style="background: #1a1a1a !important;">
                    <div class="svc-icon gradient"><i class="bi bi-sliders2-vertical"></i></div>
                    <h3>RENTAL SERVICE</h3>
                    <p>High-quality audio equipment rentals for events, parties, and special occasions.</p>
                </div>
                <div class="svc-card" style="background: #1a1a1a !important;">
                    <div class="svc-icon gradient"><i class="bi bi-shield-lock"></i></div>
                    <h3>REPAIR & MAINTENANCE</h3>
                    <p>Keep your system performing at its best with expert diagnostics and repairs.</p>
                </div>
            </div>
        </section>

        <!-- PROCESS -->
        <section class="section-block alt-surface" id="process">
            <div class="section-head">
                <h2 class="section-title">Process</h2>
                <p class="section-sub">"To Hear is To Believe"</p>
            </div>
            <div class="process-timeline" style="display: flex; justify-content: center;">
                <div class="p-step" style="background: #1a1a1a !important;">
                    <div class="p-badge">1</div>
                    <h4>Inquire</h4>
                    <p>Meet Us, Negotiate with Us.</p>
                </div>
                <div class="p-step" style="background: #1a1a1a !important;">
                    <div class="p-badge">2</div>
                    <h4>Book Your Appointment</h4>
                    <p>Book you Schedule, Secure a Spot.</p>
                </div>
                <div class="p-step" style="background: #1a1a1a !important;">
                    <div class="p-badge">3</div>
                    <h4>Check-In</h4>
                    <p>Meet our Technicians and proceed with discussed service.</p>
                </div>
                <div class="p-step" style="background: #1a1a1a !important;">
                    <div class="p-badge">4</div>
                    <h4>Check-Out</h4>
                    <p>Review the completed service and provide your feedback.</p>
                </div>
                <div class="p-step" style="background: #1a1a1a !important;">
                    <div class="p-badge">5</div>
                    <h4>Payment</h4>
                    <p>After satisfaction of the service, settle the payment</p>
                </div>
            </div>
            <div class="motto">
                <i class="bi bi-quote"></i>
                <span>""To Hear is To Believe""</span>
            </div>
        </section>

        <!-- WHY US -->
        <section class="section-block" id="why">
            <div class="section-head">
                <h2 class="section-title">Why choose us?</h2>
                <p class="section-sub">"To Hear is To Believe"</p>
            </div>
            <div class="why-grid" style="display: flex; justify-content: center;">
                <div class="why-card" style="background: #1a1a1a !important;">
                    <i class="bi bi-award-fill"></i>
                    <h3>Trained Experts</h3>
                    <p>We have highly trained automotive experts.</p>
                </div>
                <div class="why-card" style="background: #1a1a1a !important;">
                    <i class="bi bi-cash-coin"></i>
                    <h3>We use modern equipment</h3>
                    <p>To ensure reliable and efficient services.</p>
                </div>
                <div class="why-card" style="background: #1a1a1a !important;">
                    <i class="bi bi-hand-thumbs-up-fill"></i>
                    <h3>We Value your time</h3>
                    <p>Expect turnaround without compromising quality.</p>
                </div>
                <div class="why-card" style="background: #1a1a1a !important;">
                    <i class="bi bi-chat-dots-fill"></i>
                    <h3>Your satisfaction is our priority</h3>
                    <p>We provide fabrication and personalized service.</p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="final-cta" id="contact">
            <div class="cta-inner">
                <h2 class="cta-title">Book Now!</h2>
                <p class="cta-sub">Schedule your ideal consultation now.</p>
                <button type="button" id="openBookingFormBtnBottom" class="btn btn-primary cta-btn">
                    <i class="bi bi-calendar-plus"></i> Start Booking
                </button>
            </div>
        </section>

        <footer class="portal-footer">
            <div class="footer-inner">
                <span>&copy; {{ date('Y') }} SubWFour. All rights reserved.</span>
                <span class="foot-meta">SUBWFOUR • AUTOMOTIVE • INSTALLATION</span>
            </div>
        </footer>
    </div>
@endsection