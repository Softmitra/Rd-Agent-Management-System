<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RDAccountController;
use App\Http\Controllers\RDAgentAccount;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | Web Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register web routes for your application. These
 * | routes are loaded by the RouteServiceProvider and all of them will
 * | be assigned to the "web" middleware group. Make something great!
 * |
 */

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Password Reset Routes
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});

// Admin Login Routes
Route::get('admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
Route::post('admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::resource('agents', AgentController::class);
    Route::post('/agents/{agent}/verify', [AgentController::class, 'verify'])->name('agents.verify');
    Route::post('/agents/{agent}/unverify', [AgentController::class, 'unverify'])->name('agents.unverify');
    Route::post('/agents/{agent}/activate', [AgentController::class, 'activate'])->name('agents.activate');
    Route::post('/agents/{agent}/deactivate', [AgentController::class, 'deactivate'])->name('agents.deactivate');
    Route::post('/agents/{agent}/update-expiration', [AgentController::class, 'updateExpiration'])->name('agents.update-expiration');
    Route::resource('rd-accounts', RDAccountController::class)->names([
        'index' => 'admin.rd-accounts.index',
        'create' => 'admin.rd-accounts.create',
        'store' => 'admin.rd-accounts.store',
        'show' => 'admin.rd-accounts.show',
        'edit' => 'admin.rd-accounts.edit',
        'update' => 'admin.rd-accounts.update',
        'destroy' => 'admin.rd-accounts.destroy'
    ]);
    // Route::get('/agents/{agent}/customers', [RDAccountController::class, 'getCustomersByAgent'])
    //     ->name('agents.customers');
    Route::post('rd-accounts/{rdAccount}/close', [RDAccountController::class, 'close'])->name('admin.rd-accounts.close');
    Route::post('rd-accounts/{rdAccount}/mature', [RDAccountController::class, 'mature'])->name('admin.rd-accounts.mature');
    Route::resource('roles', RoleController::class);
});

// Agent Routes
Route::prefix('agent')->middleware(['auth', 'agent.access'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('rd-agent-accounts', RDAgentAccount::class);
    Route::resource('rd-agent-accounts', RDAgentAccount::class)->names([
        'index' => 'agent.rd-agent-accounts.index',
        'create' => 'agent.rd-agent-accounts.create',
        'store' => 'agent.rd-agent-accounts.store',
        'show' => 'agent.rd-agent-accounts.show',
        'edit' => 'agent.rd-agent-accounts.edit',
        'update' => 'agent.rd-agent-accounts.update',
        'destroy' => 'agent.rd-agent-accounts.destroy'
    ]);

    Route::post('rd-agent-accounts/{rdAccount}/close', [RDAgentAccount::class, 'close'])->name('rd-agent-accounts.close');
    Route::post('rd-agent-accounts/{rdAccount}/mature', [RDAgentAccount::class, 'mature'])->name('rd-agent-accounts.mature');
    Route::resource('payments', PaymentController::class);
    Route::resource('collections', CollectionController::class);
});

// Common Routes (accessible to both agents and admins)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/agents/{agent}/customers', [RDAccountController::class, 'getCustomersByAgent'])
        ->name('agents.customers');
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');
    Route::get('collections/export', [CollectionController::class, 'export'])->name('admin.collections.export');
    Route::get('collectionslist/export', [CollectionController::class, 'exportList'])->name('admin.collectionslist.export');

    Route::get('rd-accounts/export', [RDAccountController::class, 'export'])->name('admin.rd-accounts.export');
    Route::get('rd-accounts/get-agent/{customerId}', [RDAccountController::class, 'getAgentByCustomer'])->name('admin.rd-accounts.get-agent');

    // Payment routes
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
});

// Verification Notice Route
Route::get('/verification-notice', function () {
    return view('auth.verification-notice');
})->name('verification.notice');

Route::get('/icons', function () {
    return view('icons');
})->name('icons');

Route::middleware(['auth', 'verified'])->group(function () {
    // Customer routes
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::resource('customers', CustomerController::class)->names([
        'index' => 'customers.index',
        'create' => 'customers.create',
        'store' => 'customers.store',
        'show' => 'customers.show',
        'edit' => 'customers.edit',
        'update' => 'customers.update',
        'destroy' => 'customers.destroy'
    ]);
});
