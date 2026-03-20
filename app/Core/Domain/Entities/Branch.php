<?php

namespace App\Core\Domain\Entities;

use App\Core\Domain\ValueObjects\Latitude;
use App\Core\Domain\ValueObjects\Longitude;

class Branch
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $city,
        private string $country,
        private Latitude $latitude,
        private Longitude $longitude
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getLatitude(): float
    {
        return $this->latitude->value();
    }

    public function getLongitude(): float
    {
        return $this->longitude->value();
    }

    public function changeLocation(Latitude $lat, Longitude $lon): void
    {
        $this->latitude = $lat;
        $this->longitude = $lon;
    }
}