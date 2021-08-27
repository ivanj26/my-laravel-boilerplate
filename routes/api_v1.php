<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Document\DocumentController;
use App\Http\Controllers\Api\Notification\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API
Route::group([
    'as' => 'api.v1.',
], function () {
    /**
     * PUBLIC APIs
     */
    Route::get('/', [IndexController::class, 'healthCheck']);

    Route::post('session/create', [AuthController::class, 'createSession']);
    Route::post('session/sign-up', [AuthController::class, 'signUp']);

    Route::get('documents/model/types', [DocumentController::class, 'documentableTypes']);
    Route::get('documents/{fileName}', [DocumentController::class, 'getByFilename']);

    /**
     * PRIVATE APIs
     */
    Route::middleware(['auth:sanctum'])
        ->group(function () {
            Route::post('session/revoke', [AuthController::class, 'revokeSession']);

            // Notification service
            Route::post('notifications/email/send', [NotificationController::class, 'emailSend']);
            Route::post('notifications/sms/send', [NotificationController::class, 'smsSend']);

            // Document service
            Route::post('documents', [DocumentController::class, 'store']);
            Route::post('documents/bulk', [DocumentController::class, 'bulkStore']);

            // Other service
            //
        });
});
