<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedeemController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// -- Public ----------------------------------------------------------------

Route::view('/', 'home')->name('home');

Route::get('/new', [LinkController::class, 'create'])->name('links.create');
Route::post('/new', [LinkController::class, 'store'])
    ->middleware('throttle:generate')->name('links.store');

// One-time redemption endpoint - rate limited against token guessing.
Route::get('/r/{token}', [RedeemController::class, 'show'])
    ->middleware('throttle:redeem')->where('token', '[A-Fa-f0-9]{64}')->name('redeem.show');

// Payment gateway webhooks: CSRF-exempt by signature verification instead.
Route::post('/webhooks/{gateway}', [WebhookController::class, 'handle'])
    ->middleware('throttle:webhooks')->whereIn('gateway', ['crypto', 'walletpay'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('webhooks.handle');

// -- Guest-only ------------------------------------------------------------

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:auth');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:auth');

    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])
        ->middleware('throttle:auth')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:auth')->name('password.update');
});

// -- Authenticated ----------------------------------------------------------

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/verify-email', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/verify-email/resend', [VerificationController::class, 'send'])
        ->middleware('throttle:3,1')->name('verification.send');
});

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/links', [LinkController::class, 'index'])->name('links.index');
    Route::get('/links/bulk', [LinkController::class, 'bulkCreate'])->name('links.bulk');
    Route::post('/links/bulk', [LinkController::class, 'bulkStore'])
        ->middleware('throttle:generate')->name('links.bulk.store');
    Route::patch('/links/{link}/disable', [LinkController::class, 'disable'])->name('links.disable');

    Route::get('/points', [PointsController::class, 'index'])->name('points.index');
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/purchase', [WalletController::class, 'purchase'])
        ->middleware('throttle:10,1')->name('wallet.purchase');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// -- Admin -------------------------------------------------------------------

Route::middleware(['auth', 'active', 'verified', 'admin'])
    ->prefix('admin')->as('admin.')->group(function () {
        Route::get('/', Admin\AdminDashboardController::class)->name('dashboard');

        Route::get('/users', [Admin\AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/export', [Admin\AdminUserController::class, 'export'])->name('users.export');
        Route::get('/users/{user}', [Admin\AdminUserController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/status', [Admin\AdminUserController::class, 'updateStatus'])->name('users.status');
        Route::post('/users/{user}/points', [Admin\AdminUserController::class, 'adjustPoints'])->name('users.points');
        Route::post('/users/{user}/wallet', [Admin\AdminUserController::class, 'adjustWallet'])->name('users.wallet');
        Route::post('/users/{user}/wallet/freeze', [Admin\AdminUserController::class, 'toggleWalletFreeze'])->name('users.wallet.freeze');
        Route::post('/users/{uuid}/restore', [Admin\AdminUserController::class, 'restore'])->name('users.restore');

        Route::get('/links', [Admin\AdminLinkController::class, 'index'])->name('links.index');
        Route::get('/links/export', [Admin\AdminLinkController::class, 'export'])->name('links.export');
        Route::patch('/links/{link}/disable', [Admin\AdminLinkController::class, 'disable'])->name('links.disable');

        Route::get('/referrals', [Admin\AdminReferralController::class, 'index'])->name('referrals.index');
        Route::get('/payments', [Admin\AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/audit', [Admin\AdminAuditLogController::class, 'index'])->name('audit.index');

        Route::get('/settings', [Admin\AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [Admin\AdminSettingController::class, 'update'])->name('settings.update');
    });
