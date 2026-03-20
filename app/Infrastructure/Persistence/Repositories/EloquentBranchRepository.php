<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Core\Domain\Entities\Branch;
use App\Core\Domain\Ports\Repositories\BranchRepositoryPort;
use App\Core\Domain\ValueObjects\Latitude;
use App\Core\Domain\ValueObjects\Longitude;
use App\Infrastructure\Persistence\Models\BranchModel;

class EloquentBranchRepository implements BranchRepositoryPort
{
    public function getAll(): array
    {
        return BranchModel::with('employees')
            ->get()
            ->map(fn($model) => $this->toEntity($model))
            ->toArray();
    }

    public function getById(int $id): ?Branch
    {
        $model = BranchModel::with('employees')->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function save(Branch $branch): Branch
    {
        $model = BranchModel::create([
            'name' => $branch->getName(),
            'city' => $branch->getCity(),
            'country' => $branch->getCountry(),
            'latitude' => $branch->getLatitude(),
            'longitude' => $branch->getLongitude()
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, Branch $branch): Branch
    {
        $model = BranchModel::findOrFail($id);
        $model->update([
            'name' => $branch->getName(),
            'city' => $branch->getCity(),
            'country' => $branch->getCountry(),
            'latitude' => $branch->getLatitude(),
            'longitude' => $branch->getLongitude()
        ]);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return BranchModel::destroy($id) > 0;
    }

    public function searchByNameOrCity(string $term): array
    {
        return BranchModel::with('employees')
            ->where('name', 'LIKE', "%{$term}%")
            ->orWhere('city', 'LIKE', "%{$term}%")
            ->get()
            ->map(fn($model) => $this->toEntity($model))
            ->toArray();
    }

    private function toEntity(BranchModel $model): Branch
    {
        return new Branch(
            id: $model->id,
            name: $model->name,
            city: $model->city,
            country: $model->country,
            latitude: new Latitude($model->latitude),
            longitude: new Longitude($model->longitude)
        );
    }
}