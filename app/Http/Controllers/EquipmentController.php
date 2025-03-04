<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    // Admin: View all equipment
    public function index(Request $request)
    {
        $query = Equipment::query();

        if ($request->has('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('type', 'LIKE', "%$search%")
                ->orWhere('brand', 'LIKE', "%$search%")
                ->orWhere('model', 'LIKE', "%$search%")
                ->orWhere('property_number', 'LIKE', "%$search%");                                                                                                 
            });
        }

        $equipments = $query->paginate($request->limit ?? 15);

        return response()->json($equipments);
    }

    // User: Borrow Equipment (Create Request)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'condition' => 'required|integer',
            'availability' => 'required|integer',
            'registered_date' => 'required|date',
            'property_number' => 'required|string',
            'serial_number' => 'required|string',
            'photo' => 'nullable|image|max:2048',
        ]);
    
        // ✅ Store photo before inserting data
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('uploads/photos', 'public');
        }
    
        // ✅ Now store equipment
        $equipment = Equipment::create([
            'property_number' => $request->property_number,
            'serial_number' => $request->serial_number,
            'type' => $request->type,
            'brand' => $request->brand,
            'model' => $request->model,
            'condition' => $request->condition,
            'availability' => $request->availability,
            'registered_date' => $request->registered_date,
            'photo' => $photoPath, // ✅ Now assigned correctly
        ]);
    
        return response()->json(['message' => 'Equipment created successfully!', 'data' => $equipment], 201);
    }    

    // Show specific equipment
    public function show($id)
    {
        $equipment = Equipment::find($id);

        if (!$equipment) {
            return response()->json(['error' => 'Equipment not found'], 404);
        }

        return response()->json($equipment);
    }

    // Admin: Update Equipment
    public function update(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);
    
        if (!$equipment) {
            return response()->json(["message" => "Not Found"], 404);
        }
    
        // Handle File Upload if a new photo is uploaded
        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($equipment->photo) {
                Storage::disk('public')->delete($equipment->photo);
            }
            // Store new photo
            $photoPath = $request->file('photo')->store('uploads/photos', 'public');
        } else {
            // Keep existing photo if no new one is uploaded
            $photoPath = $equipment->photo;
        }
    
        // Update Equipment
        $equipment->update([
            'property_number' => $request->property_number,
            'serial_number' => $request->serial_number,
            'type' => $request->type,
            'brand' => $request->brand,
            'model' => $request->model,
            'condition' => $request->condition,
            'availability' => $request->availability,
            'registered_date' => $request->registered_date,
            'photo' => $photoPath,
        ]);
    
        return response()->json(["message" => "Updated successfully", "data" => $equipment], 200);
    }    

    // Admin: Delete Equipment
    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        if (!$equipment) return response()->json(["message" => "Not Found"], 404);

        $equipment->delete();
        return response()->json(["message" => "Deleted successfully"], 200);
    }

    // Function to get equipment availability statistics
    public function getAvailabilityStats()
    {
        // Count the number of available equipment (status = 1) and not available equipment (status = 2)
        $available = Equipment::where('availability', 1)->count();
        $notAvailable = Equipment::where('availability', 2)->count();

        // Return the data in a response
        return response()->json([
            'available' => $available,
            'notAvailable' => $notAvailable
        ]);
    }
}
