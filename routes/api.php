<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\dashboard\CampaignController;
use App\Http\Controllers\dashboard\CampaignProjectController;
use App\Http\Controllers\dashboard\CityController;
use App\Http\Controllers\dashboard\DistrictController;
use App\Http\Controllers\dashboard\FinancialController;
use App\Http\Controllers\dashboard\GovernorateController;
use App\Http\Controllers\dashboard\ProjectController;
use App\Http\Controllers\dashboard\ProjectMediaController;
use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\Mobile\DonationController;
use App\Http\Controllers\mobile\Inkind_donationController;
use App\Http\Controllers\Web\DonatersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/save-fcm-token', [FcmTokenController::class, 'saveFcmToken']);

Route::controller(AuthController::class)->group(function (){
   Route::post('/register','register');
   Route::post('/login','login');
   Route::get('/logout','logout')->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(GovernorateController::class)->group(function (){
   Route::post('/governorate/add','store');
   Route::post('/governorate/update/{uuid}','update');
    Route::get('/governorates/all','index');
    Route::post('/governorate/search','searchByname');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(CityController::class)->group(function (){
   Route::post('/city/add','store');
   Route::post('/city/update/{uuid}','update');
   Route::get('/city/index','index');
   Route::post('/city/search','searchByname');
   Route::post('/city/filter','filter');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(DistrictController::class)->group(function (){
   Route::post('/district/add','store');
   Route::post('/district/update/{uuid}','update');
   Route::get('/district/index','index');
   Route::post('/district/search','searchByname');
   Route::post('/district/filter','filter');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(ProjectController::class)->group(function (){
   Route::post('/project/add','store');
   Route::post('/project/update/{uuid}','update');
   Route::post('/projects/filter', 'filter');
   Route::post('/project/search','searchByname');
   Route::get('/project/delete/{uuid}','delete');
   Route::get('/project/restore/{uuid}','restore');
   Route::get('/project/index','index');
   Route::get('/project/sectors','get_sector');
   Route::get('/project/status','get_status');
   Route::get('/project/show/{uuid}','show');
   Route::get('/project/fundingsource','get_funding_source');
   Route::get('/project/deleted','deleted');
});


Route::middleware(['auth:sanctum', 'admin'])->controller(ProjectMediaController::class)->group(function (){
   Route::post('/project/upload/{uuid}','uploadMedia');
   Route::post('/project/update-upload/{uuid}','uploadMedia');
   Route::get('/cover-image/delete/{uuid}','delete_one');
   Route::get('/images/delete/{uuid}/{index}','deleteImageUsingModel');
   Route::get('/videos/delete/{uuid}/{index}','deleteVideoUsingModel');
   Route::post('/projects/details/add/{uuid}', 'addDetails');
   Route::post('/project/details/update/{uuidp}/{uuid}','updateDetails');
   Route::get('/details/all/{uuidp}','all_details');
   Route::get('detail/delete/{uuidp}/{uuid}','delete_detail');
   Route::get('detail/restore/{uuid}','restore_detail');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(CampaignController::class)->group(function (){
   Route::post('/campaign/add','store');
   Route::post('/campaign/update/{uuid}','update');
   Route::post('/campaign/filter', 'filter');
   Route::post('/campaign/searchByname','searchByname');
   Route::get('/campaign/index','index');
   Route::get('/campaign/show/{uuid}','show');
   Route::post('/campaign/filter','filter');
   Route::get('/campaign/delete/{uuid}','delete');
   Route::get('/campaign/restore/{uuid}','restore');
   Route::get('/campaign/stop/{uuid}','stop');
   Route::get('/campaign/status','get_status');
   Route::get('/campaign/deleted','deleted');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(CampaignProjectController::class)->group(function (){
   Route::post('/campaign/project/add/{uuid}','store');
   Route::get('/campaign/project/delete/{uuidc}/{uuidp}','delete');
   Route::get('/campaign/project/restore/{uuidc}/{uuidp}','restore');
   Route::post('/project/campaign/{uuid}', 'addCampaignToProject');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(DonatersController::class)->group(function (){
   Route::get('/donaters/all','Get_Donaters');
   Route::get('/campaign/project/restore/{uuidc}/{uuidp}','restore');
});

Route::post('/save-fcm-token', [FcmTokenController::class, 'saveFcmToken'])->middleware('auth:sanctum');

/*webRoute*/
Route::post('/donate/directly', [DonationController::class, 'donate_directly'])->middleware('auth:sanctum');
Route::get('/donation/qr', [DonationController::class, 'showQR'])->middleware('auth:sanctum');
Route::post('/verify/{uuid}', [DonationController::class, 'verify'])->middleware('auth:sanctum');
Route::post('/pledge', [DonationController::class, 'pledge_to_donate'])->middleware('auth:sanctum');
Route::post('/donate/pledge/{uuid}', [DonationController::class, 'donate_for_pledge'])->middleware('auth:sanctum');
Route::get('/show/image/{uuid}', [DonationController::class, 'show_img'])->middleware('auth:sanctum');
Route::post('/donation/add' , [Inkind_donationController::class, 'store'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'admin'])->controller(Inkind_donationController::class)->group(function (){
   Route::get('/donation/all','index');
   Route::post('/donation/update/{uuid}','update');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(FinancialController::class)->group(function (){
   Route::get('/exchange_rates/all','index');
   Route::post('/exchange_rate/update/{uuid}','update');
});
