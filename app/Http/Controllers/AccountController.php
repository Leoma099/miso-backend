<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index(Request $request) // Make sure to include Request $request
    {
        $query = Account::with('user'); // Eager load the user relationship
    
        if ($request->has('search')) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            });
        }
    
        $accounts = $query->paginate($request->limit ?? 15); // Paginate results
    
        return response()->json($accounts); // Return paginated accounts with user relationship
    }
    

    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:accounts',
            'address' => 'required',
            'mobile_number' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:1,2', // 1 => Admin, 2 => User
        ]);

         // Create the user first
         $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'locked' => 0, // Default is Active (0)
        ]);

        // Create the account and link it to the user
        $account = Account::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'mobile_number' => $request->mobile_number,
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'Account and user created successfully',
            'account' => $account,
        ]);
    }

    public function show(Account $account)
    {
        return response()->json($account->load('user'));
    }

    public function update(Request $request, Account $account)
    {
        $account->update($request->all());
        return response()->json($account);
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return response()->json(['message' => 'Account deleted successfully']);
    }
}

