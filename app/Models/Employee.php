<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'address',
        'contact_number',
        'sss_number',
        'profile_picture',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get active services assigned to this technician (in_progress status).
     */
    public function activeServices()
    {
        return $this->hasMany(Service::class, 'technician_id')
            ->where('status', Service::STATUS_IN_PROGRESS);
    }

    /**
     * Check if technician is available (not currently assigned to an in-progress service).
     */
    public function isAvailable(): bool
    {
        return $this->activeServices()->count() === 0;
    }
}