<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;

class CourierController extends Controller
{
    public function index()
    {
        $couriers = Courier::paginate(10);
        return ResponseFormatter::success($couriers, 'Couriers retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:couriers',
            'vehicle_type' => 'required|string|in:motorcycle,car,truck',
        ]);

        $courier = Courier::create($request->all());
        return ResponseFormatter::success($courier, 'Courier created successfully');
    }

    public function show($id)
    {
        $courier = Courier::findOrFail($id);
        return ResponseFormatter::success($courier, 'Courier details retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $courier = Courier::findOrFail($id);

        $request->validate([
            'name' => 'string|max:255',
            'phone' => 'string|max:20|unique:couriers,phone,' . $courier->id,
            'vehicle_type' => 'string|in:motorcycle,car,truck',
        ]);

        $courier->update($request->all());
        return ResponseFormatter::success($courier, 'Courier updated successfully');
    }

    public function destroy($id)
    {
        $courier = Courier::findOrFail($id);
        $courier->delete();
        return ResponseFormatter::success(null, 'Courier deleted successfully');
    }
}
