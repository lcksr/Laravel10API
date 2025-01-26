<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a new user and return JWT token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function register(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Membuat user baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Membuat JWT token untuk user baru
            $token = JWTAuth::fromUser($user);

            // Mengembalikan respons dengan token
            return response()->json(['token' => $token], 201);
        } catch (\Exception $e) {
            // Menangani error yang terjadi saat pembuatan user
            return response()->json([
                'error' => 'Registration Failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user and return JWT token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $credentials = $request->only('email', 'password');

        // Cek apakah kredensial cocok dan user bisa login
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }

        // Jika login berhasil, kirim token
        return response()->json(['token' => $token], 200);
    }

    public function editProfile(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . auth()->id(),
            'phone_number' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
        ]);

        $user = auth()->user();
        $user->update($request->only(['name', 'email', 'phone_number', 'alamat']));

        return response()->json(['message' => 'Profile updated successfully.', 'user' => $user], 200);
    }



    /**
     * Logout user (invalidate the token).
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        // Invalidate JWT token yang digunakan
        JWTAuth::invalidate(JWTAuth::getToken());

        // Mengembalikan respons logout yang berhasil
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Get the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function userProfile()
    {
        // Mengembalikan data user yang sedang terautentikasi
        return response()->json(auth()->user());
    }
}
