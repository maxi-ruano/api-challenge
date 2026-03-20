<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Core\Domain\Entities\Employee;
use App\Core\Domain\Ports\Repositories\EmployeeRepositoryPort;
use App\Core\Domain\ValueObjects\Email;
use App\Infrastructure\Persistence\Models\EmployeeModel;

class EloquentEmployeeRepository implements EmployeeRepositoryPort
{
    public function getAll(?int $branchId = null): array
    {
        $query = EmployeeModel::with('branch');
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get()
            ->map(fn($model) => $this->toEntity($model))
            ->toArray();
    }

    public function getById(int $id): ?Employee
    {
        $model = EmployeeModel::with('branch')->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function save(Employee $employee): Employee
    {
        $model = EmployeeModel::create([
            'name' => $employee->getName(),
            'email' => $employee->getEmail(),
            'branch_id' => $employee->getBranchId()
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, Employee $employee): Employee
    {
        $model = EmployeeModel::findOrFail($id);
        $model->update([
            'name' => $employee->getName(),
            'email' => $employee->getEmail(),
            'branch_id' => $employee->getBranchId()
        ]);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return EmployeeModel::destroy($id) > 0;
    }

    private function toEntity(EmployeeModel $model): Employee
    {
        return new Employee(
            id: $model->id,
            name: $model->name,
            email: new Email($model->email),
            branchId: $model->branch_id
        );
    }
}