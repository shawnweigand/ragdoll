<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class HevyService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $headers;

    public function __construct()
    {
        $this->apiKey = config('services.hevy.key');
        $this->baseUrl = 'https://api.hevyapp.com/v1';
        $this->headers = [
            'api-key' => $this->apiKey,
        ];
    }

    public function getWorkouts()
    {
        $url = $this->baseUrl . '/workouts';

        $response = Http::get($url, [
            // 'page' => 1,
            // 'pageSize' => 10,
        ], $this->headers);

        return json_decode($response->body(), true);
    }

    public function getWorkoutEvents($since = "1970-01-01T00:00:00Z")
    {

        $url = $this->baseUrl . '/workouts/events';

        $response = Http::withHeaders($this->headers)
            ->get($url, [
                'since' => Carbon::parse($since)->toIso8601String(),
                // 'page' => 1,
                // 'pageSize' => 10,
            ]);

        return json_decode($response->body(), true);
    }
}