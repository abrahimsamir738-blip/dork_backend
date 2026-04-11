<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\TimeSlot;

class OrderController extends Controller
{
 public function index(Request $request)
 {
  $query = $request->user()->orders();

  if ($request->has('clinicId')) {
   $query->where('clinic_id', $request->clinicId);
  }

  $orders = $query->with('clinic')->orderBy('date', 'desc')->orderBy('queue_number', 'asc')->get();

  return response()->json($orders);
 }

 public function store(Request $request)
 {
  $validated = $request->validate([
   'clinic_id' => 'required|exists:clinics,id',
   'slot_id' => 'nullable|exists:time_slots,id',
   'patient_name' => 'required|string|max:255',
   'phone_number' => 'required|string|max:255',
   'type' => 'required|in:كشف,استشارة,تحاليل,أشعة',
   'status' => 'sometimes|in:منتظر,قيد الكشف,تم الكشف,ملغي',
   'payment_status' => 'sometimes|in:تم دفع الرسوم,لم يتم الدفع',
   'date' => 'required|date',
   'notes' => 'nullable|string',
   'consultation_fee' => 'required|numeric|min:0',
   'service_fee' => 'sometimes|numeric|min:0',
  ]);

  // Get the clinic to determine consultation fee if not provided
  $clinic = $request->user()->clinics()->findOrFail($validated['clinic_id']);

  // Backend validation: Check if clinic is closed
  if ($clinic->is_closed_today) {
   return response()->json([
    'message' => 'العيادة مغلقة حالياً ولا يمكن الحجز',
    'error' => 'clinic_closed'
   ], 403);
  }

  if (!isset($validated['consultation_fee'])) {
   $validated['consultation_fee'] = $clinic->consultation_fee;
  }

  // Calculate queue number
  $lastOrder = Order::where('clinic_id', $validated['clinic_id'])
   ->where('date', $validated['date'])
   ->orderBy('queue_number', 'desc')
   ->first();

  $validated['queue_number'] = $lastOrder ? $lastOrder->queue_number + 1 : 1;
  $validated['doctor_id'] = $request->user()->id;

  $order = Order::create($validated);

  // Increment booked count if linked to a slot
  if ($order->slot_id) {
   $slot = TimeSlot::find($order->slot_id);
   if ($slot && $slot->booked_count < $slot->capacity) {
    $slot->increment('booked_count');
   }
  }

  return response()->json($order->load('clinic'), 201);
 }

 public function update(Request $request, $id)
 {
  $order = $request->user()->orders()->findOrFail($id);

  $validated = $request->validate([
   'patient_name' => 'sometimes|string|max:255',
   'phone_number' => 'sometimes|string|max:255',
   'type' => 'sometimes|in:كشف,استشارة,تحاليل,أشعة',
   'status' => 'sometimes|in:منتظر,قيد الكشف,تم الكشف,ملغي',
   'payment_status' => 'sometimes|in:تم دفع الرسوم,لم يتم الدفع',
   'notes' => 'nullable|string',
   'consultation_fee' => 'sometimes|numeric|min:0',
   'service_fee' => 'sometimes|numeric|min:0',
  ]);

  $order->update($validated);

  return response()->json($order->load('clinic'));
 }

 public function destroy(Request $request, $id)
 {
  $order = $request->user()->orders()->findOrFail($id);
  $slotId = $order->slot_id;

  $order->delete();

  // Decrement booked count if linked to a slot
  if ($slotId) {
   $slot = TimeSlot::find($slotId);
   if ($slot && $slot->booked_count > 0) {
    $slot->decrement('booked_count');
   }
  }

  return response()->json(['message' => 'Order deleted successfully']);
 }

 public function cancel(Order $order)
 {
  // السماح بالإلغاء فقط إذا كان قيد الانتظار أو قيد الكشف
  if (in_array($order->status, ['منتظر', 'قيد الكشف'])) {
   $order->update(['status' => 'ملغي']);

   return response()->json([
    'status' => 'success',
    'message' => 'تم إلغاء الحجز بنجاح'
   ]);
  }

  return response()->json([
   'status' => 'error',
   'message' => 'لا يمكن إلغاء الحجز في حالته الحالية'
  ], 400);
 }
}
