<?php

namespace App\Core\Domain\Ports\Services;

interface WeatherServicePort
{
    public function getByCoordinates(float $lat, float $lon): array;
}