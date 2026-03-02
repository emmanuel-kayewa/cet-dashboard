<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\Setting;
use App\Models\User;
use App\Models\Role;
use App\Models\Alert;
use App\Services\DataSourceManager;
use App\Services\SimulationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Admin dashboard / settings panel.
     */
    public function index(DataSourceManager $manager, SimulationService $simulation)
    {
        $settings = Setting::all();
        
        return Inertia::render('Admin/Index', [
            'users' => User::with('role', 'directorate')->orderBy('name')->get(),
            'roles' => Role::all(),
            'directorates' => Directorate::ordered()->get(),
            'allDirectorates' => Directorate::all(),
            'settings' => $settings,
            'simulationActive' => $simulation->isEnabled(),
            'currentDataSource' => config('dashboard.data_source'),
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_alerts' => Alert::count(),
                'unread_alerts' => Alert::unread()->count(),
            ],
        ]);
    }

    /**
     * Toggle simulation mode (AJAX).
     */
    public function toggleSimulation(Request $request, SimulationService $simulation)
    {
        $request->validate(['enabled' => 'required|boolean']);

        $simulation->toggle($request->enabled);

        AuditLog::log('update', 'Simulation mode ' . ($request->enabled ? 'enabled' : 'disabled'));

        return response()->json([
            'success' => true,
            'simulation_enabled' => $simulation->isEnabled(),
        ]);
    }

    /**
     * Create a new user.
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'directorate_id' => 'nullable|exists:directorates,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'role_id' => $request->role_id,
            'directorate_id' => $request->directorate_id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        AuditLog::log('create', 'User created by admin', $user);

        return redirect()->back()->with('success', "User {$user->name} created successfully.");
    }

    /**
     * Manage users.
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'directorate_id' => 'nullable|exists:directorates,id',
            'is_active' => 'required|boolean',
        ]);

        $old = $user->toArray();

        $user->update($request->only('role_id', 'directorate_id', 'is_active'));

        AuditLog::log('update', 'User updated by admin', $user, $old, $user->fresh()->toArray());

        return redirect()->back()->with('success', "User {$user->name} updated.");
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        $name = $user->name;
        AuditLog::log('delete', 'User deleted by admin', $user);
        $user->delete();

        return redirect()->back()->with('success', "User {$name} deleted.");
    }

    /**
     * Save dashboard settings.
     */
    public function saveSettings(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        AuditLog::log('update', 'Dashboard settings updated');

        return redirect()->back()->with('success', 'Settings saved successfully.');
    }

    /**
     * Create a new directorate.
     */
    public function storeDirectorate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:directorates,code',
            'description' => 'nullable|string',
            'head_name' => 'nullable|string|max:255',
            'head_email' => 'nullable|email',
            'color' => 'nullable|string|max:7',
        ]);

        $directorate = Directorate::create([
            ...$request->only('name', 'code', 'description', 'head_name', 'head_email', 'color'),
            'slug' => \Illuminate\Support\Str::slug($request->code),
            'sort_order' => Directorate::max('sort_order') + 1,
            'is_active' => true,
        ]);

        AuditLog::log('create', 'Directorate created', $directorate);

        return redirect()->back()->with('success', "Directorate {$directorate->name} created.");
    }

    /**
     * Manage directorates.
     */
    public function updateDirectorate(Request $request, Directorate $directorate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'description' => 'nullable|string',
            'head_name' => 'nullable|string|max:255',
            'head_email' => 'nullable|email',
            'color' => 'nullable|string|max:7',
            'is_active' => 'required|boolean',
        ]);

        $old = $directorate->toArray();
        $directorate->update($request->validated());

        AuditLog::log('update', 'Directorate updated', $directorate, $old, $directorate->fresh()->toArray());

        return redirect()->back()->with('success', "Directorate {$directorate->name} updated.");
    }

    /**
     * View audit logs.
     */
    public function auditLogs(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->action, fn($q, $a) => $q->where('action', $a))
            ->orderByDesc('created_at')
            ->paginate(50);

        return Inertia::render('Admin/AuditLogs', [
            'logs' => $logs,
        ]);
    }

    /**
     * Run simulation cycle manually.
     */
    public function runSimulation(SimulationService $simulation)
    {
        $result = $simulation->runSimulationCycle();

        AuditLog::log('update', 'Manual simulation cycle triggered');

        return response()->json($result);
    }
}
