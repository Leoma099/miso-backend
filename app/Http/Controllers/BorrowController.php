<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    // List all borrow requests (admin only)
    public function index()
    {
        return Borrow::with(['user', 'equipment'])->paginate(10);
 
        return response()->json([
            'data' => $borrows
        ]);
    }

    // Store a new borrow request (user submits request)
    public function store(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'date_borrowed' => 'required|date',
            'date_returned' => 'required|date|after_or_equal:date_borrowed',
        ]);

        $borrow = Borrow::create([
            'user_id' => auth()->id(),
            'equipment_id' => $request->equipment_id,
            'condition' => '1',
            'status' => '1',
            'date_borrowed' => $request->date_borrowed,
            'date_returned' => $request->date_returned,
        ]);

        return response()->json(['message' => 'Borrow request submitted', 'data' => $borrow], 201);
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
};