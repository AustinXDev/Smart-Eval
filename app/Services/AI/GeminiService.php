<?php

namespace App\Services\AI;

use Override;
use RuntimeException;

class GeminiService implements AIProviderInterface
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {

        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        $this->model  = $_ENV['model'] ?? 'gemini-3.8-flash';

        if ($this->apiKey === '') {
            throw new RuntimeException(
                'Gemini API key is not configured.'
            );
        }

    }


    public function generate(string $prompt): string
    {

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $this->model,
            $this->apiKey
        );

        $payload = [
          'contents' => [
            [
              'parts' => [
                [
                  'text' => $prompt
                ]
              ]
            ]
          ]
        ];

        $ch = curl_init($url);

        curl_setopt_array(
            $ch,
            [
            CURLOPT_POST => true,

            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],

            CURLOPT_POSTFIELDS => json_encode(
                $payload,
                JSON_THROW_ON_ERROR
            ),

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_TIMEOUT => 60,

        ]
        );

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);

            curl_close($ch);

            throw new RuntimeException(
                'Gemini request failed: ' . $error
            );
        }

        $statusCode = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        $data = json_decode(
            $response,
            true
        );

        if ($statusCode >= 400) {
            throw new RuntimeException(
                $data['error']['message']
                ?? 'Gemini API request failed.'
            );
        }

        return $data['candidates'][0]['content']['parts'][0]['text']
            ?? '';

    }

}
