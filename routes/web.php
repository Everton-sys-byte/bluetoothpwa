<?php

use App\Http\Controllers\PusherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/bluetooth', function () {
    return view('bluetooth');
})->name('scan.bluetooth');

// Route::get('/pusher', [PusherController::class, 'index'])->name('pusher');
// Route::post('/broadcast', [PusherController::class, 'broadcast'])->name('broadcast');
// Route::post('/receive', [PusherController::class, 'receive'])->name('receive');
