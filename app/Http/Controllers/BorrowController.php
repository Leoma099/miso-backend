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
use App\Exports\BorrowRecordExport;
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
            'full_name' => 'required|string',
            'id_number' => 'required|string',
            'type' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'property_number' => 'required|string',
            'office_name' => 'required|string',
            'office_address' => 'required|string',
            'position' => 'required|string',
            'mobile_number' => 'required|string',
            'date_borrow' => 'required|date',
            'date_return' => 'required|date|after_or_equal:date_borrow',
        ]);

        $equipment = Equipment::where('property_number', $request->property_number)->first();

        $borrow = Borrow::create([
            'account_id' => $user->account->id,
            'equipment_id' => $equipment->id, // FIXED LINE
            'full_name' => $request->full_name,
            'id_number' => $request->id_number,
            'type' => $request->type,
            'model' => $request->model,
            'quantity' => $request->quantity,
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

        $adminRoleUsers = User::where('role', 1)->get();
        foreach ($adminRoleUsers as $adminRoleUser)
        {
            if ($adminRoleUser->account) {
                BorrowNotification::create([
                    'notified_to' => $adminRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' => "{$borrow->full_name} requested a borrow equipment ({$borrow->property_number})",
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

        $clientRoleUsers = User::where('role', 2)->get();
        foreach ($clientRoleUsers as $clientRoleUser)
        {
            if ($clientRoleUser->account) {
                BorrowNotification::create([
                    'notified_to' => $clientRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' => "you requested borrow equipment ({$borrow->property_number})",
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
        $request->validate([
            'full_name' => 'required|string',
            'id_number' => 'required|string',
            'type' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'property_number' => 'required|string',
            'office_name' => 'required|string',
            'office_address' => 'required|string',
            'position' => 'required|string',
            'mobile_number' => 'required|string',
            'date_borrow' => 'required|date',
            'date_return' => 'required|date|after_or_equal:date_borrow',
        ]);

        $equipment = Equipment::where('property_number', $request->property_number)->first();

        if ($equipment) {
            if ($equipment->quantity >= $request->quantity) {
                $equipment->quantity -= $request->quantity;
                $equipment->save();
            } else {
                return response()->json(['error' => 'Not enough stock available.'], 400);
            }
        }

        $borrow = Borrow::create([
            'equipment_id' => $equipment->id, // FIXED LINE
            'full_name' => $request->full_name,
            'id_number' => $request->id_number,
            'type' => $request->type,
            'model' => $request->model,
            'quantity' => $request->quantity,
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

        if ($equipment) {
            $borrow->equipment_id = $equipment->id;
            $borrow->save();
        }

        $adminRoleUsers = User::where('role', 1)->get();
        foreach ($adminRoleUsers as $adminRoleUser)
        {
            if ($adminRoleUser->account) {
                BorrowNotification::create([
                    'notified_to' => $adminRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' => "{$borrow->full_name} requested to borrow equipment ({$borrow->property_number})",
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

        $adminRoleUsers = User::where('role', 1)->get();
        foreach ($adminRoleUsers as $adminRoleUser)
        {
            if ($adminRoleUser->account) {
                BorrowNotification::create([
                    'notified_to' => $adminRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' => "you update the borrow equipment ({$borrow->property_number})",
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
        
        return response()->json(['message' => 'Borrow updated successfully']);
    }

    // Admin deletes a borrow record
    public function destroy($id)
    {
        // Get the borrow record first
        $borrow = Borrow::findOrFail($id);

        // Store values before deletion
        $propertyNumber = $borrow->property_number;
        $borrowId = $borrow->id;
        $borrowClass = get_class($borrow);

        // Delete the record
        $borrow->delete();

        $adminRoleUsers = User::where('role', 1)->get();
        foreach ($adminRoleUsers as $adminRoleUser)
        {
            if ($adminRoleUser->account) {
                BorrowNotification::create([
                    'notified_to' => $adminRoleUser->account->id,
                    'notified_by' => Auth::user()->account->id,
                    'message' => "You deleted the borrow equipment ({$propertyNumber})",
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

        return response()->json(['message' => 'Borrow record deleted']);
    }

    public function getBorrowStatistics()
    {
        // Count the borrows based on status (1 = Pending, 2 = Approved, 3 = Returned)
        $pendingCount = Borrow::where('status', 1)->count();
        $approvedCount = Borrow::where('status', 2)->count();
        $declinedCount = Borrow::where('status', 3)->count();
        $recievedCount = Borrow::where('status', 4)->count();
        $returnedCount = Borrow::where('status', 5)->count();

        return response()->json([
            'pending' => $pendingCount,
            'approved' => $approvedCount,
            'declined' => $declinedCount,
            'recieved' => $recievedCount,
            'returned' => $returnedCount,
        ]);
    }

    // Get record borrow
    public function getRecordBorrower(Request $request)
    {
        $query = Borrow::with(['equipment', 'account.user']);
    
        if (!empty($request->date_borrow) && !empty($request->date_return)) {
            $query->whereBetween('date_borrow', [$request->date_borrow, $request->date_return]);
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

    public function borrowRecordExport(Request $request)
    {
        $filters = $request->only([
            'date_borrow',
            'date_return',
            'office_name',
            'full_name',
            'property_number',
            'type',
        ]);
    
        // Generate the filename with the current date
        $filename = 'borrow-list-record-' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(new BorrowRecordExport($filters), $filename, \Maatwebsite\Excel\Excel::XLSX);
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
        $returnedBorrowCount = Borrow::where('status', 4)->count(); // status 4 = Returned

        return response()->json([
            'returned' => $returnedBorrowCount,
        ]);
    }

    // Client change status to return
    public function markAsReturned($id)
    {
        $borrow = Borrow::findOrFail($id);
        $borrow->status = 4; // 4 means Returned
        $borrow->save();
    
        return response()->json(['message' => 'Marked as recieved']);
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

    public function approve($id)
    {
        $borrow = Borrow::findOrFail($id);

        // Check if equipment has enough stock
        $equipment = Equipment::where('property_number', $borrow->property_number)->first();
        if (!$equipment || $equipment->quantity < $borrow->quantity) {
            return response()->json(['error' => 'Not enough stock available.'], 400);
        }

        // Deduct the quantity
        $equipment->quantity -= $borrow->quantity;
        $equipment->save();

        // Update the borrow status
        $borrow->status = 2; // Approved
        $borrow->save();

        if($borrow->account_id)
        {

            BorrowNotification::create([
                'notified_to' => $borrow->account_id,
                'notified_by' => Auth::user()->account->id,
                'message' => "Your borrow request ({$borrow->property_number}) has been approved.",
                'data' => json_encode([
                    'module_type' => get_class($borrow),
                    'module_id' => $borrow->id,
                    'is_read' => 0,
                    'created_by' => Auth::id()
                ])
            ]);

        }
        else
        {

            $adminRoleUsers = User::where('role', 1)->get();
            foreach ($adminRoleUsers as $admin)
            {
                if ($admin->account)
                {
                    BorrowNotification::create([
                        'notified_to' => $admin->account->id,
                        'notified_by' => Auth::user()->account->id ?? null,
                        'message' => "You approved {$borrow->full_name} ID Number: ({$borrow->id_number}).",
                        'data' => json_encode([
                            'module_type' => get_class($borrow),
                            'module_id' => $borrow->id,
                            'is_read' => 0,
                            'created_by' => Auth::id(),
                            'borrower_name' => $borrow->full_name,
                        ])
                    ]);
                }
            }

        }

        return response()->json(['message' => 'Borrow request approved and quantity updated.']);
    }

    public function decline(Request $request, $id)
    {
        $borrow = Borrow::findOrFail($id);
    
        $borrow->status = 3; // 3 = Declined
        $borrow->save();
    
        // Notify the user who requested the borrow
        $notifiedTo = $borrow->account_id;
    
        if ($notifiedTo) {
            BorrowNotification::create([
                'notified_to' => $notifiedTo,
                'notified_by' => Auth::user()->account->id ?? null,
                'message' => "Your borrow request for {$borrow->property_number} has been declined.",
                'data' => json_encode([
                    'module_type' => get_class($borrow),
                    'module_id' => $borrow->id,
                    'is_read' => 0,
                    'created_by' => Auth::id(),
                ]),
            ]);
        }
    
        return response()->json([
            'message' => 'Borrow request declined successfully.',
            'borrow' => $borrow
        ], 200);
    }

    public function returned(Request $request, $id)
    {
        $borrow = Borrow::findOrFail($id);
    
        // Update status to returned
        $borrow->status = 5; // Returned
        $borrow->save();
    
        // Return the equipment quantity back to inventory
        $equipment = Equipment::where('property_number', $borrow->property_number)->first();
        if ($equipment) {
            $equipment->quantity += $borrow->quantity;
            $equipment->save();
        }
    
        // Notify the user
        BorrowNotification::create([
            'notified_to' => $borrow->account_id,
            'notified_by' => Auth::user()->account->id,
            'message' => "Your borrowed item ({$borrow->property_number}) has been marked as returned.",
            'data' => json_encode([
                'module_type' => get_class($borrow),
                'module_id' => $borrow->id,
                'is_read' => 0,
                'created_by' => Auth::id(),
            ])
        ]);
    
        return response()->json([
            'message' => 'Item successfully marked as returned and quantity updated.',
            'borrow' => $borrow
        ]);
    }

};