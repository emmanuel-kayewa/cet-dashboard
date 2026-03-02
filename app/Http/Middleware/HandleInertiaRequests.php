<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar' => $request->user()->avatar,
                    'role' => $request->user()->role?->name,
                    'role_display' => $request->user()->role?->display_name,
                    'directorate_id' => $request->user()->directorate_id,
                    'directorate' => $request->user()->directorate?->name,
                    'is_admin' => $request->user()->isAdmin(),
                    'can_input_data' => $request->user()->canInputData(),
                    'preferences' => $request->user()->preferences,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'app' => [
                'name' => config('app.name'),
                'simulation_enabled' => config('dashboard.simulation.enabled'),
                'data_source' => config('dashboard.data_source'),
            ],
        ];
    }
}
