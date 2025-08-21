<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DailyController extends Controller
{
    public function createRoom()
    {
        $apiKey = env('DAILY_API_KEY');
        $response = Http::withToken($apiKey)->post('https://api.daily.co/v1/rooms', [
            'properties' => [
                'exp' => time() + 3600,
            ]
        ]);

        if ($response->successful()) {
            return response()->json([
                'url' => $response['url'],
            ]);
        } else {
            return response()->json(['error' => 'Failed to create room'], 500);
        }
    }
}
