<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
 public function login(Request $request)
 {
  try {
   $request->validate([
    'email' => 'required|email',
    'password' => 'required',
   ]);

   // Check if doctor exists with this email
   $doctor = Doctor::where('email', $request->email)->first();

   if (!$doctor) {
    return response()->json([
     'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
     'errors' => ['email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة']]
    ], 401);
   }

   // Verify password
   if (!Hash::check($request->password, $doctor->password)) {
    return response()->json([
     'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
     'errors' => ['email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة']]
    ], 401);
   }

   // Create token
   $token = $doctor->createToken('auth-token')->plainTextToken;

   return response()->json([
    'doctor' => $doctor->load('clinics'),
    'token' => $token,
   ], 200);
  } catch (ValidationException $e) {
   return response()->json([
    'message' => 'التحقق من البيانات فشل',
    'errors' => $e->errors()
   ], 422);
  } catch (\Exception $e) {
   return response()->json([
    'message' => 'حدث خطأ أثناء تسجيل الدخول',
    'error' => $e->getMessage()
   ], 500);
  }
 }

 public function register(Request $request)
 {
  try {
   // 1. التحقق من صحة البيانات
   $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:doctors,email', // التأكد من عدم تكرار البريد
    'password' => 'required|',
   ], [
    'email.unique' => 'البريد الإلكتروني مسجل بالفعل لدينا',
    'email.required' => 'البريد الإلكتروني مطلوب',
   ]);

   // 2. إنشاء حساب الطبيب الجديد
   $doctor = Doctor::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password), // تشفير كلمة المرور

   ]);

   // 3. إنشاء توكن الدخول مباشرة بعد التسجيل (اختياري)
   $token = $doctor->createToken('auth-token')->plainTextToken;

   return response()->json([
    'message' => 'تم إنشاء الحساب بنجاح',
    'doctor' => $doctor,
    'token' => $token,
   ], 201);
  } catch (\Illuminate\Validation\ValidationException $e) {
   return response()->json([
    'message' => 'بيانات التسجيل غير صالحة',
    'errors' => $e->errors()
   ], 422);
  } catch (\Exception $e) {
   return response()->json([
    'message' => 'حدث خطأ أثناء إنشاء الحساب',
    'error' => $e->getMessage()
   ], 500);
  }
 }
 public function createAccount(Request $request)
 {
  $staticData = [
   'name'      => 'Test User',
   'email'     => 'test' . rand(1, 999) . '@example.com', // Randomized to avoid unique constraint errors
   'password'  => bcrypt('password123'),
   'title'     => 'Dr.',
   'specialty' => 'General Medicine',
   'bio'       => 'This is a static test bio.',
   'phone'     => '123-456-7890',
  ];
  $doctor = Doctor::create($staticData);
 }

 public function logout(Request $request)
 {
  $request->user()->currentAccessToken()->delete();

  return response()->json(['message' => 'Logged out successfully']);
 }

 public function me(Request $request)
 {
  return response()->json($request->user()->load('clinics'));
 }

 public function createUser(Request $request)
 {
  $validator = Validator::make($request->all(), [
   'name'     => 'required|string|min:3|max:50',
   'phone'    => [
    'required',
    'regex:/^01[0125][0-9]{8}$/',
    'unique:users,phone'
   ],
   'password' => 'required|string|min:6',
  ], [
   'phone.unique'      => 'هذا الرقم مسجل مسبقاً، جرب تسجيل الدخول.',
   'phone.regex'       => 'يرجى إدخال رقم موبايل مصري صحيح.',
   'password.required' => 'كلمة المرور مطلوبة.',
   'password.min'      => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
  ]);

  if ($validator->fails()) {
   return response()->json([
    'status'  => 'error',
    'message' => $validator->errors()->first()
   ], 422);
  }

  $user = User::create([
   'name'     => $request->name,
   'phone'    => $request->phone,
   'password' => Hash::make($request->password),
  ]);

  $token = $user->createToken('auth-token')->plainTextToken;

  return response()->json([
   'status'  => 'success',
   'message' => 'تم إنشاء الحساب بنجاح',
   'user'    => $user,
   'token'   => $token,
  ], 201);
 }

 public function loginUser(Request $request)
 {
  $validator = Validator::make($request->all(), [
   'phone'    => [
    'required',
    'regex:/^01[0125][0-9]{8}$/',
   ],
   'password' => 'required|string',
  ], [
   'phone.regex'       => 'يرجى إدخال رقم موبايل مصري صحيح.',
   'phone.required'    => 'رقم الموبايل مطلوب.',
   'password.required' => 'كلمة المرور مطلوبة.',
  ]);

  if ($validator->fails()) {
   return response()->json([
    'status'  => 'error',
    'message' => $validator->errors()->first()
   ], 422);
  }

  $user = User::where('phone', $request->phone)->first();

  if (!$user || !Hash::check($request->password, $user->password)) {
   return response()->json([
    'status'  => 'error',
    'message' => 'رقم الموبايل أو كلمة المرور غير صحيحة',
   ], 401);
  }

  $token = $user->createToken('auth-token')->plainTextToken;

  return response()->json([
   'status' => 'success',
   'user'   => $user,
   'token'  => $token,
  ], 200);
 }
}
