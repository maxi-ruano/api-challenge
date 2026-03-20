<?php

namespace App\Infrastructure\Adapters;

use App\Core\Domain\Ports\Services\WeatherServicePort;
use Illuminate\Support\Facades\Http;
use App\Exceptions\WeatherApiException;
use Illuminate\Http\Client\ConnectionException;

class OpenMeteoAdapter implements WeatherServicePort
{
    private string $url;
    private int $timeout;

    public function __construct()
    {
        $this->url = config('services.open_meteo.url');
        $this->timeout = config('services.open_meteo.timeout');
    }
    public function getByCoordinates(float $lat, float $lon): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->url, [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current_weather' => true
                ]);

            if ($response->failed()) {
                throw new WeatherApiException(
                    "OpenMeteo responded with error: {$response->status()}"
                );
            }

            $data = $response->json();

            return [
                'temperature' => $data['current_weather']['temperature'] ?? null,
                'windspeed' => $data['current_weather']['windspeed'] ?? null,
                'winddirection' => $data['current_weather']['winddirection'] ?? null
            ];
        } catch (ConnectionException $e) {
            throw new WeatherApiException('Timeout connecting to OpenMeteo', 504);
        }
    }
}
