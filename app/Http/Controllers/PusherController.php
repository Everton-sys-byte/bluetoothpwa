<?php

namespace App\Http\Controllers;

use App\Events\PusherBroadcast;
use Illuminate\Http\Request;

class PusherController extends Controller
{
    public function broadcast(Request $request) 
    {
        broadcast(new PusherBroadcast("public", $request->all()));

        return response()->json([
            'status' => 'OK',
            'scannedBeacon' => $request->all()
        ]);
    }
}
