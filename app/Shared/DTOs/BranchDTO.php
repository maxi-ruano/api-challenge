<?php

namespace App\Shared\DTOs;

use App\Core\Domain\Entities\Branch;

class BranchDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $city,
        public readonly string $country,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?array $weather = null,
        public readonly array $employees = []
    ) {}

    public static function fromEntity(
        Branch $branch,
        ?array $weather = null,
        array $employees = []
    ): self {
        return new self(
            id: $branch->getId(),
            name: $branch->getName(),
            city: $branch->getCity(),
            country: $branch->getCountry(),
            latitude: $branch->getLatitude(),
            longitude: $branch->getLongitude(),
            weather: $weather,
            employees: array_map(
                fn($emp) => EmployeeDTO::fromEntity($emp),
                $employees
            )
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'weather' => $this->weather,
            'employees' => $this->employees
        ];
    }
}