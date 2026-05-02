<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinic;
use Illuminate\Support\Facades\Storage;

class ClinicController extends Controller
{
 public function index(Request $request)
 {
  $clinics = $request->user()->clinics()->with(['timeSlots' => function ($query) {
   $query->orderBy('day_of_week', 'asc')
    ->orderBy('start_time', 'asc');
  }])->get();
  return response()->json($clinics);
 }

 public function store(Request $request)
 {
  $validated = $request->validate([
   'name'                 => 'required|string|max:255',
   'address'              => 'required|string|max:255',
   'consultation_fee'     => 'required|numeric|min:0',
   'working_hours'        => 'required|string|max:255',
   'max_patients_per_day' => 'required|integer|min:1',
   'photo'                => 'nullable|image',
   'map_link'             => 'nullable|string',
  ]);

  if ($request->hasFile('photo')) {
   $path = $request->file('photo')->store('clinics', 'public');
   $validated['photo'] = config('app.url') . Storage::url($path); // /storage/clinics/filename.jpg
  }

  $clinic = $request->user()->clinics()->create($validated);

  return response()->json($clinic, 201);
 }

 public function update(Request $request, $id)
 {
  $clinic = $request->user()->clinics()->findOrFail($id);

  $validated = $request->validate([
   'name'                    => 'sometimes|string|max:255',
   'address'                 => 'sometimes|string|max:255',
   'consultation_fee'        => 'sometimes|numeric|min:0',
   'working_hours'           => 'sometimes|string|max:255',
   'max_patients_per_day'    => 'sometimes|integer|min:1',
   'current_serving_number'  => 'sometimes|integer|min:0',
   'is_closed_today'         => 'sometimes|boolean',
   'photo'                   => 'nullable|image',
   'map_link'                => 'nullable|string',
  ]);

  if ($request->hasFile('photo')) {
   if ($clinic->photo) {
    $oldPath = str_replace('/storage/', 'public/', $clinic->photo);
    Storage::delete($oldPath);
   }

   $path = $request->file('photo')->store('clinics', 'public');
   $validated['photo'] = Storage::url($path);
  }

  $clinic->update($validated);

  return response()->json($clinic);
 }

 public function destroy(Request $request, $id)
 {
  $clinic = $request->user()->clinics()->findOrFail($id);

  // حذف الصورة من السيرفر
  if ($clinic->photo) {
   $oldPath = str_replace('/storage/', 'public/', $clinic->photo);
   Storage::delete($oldPath);
  }

  $clinic->delete();

  return response()->json(['message' => 'Clinic deleted successfully']);
 }
}
