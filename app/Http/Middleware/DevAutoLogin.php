<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DevAutoLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local')) {
            return $next($request);
        }

        if (Auth::check()) {
            return $next($request);
        }

        $adminRole = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            [
                'display_name' => 'Admin',
                'description' => 'Local development admin role',
                'permissions' => [],
            ]
        );

        $email = config('app.dev_autologin_email', 'dev.admin@local.test');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Dev Admin',
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        if (! $user->role_id) {
            $user->forceFill(['role_id' => $adminRole->id])->save();
        }

        if (! $user->is_active) {
            $user->forceFill(['is_active' => true])->save();
        }

        Auth::login($user, true);

        return $next($request);
    }
}
