<?php

namespace App\Core\Domain\Ports\Repositories;

use App\Core\Domain\Entities\Employee;

interface EmployeeRepositoryPort
{
    /** @return Employee[] */
    public function getAll(?int $branchId = null): array;

    public function getById(int $id): ?Employee;

    public function save(Employee $employee): Employee;

    public function update(int $id, Employee $employee): Employee;

    public function delete(int $id): bool;
}