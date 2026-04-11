<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinic;

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
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'consultation_fee' => 'required|numeric|min:0',
            'working_hours' => 'required|string|max:255',
            'max_patients_per_day' => 'required|integer|min:1',
            'photo' => 'nullable|string',
            'map_link' => 'nullable|string',
        ]);

        $clinic = $request->user()->clinics()->create($validated);

        return response()->json($clinic, 201);
    }

    public function update(Request $request, $id)
    {
        $clinic = $request->user()->clinics()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'consultation_fee' => 'sometimes|numeric|min:0',
            'working_hours' => 'sometimes|string|max:255',
            'max_patients_per_day' => 'sometimes|integer|min:1',
            'current_serving_number' => 'sometimes|integer|min:0',
            'is_closed_today' => 'sometimes|boolean',
            'photo' => 'nullable|string',
            'map_link' => 'nullable|string',
        ]);

        $clinic->update($validated);

        return response()->json($clinic);
    }

    public function destroy(Request $request, $id)
    {
        $clinic = $request->user()->clinics()->findOrFail($id);
        $clinic->delete();

        return response()->json(['message' => 'Clinic deleted successfully']);
    }
}
