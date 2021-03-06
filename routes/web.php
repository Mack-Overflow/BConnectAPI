<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\{
    CampaignController,
    SmsController,
    UploadRecordsController,
    TestEndpointController,
    ReviewController,
    IncomingSmsController,
    RedemptionController,
};
use App\Http\Controllers\Auth\{
    LoginController,
    LogoutController,
};

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Login/Logout Routes
Route::post('/login', LoginController::class);
Route::post('/logout', LogoutController::class);

// get curr user
Route::get('/get-user', [TestEndpointController::class, 'getUser']);

// Route::get('campaign', [CampaignController::class, 'index'])->name('campaign.index');
Route::get('sendSMS', [SmsController::class, 'index']);
Route::post('uploadRecords', [UploadRecordsController:: class, 'upload']);
Route::post('/createCampaign', [CampaignController::class, 'create']);
Route::post('/send-campaign', [CampaignController::class, 'send']);
Route::post('/update-campaign', [CampaignController::class, 'update']);

Route::post('/fetch-subscribers', [UploadRecordsController::class, 'fetch']);
Route::post('/fetch-campaigns', [CampaignController::class, 'fetch']);

// Reviews
Route::get('/reviews/fetch/{businessId}/{reviewId}', [ReviewController::class, 'fetchReview']);
Route::get('/reviews/fetch-all/{businessId}', [ReviewController::class, 'fetchAll']);
Route::post('/reviews/review-data', [ReviewController::class, 'fetchData']);
Route::post('/reviews/store', [ReviewController::class, 'store']);

// Redemptions
Route::get('/redemptions/data/fetch-all/{businessId}', [RedemptionController::class, 'fetchAll']);

// Handle Short URL links
Route::get('/link/{shortUrl}', [LinkController::class, 'index']);

// Twilio incoming webhooks
Route::post('/receive-sms', [IncomingSmsController::class, 'receiveSms']);
// Route::post('/receive-sms', [IncomingSmsController::class, 'receiveSms']);
Route::post('/delivery-status', [IncomingSmsController::class, 'deliveryStatus']);

