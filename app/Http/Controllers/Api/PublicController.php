<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\Order;
use App\Models\TimeSlot;
use Carbon\Carbon;

class PublicController extends Controller
{
 // Get all doctors (public)
 public function doctors(Request $request)
 {
  // اليوم الحالي لحساب الدور
  $today = now()->toDateString();

  // نستخدم Eager Loading لتحميل العيادات ومواعيدها كاملة بدون قيود (Full Data)
  // Include is_closed_today in clinics
  $doctors = Doctor::with(['clinics' => function ($query) {
   // Include all clinics (open and closed) so frontend can show status
   $query->select('id', 'doctor_id', 'name', 'address', 'consultation_fee', 'working_hours', 'is_closed_today', 'map_link', 'photo');
  }, 'clinics.timeSlots' => function ($query) {
   // ترتيب المواعيد حسب اليوم لضمان تجربة مستخدم منظمة
   $query->orderBy('day_of_week', 'asc')
    ->orderBy('start_time', 'asc');
  }])->get();

  // إضافة رقم الدور الحالي لكل دكتور
  $doctors->transform(function ($doctor) use ($today) {
   $doctor->current_queue_count = \App\Models\Order::where('doctor_id', $doctor->id)
    ->where('date', $today)
    ->where('status', 'منتظر')
    ->count();
   return $doctor;
  });

  return response()->json($doctors);
 }

 // Get single doctor with clinics (public)
 public function doctor($id)
 {
  // Ensure timeSlots relationship is loaded with clinics
  $doctor = Doctor::with(['clinics.timeSlots' => function ($query) {
   $query->orderBy('day_of_week', 'asc')
    ->orderBy('start_time', 'asc');
  }])->findOrFail($id);

  return response()->json($doctor);
 }

 // Get clinics for a doctor (public)
 public function doctorClinics($doctorId)
 {
  $doctor = Doctor::findOrFail($doctorId);
  $clinics = $doctor->clinics()->where('is_closed_today', false)->get();

  return response()->json($clinics);
 }

 // Get available time slots for a clinic (public)
 // Returns all schedules for the clinic, not just today's
 public function clinicSchedules($clinicId)
 {
  // ========== DEBUG: Log incoming parameters ==========
  \Log::info('🔍 ========== BACKEND CLINIC SCHEDULES DEBUG START ==========');
  \Log::info('🔍 Clinic ID received:', ['clinicId' => $clinicId, 'type' => gettype($clinicId)]);
  
  // Normalize clinicId to integer to handle both string and integer inputs
  $normalizedClinicId = (int) $clinicId;
  \Log::info('🔍 Normalized Clinic ID:', ['normalizedId' => $normalizedClinicId, 'type' => gettype($normalizedClinicId)]);
  
  $clinic = Clinic::findOrFail($normalizedClinicId);
  \Log::info('🔍 Clinic found:', ['id' => $clinic->id, 'name' => $clinic->name, 'doctor_id' => $clinic->doctor_id]);

  // ========== DEBUG: Check all slots before filtering ==========
  $allSlots = TimeSlot::where('clinic_id', $normalizedClinicId)->get();
  \Log::info('🔍 All slots for clinic (before capacity filter):', [
   'count' => $allSlots->count(),
   'slots' => $allSlots->map(function($slot) {
    return [
     'id' => $slot->id,
     'clinic_id' => $slot->clinic_id,
     'day_of_week' => $slot->day_of_week,
     'day_name' => $slot->day_name,
     'start_time' => $slot->start_time,
     'end_time' => $slot->end_time,
     'capacity' => $slot->capacity,
     'booked_count' => $slot->booked_count,
     'has_capacity' => $slot->booked_count < $slot->capacity,
    ];
   })
  ]);

  // Get all time slots for this clinic, ordered by day and time
  // TEMPORARY: Remove capacity filter to debug - show all slots regardless of capacity
  // TODO: Re-enable capacity filter after debugging: ->whereColumn('booked_count', '<', 'capacity')
  $slotsQuery = TimeSlot::where('clinic_id', $normalizedClinicId);
  
  // Check if we should apply capacity filter (for now, disabled for debugging)
  $applyCapacityFilter = false; // Set to true to re-enable capacity filtering
  if ($applyCapacityFilter) {
   $slotsQuery->whereColumn('booked_count', '<', 'capacity');
   \Log::info('🔍 Capacity filter ENABLED');
  } else {
   \Log::info('🔍 Capacity filter DISABLED (debugging mode)');
  }
  
  $slots = $slotsQuery
   ->orderBy('day_of_week', 'asc')
   ->orderBy('start_time', 'asc')
   ->get();
  
  \Log::info('🔍 Slots after capacity filter:', [
   'count' => $slots->count(),
   'slots' => $slots->map(function($slot) {
    return [
     'id' => $slot->id,
     'day_name' => $slot->day_name,
     'start_time' => $slot->start_time,
     'capacity' => $slot->capacity,
     'booked_count' => $slot->booked_count,
    ];
   })
  ]);

  // Format times to HH:mm format for frontend
  $formattedSlots = $slots->map(function ($slot) {
   return [
    'id' => $slot->id,
    'clinic_id' => $slot->clinic_id,
    'doctor_id' => $slot->doctor_id,
    'day_of_week' => (int) $slot->day_of_week, // Ensure integer
    'day_name' => $slot->day_name, // String: 'السبت', 'الأحد', etc.
    'start_time' => $slot->start_time ? date('H:i', strtotime($slot->start_time)) : null,
    'end_time' => $slot->end_time ? date('H:i', strtotime($slot->end_time)) : null,
    'duration' => $slot->duration,
    'type' => $slot->type,
    'capacity' => (int) $slot->capacity,
    'booked_count' => (int) $slot->booked_count,
   ];
  });

  \Log::info('🔍 Formatted slots to return:', [
   'count' => $formattedSlots->count(),
   'slots' => $formattedSlots->toArray()
  ]);
  \Log::info('🔍 ========== BACKEND CLINIC SCHEDULES DEBUG END ==========');

  return response()->json($formattedSlots);
 }

 // Get current queue status for a clinic (public)
 public function clinicQueue($clinicId)
 {
  $clinic = Clinic::findOrFail($clinicId);
  $today = Carbon::today()->toDateString();

  $waitingCount = Order::where('clinic_id', $clinicId)
   ->where('date', $today)
   ->where('status', 'منتظر')
   ->count();

  $currentServing = $clinic->current_serving_number;

  return response()->json([
   'current_serving_number' => $currentServing,
   'waiting_count' => $waitingCount,
   'clinic_name' => $clinic->name,
  ]);
 }

 // Create booking (public - no auth required)
 public function createBooking(Request $request)
 {
  $validated = $request->validate([
   'clinic_id' => 'required|exists:clinics,id',
   'doctor_id' => 'required|exists:doctors,id',
   'slot_id' => 'nullable|exists:time_slots,id',
   'patient_name' => 'required|string|max:255',
   'phone_number' => 'required|string|max:255',
   'type' => 'required|in:كشف,استشارة',
   'date' => 'required|date',
   'notes' => 'nullable|string',
  ]);

  $clinic = Clinic::findOrFail($validated['clinic_id']);

  // Verify clinic belongs to doctor
  if ($clinic->doctor_id != $validated['doctor_id']) {
   return response()->json([
    'message' => 'العيادة لا تنتمي لهذا الطبيب'
   ], 400);
  }

  // Check if clinic is closed
  if ($clinic->is_closed_today) {
   return response()->json([
    'message' => 'العيادة مغلقة اليوم'
   ], 400);
  }

  // Calculate queue number
  $today = $validated['date'];
  $lastOrder = Order::where('clinic_id', $validated['clinic_id'])
   ->where('date', $today)
   ->orderBy('queue_number', 'desc')
   ->first();

  $queueNumber = $lastOrder ? $lastOrder->queue_number + 1 : 1;

  // Create order
  $order = Order::create([
   'clinic_id' => $validated['clinic_id'],
   'doctor_id' => $validated['doctor_id'],
   'slot_id' => $validated['slot_id'] ?? null,
   'patient_name' => $validated['patient_name'],
   'phone_number' => $validated['phone_number'],
   'queue_number' => $queueNumber,
   'type' => $validated['type'],
   'status' => 'منتظر',
   'payment_status' => 'لم يتم الدفع',
   'date' => $validated['date'],
   'notes' => $validated['notes'] ?? null,
   'consultation_fee' => $clinic->consultation_fee,
   'service_fee' => 15,
  ]);

  // Increment booked count if linked to a slot
  if ($order->slot_id) {
   $slot = TimeSlot::find($order->slot_id);
   if ($slot && $slot->booked_count < $slot->capacity) {
    $slot->increment('booked_count');
   }
  }

  return response()->json([
   'order' => $order->load('clinic', 'doctor'),
   'queue_number' => $queueNumber,
   'message' => 'تم الحجز بنجاح'
  ], 201);
 }
}
