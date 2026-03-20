<?php

namespace App\Core\Domain\Ports\Repositories;

use App\Core\Domain\Entities\Branch;

interface BranchRepositoryPort
{
    /** @return Branch[] */
    public function getAll(): array;

    public function getById(int $id): ?Branch;

    public function save(Branch $branch): Branch;

    public function update(int $id, Branch $branch): Branch;

    public function delete(int $id): bool;

    /** @return Branch[] */
    public function searchByNameOrCity(string $term): array;
}