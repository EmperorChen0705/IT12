<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_manager',
        'elevated_until',
        'elevated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'elevated_until' => 'datetime',
        ];
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Check if user currently has elevated admin access
     */
    public function isElevated(): bool
    {
        return $this->elevated_until && $this->elevated_until->isFuture();
    }

    /**
     * Check if user can access admin functions (either admin role or elevated)
     */
    public function canAccessAdmin(): bool
    {
        return $this->role === 'admin' || $this->isElevated();
    }

    /**
     * Grant temporary admin elevation
     */
    public function grantElevation(int $hours, int $adminId): void
    {
        $this->update([
            'elevated_until' => now()->addHours($hours),
            'elevated_by' => $adminId,
        ]);
    }

    /**
     * Revoke admin elevation immediately
     */
    public function revokeElevation(): void
    {
        $this->update([
            'elevated_until' => null,
            'elevated_by' => null,
        ]);
    }

    /**
     * Get the admin who granted elevation
     */
    public function elevatedBy()
    {
        return $this->belongsTo(User::class, 'elevated_by');
    }
}