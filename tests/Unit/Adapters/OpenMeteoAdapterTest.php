<?php

namespace Tests\Unit\Adapters;

use Tests\TestCase;
use App\Infrastructure\Adapters\OpenMeteoAdapter;
use App\Exceptions\WeatherApiException;
use Illuminate\Support\Facades\Http;

class OpenMeteoAdapterTest extends TestCase
{
    private OpenMeteoAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new OpenMeteoAdapter();
    }

    public function test_gets_weather_correctly(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current_weather' => [
                    'temperature' => 22.5,
                    'windspeed' => 15.3,
                    'winddirection' => 180
                ]
            ], 200)
        ]);

        $result = $this->adapter->getByCoordinates(-34.60, -58.38);

        $this->assertIsArray($result);
        $this->assertEquals(22.5, $result['temperature']);
        $this->assertEquals(15.3, $result['windspeed']);
    }

    public function test_throws_exception_when_api_is_down(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response(null, 500)
        ]);

        $this->expectException(WeatherApiException::class);
        
        $this->adapter->getByCoordinates(-34.60, -58.38);
    }
}