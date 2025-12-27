<?php

namespace App\Http\Controllers;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Notifications\CustomResetPassword;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $profilePicturePath = 'https://kelola.abdaziz.my.id/storage/' . $profilePicturePath;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'profile_picture' => $profilePicturePath,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required'
        ]);

        if (!User::where('username', $credentials['username'])->exists()) {
            return response()->json(['error' => 'Username tidak ditemukan'], 404);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Password salah'], 401);
        }

        return response()->json([
            'message' => 'Login Berhasil',
            'user' => auth()->user(),
            'token' => $token
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Berhasil Logout']);
    }

    public function refresh()
    {
        return response()->json([
            'token' => JWTAuth::refresh()
        ]);
    }

    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'            => 'sometimes|required|string|max:255',
            'username'        => 'sometimes|required|string|max:255|unique:users,username,' . $user->id,
            'email'           => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'profile_picture' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'password'        => 'sometimes|required|string|min:6',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = 'https://kelola.abdaziz.my.id/storage/' . $profilePicturePath;
        }

        // if ($request->has('password')) {
        //     $user->password = Hash::make($request->password);
        // }

        $user->save();

        if ($user->profile_picture) {
            $user->profile_picture =  $user->profile_picture;
        }

        return response()->json([
            'message' => 'Profil berhasil di perbarui',
            'user'    => $user,
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'password' => 'required|string',
        ]);
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Password salah'], 401);
        }

        if (!$user) {
            return response()->json(['error' => 'User tidak terautentikasi'], 401);
        }

        $user->delete();

        return response()->json([
            'message' => 'Akun berhasil dihapus',
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Password lama salah'], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah']);
    }

    public function getUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'User berhasil diambil',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    // public function forgotPassword(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         return response()->json(['message' => 'Email tidak ditemukan'], 404);
    //     }

    //     $status = Password::sendResetLink(
    //         $request->only('email'),
    //         function ($user, $token) {
    //             $user->notify(new CustomResetPassword($token));
    //         }
    //     );

    //     return $status === Password::RESET_LINK_SENT
    //         ? response()->json(['message' => __($status)])
    //         : response()->json(['error' => __($status)], 400);
    // }

    // public function resetPassword(Request $request)
    // {
    //     $request->validate([
    //         'token' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required|string|min:6|confirmed',
    //     ]);

    //     $status = Password::reset(
    //         $request->only('email', 'password', 'password_confirmation', 'token'),
    //         function ($user) use ($request) {
    //             $user->password = Hash::make($request->password);
    //             $user->save();
    //         }
    //     );

    //     return $status === Password::PASSWORD_RESET
    //         ? response()->json(['message' => __($status)])
    //         : response()->json(['error' => __($status)], 400);
    // }
    public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Email tidak ditemukan'
        ], 404);
    }

    // Generate kode 6 digit
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Simpan ke database dengan expiry 10 menit
    \Illuminate\Support\Facades\DB::table('password_reset_codes')->updateOrInsert(
        ['email' => $user->email],
        [
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now()
        ]
    );

    // Kirim email
    Mail::to($user->email)->send(new ResetPasswordMail(
        $user->name,
        $code,
        $user->email,
        now()->addMinutes(10)
    ));

    return response()->json([
        'success' => true,
        'message' => 'Kode reset password (6 digit) telah dikirim ke email Anda',
        'expires_in' => '10 menit'
    ]);
}

public function verifyResetCode(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string|size:6',
    ]);

    $resetCode = \Illuminate\Support\Facades\DB::table('password_reset_codes')
        ->where('email', $request->email)
        ->first();

    if (!$resetCode) {
        return response()->json([
            'success' => false,
            'message' => 'Kode tidak ditemukan'
        ], 400);
    }

    if (now()->greaterThan($resetCode->expires_at)) {
        return response()->json([
            'success' => false,
            'message' => 'Kode sudah kadaluarsa'
        ], 400);
    }

    if ($resetCode->code !== $request->code) {
        // Update attempts
        $attempts = $resetCode->attempts + 1;
        \Illuminate\Support\Facades\DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->update(['attempts' => $attempts]);
        
        if ($attempts >= 3) {
            \Illuminate\Support\Facades\DB::table('password_reset_codes')
                ->where('email', $request->email)
                ->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Silakan request kode baru.'
            ], 400);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Kode tidak valid',
            'attempts_remaining' => 3 - $attempts
        ], 400);
    }

    // Kode valid
    return response()->json([
        'success' => true,
        'message' => 'Kode valid',
        'data' => [
            'email' => $request->email,
            'code' => $request->code,
            'expires_at' => $resetCode->expires_at
        ]
    ]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string|size:6',
        'password' => 'required|string|min:6|confirmed',
    ]);

    // Verifikasi kode terlebih dahulu
    $resetCode = \Illuminate\Support\Facades\DB::table('password_reset_codes')
        ->where('email', $request->email)
        ->first();

    if (!$resetCode || $resetCode->code !== $request->code) {
        return response()->json([
            'success' => false,
            'message' => 'Kode tidak valid'
        ], 400);
    }

    if (now()->greaterThan($resetCode->expires_at)) {
        \Illuminate\Support\Facades\DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->delete();
        
        return response()->json([
            'success' => false,
            'message' => 'Kode sudah kadaluarsa'
        ], 400);
    }

    // Reset password user
    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    // Hapus kode setelah berhasil
    \Illuminate\Support\Facades\DB::table('password_reset_codes')
        ->where('email', $request->email)
        ->delete();

    // Optional: Kirim notifikasi password berhasil diubah
    // Mail::to($user->email)->send(new PasswordChangedMail($user->name));

    return response()->json([
        'success' => true,
        'message' => 'Password berhasil direset'
    ]);
}

public function resendResetCode(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Email tidak ditemukan'
        ], 404);
    }

    // Hapus kode lama jika ada
    \Illuminate\Support\Facades\DB::table('password_reset_codes')
        ->where('email', $user->email)
        ->delete();

    // Generate kode baru
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Simpan kode baru
    \Illuminate\Support\Facades\DB::table('password_reset_codes')->updateOrInsert(
        ['email' => $user->email],
        [
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now()
        ]
    );

    // Kirim email baru
    Mail::to($user->email)->send(new ResetPasswordMail(
        $user->name,
        $code,
        $user->email,
        now()->addMinutes(10)
    ));

    return response()->json([
        'success' => true,
        'message' => 'Kode reset baru telah dikirim',
        'expires_in' => '10 menit'
    ]);
}
}