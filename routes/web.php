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
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RDAccountController;
use App\Http\Controllers\RDAgentAccount;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Auth;
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
    Route::get('/agents/{agent}/document/{type}', [AgentController::class, 'viewDocument'])->name('agents.document');
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
    
    // Admin Lot Management Routes
    Route::resource('lots', LotController::class)->names([
        'index' => 'admin.lots.index',
        'create' => 'admin.lots.create',
        'store' => 'admin.lots.store',
        'show' => 'admin.lots.show',
        'edit' => 'admin.lots.edit',
        'update' => 'admin.lots.update',
        'destroy' => 'admin.lots.destroy'
    ]);
    
    // Admin Additional Lot Routes
    Route::get('lots-import', [LotController::class, 'showImport'])->name('admin.lots.import');
    Route::post('lots-import', [LotController::class, 'import'])->name('admin.lots.import.store');
    Route::post('lots/{lot}/assign-collection', [LotController::class, 'assignCollection'])->name('admin.lots.assign-collection');
    Route::post('lots/{lot}/remove-collection', [LotController::class, 'removeCollection'])->name('admin.lots.remove-collection');
    Route::get('lots/{lot}/download-errors', [LotController::class, 'downloadErrors'])->name('admin.lots.download-errors');
    Route::post('lots/{lot}/finalize', [LotController::class, 'finalize'])->name('admin.lots.finalize');
    Route::post('lots/{lot}/verify', [LotController::class, 'verify'])->name('admin.lots.verify');
    
    // Excel Import Routes
    Route::get('excel-import', [ExcelImportController::class, 'index'])->name('admin.excel-import.index');
    Route::post('excel-import/upload', [ExcelImportController::class, 'upload'])->name('admin.excel-import.upload');
    Route::post('excel-import/import', [ExcelImportController::class, 'import'])->name('admin.excel-import.import');
    Route::get('excel-import/agents', [ExcelImportController::class, 'getAgents'])->name('admin.excel-import.agents');
    
    // API route to fetch customer's RD accounts for duplicate checking
    Route::get('customers/{customer}/rd-accounts', [CustomerController::class, 'getRdAccounts'])->name('admin.customers.rd-accounts');
    
    // Log Viewer Routes - Accessible only to admins
    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->name('admin.logs.index');
});

// Agent Routes
Route::prefix('agent')->middleware(['auth', 'agent.access'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // RD Agent Accounts Routes
    Route::resource('rd-agent-accounts', RDAgentAccount::class)->names([
        'index' => 'agent.rd-agent-accounts.index',
        'create' => 'agent.rd-agent-accounts.create',
        'store' => 'agent.rd-agent-accounts.store',
        'show' => 'agent.rd-agent-accounts.show',
        'edit' => 'agent.rd-agent-accounts.edit',
        'update' => 'agent.rd-agent-accounts.update',
        'destroy' => 'agent.rd-agent-accounts.destroy'
    ])->parameters([
        'rd-agent-accounts' => 'rdAccount'
    ]);
    
    Route::post('rd-agent-accounts/{rdAccount}/close', [RDAgentAccount::class, 'close'])->name('agent.rd-agent-accounts.close');
    Route::post('rd-agent-accounts/{rdAccount}/mature', [RDAgentAccount::class, 'mature'])->name('agent.rd-agent-accounts.mature');
    Route::get('rd-agent-accounts-export', [RDAgentAccount::class, 'export'])->name('agent.rd-agent-accounts.export');
    
    // RD Account completion routes
    Route::get('rd-accounts/incomplete', [RDAgentAccount::class, 'getIncompleteRdAccounts'])->name('agent.rd-accounts.api.incomplete');
    Route::post('rd-accounts/{rdAccount}/complete', [RDAgentAccount::class, 'completeRdAccount'])->name('agent.rd-accounts.complete');
    
    // Lot Management Routes for Agents
    Route::resource('lots', LotController::class)->names([
        'index' => 'agent.lots.index',
        'create' => 'agent.lots.create',
        'store' => 'agent.lots.store',
        'show' => 'agent.lots.show',
        'edit' => 'agent.lots.edit',
        'update' => 'agent.lots.update',
        'destroy' => 'agent.lots.destroy'
    ])->middleware('check.incomplete');
    
    // Additional Lot Routes (with incomplete customers check)
    Route::middleware('check.incomplete')->group(function () {
        Route::get('lots-import', [LotController::class, 'showImport'])->name('agent.lots.import');
        Route::post('lots-import', [LotController::class, 'import'])->name('agent.lots.import.store');
        Route::post('lots/{lot}/assign-collection', [LotController::class, 'assignCollection'])->name('agent.lots.assign-collection');
        Route::post('lots/{lot}/remove-collection', [LotController::class, 'removeCollection'])->name('agent.lots.remove-collection');
        Route::post('lots/{lot}/finalize', [LotController::class, 'finalize'])->name('agent.lots.finalize');
    });
    
    // Routes that don't need completion check
    Route::get('lots/{lot}/download-errors', [LotController::class, 'downloadErrors'])->name('agent.lots.download-errors');
    
    // Other Agent Routes
    Route::resource('payments', PaymentController::class);
    Route::resource('collections', CollectionController::class)->middleware('check.incomplete:customers');
    
    // Excel Import Routes for Agents
    Route::get('excel-import', [ExcelImportController::class, 'index'])->name('agent.excel-import.index');
    Route::post('excel-import/upload', [ExcelImportController::class, 'upload'])->name('agent.excel-import.upload');
    Route::post('excel-import/import', [ExcelImportController::class, 'import'])->name('agent.excel-import.import');
    Route::get('excel-import/agents', [ExcelImportController::class, 'getAgents'])->name('agent.excel-import.agents');
    
    // Test route to check agent authentication
    Route::get('/test-agent-id', function () {
        $agent = Auth::user();
        return response()->json([
            'agent_id' => $agent ? $agent->id : null,
            'agent_name' => $agent ? $agent->name : null,
            'agent_email' => $agent ? $agent->email : null,
            'is_agent_model' => $agent instanceof \App\Models\Agent,
            'auth_guard' => Auth::getDefaultDriver(),
            'auth_check' => Auth::check(),
            'message' => 'Agent authentication test'
        ]);
    });
    
    // Agent Customer Management Routes
    Route::prefix('customers')->name('agent.customers.')->group(function () {
        Route::get('/', [App\Http\Controllers\Agent\CustomerController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Agent\CustomerController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Agent\CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [App\Http\Controllers\Agent\CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [App\Http\Controllers\Agent\CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [App\Http\Controllers\Agent\CustomerController::class, 'update'])->name('update');
        
        // AJAX routes for incomplete customer completion
        Route::get('/api/incomplete', [App\Http\Controllers\Agent\CustomerController::class, 'getIncompleteCustomers'])->name('api.incomplete');
        Route::post('/{customer}/complete', [App\Http\Controllers\Agent\CustomerController::class, 'completeCustomer'])->name('complete');
        
        // API route to fetch customer's RD accounts for duplicate checking
        Route::get('/{customer}/rd-accounts', [CustomerController::class, 'getRdAccounts'])->name('rd-accounts');
    });
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

// Test email route
Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('Test email from RD Agent System', function($message) {
            $message->to('softmitrapvtltd@gmail.com')->subject('Test Email from RD Agent');
        });
        
        return response()->json(['success' => true, 'message' => 'Email sent successfully!']);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
});
