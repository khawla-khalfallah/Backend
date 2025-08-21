<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class HMSService
{
    public function generateToken()
    {
        $key = env('HMS_API_KEY');
        $secret = env('HMS_API_SECRET');

        $payload = [
            'access_key' => $key,
            'type' => 'management',
            'version' => 2,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60),
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    public function createRoom(string $roomName)
    {
        $token = $this->generateToken();

        $response = Http::withToken($token)
            ->post('https://api.100ms.live/v2/rooms', [
                'name' => $roomName,
                'template' => 'video-conferencing',
            ]);

        return $response->json();
    }
}
