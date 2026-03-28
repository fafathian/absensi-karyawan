<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi inputan dari frontend
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_id' => 'required',
        ]);

        // 2. Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // 3. Cek apakah user ada dan passwordnya benar
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        // (Opsional tapi direkomendasikan): Pastikan admin tidak login lewat aplikasi HP
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Admin harus login melalui dashboard web.'
            ], 403);
        }

        if (empty($user->device_id)) {
            // Ikat device ini ke akun user
            $user->device_id = $request->device_id;
            $user->save();
        }

        // Skenario B: Login Kedua dst (Cek apakah device-nya sama)
        else if ($user->device_id !== $request->device_id) {
            // Jika device berbeda, tolak login
            return response()->json([
                'message' => 'Akun Anda sudah terikat di device lain. Silakan reset device melalui Admin HRD.'
            ], 403); // Gunakan kode 403 Forbidden
        }

        // 4. Buatkan Token Sanctum
        $token = $user->createToken('employee-app-token')->plainTextToken;

        // 5. Kembalikan response sukses ke frontend
        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.'
        ], 200);
    }
}
