<?php

namespace Tests\Feature\Http;

use Tests\TestCase;
use App\Infrastructure\Persistence\Models\BranchModel;
use App\Infrastructure\Persistence\Models\EmployeeModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = BranchModel::factory()->create();
    }

    public function test_can_list_employees(): void
    {
        EmployeeModel::factory()->count(3)->create(['branch_id' => $this->branch->id]);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_employees_by_branch(): void
    {
        $branch2 = BranchModel::factory()->create();

        EmployeeModel::factory()->count(2)->create(['branch_id' => $this->branch->id]);
        EmployeeModel::factory()->count(3)->create(['branch_id' => $branch2->id]);

        $response = $this->getJson("/api/employees?branch_id={$this->branch->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_employee(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'branch_id' => $this->branch->id
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'John Doe')
            ->assertJsonPath('data.email', 'john@example.com');

        $this->assertDatabaseHas('employees', $data);
    }

    public function test_cannot_create_employee_with_invalid_email(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'branch_id' => $this->branch->id
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_create_employee_with_duplicate_email(): void
    {
        EmployeeModel::factory()->create([
            'email' => 'duplicate@example.com',
            'branch_id' => $this->branch->id
        ]);

        $data = [
            'name' => 'Another Person',
            'email' => 'duplicate@example.com',
            'branch_id' => $this->branch->id
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_get_employee(): void
    {
        $employee = EmployeeModel::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->getJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', $employee->name)
            ->assertJsonPath('data.email', $employee->email);
    }

    public function test_returns_404_for_nonexistent_employee(): void
    {
        $response = $this->getJson('/api/employees/999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Employee not found');
    }

    public function test_can_update_employee(): void
    {
        $employee = EmployeeModel::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->putJson("/api/employees/{$employee->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com');

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_can_update_employee_partially(): void
    {
        $employee = EmployeeModel::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'branch_id' => $this->branch->id
        ]);

        $response = $this->putJson("/api/employees/{$employee->id}", [
            'name' => 'Only Name Updated'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Only Name Updated')
            ->assertJsonPath('data.email', 'original@example.com');
    }

    public function test_can_delete_employee(): void
    {
        $employee = EmployeeModel::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_cannot_delete_nonexistent_employee(): void
    {
        $response = $this->deleteJson('/api/employees/999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Employee not found');
    }
}