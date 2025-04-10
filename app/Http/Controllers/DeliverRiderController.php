<?php

namespace App\Http\Controllers;

use App\Models\DeliverRider;
use Illuminate\Http\Request;

class DeliverRiderController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliverRider::query();
    
        if ($request->has('search')) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('agent', 'LIKE', "%$search%");
            });
        }
    
        $deliverRiders = $query->get();
    
        return response()->json($deliverRiders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent' => 'required',
            'mobile_number' => 'required',
            'address' => 'required',
            'date_of_birth' => 'required',
        ]);

        $deliverRider = DeliverRider::create([
            'agent' => $request->agent,
            'email' => $request->email,
            'address' => $request->address,
            'mobile_number' => $request->mobile_number,
            'date_of_birth' => $request->date_of_birth,
        ]);

        return response()->json(['message' => 'Deliver rider request submitted successfully', 'deliver rider' => $deliverRider], 201);
    }

    public function show($id)
    {
        $deliverRider = DeliverRider::find($id);

        if (!$deliverRider) {
            return response()->json(['error' => 'Deliver Rider not found'], 404);
        }

        return response()->json($deliverRider);
    }

    public function update()
    {
        $deliverRider = DeliverRider::findOrFail($id);

        if (!$deliveryRider) {
            return response()->json(["message" => "Not Found"], 404);
        }

        $deliverRider->update([

            'agent' => $$request->agent,
            'email' => $$request->email,
            'address' => $$request->address,
            'mobile_number' => $$request->mobile_number,
            'date_of_birth' => $$request->date_of_birth,

        ]);
    }

    public function destroy($id)
    {
        DeliverRider::findOrFail($id)->delete();
        return response()->json(['message' => 'DeliverRider record deleted']);
    }
}
