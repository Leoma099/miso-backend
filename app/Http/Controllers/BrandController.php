<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();
    
        if ($request->has('search')) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'LIKE', "%$search%");
            });
        }
    
        $brand = $query->get();
    
        return response()->json($brand);
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand' => 'required',
        ]);

        $brand = Brand::create([
            'brand' => $request->brand,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Brand request submitted successfully', 'brand' => $brand], 201);
    }

    public function show($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['error' => 'Brand not found'], 404);
        }

        return response()->json($brand);
    }

    public function update()
    {
        $brand = Brand::findOrFail($id);

        if (!$brand) {
            return response()->json(["message" => "Not Found"], 404);
        }

        $brand->update([

            'brand' => $$request->brand,
            'description' => $$request->description,

        ]);
    }

    public function destroy($id)
    {
        Brand::findOrFail($id)->delete();
        return response()->json(['message' => 'Brand record deleted']);
    }
}
