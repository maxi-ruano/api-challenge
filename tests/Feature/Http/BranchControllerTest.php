<?php

namespace Tests\Feature\Http;

use Tests\TestCase;
use App\Infrastructure\Persistence\Models\BranchModel;
use App\Infrastructure\Persistence\Models\EmployeeModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class BranchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_branches(): void
    {
        BranchModel::factory()->count(3)->create();

        $response = $this->getJson('/api/branches');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_branch(): void
    {
        $data = [
            'name' => 'New Branch',
            'city' => 'Córdoba',
            'country' => 'Argentina',
            'latitude' => -31.42,
            'longitude' => -64.18
        ];

        $response = $this->postJson('/api/branches', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Branch');
        
        $this->assertDatabaseHas('branches', $data);
    }

    public function test_cannot_create_branch_with_invalid_latitude(): void
    {
        $data = [
            'name' => 'Invalid Branch',
            'city' => 'Córdoba',
            'country' => 'Argentina',
            'latitude' => -100,
            'longitude' => -64.18
        ];

        $response = $this->postJson('/api/branches', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_can_get_branch_with_weather(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current_weather' => [
                    'temperature' => 25.5,
                    'windspeed' => 10.2,
                    'winddirection' => 90
                ]
            ], 200)
        ]);

        $branch = BranchModel::factory()->create([
            'latitude' => -34.60,
            'longitude' => -58.38
        ]);

        $response = $this->getJson("/api/branches/{$branch->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.weather.temperature', 25.5);
    }

    public function test_can_update_branch(): void
    {
        $branch = BranchModel::factory()->create([
            'name' => 'Old Name'
        ]);

        $response = $this->putJson("/api/branches/{$branch->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
        
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_can_delete_branch(): void
    {
        $branch = BranchModel::factory()->create();

        $response = $this->deleteJson("/api/branches/{$branch->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }

    public function test_deleting_branch_detaches_employees(): void
    {
        $branch = BranchModel::factory()->create();
        EmployeeModel::factory()->count(2)->create([
            'branch_id' => $branch->id
        ]);

        $response = $this->deleteJson("/api/branches/{$branch->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('employees', [
            'branch_id' => null
        ]);
        $this->assertDatabaseCount('employees', 2);
    }

    public function test_can_search_branches_by_name(): void
    {
        BranchModel::factory()->create(['name' => 'Downtown']);
        BranchModel::factory()->create(['name' => 'Uptown']);
        BranchModel::factory()->create(['name' => 'Midtown']);

        $response = $this->getJson('/api/branches/search?q=Downtown');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Downtown');
    }
}