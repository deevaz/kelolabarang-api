<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('send-welcome-mail', function () {
    Mail::to('regitacahyaningfitri@gmail.com')->send(new WelcomeMail("Jon"));
    
    // Also, you can use specific mailer if your default mailer is not "mailtrap-sdk" but you want to use it for welcome mails
    // Mail::mailer('mailtrap-sdk')->to('testreceiver@gmail.com')->send(new WelcomeMail("Jon"));
})->purpose('Send welcome mail');

// ! TESTING ONLY, DO NOT USE IN PRODUCTION
Artisan::command('test-reset-password', function () {
    $user = \App\Models\User::where('email', 'abdaziz1713@gmail.com')->first();
    
    if (!$user) {
        $this->error('User not found!');
        return;
    }
    
    $token = \Illuminate\Support\Facades\Password::createToken($user);
    $resetUrl = url(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ], false));
    
    \Illuminate\Support\Facades\Mail::to($user->email)
        ->send(new \App\Mail\ResetPasswordMail(
            $user->name,
            $resetUrl,
            $token,
            $user->email
        ));
        
    $this->info('Reset password email sent to ' . $user->email);
    $this->line('Token: ' . $token);
    $this->line('Reset URL: ' . $resetUrl);
})->purpose('Test reset password email');

Artisan::command('test-reset-password2', function () {
    $user = \App\Models\User::where('email', 'abdaziz1713@gmail.com')->first();
    
    if (!$user) {
        $this->error('User not found!');
        return;
    }
    
    // Generate kode 6 digit
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Simpan ke database
    \Illuminate\Support\Facades\DB::table('password_reset_codes')->updateOrInsert(
        ['email' => $user->email],
        [
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now()
        ]
    );
    
    // Kirim email
    \Illuminate\Support\Facades\Mail::to($user->email)
        ->send(new \App\Mail\ResetPasswordMail(
            $user->name,
            $code,
            $user->email,
            now()->addMinutes(10)
        ));
        
    $this->info('Reset password email sent to ' . $user->email);
    $this->line('Code: ' . $code);
    $this->line('Expires at: ' . now()->addMinutes(10)->format('Y-m-d H:i:s'));
})->purpose('Test reset password with 6-digit code');

// ! TEST
Artisan::command('test-reset-code3', function () {
    $this->info('=== Testing Reset Password with 6-Digit Code ===');
    
    $user = \App\Models\User::where('email', 'abdaziz1713@gmail.com')->first();
    
    if (!$user) {
        $this->error('User not found!');
        return;
    }
    
    // 1. Generate kode
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // 2. Simpan ke DB
    \Illuminate\Support\Facades\DB::table('password_reset_codes')->updateOrInsert(
        ['email' => $user->email],
        [
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now()
        ]
    );
    
    // 3. Kirim email
    \Illuminate\Support\Facades\Mail::to($user->email)
        ->send(new \App\Mail\ResetPasswordMail(
            $user->name,
            $code,
            $user->email,
            now()->addMinutes(10)
        ));
    
    $this->info('✓ Email sent to: ' . $user->email);
    $this->info('✓ Code: ' . $code);
    $this->info('✓ Expires at: ' . now()->addMinutes(10)->format('H:i:s'));
    
    // 4. Test verify code
    $this->info('');
    $this->info('To verify via API:');
    $this->line('POST /api/auth/verify-reset-code');
    $this->line('Body: {"email": "' . $user->email . '", "code": "' . $code . '"}');
})->purpose('Test 6-digit reset password code');