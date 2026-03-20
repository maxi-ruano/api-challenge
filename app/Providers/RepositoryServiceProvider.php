<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Domain\Ports\Repositories\BranchRepositoryPort;
use App\Core\Domain\Ports\Repositories\EmployeeRepositoryPort;
use App\Core\Domain\Ports\Services\WeatherServicePort;
use App\Infrastructure\Persistence\Repositories\EloquentBranchRepository;
use App\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
use App\Infrastructure\Adapters\OpenMeteoAdapter;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BranchRepositoryPort::class,
            EloquentBranchRepository::class
        );

        $this->app->bind(
            EmployeeRepositoryPort::class,
            EloquentEmployeeRepository::class
        );

        $this->app->bind(
            WeatherServicePort::class,
            OpenMeteoAdapter::class
        );
    }
}