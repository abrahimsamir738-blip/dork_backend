<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Authenticatable
{
 use HasApiTokens, Notifiable;

 protected $table = 'doctors';

 protected $fillable = [
  'name',
  'email',
  'password',
  'title',
  'specialty',
  'bio',
  'photo',
 ];

 protected $hidden = [
  'password',
  'remember_token',
 ];

 protected function casts(): array
 {
  return [
   'password' => 'hashed',
  ];
 }

 public function clinics()
 {
  return $this->hasMany(Clinic::class);
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
