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
        $query = Account::with('user');
    
        if ($request->has('search')) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%$search%");
            });
        }
    
        $accounts = $query->paginate($request->limit ?? 10000);
    
        return response()->json($accounts);
    }
    

    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:accounts',
            'address' => 'required',
            'mobile_number' => 'required',
            'id_number' => 'required',
            'position' => 'required',
            'office_name' => 'required',
            'office_address' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required',
        ]);

         // Create the user first
         $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Create the account and link it to the user
        $account = Account::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'address' => $request->address,
            'id_number' => $request->id_number,
            'office_name' => $request->office_name,
            'office_address' => $request->office_address,
            'mobile_number' => $request->mobile_number,
            'position' => $request->position,
        ]);

        return response()->json([
            'message' => 'Account and user created successfully',
            'account' => $account,
        ]);
    }

    public function show($id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        return response()->json($account);
    }

    public function update(Request $request, Account $account)
    {
        $account->update($request->all());
        return response()->json($account);
    }

    public function destroy($id)
    {
        Account::findOrFail($id)->delete();
        return response()->json(['message' => 'Account record deleted']);
    }

    public function clientDataInfo()
    {
        $client = Account::with('user')
        ->whereHas('user', function ($query) {
            $query->where('role', 2);
        })
        ->get();

        return response()->json($client);
    }
}

