<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $calendar = Calendar::all();
        return response()->json($calendar);
    }

    public function store(Request $request)
    {
        $request->validate([
            'event' => 'required',
            'place' => 'required',
            'date' => 'required',
            'time_from' => 'required',
            'time_to' => 'required',
            'description' => 'required',
        ]);

        $calendar = Calendar::create([
            'event' => $request->event,
            'place' => $request->place,
            'date' => $request->date,
            'time_from' => $request->time_from,
            'time_to' => $request->time_to,
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
