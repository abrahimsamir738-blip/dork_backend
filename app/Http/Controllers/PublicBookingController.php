<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PublicBookingController extends Controller

{
 // App\Http\Controllers\PublicBookingController.php

 public function show($id)
 {
  $booking = Order::with(['doctor', 'clinic'])->find($id);

  if (!$booking) {
   return response()->json([
    'status'  => 'error',
    'message' => 'الحجز غير موجود'
   ], 404);
  }

  $today      = $booking->created_at->toDateString();
  $doctorId   = $booking->doctor_id;

  // ── رقم دور المريض ──────────────────────────────────
  // عد كل الحجوزات غير الملغية في نفس اليوم ونفس الدكتور لحد الـ id بتاعه
  $turn_number = Order::whereDate('created_at', $today)
   ->where('doctor_id', $doctorId)
   ->where('status', '!=', 'ملغي')
   ->where('id', '<=', $booking->id)
   ->count();

  // ── الدور الحالي اللي بيتخدم دلوقتي ────────────────
  // جيب أقدم حجز حالته "قيد الكشف" في نفس اليوم ونفس الدكتور
  $currentServingId = Order::whereDate('created_at', $today)
   ->where('doctor_id', $doctorId)
   ->where('status', 'قيد الكشف')
   ->min('id');

  if ($currentServingId) {
   // حوّل الـ id لرقم دور
   $current_serving_number = Order::whereDate('created_at', $today)
    ->where('doctor_id', $doctorId)
    ->where('status', '!=', 'ملغي')
    ->where('id', '<=', $currentServingId)
    ->count();
  } else {
   // مفيش حد قيد الكشف دلوقتي → ابدأ من 0
   $current_serving_number = 0;
  }

  return response()->json([
   'status' => 'success',
   'data'   => [
    'id'                     => $booking->id,
    'status'                 => $booking->status,
    'turn_number'            => $turn_number,
    'current_serving_number' => $current_serving_number,
    'doctorName'             => $booking->doctor->name    ?? 'طبيب غير معروف',
    'branchName'             => $booking->clinic->name    ?? 'عيادة غير معروفة',
    'clinic_id'              => $booking->clinic_id,
    'doctorLocation'         => $booking->clinic->address ?? '',
   ]
  ]);
 }
 public function getBookingsByPhones(Request $request)
 {
  // التحقق من أن البيانات المرسلة مصفوفة أرقام
  $request->validate([
   'phones' => 'required|array',
   'phones.*' => 'string',
  ]);

  $phones = $request->phones;

  // جلب الحجوزات مع بيانات الطبيب والعيادة
  $bookings = Order::whereIn('phone_number', $phones) // تأكد من اسم العمود في قاعدة بياناتك (phone_number أو patient_phone)
   ->with(['doctor', 'clinic'])
   ->orderBy('date', 'desc') // ترتيب من الأحدث للأقدم
   ->get();

  // تحويل البيانات لشكل يفهمه الفرونت إند (Data Mapping)
  $formattedBookings = $bookings->map(function ($booking) {
   return [
    'id' => $booking->id,
    'doctorName' => $booking->doctor->name ?? 'طبيب غير معروف',
    'doctorImage' => $booking->doctor->photo ?? null,
    'doctorSpecialty' => $booking->doctor->specialty ?? '',
    'bookedAt' => $booking->date,
    'branchName' => $booking->clinic->name ?? 'عيادة غير معروفة',
    'consultationFee' => $booking->doctor->consultation_fee ?? 0, // أو من جدول العيادة حسب تصميمك
    'doctorId' => $booking->doctor_id,
    'status' => $booking->status, // (confirmed, pending, etc.)
   ];
  });

  return response()->json([
   'status' => 'success',
   'data' => $formattedBookings
  ]);
 }

 public function cancelBooking($id)
 {
  $booking = Order::findOrFail($id);

  // الحماية: لا يمكن الإلغاء إذا انتهى الكشف بالفعل
  if ($booking->status === 'تم الكشف') {
   return response()->json(['message' => 'لا يمكن إلغاء حجز مكتمل'], 400);
  }

  $booking->update(['status' => 'ملغي']);

  return response()->json([
   'status' => 'success',
   'message' => 'تم إلغاء الحجز بنجاح'
  ]);
 }

 // 3. تأجيل الحجز (تغيير التاريخ)
 public function rescheduleBooking(Request $request, $id)
 {
  $request->validate(['new_date' => 'required|date|after_or_equal:today']);

  $booking = Order::findOrFail($id);
  $booking->update([
   'date' => $request->new_date,
   'status' => 'منتظر' // إعادة الحالة لمنتظر عند التأجيل
  ]);

  return response()->json(['status' => 'success', 'message' => 'تم تأجيل الموعد']);
 }
}
