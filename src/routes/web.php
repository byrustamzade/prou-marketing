<?php

use Illuminate\Support\Facades\Route;
use MRustamzade\MarketingTouchpoints\Http\Controllers\MarketingController;

Route::get('/', [MarketingController::class, 'index'])->name('index');
