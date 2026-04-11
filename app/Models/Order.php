<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'slot_id',
        'patient_name',
        'phone_number',
        'queue_number',
        'type',
        'status',
        'payment_status',
        'date',
        'notes',
        'consultation_fee',
        'service_fee',
    ];

    protected $casts = [
        'date' => 'date',
        'consultation_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'queue_number' => 'integer',
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
