<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSync extends Model
{
    use HasFactory;

    protected $table = 'attendance_syncs';

    protected $fillable = [
        'unit_id',
        'officer_id',
        'videaclass_id',
        'registered_number',
        'fullname',
        'presence_count',
        'absence_count',
        'is_active',
        'synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'synced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan Unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi dengan Officer
     */
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    /**
     * Scope untuk mendapatkan data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk mendapatkan data berdasarkan unit
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope untuk mendapatkan data berdasarkan officer
     */
    public function scopeByOfficer($query, $officerId)
    {
        return $query->where('officer_id', $officerId);
    }
}
