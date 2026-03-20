<?php

namespace App\Shared\DTOs;

use App\Core\Domain\Entities\Employee;

class EmployeeDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?int $branch_id,
        public readonly ?array $branch = null
    ) {}

    public static function fromEntity(
        Employee $employee,
        ?BranchDTO $branch = null
    ): self {
        return new self(
            id: $employee->getId(),
            name: $employee->getName(),
            email: $employee->getEmail(),
            branch_id: $employee->getBranchId(),
            branch: $branch?->toArray()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'branch_id' => $this->branch_id,
            'branch' => $this->branch
        ];
    }
}