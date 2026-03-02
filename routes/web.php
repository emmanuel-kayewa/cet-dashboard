<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiInsightsController;
use App\Http\Controllers\Auth\AzureAdController;
use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FinancialEntryController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\KpiEntryController;
use App\Http\Controllers\KpiImportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RiskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Authentication ─────────────────────────────────────────
// if (app()->environment('local')) {
//     Route::get('/login', fn () => redirect('/dashboard'))->name('login');
// } else {
    Route::middleware(['guest', 'throttle:auth'])->group(function () {
        Route::get('/login', [AzureAdController::class, 'showLogin'])->name('login');

        // Azure AD OAuth
        Route::get('/auth/azure/redirect', [AzureAdController::class, 'redirect'])->name('auth.azure.redirect');
        Route::get('/auth/azure/callback', [AzureAdController::class, 'callback'])->name('auth.azure.callback');

        // Magic Link
        Route::get('/auth/magic-link', [MagicLinkController::class, 'showForm'])->name('auth.magic-link');
        Route::post('/auth/magic-link', [MagicLinkController::class, 'sendLink'])->name('auth.magic-link.send');
        Route::get('/auth/magic-link/verify', [MagicLinkController::class, 'verify'])->name('auth.magic-link.verify');
    });
// }

if (app()->environment('local')) {
    Route::post('/logout', [AzureAdController::class, 'logout'])->name('logout');
} else {
    Route::post('/logout', [AzureAdController::class, 'logout'])->name('logout')->middleware('auth');
}

// ── Protected Routes ───────────────────────────────────────
Route::middleware(app()->environment('local')
    ? ['dev.autologin', 'session.timeout']
    : ['auth', 'session.timeout']
)->group(function () {

    // Redirect root to dashboard
    Route::redirect('/', '/dashboard');

    // Component Showcase (for testing)
    Route::get('/components', fn() => Inertia::render('ComponentShowcase'))->name('components.showcase');

    // ── Dashboard ──────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/directorate/{directorate:slug}', [DashboardController::class, 'directorate'])->name('dashboard.directorate');
    Route::get('/dashboard/comparison', [DashboardController::class, 'comparison'])->name('dashboard.comparison');
    Route::get('/dashboard/wayleaves', [DashboardController::class, 'wayleaves'])->name('dashboard.wayleaves');

    // ── API Endpoints ──────────────────────────────────────
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/kpi-trend', [DashboardController::class, 'kpiTrend'])->name('kpi.trend');
    });

    // ── Export ──────────────────────────────────────────────
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/executive/pdf', [ExportController::class, 'executivePdf'])->name('executive.pdf');
        Route::get('/executive/excel', [ExportController::class, 'executiveExcel'])->name('executive.excel');
        Route::get('/directorate/{directorate:slug}/pdf', [ExportController::class, 'directoratePdf'])->name('directorate.pdf');
        Route::get('/directorate/{directorate:slug}/excel', [ExportController::class, 'directorateExcel'])->name('directorate.excel');
    });

    // ── Data Entry (Directorate Heads + Admin) ─────────────
    Route::middleware(['role:directorate_head,admin', 'throttle:data-entry'])->prefix('data-entry')->name('data-entry.')->group(function () {
        Route::resource('kpi-entries', KpiEntryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('financial-entries', FinancialEntryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('projects', ProjectController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('risks', RiskController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('incidents', IncidentController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // ── AI Insights ──────────────────────────────────────────
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AiInsightsController::class, 'index'])->name('index');
        Route::get('/status', [AiInsightsController::class, 'status'])->name('status');
    });

    Route::prefix('api/ai')->name('api.ai.')->group(function () {
        Route::post('/executive-insights', [AiInsightsController::class, 'executiveInsights'])->name('executive-insights');
        Route::post('/explain-anomaly', [AiInsightsController::class, 'explainAnomaly'])->name('explain-anomaly');
        Route::post('/recommendations', [AiInsightsController::class, 'recommendations'])->name('recommendations');
        Route::post('/query', [AiInsightsController::class, 'query'])->name('query');
        Route::post('/predict-breach', [AiInsightsController::class, 'predictBreach'])->name('predict-breach');
        Route::post('/clear-cache', [AiInsightsController::class, 'clearCache'])->name('clear-cache');
        Route::get('/task/{taskId}', [AiInsightsController::class, 'pollTask'])->name('task.poll');
    });

    // ── Admin ──────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/simulation/toggle', [AdminController::class, 'toggleSimulation'])->name('simulation.toggle');
        Route::post('/simulation/run', [AdminController::class, 'runSimulation'])->name('simulation.run');
        Route::post('/settings', [AdminController::class, 'saveSettings'])->name('settings.save');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::post('/directorates', [AdminController::class, 'storeDirectorate'])->name('directorates.store');
        Route::put('/directorates/{directorate}', [AdminController::class, 'updateDirectorate'])->name('directorates.update');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');

        // ── KPI Import ─────────────────────────────────────
        Route::get('/kpi-import', [KpiImportController::class, 'showUploadForm'])->name('kpi-import');
        Route::post('/kpi-import/parse', [KpiImportController::class, 'parseFile'])->name('kpi-import.parse');
        Route::post('/kpi-import/ai-enrich', [KpiImportController::class, 'aiEnrich'])->name('kpi-import.ai-enrich');
        Route::post('/kpi-import/confirm', [KpiImportController::class, 'confirmImport'])->name('kpi-import.confirm');
        Route::get('/kpi-import/template', [KpiImportController::class, 'downloadTemplate'])->name('kpi-import.template');
    });
});
