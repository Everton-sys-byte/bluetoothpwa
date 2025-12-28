<?php

use App\Events\BeaconUpdated;
use App\Http\Controllers\PusherController;
use Illuminate\Support\Facades\Cache;
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
    // return view('read-bluetooth');
});


Route::get('/bluetooth', function () {
    return view('bluetooth');
})->name('scan.bluetooth');

Route::get('/cortina', function () {
    return view('cortina');
});

Broadcast::routes();


