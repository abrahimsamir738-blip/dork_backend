<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'day_of_week',
        'day_name',
        'start_time',
        'end_time',
        'duration',
        'type',
        'capacity',
        'booked_count',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'duration' => 'integer',
        'capacity' => 'integer',
        'booked_count' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
