<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Borrow;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\BorrowNotification;
use App\Imports\BorrowImport;
use App\Exports\BorrowExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BorrowController extends Controller
{
    // List all borrow requests (admin only)
    public function index(Request $request)
    {
        $user = $request->user(); // Get authenticated user
    
        // Ensure the user has an account before querying
        if ($user->role === 1) {
            // Admin sees all borrow records
            $borrow = Borrow::with(['equipment', 'account.user'])->get();

            $query = Borrow::query();

            if($request->has('search')) {
                $search = $request->search;
    
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'LIKE', "%$search%")
                    ->orWhere('office_name', 'LIKE', "%$search%")
                    ->orWhere('full_name', 'LIKE', "%$search%")
                    ->orWhere('status', 'LIKE', "%$search%");
                });
            }
    
            $borrow = $query->get();

        } else {
            // Ensure the user has an account
            if (!$user->account) {
                return response()->json(['error' => 'No account associated with this user.'], 404);
            }
    
            // Client user sees only their own borrow records
            $borrow = Borrow::with('equipment')
                ->where('account_id', $user->account->id)
                ->get();
        }
    
        return response()->json($borrow);
    }
    
    // Store a new borrow request (user submits request)
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

        $request->validate([
            'full_name' => 'required',
            'id_number' => 'required',
            'type' => 'required',
            'brand' => 'required',
            'model' => 'required',
            'property_number' => 'required',
            'office_name' => 'required',
            'office_address' => 'required',
            'position' => 'required',
            'mobile_number' => 'required',
            'date_borrow' => 'required|date',
            'date_return' => 'required|date|after_or_equal:date_borrow',
        ]);

        $borrow = Borrow::create([
            'account_id' => $user->account->id,
            'equipment_id' => $request->equipment_id, // FIXED LINE
            'full_name' => $request->full_name,
            'id_number' => $request->id_number,
            'type' => $request->type,
            'model' => $request->model,
            'property_number' => $request->property_number,
            'brand' => $request->brand,
            'office_name' => $request->office_name,
            'office_address' => $request->office_address,
            'position' => $request->position,
            'mobile_number' => $request->mobile_number,
            'purpose' => $request->purpose,
            'status' => 1,
            'date_borrow' => $request->date_borrow,
            'date_return' => $request->date_return,

            'agent' => $request->agent,
            'date' => $request->date,
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
                    'message' => $borrow->full_name . ' requested a borrow equipment ' . $borrow->property_number,
                    'data' => json_encode([
                        'module_type' => get_class($borrow),
                        'module_id' => $borrow->id,
                        'is_read' => 0,
                        'created_by' => Auth::id()
                    ])
                ]);
            } else {
                Log::warning('No account associated with admin user ID: ' . $adminRoleUser->id);
            }
        }

        // Get Client Roles
        $clientRoleUsers = User::where('role', 2)->get();

        foreach ($clientRoleUsers as $clientRoleUser) {

            // Check if account exists before accessing 'id'
            if ($clientRoleUser->account) {
                // Create Borrow Equipment Notification
                BorrowNotification::create([
                    'notified_to' => $clientRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' => 'you requested borrow equipment'  . $borrow->property_number ,
                    'data' => json_encode([
                        'module_type' => get_class($borrow),
                        'module_id' => $borrow->id,
                        'is_read' => 0,
                        'created_by' => Auth::id()
                    ])
                ]);
            } else {
                Log::warning('No account associated with admin user ID: ' . $clientRoleUser->id);
            }
        }

        return response()->json(['message' => 'Borrow request submitted successfully', 'borrow' => $borrow], 201);
    }

    // Store a new borrow request (user submits request)
    public function storeWalkin(Request $request)
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

        $request->validate([
            'full_name' => 'required',
            'id_number' => 'required',
            'type' => 'required',
            'brand' => 'required',
            'model' => 'required',
            'property_number' => 'required',
            'office_name' => 'required',
            'office_address' => 'required',
            'position' => 'required',
            'mobile_number' => 'required',
            'date_borrow' => 'required|date',
            'date_return' => 'required|date|after_or_equal:date_borrow',
        ]);

        $borrow = Borrow::create([
            'full_name' => $request->full_name,
            'id_number' => $request->id_number,
            'type' => $request->type,
            'model' => $request->model,
            'property_number' => $request->property_number,
            'brand' => $request->brand,
            'office_name' => $request->office_name,
            'office_address' => $request->office_address,
            'position' => $request->position,
            'mobile_number' => $request->mobile_number,
            'purpose' => $request->purpose,
            'status' => 1,
            'date_borrow' => $request->date_borrow,
            'date_return' => $request->date_return,

            'agent' => $request->agent,
            'date' => $request->date,
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
                    'message' => $borrow->full_name . ' requested a borrow equipment ' . $borrow->property_number,
                    'data' => json_encode([
                        'module_type' => get_class($borrow),
                        'module_id' => $borrow->id,
                        'is_read' => 0,
                        'created_by' => Auth::id()
                    ])
                ]);
            } else {
                Log::warning('No account associated with admin user ID: ' . $adminRoleUser->id);
            }
        }

        // Get Client Roles
        $clientRoleUsers = User::where('role', 2)->get();

        foreach ($clientRoleUsers as $clientRoleUser) {

            // Check if account exists before accessing 'id'
            if ($clientRoleUser->account) {
                // Create Borrow Equipment Notification
                BorrowNotification::create([
                    'notified_to' => $clientRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' => 'you requested borrow equipment'  . $borrow->property_number ,
                    'data' => json_encode([
                        'module_type' => get_class($borrow),
                        'module_id' => $borrow->id,
                        'is_read' => 0,
                        'created_by' => Auth::id()
                    ])
                ]);
            } else {
                Log::warning('No account associated with admin user ID: ' . $clientRoleUser->id);
            }
        }

        return response()->json(['message' => 'Borrow request submitted successfully', 'borrow' => $borrow], 201);
    }

    // Show specific borrow request (for user/admin)
    public function show($id)
    {
        $borrow = Borrow::findOrFail($id);
        return response()->json($borrow);
    }

    // Admin updates borrow status (approve/reject)
    public function update(Request $request, $id)
    {
        // Find the existing borrow record
        $borrow = Borrow::findOrFail($id);
    
        // Update the borrow record
        $borrow->update([
            'full_name' => $request->full_name,
            'id_number' => $request->id_number,
            'type' => $request->type,
            'model' => $request->model,
            'brand' => $request->brand,
            'office_name' => $request->office_name,
            'office_address' => $request->office_address,
            'position' => $request->position,
            'mobile_number' => $request->mobile_number,
            'purpose' => $request->purpose,
            'status' => $request->status,
            'date_borrow' => $request->date_borrow,
            'date_return' => $request->date_return,

            'agent' => $request->agent,
            'date' => $request->date,
        ]);
        
        return response()->json(['message' => 'Borrow updated successfully']);
    }

    // Admin deletes a borrow record
    public function destroy($id)
    {
        Borrow::findOrFail($id)->delete();
        return response()->json(['message' => 'Borrow record deleted']);
    }

    public function getBorrowStatistics()
    {
        // Count the borrows based on status (1 = Pending, 2 = Approved, 3 = Returned)
        $pendingCount = Borrow::where('status', 1)->count();
        $approvedCount = Borrow::where('status', 2)->count();
        $returnedCount = Borrow::where('status', 3)->count();

        return response()->json([
            'pending' => $pendingCount,
            'approved' => $approvedCount,
            'returned' => $returnedCount,
        ]);
    }

    // Get record borrow
    public function getRecordBorrower(Request $request)
    {
        $query = Borrow::with(['equipment', 'account.user']);
    
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date_borrow', [$request->start_date, $request->end_date]);
        }
    
        if ($request->has('office_name') && !empty($request->office_name)) {
            $query->whereHas('account', function ($q) use ($request) {
                $q->where('office_name', $request->office_name);
            });
        }
    
        if ($request->has('full_name') && !empty($request->full_name)) {
            $query->whereHas('account', function ($q) use ($request) {
                $q->where('full_name', $request->full_name);
            });
        }
    
        if ($request->has('property_number') && !empty($request->property_number)) {
            $query->where('property_number', 'LIKE', '%' . $request->property_number . '%');
        }
    
        if ($request->has('type') && !empty($request->type)) {
            $query->whereHas('equipment', function ($q) use ($request) {
                $q->where('type', 'LIKE', '%' . $request->type . '%');
            });
        }
    
        $records = $query->get();
    
        return response()->json([
            'request' => $request->all(),
            'query' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'data' => $records
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
    
        Excel::import(new BorrowImport, $request->file('file'));

        return response()->json(['message' => 'Borrow imported successfully!']);
    }

    // Export Excel
    public function export()
    {
        return Excel::download(new BorrowExport, 'borrow-list.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    // Number of Department Borrow
    public function numberOfDepartmentBorrow()
    {
        $borrowCounts = Borrow::with('account')
            ->selectRaw('account_id, count(*) as borrow_count')
            ->groupBy('account_id')
            ->get()
            ->map(function ($borrow) {
                return [
                    'office_name' => optional($borrow->account)->office_name ?? 'Unknown',
                    'borrow_count' => $borrow->borrow_count,
                ];
            });

        return response()->json($borrowCounts);
    }

    // Number of Equipment Borrow (grouped by 'type')
    public function numberOfEquipmentBorrow()
    {
        $borrowCounts = Borrow::selectRaw('type, COUNT(*) as borrow_count')
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'borrow_count' => $item->borrow_count,
                ];
            });

        return response()->json($borrowCounts);
    }


    public function equipmentAvailable(Request $request, $id)
    {
        $borrow = Borrow::findOrFail($id);

        // Validate the request
        $request->validate([
            'availability' => 'required|in:1,2', // 1 = Available, 2 = Not Available
        ]);

        // Update the equipment availability
        $borrow->equipment->update([
            'availability' => $request->availability
        ]);

        return response()->json(['message' => 'Equipment availability updated successfully']);
    }

    public function getReturnedBorrow()
    {
        $returnedBorrowCount = Borrow::where('status', 3)->count(); // status 3 = Returned

        return response()->json([
            'returned' => $returnedBorrowCount,
        ]);
    }

    // Client change status to return
    public function markAsReturned($id)
    {
        $borrow = Borrow::findOrFail($id);
        $borrow->status = 3; // 3 means Returned
        $borrow->save();
    
        return response()->json(['message' => 'Marked as returned']);
    }
    
    // List of Pending Equipment Borrow
    public function getPendingBorrow()
    {
        try {
            $pendingBorrows = Borrow::with(['equipment', 'account.user'])
                ->where('status', 1) // Status 1 = Pending
                ->get();
    
            return response()->json([
                'message' => 'Pending borrows retrieved successfully.',
                'data' => $pendingBorrows
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching pending borrows: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Failed to retrieve pending borrows.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

};