<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TimeSlot;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = TimeSlot::where('doctor_id', $request->user()->id);

        if ($request->has('clinicId')) {
            $query->where('clinic_id', $request->clinicId);
        }

        $slots = $query->with('clinic')->orderBy('day_of_week')->orderBy('start_time')->get();

        return response()->json($slots);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'day_name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration' => 'required|integer|min:1',
            'type' => 'required|in:كشف,استشارة',
            'capacity' => 'required|integer|min:1',
        ]);

        $validated['doctor_id'] = $request->user()->id;
        $validated['booked_count'] = 0;

        $slot = TimeSlot::create($validated);

        return response()->json($slot->load('clinic'), 201);
    }

    public function update(Request $request, $id)
    {
        $slot = TimeSlot::where('doctor_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'day_of_week' => 'sometimes|integer|min:0|max:6',
            'day_name' => 'sometimes|string|max:255',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'duration' => 'sometimes|integer|min:1',
            'type' => 'sometimes|in:كشف,استشارة',
            'capacity' => 'sometimes|integer|min:1',
        ]);

        $slot->update($validated);

        return response()->json($slot->load('clinic'));
    }

    public function destroy(Request $request, $id)
    {
        $slot = TimeSlot::where('doctor_id', $request->user()->id)->findOrFail($id);
        $slot->delete();

        return response()->json(['message' => 'Time slot deleted successfully']);
    }
}
