<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\Customer\AccessibilityScanController;
use App\Http\Controllers\Api\Customer\AuthCustomerController;
use App\Http\Controllers\Api\Customer\PaymentController;
use App\Http\Controllers\Api\Customer\UserController;
use App\Http\Controllers\Api\Customer\WebsiteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthCustomerController::class)->group(function () {
    Route::post('/login', 'login')->name('adminAuth.login');
    Route::post('/otp-resend', 'reqOtpResend')->name('adminAuth.otp_resend');
    Route::post('/otp-verify', 'reqOtpVerify')->name('adminAuth.otp_verify');
    Route::post('/set-password', 'setNewPassword')->name('adminAuth.set_password');
    Route::post('/forgot-password', 'forgotPassword')->name('adminAuth.forgotPassword');
    Route::post('/register', 'reqSignup')->name('adminAuth.register');
    Route::post('/sso-login', 'ssoFirebaseLogin')->name('adminAuth.sso_login');
});
//Use Refresh Token
Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])->group(function () {
    Route::post('/refresh-token', [AuthCustomerController::class, 'refreshToken']);
});
//Use Access Token
Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {
    Route::controller(AuthCustomerController::class)->group(function () {
        Route::post('/user', 'getUser')->name('adminAuth.getUser');
        Route::post('/upload-photo', 'uploadPhoto')->name('adminAuth.upload_photo');
        Route::post('/logout', 'logout')->name('adminAuth.logout');
    });
    Route::controller(AccessibilityScanController::class)->group(function () {
        Route::get('/scan-history', 'getScanHistory')->name('adminAuth.scan_history');
        Route::get('/scan-details/{scanId}', 'getScanDetails')->name('adminAuth.scan_details');
        Route::get('/scan', 'scan')->name('adminAuth.scan');
    });

    Route::apiResource('websites', WebsiteController::class);
    Route::controller(UserController::class)->group(function () {
        Route::post('/account-information/{id}', 'updateAccountInformation')->name('adminAuth.update_account_information');
        Route::post('/account-details/{id}', 'updateAccountDetails')->name('adminAuth.update_account_details');
    });
    Route::prefix('payment')->controller(PaymentController::class)->group(function () {
        Route::post('create-intent', 'createIntent');
    });
});
// Webhook doesn't need auth



Route::prefix('payment/stripe')->controller(PaymentController::class)->group(function () {
    Route::get('status/{paymentIntentId}',  'getPaymentStatus');
    Route::post('payment/webhook', 'handleStripeWebhook');
});

// PayPal Routes
Route::prefix('payment/paypal')->controller(PaymentController::class)->group(function () {
    Route::get('success', 'paypalSuccess')->name('paypal.success');
    Route::get('cancel', 'paypalCancel')->name('paypal.cancel');
    Route::post('webhook', 'paypalWebhook')
        ->middleware('verify.paypal.webhook')
        ->name('paypal.webhook');
});
// SSLCommerz IPN Routes (No auth required)
Route::prefix('payment/sslcommerz')->controller(PaymentController::class)->group(function () {
    Route::post('success', 'sslcommerzSuccess');
    Route::post('fail', 'sslcommerzFail');
    Route::post('cancel', 'sslcommerzCancel');
    Route::post('ipn', 'sslcommerzIpn');
});





// Route::middleware('auth:sanctum')->group(function () {
//     Route::prefix('subscriptions')->group(function () {
//         Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
//         Route::get('my-subscriptions', [SubscriptionController::class, 'mySubscriptions']);
//         Route::post('{subscriptionId}/cancel', [SubscriptionController::class, 'cancel']);
//         Route::patch('{subscriptionId}/auto-renewal', [SubscriptionController::class, 'updateAutoRenewal']);
//     });
// });
