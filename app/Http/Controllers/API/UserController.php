<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Actions\Fortify\PasswordValidationRules;

class UserController extends Controller
{
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15|unique:users',
            'email' => 'required|string|unique:users|email',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors());
        }

        try {
            // Buat pengguna baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
            ]);
            $user::where('email', $request->email)->first();
            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Regsitrasi Akun Berhasil', 200);
        } catch (Exception $e) {
            return ResponseFormatter::error('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors());
        }

        try {
            $user = User::where('phone_number', $request->identifier)
                ->orWhere('email', $request->identifier)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return ResponseFormatter::error('Unauthorized', 401);
            }

            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');
        } catch (Exception $e) {
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
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'email' => 'email|unique:users,email,'.$user->id,
                'phone_number' => 'string|max:15|unique:users,phone_number,'.$user->id,
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
            }

            User::where('id', $user->id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number
            ]);

            $user = User::find($user->id);

            return ResponseFormatter::success($user, 'Profile berhasil diperbarui');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Gagal memperbarui profile', 500);
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(),
            'Data Profile User berhasil diambil'
        );
    }

    public function updatePhoto(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'photo' => 'required|image' // Removed size limit, only validating that it's an image
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
            }

            $user = Auth::user();

            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }

                // Store new photo without any size restrictions
                $path = $request->file('photo')->store('profile-photos', 'public');
                
                // Update user profile photo and reload data
                User::where('id', $user->id)->update([
                    'profile_photo_path' => $path
                ]);
                $user = User::find($user->id);

                return ResponseFormatter::success([
                    'path' => $path,
                    'url' => $user->profile_photo_url
                ], 'Foto profile berhasil diupload');
            }

            return ResponseFormatter::error('No photo uploaded', 'Gagal mengupload foto', 400);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Gagal mengupload foto', 500);
        }
    }
}
