<?php

namespace App\Exceptions;

use Exception;

class WeatherApiException extends Exception
{
    protected $code = 502;
    
    public function __construct(
        string $message = "Error al consultar el servicio de clima",
        int $code = 502,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}