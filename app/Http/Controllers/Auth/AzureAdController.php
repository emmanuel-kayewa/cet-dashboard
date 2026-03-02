<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Inertia\Inertia;

class AzureAdController extends Controller
{
    /**
     * Show login page.
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Redirect to Azure AD for OAuth authentication.
     */
    public function redirect()
    {
        return Socialite::driver('azure')
            ->scopes(['openid', 'profile', 'email', 'User.Read'])
            ->redirect();
    }

    /**
     * Handle callback from Azure AD.
     */
    public function callback(Request $request)
    {
        try {
            $azureUser = Socialite::driver('azure')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['azure' => 'Authentication failed. Please try again.']);
        }

        // Validate email domain
        $email = $azureUser->getEmail();
        $allowedDomain = config('dashboard.allowed_email_domain', 'zesco.co.zm');

        if (!str_ends_with(strtolower($email), '@' . $allowedDomain)) {
            return redirect()->route('login')
                ->withErrors(['email' => "Only @{$allowedDomain} accounts are allowed."]);
        }

        // Find or create user (auto-provisioning)
        $user = User::updateOrCreate(
            ['azure_id' => $azureUser->getId()],
            [
                'name' => $azureUser->getName(),
                'email' => $email,
                'avatar' => $azureUser->getAvatar(),
                'job_title' => $azureUser->user['jobTitle'] ?? null,
                'department' => $azureUser->user['department'] ?? null,
                'role_id' => $this->determineRole($azureUser)->id,
                'email_verified_at' => now(),
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]
        );

        Auth::login($user, true);

        AuditLog::log('login', 'User logged in via Azure AD', $user);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request)
    {
        AuditLog::log('logout', 'User logged out');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Determine user role based on Azure AD groups or defaults.
     */
    private function determineRole($azureUser): Role
    {
        // Check Azure AD groups if available
        $groups = $azureUser->user['groups'] ?? [];

        if (in_array('ZESCO_Admins', $groups)) {
            return Role::getByName(Role::ADMIN) ?? Role::firstOrFail();
        }

        if (in_array('ZESCO_Directorate_Heads', $groups)) {
            return Role::getByName(Role::DIRECTORATE_HEAD) ?? Role::firstOrFail();
        }

        // Default to Executive role
        return Role::getByName(Role::EXECUTIVE) ?? Role::firstOrFail();
    }
}
