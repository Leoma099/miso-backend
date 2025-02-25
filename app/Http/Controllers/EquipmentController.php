<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;

class EquipmentController extends Controller
{
    // Admin: View all equipment
    public function index(Request $request)
    {
        $query = Equipment::query();

        if ($request->has('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('equipment_type', 'LIKE', "%$search%")
                ->orWhere('brand', 'LIKE', "%$search%")
                ->orWhere('model', 'LIKE', "%$search%");
            });
        }

        $equipments = $query->paginate($request->limit ?? 15);

        return response()->json($equipments);
    }

    // User: Borrow Equipment (Create Request)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_type' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'condition' => 'required|integer',
            'availability' => 'required|integer',
            'status' => 'required|integer',
            'registered_date' => 'required|date',
        ]);
    
        $equipment = Equipment::create($validated);
    
        return response()->json(['message' => 'Equipment created successfully!', 'data' => $equipment], 201);
    }

    // Show specific equipment
    public function show($id)
    {
        $equipment = Equipment::findOrFail($id);
        if (!$equipment) {
            return response()->json(['message' => 'Equipment not found'], 404);
        }
        return response()->json($equipment);
    }

    // Admin: Update Equipment
    public function update(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);
        if (!$equipment) return response()->json(["message" => "Not Found"], 404);

        $equipment->update($request->all());
        return response()->json($equipment, 200);
    }

    // Admin: Delete Equipment
    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        if (!$equipment) return response()->json(["message" => "Not Found"], 404);

        $equipment->delete();
        return response()->json(["message" => "Deleted successfully"], 200);
    }
}
