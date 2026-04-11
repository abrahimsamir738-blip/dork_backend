<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
 protected $fillable = [
  'doctor_id',
  'name',
  'address',
  'consultation_fee',
  'working_hours',
  'current_serving_number',
  'max_patients_per_day',
  'is_closed_today',
  'photo',
  'map_link',
 ];

 protected $casts = [
  'consultation_fee' => 'decimal:2',
  'is_closed_today' => 'boolean',
 ];

 public function doctor()
 {
  return $this->belongsTo(Doctor::class);
 }

 public function orders()
 {
  return $this->hasMany(Order::class);
 }
 public function timeSlots()
 {
  return $this->hasMany(TimeSlot::class);
 }
}
