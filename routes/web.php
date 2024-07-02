<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalculationController;
Route::get('/', function () {
    return view('welcome');
});

Route::post('/uploadCsv', [CalculationController::class, 'uploadCsv'])->name('uploadCsv');
