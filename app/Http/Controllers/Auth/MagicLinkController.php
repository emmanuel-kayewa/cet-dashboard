<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class MagicLinkController extends Controller
{
    /**
     * Show Magic Link request form.
     */
    public function showForm()
    {
        return Inertia::render('Auth/MagicLink');
    }

    /**
     * Send a magic link to the user's email.
     */
    public function sendLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower($request->email);
        $allowedDomain = config('dashboard.allowed_email_domain', 'zesco.co.zm');

        // Validate domain
        if (!str_ends_with($email, '@' . $allowedDomain)) {
            return back()->withErrors(['email' => "Only @{$allowedDomain} accounts are allowed."]);
        }

        // Rate limiting: 3 attempts per minute per email
        $key = 'magic-link:' . $email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many attempts. Please try again in {$seconds} seconds."
            ]);
        }
        RateLimiter::hit($key, 60);

        $user = User::where('email', $email)->first();

        if (!$user) {
            // For security, don't reveal whether the user exists
            return back()->with('success', 'If your email is registered, a magic link has been sent.');
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        $token = $user->generateMagicLink();
        $magicUrl = route('auth.magic-link.verify', ['token' => $token, 'email' => $email]);

        // Send email
        Mail::send('emails.magic-link', ['url' => $magicUrl, 'user' => $user], function ($message) use ($email) {
            $message->to($email)
                    ->subject('ZESCO Dashboard - Sign In Link');
        });

        return back()->with('success', 'A sign-in link has been sent to your email.');
    }

    /**
     * Verify the magic link and log in.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if (!$user || !$user->validateMagicLink($request->token)) {
            return redirect()->route('login')
                ->withErrors(['token' => 'Invalid or expired magic link. Please request a new one.']);
        }

        $user->clearMagicLink();
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        Auth::login($user, true);

        AuditLog::log('login', 'User logged in via Magic Link', $user);

        return redirect()->intended(route('dashboard'));
    }
}
