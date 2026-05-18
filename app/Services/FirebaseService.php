<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class FirebaseService
{
    /**
     * 🔐 جلب Access Token من Google
     */
    public function getAccessToken()
    {
        $credentials = json_decode(
            file_get_contents(storage_path('app/firebase.json')),
            true
        );

        $now = time();

        $payload = [
            "iss" => $credentials['client_email'],
            "scope" => "https://www.googleapis.com/auth/firebase.messaging",
            "aud" => "https://oauth2.googleapis.com/token",
            "iat" => $now,
            "exp" => $now + 3600,
        ];

        // 🔥 إنشاء JWT
        $jwt = JWT::encode($payload, $credentials['private_key'], 'RS256');

        // 🔥 طلب Access Token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        // ❗ يرمي Exception إذا في خطأ
        $response->throw();

        return $response->json()['access_token'];
    }

    /**
     * 📩 إرسال إشعار عبر Firebase
     */
    public function sendNotification($token, $title, $body)
    {
        $accessToken = $this->getAccessToken();

        $credentials = json_decode(
            file_get_contents(storage_path('app/firebase.json')),
            true
        );

        $projectId = $credentials['project_id'];

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                "message" => [
                    "token" => $token,
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                    ],
                ],
            ]);

        // ❗ مهم جداً
        $response->throw();

        return $response->json();
    }
}
