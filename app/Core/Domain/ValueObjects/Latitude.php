<?php

namespace App\Core\Domain\ValueObjects;

class Latitude
{
    private float $value;

    public function __construct(float $value)
    {
        if ($value < -90 || $value > 90) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90');
        }
        $this->value = $value;
    }

    public function value(): float
    {
        return $this->value;
    }
}