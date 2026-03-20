<?php

namespace App\Core\Domain\ValueObjects;

class Longitude
{
    private float $value;

    public function __construct(float $value)
    {
        if ($value < -180 || $value > 180) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180');
        }
        $this->value = $value;
    }

    public function value(): float
    {
        return $this->value;
    }
}