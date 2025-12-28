<?php


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

Route::post('/test-beacon', function (Request $request) {
    $payload = $request->validate([
        'user_id' => ['required'],
        'name' => ['required', 'string'],
        'mac_address' => ['required', 'string'],
        'distance' => ['required','numeric'],
    ]);

    $payload['updated_at'] = now()->format('H:i:s');

    $cacheKey = "beacons_user_{$payload['user_id']}";
    $beacons = Cache::get($cacheKey, []);

    // Atenção: seu array estava usando $payload['mac_address'], não $payload['mac']
    $beacons[$payload['mac_address']] = $payload;

    // salva novamente no cache
    Cache::put($cacheKey, $beacons, 60);

    return response()->json(['status' => 'OK',]);
});

