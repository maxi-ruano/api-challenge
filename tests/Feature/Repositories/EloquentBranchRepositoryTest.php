<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Core\Domain\Entities\Branch;
use App\Core\Domain\ValueObjects\Latitude;
use App\Core\Domain\ValueObjects\Longitude;
use App\Infrastructure\Persistence\Repositories\EloquentBranchRepository;
use App\Infrastructure\Persistence\Models\BranchModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EloquentBranchRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentBranchRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentBranchRepository();
    }

    public function test_can_save_branch(): void
    {
        $branch = new Branch(
            id: null,
            name: 'Test Branch',
            city: 'Buenos Aires',
            country: 'Argentina',
            latitude: new Latitude(-34.60),
            longitude: new Longitude(-58.38)
        );

        $saved = $this->repository->save($branch);

        $this->assertNotNull($saved->getId());
        $this->assertEquals('Test Branch', $saved->getName());
        
        $this->assertDatabaseHas('branches', [
            'name' => 'Test Branch',
            'latitude' => -34.60
        ]);
    }

    public function test_can_get_all_branches(): void
    {
        BranchModel::factory()->count(3)->create();

        $branches = $this->repository->getAll();

        $this->assertCount(3, $branches);
        $this->assertContainsOnlyInstancesOf(Branch::class, $branches);
    }

    public function test_can_search_by_name(): void
    {
        BranchModel::factory()->create(['name' => 'Downtown']);
        BranchModel::factory()->create(['name' => 'Uptown']);
        BranchModel::factory()->create(['name' => 'Midtown']);

        $results = $this->repository->searchByNameOrCity('Downtown');

        $this->assertCount(1, $results);
        $this->assertEquals('Downtown', $results[0]->getName());
    }
}