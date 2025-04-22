<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();
    
        if ($request->has('search')) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('office_name', 'LIKE', "%$search%") // Fixed missing wildcard
                  ->orWhere('office_address', 'LIKE', "%$search%");
            });
        }
    
        $departments = $query->get();
    
        return response()->json($departments);
    }
    

    public function store(Request $request)
    {
        $request->validate([
            'office_name' => 'required',
            'office_address' => 'required',
        ]);

        $department = Department::create([
            'office_name' => $request->office_name,
            'office_address' => $request->office_address,
            'tell_number' => $request->tell_number,
            'fax_number' => $request->fax_number,
        ]);

        return response()->json(['message' => 'Department request submitted successfully', 'department' => $department], 201);
    }

    public function show($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        return response()->json($department);
    }

    public function update()
    {}

    public function destroy()
    {}
}
