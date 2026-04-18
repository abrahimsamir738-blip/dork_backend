<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{
 public function index(Request $request)
 {
  return response()->json($request->user()->load('clinics'));
 }

 public function update(Request $request)
 {
  $doctor = $request->user();

  $validated = $request->validate([
   'name'      => 'sometimes|string|max:255',
   'title'     => 'nullable|string|max:255',
   'specialty' => 'nullable|string|max:255',
   'bio'       => 'nullable|string',
   'photo'     => 'nullable|image',
  ]);

  // لو في صورة اترفعت
  if ($request->hasFile('photo')) {
   // احذف الصورة القديمة لو موجودة
   if ($doctor->photo) {
    $oldPath = str_replace(config('app.url') . '/storage/', '', $doctor->photo);
    Storage::disk('public')->delete($oldPath);
   }

   // خزّن الصورة الجديدة
   $path = $request->file('photo')->store('doctors/photos', 'public');

   // خزّن المسار الكامل مع APP_URL
   $validated['photo'] = config('app.url') . '/storage/' . $path;
  }

  $doctor->update($validated);

  return response()->json($doctor->load('clinics'));
 }
}
