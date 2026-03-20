<?php

namespace App\Core\Domain\Entities;

use App\Core\Domain\ValueObjects\Email;

class Employee
{
    public function __construct(
        private ?int $id,
        private string $name,
        private Email $email,
        private ?int $branchId
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email->value();
    }

    public function getBranchId(): ?int 
    {
        return $this->branchId;
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
    }
}