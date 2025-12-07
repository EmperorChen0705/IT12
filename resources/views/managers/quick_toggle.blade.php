{{-- Quick Toggle Widget for Manager Elevation --}}
@php
    // Check if migration has been run
    $migrationRun = \Illuminate\Support\Facades\Schema::hasColumn('users', 'is_manager');
@endphp

@if(!$migrationRun)
    <div class="alert alert-warning mb-4">
        <h5><i class="bi bi-exclamation-triangle"></i> Manager Elevation System Not Activated</h5>
        <p class="mb-2">Please run the database migration to activate the manager elevation system:</p>
        <code>php artisan migrate</code>
    </div>
@else
    <div class="glass-card mb-4" style="background: rgba(255, 255, 255, 0.95); border-radius: 16px; padding: 24px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0" style="color: #1a1a1a; font-weight: 600; font-size: 1.25rem;">
                <i class="bi bi-shield-lock" style="color: #4f46e5;"></i> Manager Access Control
            </h4>
            @php
                $elevatedManagers = \App\Models\User::where('is_manager', true)
                    ->whereNotNull('elevated_until')
                    ->where('elevated_until', '>', now())
                    ->with('employee')
                    ->get();
                $hasElevations = $elevatedManagers->count() > 0;
            @endphp
            <span class="badge" style="padding: 8px 16px; border-radius: 20px; font-size: 0.875rem; font-weight: 500; {{ $hasElevations ? 'background: #fbbf24; color: #78350f;' : 'background: #10b981; color: #064e3b;' }}">
                @if($hasElevations)
                    <i class="bi bi-exclamation-triangle"></i> {{ $elevatedManagers->count() }} Active
                @else
                    <i class="bi bi-check-circle"></i> No Active Elevations
                @endif
            </span>
        </div>

        @if($hasElevations)
            {{-- Active Elevations Alert --}}
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <strong style="color: #92400e; font-size: 0.95rem;">Active Elevations:</strong>
                <ul style="margin: 12px 0 0 0; padding-left: 20px; color: #78350f;">
                    @foreach($elevatedManagers as $manager)
                        <li style="margin-bottom: 8px;">
                            <strong>{{ $manager->name }}</strong>
                            - Expires in <span class="countdown" data-expires="{{ $manager->elevated_until->toIso8601String() }}" style="font-weight: 600; color: #b45309;">
                                {{ $manager->elevated_until->diffForHumans() }}
                            </span>
                            <form action="{{ route('managers.revoke', $manager) }}" method="POST" class="d-inline" 
                                  onsubmit="return confirm('Revoke admin access for {{ $manager->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #dc2626; color: white; border: none; padding: 4px 12px; border-radius: 6px; font-size: 0.8rem; margin-left: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
                                    <i class="bi bi-x-circle"></i> Revoke
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- I'm Back Button --}}
            <form action="{{ route('managers.revoke_all') }}" method="POST" 
                  onsubmit="return confirm('Revoke admin access for all managers?');">
                @csrf
                <button type="submit" style="width: 100%; background: #dc2626; color: white; border: none; padding: 16px; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-bottom: 24px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);" onmouseover="this.style.background='#b91c1c'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(220, 38, 38, 0.4)'" onmouseout="this.style.background='#dc2626'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(220, 38, 38, 0.3)'">
                    <i class="bi bi-person-check"></i> I'm Back - Revoke All Access
                </button>
            </form>
        @endif

        @php
            $availableManagers = \App\Models\User::where('is_manager', true)
                ->where(function($q) {
                    $q->whereNull('elevated_until')
                      ->orWhere('elevated_until', '<=', now());
                })
                ->with('employee')
                ->orderBy('name')
                ->get();
        @endphp

        @if($availableManagers->count() > 0)
            {{-- Quick Grant Access Section --}}
            <div style="background: #f9fafb; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb;">
                <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 12px; font-size: 0.95rem;">
                    Quick Grant Access
                </label>
                <select id="quickToggleManager" style="width: 100%; padding: 12px 16px; border: 2px solid #d1d5db; border-radius: 10px; font-size: 0.95rem; color: #1f2937; background: white; cursor: pointer; transition: all 0.2s; margin-bottom: 16px;" onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.1)'" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                    <option value="">Select a manager...</option>
                    @foreach($availableManagers as $manager)
                        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                    @endforeach
                </select>

                <div id="quickToggleButtons" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <button type="button" class="quick-toggle-btn" data-hours="2" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; border: none; padding: 16px; border-radius: 12px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(79, 70, 229, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(79, 70, 229, 0.3)'">
                            <i class="bi bi-clock"></i> 2 Hours
                        </button>
                        <button type="button" class="quick-toggle-btn" data-hours="4" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; border: none; padding: 16px; border-radius: 12px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(79, 70, 229, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(79, 70, 229, 0.3)'">
                            <i class="bi bi-clock"></i> 4 Hours
                        </button>
                        <button type="button" class="quick-toggle-btn" data-hours="8" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; border: none; padding: 16px; border-radius: 12px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(79, 70, 229, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(79, 70, 229, 0.3)'">
                            <i class="bi bi-clock"></i> 8 Hours
                        </button>
                        <button type="button" class="quick-toggle-btn" data-hours="24" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); color: #78350f; border: none; padding: 16px; border-radius: 12px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(245, 158, 11, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(245, 158, 11, 0.3)'">
                            <i class="bi bi-hourglass-split"></i> 24 Hours
                        </button>
                    </div>
                    {{-- Indefinite Button --}}
                    <button type="button" class="quick-toggle-btn" data-hours="indefinite" style="width: 100%; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); color: white; border: none; padding: 18px; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.3)'">
                        <i class="bi bi-infinity"></i> Indefinite (Until I Return)
                    </button>
                    <p style="margin-top: 8px; font-size: 0.8rem; color: #6b7280; text-align: center; margin-bottom: 0;">
                        <i class="bi bi-info-circle"></i> Indefinite grants 7 days max. Use "I'm Back" button to revoke anytime.
                    </p>
                </div>
            </div>
        @else
            <div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 8px;">
                <i class="bi bi-info-circle" style="color: #1e40af;"></i> 
                <span style="color: #1e3a8a;">No managers available.</span>
                <a href="{{ route('managers.index') }}" style="color: #2563eb; font-weight: 600; text-decoration: none;">Designate managers</a> 
                <span style="color: #1e3a8a;">to use quick toggle.</span>
            </div>
        @endif
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const managerSelect = document.getElementById('quickToggleManager');
        const buttonsDiv = document.getElementById('quickToggleButtons');
        const toggleButtons = document.querySelectorAll('.quick-toggle-btn');

        if (managerSelect) {
            managerSelect.addEventListener('change', function() {
                if (this.value) {
                    buttonsDiv.style.display = 'block';
                } else {
                    buttonsDiv.style.display = 'none';
                }
            });
        }

        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const managerId = managerSelect.value;
                const hours = this.dataset.hours;
                
                if (!managerId) {
                    alert('Please select a manager first.');
                    return;
                }

                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/managers/${managerId}/quick-toggle`;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                
                const durationInput = document.createElement('input');
                durationInput.type = 'hidden';
                durationInput.name = 'duration';
                durationInput.value = hours;
                
                form.appendChild(csrfInput);
                form.appendChild(durationInput);
                document.body.appendChild(form);
                form.submit();
            });
        });

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
@endif