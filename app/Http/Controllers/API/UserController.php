<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Actions\Fortify\PasswordValidationRules;

use Exception; // Pastikan untuk mengimpor Exception
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15|unique:users', // Validasi nomor telepon
            'email' => 'required|string|unique:users|email',
            'password' => $this->PasswordValidationRules,
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            // Buat pengguna baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number, // Menyimpan nomor telepon
                'password' => Hash::make($request->password),
            ]);
            $user::where('email', $request->email)->first();
            $token = $user->createToken('authToken')->plainTextToken;

            // Mengembalikan respons sukses
            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Regsitrasi Akun Berhasil', 200);
        } catch (Exception $e) {
            // Menangani kesalahan
            return ResponseFormatter::error('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // Identifier untuk nomor telepon atau email
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            // Mencari pengguna berdasarkan nomor telepon atau email
            $user = User::where('phone_number', $request->identifier)
                ->orWhere('email', $request->identifier)
                ->first();

            // Memeriksa kredensial
            if (!$user || !Hash::check($request->password, $user->password)) {
                return ResponseFormatter::error('Unauthorized', 401);
            }

            // Mengembalikan respons sukses dengan token
            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');
        } catch (Exception $e) {
            // Menangani kesalahan
            return ResponseFormatter::error('Login failed: ' . $e->getMessage(), 500);
        }
    }



    public function logout(Request $request)
    {

        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Berhasil Logout', 200);
    }
    public function profileUpdate(Request $request)
    {
        $data = $request->all();
        $user = auth()->user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile updated successfully', 200);
    }

    public function fetcth(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(),
            'Data Profile User Berhasil di ambil ',
        );
    }


    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2848'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error('Gagal mengupload foto', 422);
        }
        if ($request->file('file')) {
            $file = $request->file('file')->store('assets/user', 'public');
            $user = Auth::user();
            $user->profile_photo_url = $file;
            $user->update();

            return ResponseFormatter::success([$file, 'Upload foto berhasil']);
        }
    }
}
