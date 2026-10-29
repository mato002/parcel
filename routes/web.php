<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DeploymentOperationController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PublicVerificationController;
use App\Services\PaymentGatewayManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function (PaymentGatewayManager $gateways) {
    return view('welcome', ['gatewayName' => $gateways->current()->name()]);
})->name('home');

Route::get('/verify/{token}', [PublicVerificationController::class, 'show'])->name('verify.show')->middleware('throttle:20,1');
Route::post('/verify/{claim}/initialize', [PublicVerificationController::class, 'initialize'])->name('verify.initialize')->middleware('throttle:5,1');
Route::get('/verify/{token}/status/{reference}', [PublicVerificationController::class, 'status'])->name('verify.status')->middleware('throttle:30,1');
Route::get('/verify/{token}/complete', [PublicVerificationController::class, 'complete'])->name('verify.complete')->middleware('throttle:10,1');
Route::post('/webhooks/{gateway}', [PaymentWebhookController::class, 'handle'])->name('webhooks.gateway');
Route::post('/deploy-ops/{operation}', DeploymentOperationController::class)
    ->whereIn('operation', ['migrate', 'cache-clear'])
    ->middleware('throttle:3,1')
    ->name('deploy-ops.run');

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1');
Route::middleware(['auth', 'can:manage-payments'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/claims', [AdminController::class, 'claims'])->name('claims');
    Route::get('/claims/create', [AdminController::class, 'createClaim'])->name('claims.create');
    Route::post('/claims', [AdminController::class, 'storeClaim'])->name('claims.store');
    Route::post('/claims/bulk-status', [AdminController::class, 'bulkUpdateClaims'])->name('claims.bulk-status');
    Route::get('/claims/{claim}/edit', [AdminController::class, 'editClaim'])->name('claims.edit');
    Route::put('/claims/{claim}', [AdminController::class, 'updateClaim'])->name('claims.update');
    Route::delete('/claims/{claim}', [AdminController::class, 'destroyClaim'])->name('claims.destroy');
    Route::get('/cards', [AdminController::class, 'cards'])->name('cards');
    Route::delete('/cards/{card}', [AdminController::class, 'destroyCard'])->name('cards.destroy');
    Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{transaction}', [AdminController::class, 'showTransaction'])->name('transactions.show');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    Route::post('/verification-links', [AdminController::class, 'createLink'])->name('links.create');
    Route::get('/verification-links', fn () => redirect()->route('admin.index'));
    Route::post('/cards/{card}/charge', [AdminController::class, 'charge'])->name('cards.charge');
    Route::post('/transactions/{transaction}/refund', [AdminController::class, 'refund'])->name('transactions.refund');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::get('/billing/products/create', [BillingController::class, 'createProduct'])->name('billing.products.create');
    Route::post('/billing/products', [BillingController::class, 'storeProduct'])->name('billing.products.store');
    Route::get('/billing/products/{product}/edit', [BillingController::class, 'editProduct'])->name('billing.products.edit');
    Route::put('/billing/products/{product}', [BillingController::class, 'updateProduct'])->name('billing.products.update');
    Route::delete('/billing/products/{product}', [BillingController::class, 'destroyProduct'])->name('billing.products.destroy');
    Route::get('/billing/subscriptions/create', [BillingController::class, 'createSubscription'])->name('billing.subscriptions.create');
    Route::post('/billing/subscriptions', [BillingController::class, 'storeSubscription'])->name('billing.subscriptions.store');
    Route::post('/billing/subscriptions/{subscription}/charge', [BillingController::class, 'chargeNow'])->name('billing.subscriptions.charge');
    Route::put('/billing/subscriptions/{subscription}/status', [BillingController::class, 'updateStatus'])->name('billing.subscriptions.status');
});
