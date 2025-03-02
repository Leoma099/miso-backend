<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use Illuminate\Http\Request;
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
        $user = Auth::user();

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
            'equipment_id' => 'required|exists:equipment,id',
            'full_name' => 'required',
            'type' => 'required',
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
            'type' => $request->type,
            'office_name' => $request->office_name,
            'office_address' => $request->office_address,
            'position' => $request->position,
            'mobile_number' => $request->mobile_number,
            'purpose' => $request->purpose,
            'status' => 1,
            'date_borrow' => $request->date_borrow,
            'date_return' => $request->date_return,
        ]);

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
        $borrow = Borrow::findOrFail($id);
        $borrow->update(['status' => $request->status]); // approved, rejected, returned
        return response()->json(['message' => 'Borrow status updated']);
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
};