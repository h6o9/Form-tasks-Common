<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\SideMenueController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\SideMenuPermissionController;



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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/roles', [RoleController::class, 'store']);

Route::post('/permissions', [PermissionController::class, 'store']);
Route::post('/sidemenue', [SideMenueController::class, 'store']);

Route::post('/permission-insert', [SideMenuPermissionController::class, 'assignPermissions']);

// seo routes
Route::post('/seo-bulk', [SeoController::class, 'storeBulk'])
     ->name('seo.bulk-update');
//Auth routes for user
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/register-user', [AuthController::class, 'registerUser']);
Route::post('/user-login', [AuthController::class, 'login']);
Route::post('/forgotpassword', [AuthController::class, 'forgotPassword']);
Route::post('/forgotverifyotp', [AuthController::class, 'forgotverifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/resetpassword', [AuthController::class, 'resetPassword']);
Route::get('/form-index', [FormController::class, 'index']);
Route::post('/form-submission', [FormController::class, 'receiveData']);
Route::get('/form-responses', [FormController::class, 'getFormResponses']);
Route::get('/companies', [FormController::class, 'getCompanies']);
Route::get('/companies-for-form/{id}', [FormController::class, 'getCompaniesForForm']);
Route::get('/companies-form-details/{form_no}', [FormController::class, 'getCompaniesFormDetails']);






Route::middleware('auth:sanctum')->group(function () {
	Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('get-profile', [AuthController::class, 'getProfile']); // Get Profile
    Route::put('update-profile', [AuthController::class, 'updateProfile']); // Update Profile
	Route::post('/update-profile-verify', [AuthController::class, 'verifyAndUpdateContact']);
	    Route::get('/get-logged-in-user-info', [AuthController::class, 'getLoggedInUserInfo']);


    // Password reset for Admin & SubAdmin via API
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
    Route::get('/verify-reset-token/{token}', [AuthController::class, 'verifyResetToken']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

	
Route::post('/submit-contact-us', [ContactUsController::class, 'Submitcontact'])->name('contact.send');

  Route::post('/update-profile', [AuthController::class, 'requestUpdateOtp']);
    Route::post('/update-profile-verify', [AuthController::class, 'verifyAndUpdateContact']);
//contact us 
Route::post('/submit-contact-us', [ContactUsController::class, 'Submitcontact'])->name('contact.send');
Route::get('/getcontact', [ContactUsController::class, 'contactUs'])->name('getcontact');


});



	// Notifications
Route::get('/notifications', [NotificationController::class, 'getUserNotifications'])->middleware('auth:sanctum');
Route::get('/notification/{id}', [NotificationController::class, 'showNotification'])->middleware('auth:sanctum');
Route::post('/clearnotification', [NotificationController::class, 'clearAll'])->middleware('auth:sanctum');
Route::post('/notifications-seen', [NotificationController::class, 'seenNotification'])
    ->name('notifications.seen');
