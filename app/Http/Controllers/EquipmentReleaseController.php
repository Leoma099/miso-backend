<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EquipmentRelease;
use App\Models\Equipment;
use App\Models\BorrowNotification;
use App\Imports\BorrowImport;
use App\Exports\BorrowExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EquipmentReleaseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // Get authenticated user
    
        // Ensure the user has an account before querying
        if ($user->role === 1) {
            // Admin sees all equipmentRelease records
            $equipmentRelease = EquipmentRelease::with(['borrow', 'account.user'])->get();

            $query = EquipmentRelease::query();

            if($request->has('search')) {
                $search = $request->search;
    
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'LIKE', "%$search%")
                    ->orWhere('office_name', 'LIKE', "%$search%")
                    ->orWhere('full_name', 'LIKE', "%$search%");
                });
            }
    
            $equipmentRelease = $query->get();

        } else {
            // Ensure the user has an account
            if (!$user->account) {
                return response()->json(['error' => 'No account associated with this user.'], 404);
            }
    
            // Client user sees only their own equipmentRelease records
            $equipmentRelease = EquipmentRelease::with('equipment')
                ->where('account_id', $user->account->id)
                ->get();
        }
    
        return response()->json($equipmentRelease);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->load('account');
    }
}
