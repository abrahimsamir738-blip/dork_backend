<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;

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
            'name' => 'sometimes|string|max:255',
            'title' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|string',
        ]);

        $doctor->update($validated);

        return response()->json($doctor->load('clinics'));
    }
}
