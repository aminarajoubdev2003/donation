<?php

use App\Http\Controllers\Mobile\DonationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/donate', [DonationController::class, 'create']);
Route::get('/donation/{id}/qr', [DonationController::class, 'showQR'])->name('donation.qr');
Route::post('/verify', [DonationController::class, 'verify'])->name('donation.verify');
