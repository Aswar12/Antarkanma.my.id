<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseFormatter;

class UserLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $locations = UserLocation::where('user_id', $user_id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->get();

        return ResponseFormatter::success(
            $locations,
            'Data lokasi berhasil diambil'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string',
            'district' => 'nullable|string',
            'postal_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address_type' => 'required|in:RUMAH,KANTOR,TOKO,LAINNYA',
            'phone_number' => 'required|string',
            'is_default' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                $validator->errors(),
                'Data lokasi tidak valid',
                422
            );
        }

        // Jika lokasi baru diset sebagai default, reset semua lokasi lain
        if ($request->is_default) {
            UserLocation::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $location = UserLocation::create([
            'user_id' => $request->user()->id,
            'customer_name' => $request->customer_name,
            'address' => $request->address,
            'city' => $request->city,
            'district' => $request->district,
            'postal_code' => $request->postal_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address_type' => $request->address_type,
            'phone_number' => $request->phone_number,
            'is_default' => $request->is_default ?? false,
            'notes' => $request->notes,
            'is_active' => true,
            'country' => $request->country ?? 'Indonesia'
        ]);

        return ResponseFormatter::success(
            $location,
            'Lokasi berhasil ditambahkan'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $location = UserLocation::find($id);

        if (!$location) {
            return ResponseFormatter::error(
                null,
                'Lokasi tidak ditemukan',
                404
            );
        }

        return ResponseFormatter::success(
            $location,
            'Data lokasi berhasil diambil'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $location = UserLocation::find($id);

        if (!$location) {
            return ResponseFormatter::error(
                null,
                'Lokasi tidak ditemukan',
                404
            );
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string',
            'district' => 'nullable|string',
            'postal_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address_type' => 'required|in:RUMAH,KANTOR,TOKO,LAINNYA',
            'phone_number' => 'required|string',
            'is_default' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                $validator->errors(),
                'Data lokasi tidak valid',
                422
            );
        }

        // Jika lokasi diset sebagai default, reset semua lokasi lain
        if ($request->is_default) {
            UserLocation::where('user_id', $request->user()->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $location->update($request->all());

        return ResponseFormatter::success(
            $location,
            'Lokasi berhasil diperbarui'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $location = UserLocation::find($id);

        if (!$location) {
            return ResponseFormatter::error(
                null,
                'Lokasi tidak ditemukan',
                404
            );
        }

        // Soft delete
        $location->is_active = false;
        $location->save();
        $location->delete();

        return ResponseFormatter::success(
            null,
            'Lokasi berhasil dihapus'
        );
    }

    /**
     * Set location as default
     */
    public function setDefault($id)
    {
        $location = UserLocation::find($id);

        if (!$location) {
            return ResponseFormatter::error(
                null,
                'Lokasi tidak ditemukan',
                404
            );
        }

        // Reset semua lokasi default
        UserLocation::where('user_id', auth()->id())
            ->update(['is_default' => false]);

        // Set lokasi yang dipilih sebagai default
        $location->is_default = true;
        $location->save();

        return ResponseFormatter::success(
            $location,
            'Lokasi berhasil diset sebagai default'
        );
    }
}
