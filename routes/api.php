
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;

Route::middleware('wallet.auth')->group(function () {

    Route::post('/credit', [WalletController::class, 'credit']);

    Route::post('/debit', [WalletController::class, 'debit']);

    Route::get('/balance/{user_id}', [WalletController::class, 'balance']);

});
