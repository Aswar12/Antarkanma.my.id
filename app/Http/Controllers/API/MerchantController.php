<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\OrderItem;

use App\Models\Merchant;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MerchantController extends Controller
{
    public function index()
    {
        $merchants = Merchant::all();
        return ResponseFormatter::success(
            $merchants,
            'Merchants retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'owner_id' => 'required|exists:users,id',
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'phone_number' => 'required|string|max:15',
                'status' => 'required|string',
                'description' => 'nullable|string',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i',
                'operating_days' => 'nullable|string|array',
            ]);

            $data = $request->all();

            if ($request->has('operating_days') && is_array($request->operating_days)) {
                $data['operating_days'] = implode(',', $request->operating_days);
            }

            $merchant = Merchant::create($data);

            return ResponseFormatter::success(
                $merchant,
                'Merchant created successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to create merchant: ' . $e->getMessage(),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $merchant = Merchant::findOrFail($id);
            return ResponseFormatter::success(
                $merchant,
                'Merchant details retrieved successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Merchant not found',
                404
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $merchant = Merchant::findOrFail($id);
            $request->validate([
                'owner_id' => 'sometimes|exists:users,id',
                'name' => 'sometimes|string|max:255',
                'address' => 'sometimes|string|max:255',
                'phone_number' => 'sometimes|string|max:15',
                'status' => 'sometimes|string',
                'description' => 'sometimes|string',
                'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i',
                'operating_days' => 'nullable|array',
                'operating_days.*' => 'string',
            ]);

            $data = $request->all();

            if ($request->has('operating_days') && is_array($request->operating_days)) {
                $data['operating_days'] = implode(',', $request->operating_days);
            }

            $merchant->update($data);

            return ResponseFormatter::success(
                $merchant,
                'Merchant updated successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to update merchant: ' . $e->getMessage(),
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $merchant = Merchant::findOrFail($id);
            $merchant->delete();

            return ResponseFormatter::success(
                null,
                'Merchant deleted successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to delete merchant: ' . $e->getMessage(),
                500
            );
        }
    }

    public function getByOwnerId($id)
    {

        $merchants = Merchant::where('owner_id', $id)->get();
        $merchants->transform(function ($merchant) {
            $merchant->product_count = $merchant->products()->count(); // Count of products
    $merchant->order_count = OrderItem::where('merchant_id', $merchant->id)->count(); // Count of orders
            $merchant->products_sold = OrderItem::where('merchant_id', $merchant->id)->sum('quantity'); // Total products sold
            $merchant->total_sales = OrderItem::where('merchant_id', $merchant->id)->sum('price'); // Total sales
            $merchant->monthly_revenue = OrderItem::where('merchant_id', $merchant->id)
                ->whereMonth('created_at', now()->month)
                ->sum('price'); // Monthly revenue
            $merchant->product_count = $merchant->products()->count(); // Assuming a relationship exists
            return $merchant;
        });
        return ResponseFormatter::success(
            $merchants,
            'Merchant list by owner retrieved successfully'
        );
    }
}
