<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LabController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLevelController;
use App\Http\Middleware\CheckMenuAccess;
use App\Http\Controllers\ConsumablesReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExportAllController;
use App\Http\Controllers\Auth\PasswordResetController;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:10,1')->name('password.update');        
});

Route::get('/dashboard/export-all', [ExportAllController::class, 'manifest'])->name('dashboard.export-all');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/lab', [LabController::class, 'switch'])->name('lab.switch');

    Route::prefix('m')->group(function () {

        // ---- Reports (menu url = key di config/reports.php: rep_o3, rep_o2a, rep_o2b, rep_nhmrc, rep_frez, rep_se)
        foreach (array_keys(config('reports', [])) as $rep) {
            Route::middleware(CheckMenuAccess::class . ':' . $rep)->group(function () use ($rep) {
                Route::get($rep, [ReportController::class, 'index'])->defaults('report', $rep)->name("report.$rep");
                Route::get("$rep/rows", [ReportController::class, 'rows'])->defaults('report', $rep)->name("report.$rep.rows");
                Route::get("$rep/export", [ReportController::class, 'export'])->defaults('report', $rep)->name("report.$rep.export");
            });
        }

        // ---- Lab Consumables - Report (menu url: consumables_report)
        Route::middleware(CheckMenuAccess::class . ':consumables_report')->group(function () {
            Route::get('consumables_report', [ConsumablesReportController::class, 'index'])->name('consumables.report');
            Route::get('consumables_report/products', [ConsumablesReportController::class, 'products'])->name('consumables.report.products');
            Route::get('consumables_report/rows', [ConsumablesReportController::class, 'rows'])->name('consumables.report.rows');
            Route::get('consumables_report/export', [ConsumablesReportController::class, 'export'])->name('consumables.report.export');
        });

     // ---- User settings (menu url: user)
        Route::middleware(CheckMenuAccess::class . ':user')->group(function () {
            Route::get('user', [UserController::class, 'index'])->name('user.index');
            Route::get('user/create', [UserController::class, 'create'])->name('user.create');
            Route::post('user', [UserController::class, 'store'])->name('user.store');
            Route::get('user/{id}/edit', [UserController::class, 'edit'])->whereNumber('id')->name('user.edit');
            Route::put('user/{id}', [UserController::class, 'update'])->whereNumber('id')->name('user.update');
            Route::delete('user/{id}', [UserController::class, 'destroy'])->whereNumber('id')->name('user.destroy');
        });

        // ---- User level and access (menu url: userlevel)
        Route::middleware(CheckMenuAccess::class . ':userlevel')->group(function () {
            Route::get('userlevel', [UserLevelController::class, 'index'])->name('userlevel.index');
            Route::get('userlevel/create', [UserLevelController::class, 'create'])->name('userlevel.create');
            Route::post('userlevel', [UserLevelController::class, 'store'])->name('userlevel.store');
            Route::get('userlevel/{id}/edit', [UserLevelController::class, 'edit'])->whereNumber('id')->name('userlevel.edit');
            Route::put('userlevel/{id}', [UserLevelController::class, 'update'])->whereNumber('id')->name('userlevel.update');
            Route::delete('userlevel/{id}', [UserLevelController::class, 'destroy'])->whereNumber('id')->name('userlevel.destroy');
            Route::get('userlevel/{id}/access', [UserLevelController::class, 'access'])->whereNumber('id')->name('userlevel.access');
            Route::put('userlevel/{id}/access/{menu}', [UserLevelController::class, 'setAccess'])
                ->whereNumber(['id', 'menu'])->name('userlevel.access.set');
        });

        Route::get('{module}/data',   [ModuleController::class, 'data'])->name('module.data');
        Route::get('{module}/export', [ModuleController::class, 'export'])->name('module.export');
        Route::get('{module}/detail/{id}', [ModuleController::class, 'detail'])->name('module.detail');
        Route::get('{module}',        [ModuleController::class, 'index'])->name('module.index');
        Route::get('{module}/export/{variant}', [ModuleController::class, 'export'])->name('module.export.variant');
    });
});