<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // Get authenticated user
    
        if ($user->role === 1) {
            // Admin sees all calendar records
            $query = Calendar::with(['account.user']);
    
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('event', 'LIKE', "%$search%");
            }
    
            $calendar = $query->get();
        } else {
            // Ensure the user has an account
            if (!$user->account) {
                return response()->json(['error' => 'No account associated with this user.'], 404);
            }
    
            // Client user sees only their own calendar records
            $query = Calendar::with(['account.user'])
                ->where('account_id', $user->account->id);
    
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('event', 'LIKE', "%$search%");
            }
    
            $calendar = $query->get();
        }
    
        return response()->json($calendar);
    }
    

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
            'event' => 'required',
            'place' => 'required',
            'date' => 'required',
            'description' => 'required',
        ]);

        $calendar = Calendar::create([
            'account_id' => $user->account->id,
            'event' => $request->event,
            'place' => $request->place,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Calendar request submitted successfully', 'calendar' => $calendar], 201);
    }

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
            'event' => 'required',
            'place' => 'required',
            'date' => 'required',
            'description' => 'required',
        ]);

        $calendar = Calendar::create([
            'event' => $request->event,
            'place' => $request->place,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Calendar request submitted successfully', 'calendar' => $calendar], 201);
    }

    public function show($id)
    {
        $calendar = Calendar::findOrFail($id);
        return response()->json($calendar);
    }
}
