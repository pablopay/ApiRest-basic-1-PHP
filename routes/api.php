<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix"=> "v1", 'middleware'=>'auth:sanctum'], function () {
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoice/bulk', [InvoiceController::class, 'bulkStore']);
});
