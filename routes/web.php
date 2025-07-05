<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\DownloadController;

Route::group(['prefix' => 'downloads'], function () {
    Route::get('/template-tp/{id?}/{rombongan_belajar_id?}/{pembelajaran_id?}', [DownloadController::class, 'template_tp']);
});
Route::get('{any?}', function() {
    return view('application');
})->where('any', '.*');
