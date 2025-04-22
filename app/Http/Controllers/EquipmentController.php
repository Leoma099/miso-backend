<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\BorrowNotification;
use Illuminate\Support\Facades\Storage;
use App\Imports\EquipmentImport;
use App\Exports\EquipmentExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        $equipments = $query->paginate($request->limit ?? 10000);

        return response()->json($equipments);
    }

    // User: Borrow Equipment (Create Request)
    public function store(Request $request)
    {
        // Ensure user is authenticated
        $user = Auth::user()->load('account');

        if (!$user) {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }

        // Retrieve the user's account
        $account = $user->account;

        if (!$account) {
            return response()->json(['error' => 'No account found for this user.'], 404);
        }

        // Debugging Logs
        Log::info('Authenticated User:', [$user]);
        Log::info('User Account:', [$user->account]);

        $validated = $request->validate([
            'type' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'quantity' => 'required|integer',
            'equipmentStatus' => 'required|string',
            'property_number' => 'required|string',
            'serial_number' => 'required|string',
            'photo' => 'nullable|mimes:jpeg,png,jpg,gif,bmp,svg,webp,avif|max:10240', // max 10MB
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
            'quantity' => $request->quantity,
            'equipmentStatus' => $request->equipmentStatus,
            'photo' => $photoPath, // ✅ Now assigned correctly
        ]);

        // Get Admin Roles
        $adminRoleUsers = User::where('role', 1)->get();

        foreach ($adminRoleUsers as $adminRoleUser) {

            // Check if account exists before accessing 'id'
            if ($adminRoleUser->account) {
                // Create Borrow Notification
                BorrowNotification::create([
                    'notified_to' => $adminRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' =>  ' you added new equipment ' .  $equipment->property_number,
                    'data' => json_encode([
                        'module_type' => get_class($equipment),
                        'module_id' => $equipment->id,
                        'is_read' => 0,
                        'created_by' => Auth::id()
                    ])
                ]);
            } else {
                Log::warning('No account associated with admin user ID: ' . $adminRoleUser->id);
            }
        }
    
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
            'quantity' => $request->quantity,
            'equipmentStatus' => $request->equipmentStatus,
            'photo' => $photoPath,
        ]);

        // Get Admin Roles
        $adminRoleUsers = User::where('role', 1)->get();

        foreach ($adminRoleUsers as $adminRoleUser) {

            // Check if account exists before accessing 'id'
            if ($adminRoleUser->account) {
                // Create Borrow Notification
                BorrowNotification::create([
                    'notified_to' => $adminRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' =>  ' you updated the equipment status ' . $equipment->property_number,
                    'data' => json_encode([
                        'module_type' => get_class($equipment),
                        'module_id' => $equipment->id,
                        'is_read' => 0,
                        'created_by' => Auth::id()
                    ])
                ]);
            } else {
                Log::warning('No account associated with admin user ID: ' . $adminRoleUser->id);
            }
        }
    
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

    // Import Excel
    public function import(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded.'], 400);
        }
    
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt',
        ]);
    
        // Perform the import
        Excel::import(new EquipmentImport, $request->file('file'));
    
        // Get Admin Roles for Notification
        $adminRoleUsers = User::where('role', 1)->get();
    
        foreach ($adminRoleUsers as $adminRoleUser) {
    
            // Check if account exists before accessing 'id'
            if ($adminRoleUser->account) {
                // Create Borrow Notification for Equipment Import
                BorrowNotification::create([
                    'notified_to' => $adminRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' => 'You imported equipment lists.',
                    'data' => json_encode([
                        'module_type' => 'Equipment',
                        'module_id' => null, // No specific equipment ID for import
                        'is_read' => 0,
                        'created_by' => Auth::id()
                    ])
                ]);
            } else {
                Log::warning('No account associated with admin user ID: ' . $adminRoleUser->id);
            }
        }
    
        return response()->json(['message' => 'Equipment imported successfully!']);
    }

    // Export Excel
    public function export()
    {

        return Excel::download(new EquipmentExport, 'equipment-list.xlsx', \Maatwebsite\Excel\Excel::XLSX);

    }
}
