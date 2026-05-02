<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\PublicBookingController;
use Illuminate\Support\Facades\Route;


// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-user', [AuthController::class, 'loginUser']);
Route::post('/loginUser', [AuthController::class, 'loginUser']);


Route::post('register', [AuthController::class, 'register']);
Route::post('createUser', [AuthController::class, 'createUser']);

// Public website endpoints
Route::get('/public/doctors', [PublicController::class, 'doctors']);
Route::get('/public/doctors/{id}', [PublicController::class, 'doctor']);
Route::get('/public/doctors/{id}/clinics', [PublicController::class, 'doctorClinics']);
Route::get('/public/clinics/{id}/schedules', [PublicController::class, 'clinicSchedules']);
Route::get('/public/clinics/{id}/queue', [PublicController::class, 'clinicQueue']);
Route::post('/public/bookings', [PublicController::class, 'createBooking']);
Route::post('/public/bookings/by-phones', [PublicBookingController::class, 'getBookingsByPhones']);
Route::delete('public/orders/{id}', [OrderController::class, 'destroy']);
Route::post('/bookings/{id}/cancel', [PublicBookingController::class, 'cancelBooking']);
Route::post('/bookings/{id}/reschedule', [PublicBookingController::class, 'rescheduleBooking']);
Route::get('/bookings/{id}', [PublicBookingController::class, 'show']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {
 Route::post('/logout', [AuthController::class, 'logout']);
 Route::get('/me', [AuthController::class, 'me']);

 // Doctor routes
 Route::get('/doctor', [DoctorController::class, 'index']);
 Route::put('/doctor', [DoctorController::class, 'update']);

 // Clinic routes
 Route::get('/clinics', [ClinicController::class, 'index']);
 Route::post('/clinics', [ClinicController::class, 'store']);
 Route::put('/clinics/{id}', [ClinicController::class, 'update']);
 Route::delete('/clinics/{id}', [ClinicController::class, 'destroy']);

 // Order routes
 Route::get('/orders', [OrderController::class, 'index']);
 Route::post('/orders', [OrderController::class, 'store']);
 Route::put('/orders/{id}', [OrderController::class, 'update']);
 Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

 // Schedule routes
 Route::get('/schedules', [ScheduleController::class, 'index']);
 Route::post('/schedules', [ScheduleController::class, 'store']);
 Route::put('/schedules/{id}', [ScheduleController::class, 'update']);
 Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
});
