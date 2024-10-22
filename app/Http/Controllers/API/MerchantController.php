<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Auth;

class MerchantController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:15|unique:merchants',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            $merchant = Merchant::create([
                'name' => $request->name,
                'owner_id' => Auth::id(),
                'address' => $request->address,
                'phone_number' => $request->phone_number,
            ]);

            return ResponseFormatter::success($merchant, 'Merchant created successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to create merchant: ' . $e->getMessage(), 500);
        }
    }

    public function get($id)
    {
        try {
            $merchant = Merchant::findOrFail($id);
            return ResponseFormatter::success($merchant, 'Merchant retrieved successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Merchant not found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'address' => 'string',
            'phone_number' => 'string|max:15|unique:merchants,phone_number,' . $id,
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            $merchant = Merchant::findOrFail($id);

            if ($merchant->owner_id !== Auth::id()) {
                return ResponseFormatter::error('Unauthorized', 403);
            }

            $merchant->update($request->all());

            return ResponseFormatter::success($merchant, 'Merchant updated successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to update merchant: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            $merchant = Merchant::findOrFail($id);

            if ($merchant->owner_id !== Auth::id()) {
                return ResponseFormatter::error('Unauthorized', 403);
            }

            $merchant->delete();

            return ResponseFormatter::success(null, 'Merchant deleted successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to delete merchant: ' . $e->getMessage(), 500);
        }
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $name = $request->input('name');

        $merchantQuery = Merchant::query();

        if ($name) {
            $merchantQuery->where('name', 'like', '%' . $name . '%');
        }

        $merchants = $merchantQuery->paginate($limit);

        return ResponseFormatter::success(
            $merchants,
            'Merchants list retrieved successfully'
        );
    }
}
