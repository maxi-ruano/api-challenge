<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Core\Domain\Entities\Employee;
use App\Core\Domain\ValueObjects\Email;
use App\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
use App\Infrastructure\Persistence\Models\BranchModel;
use App\Infrastructure\Persistence\Models\EmployeeModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EloquentEmployeeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentEmployeeRepository $repository;
    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentEmployeeRepository();
        $this->branch = BranchModel::factory()->create();
    }

    public function test_can_save_employee(): void
    {
        $employee = new Employee(
            id: null,
            name: 'John Doe',
            email: new Email('john@example.com'),
            branchId: $this->branch->id
        );

        $saved = $this->repository->save($employee);

        $this->assertNotNull($saved->getId());
        $this->assertEquals('John Doe', $saved->getName());
        $this->assertEquals('john@example.com', $saved->getEmail());
        $this->assertEquals($this->branch->id, $saved->getBranchId());

        $this->assertDatabaseHas('employees', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'branch_id' => $this->branch->id
        ]);
    }

    public function test_can_get_all_employees(): void
    {
        EmployeeModel::factory()->count(3)->create(['branch_id' => $this->branch->id]);

        $employees = $this->repository->getAll();

        $this->assertCount(3, $employees);
        $this->assertContainsOnlyInstancesOf(Employee::class, $employees);
    }

    public function test_can_get_employees_filtered_by_branch(): void
    {
        $branch2 = BranchModel::factory()->create();

        EmployeeModel::factory()->count(2)->create(['branch_id' => $this->branch->id]);
        EmployeeModel::factory()->count(3)->create(['branch_id' => $branch2->id]);

        $employees = $this->repository->getAll($this->branch->id);

        $this->assertCount(2, $employees);
    }

    public function test_can_get_employee_by_id(): void
    {
        $model = EmployeeModel::factory()->create(['branch_id' => $this->branch->id]);

        $employee = $this->repository->getById($model->id);

        $this->assertNotNull($employee);
        $this->assertEquals($model->name, $employee->getName());
        $this->assertEquals($model->email, $employee->getEmail());
    }

    public function test_returns_null_for_nonexistent_employee(): void
    {
        $employee = $this->repository->getById(999);

        $this->assertNull($employee);
    }

    public function test_can_update_employee(): void
    {
        $model = EmployeeModel::factory()->create(['branch_id' => $this->branch->id]);

        $updatedEmployee = new Employee(
            id: $model->id,
            name: 'Updated Name',
            email: new Email('updated@example.com'),
            branchId: $this->branch->id
        );

        $result = $this->repository->update($model->id, $updatedEmployee);

        $this->assertEquals('Updated Name', $result->getName());
        $this->assertEquals('updated@example.com', $result->getEmail());

        $this->assertDatabaseHas('employees', [
            'id' => $model->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_can_delete_employee(): void
    {
        $model = EmployeeModel::factory()->create(['branch_id' => $this->branch->id]);

        $deleted = $this->repository->delete($model->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('employees', ['id' => $model->id]);
    }

    public function test_delete_returns_false_for_nonexistent_employee(): void
    {
        $deleted = $this->repository->delete(999);

        $this->assertFalse($deleted);
    }
}