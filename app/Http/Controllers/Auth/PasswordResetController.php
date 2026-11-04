<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    private const MINUTES = 60;

    public function request()
    {
        return view('auth.forgot');
    }

    public function sendLink(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        // at most 5 requests per hour for the same address
        $limitKey = 'pwreset-mail:' . sha1(Str::lower($data['email']));
        if (RateLimiter::tooManyAttempts($limitKey, 5)) {
            return $this->sent();
        }
        RateLimiter::hit($limitKey, 3600);

        $user = User::where('email', $data['email'])->where('is_aktif', 'y')->first();

        if ($user) {
            // only one valid link per user: drop the previous one
            if ($oldHash = Cache::pull($this->userKey($user->id_users))) {
                Cache::forget($this->tokenKey($oldHash));
            }

            $token = Str::random(64);
            $hash  = hash('sha256', $token);
            $ttl   = now()->addMinutes(self::MINUTES);

            Cache::put($this->tokenKey($hash), $user->id_users, $ttl);
            Cache::put($this->userKey($user->id_users), $hash, $ttl);

            $url = route('password.reset', ['token' => $token]);

            if (app()->isLocal()) {
                Log::info("Password reset link for {$user->email}: {$url}");   // handy while developing
            }

            try {
                Mail::to($user->email)->send(new PasswordResetMail($url, $user->full_name, self::MINUTES));
            } catch (\Throwable $e) {
                report($e);   // the screen still shows the same message, so nothing is revealed
            }
        }

        return $this->sent();
    }

    public function resetForm(string $token)
    {
        if (! $this->userIdFor($token)) {
            return $this->invalid();
        }

        return view('auth.reset', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token'    => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $userId = $this->userIdFor($data['token']);
        if (! $userId) {
            return $this->invalid();
        }

        $updated = User::where('id_users', $userId)->where('is_aktif', 'y')
            ->update(['password' => Hash::make($data['password'])]);

        // the link works only once
        Cache::forget($this->tokenKey(hash('sha256', $data['token'])));
        Cache::forget($this->userKey($userId));

        if (! $updated) {
            return $this->invalid();
        }

        return redirect()->route('login')
            ->with('status', 'Your password has been reset. Please sign in with your new password.');
    }

    // ---------------------------------------------------------------- helpers

    private function sent()
    {
        return back()->with('status',
            'If that email address is registered, a password reset link has been sent. The link is valid for ' . self::MINUTES . ' minutes.');
    }

    private function invalid()
    {
        return redirect()->route('password.request')
            ->withErrors(['email' => 'This reset link is invalid or has expired. Please request a new one.']);
    }

    private function userIdFor(string $token): ?int
    {
        $id = Cache::get($this->tokenKey(hash('sha256', $token)));
        return $id ? (int) $id : null;
    }

    private function tokenKey(string $hash): string
    {
        return 'pwreset:token:' . $hash;
    }

    private function userKey(int|string $id): string
    {
        return 'pwreset:user:' . $id;
    }
}