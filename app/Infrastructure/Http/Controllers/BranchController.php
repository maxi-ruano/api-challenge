<?php

namespace App\Infrastructure\Http\Controllers;

use App\Core\Domain\Ports\Repositories\BranchRepositoryPort;
use App\Core\Domain\Ports\Services\WeatherServicePort;
use App\Core\Domain\ValueObjects\Latitude;
use App\Core\Domain\ValueObjects\Longitude;
use App\Core\Domain\Entities\Branch;
use App\Shared\DTOs\BranchDTO;
use App\Shared\Traits\ApiResponseTrait;
use App\Exceptions\WeatherApiException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Infrastructure\Persistence\Models\BranchModel;
use App\Infrastructure\Persistence\Models\EmployeeModel;


class BranchController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private BranchRepositoryPort $branchRepo,
        private WeatherServicePort $weatherService
    ) {}

    public function index()
    {
        try {
            $branches = $this->branchRepo->getAll();

            $result = array_map(function ($branch) {
                $employees = $this->getEmployeesForBranch($branch->getId());
                $weather = $this->getWeatherForBranch($branch);

                return $this->formatBranchResponse($branch, $employees, $weather);
            }, $branches);

            return $this->successResponse($result);
        } catch (\Exception $e) {
            Log::error('Error listing branches', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error fetching branches', 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180'
            ]);

            $branch = new Branch(
                id: null,
                name: $validated['name'],
                city: $validated['city'],
                country: $validated['country'],
                latitude: new Latitude($validated['latitude']),
                longitude: new Longitude($validated['longitude'])
            );

            $saved = $this->branchRepo->save($branch);

            return $this->successResponse(
                BranchDTO::fromEntity($saved)->toArray(),
                'Branch created successfully',
                201
            );
        } catch (ValidationException $e) {
            return $this->errorResponse('Invalid data', 422, $e->errors());
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Error creating branch', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error creating branch', 500);
        }
    }

    public function show(string $id)
    {
        $id = (int) $id;
        try {
            $branch = $this->branchRepo->getById($id);

            if (!$branch) {
                return $this->errorResponse('Branch not found', 404);
            }

            $employees = $this->getEmployeesForBranch($branch->getId());
            $weather = $this->getWeatherForBranch($branch);

            return $this->successResponse(
                $this->formatBranchResponse($branch, $employees, $weather)
            );
        } catch (\Exception $e) {
            Log::error('Error in show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error fetching branch', 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'city' => 'sometimes|string|max:255',
                'country' => 'sometimes|string|max:255',
                'latitude' => 'sometimes|numeric|between:-90,90',
                'longitude' => 'sometimes|numeric|between:-180,180'
            ]);

            $existingBranch = $this->branchRepo->getById($id);

            if (! $existingBranch) {
                return $this->errorResponse('Branch not found', 404);
            }

            $branch = new Branch(
                id: $id,
                name: $validated['name'] ??  $existingBranch->getName(),
                city: $validated['city'] ??  $existingBranch->getCity(),
                country: $validated['country'] ??  $existingBranch->getCountry(),
                latitude: isset($validated['latitude'])
                    ? new Latitude($validated['latitude'])
                    : new Latitude( $existingBranch->getLatitude()),
                longitude: isset($validated['longitude'])
                    ? new Longitude($validated['longitude'])
                    : new Longitude( $existingBranch->getLongitude())
            );

            $updatedBranch = $this->branchRepo->update($id, $branch);

            return $this->successResponse(
                BranchDTO::fromEntity($updatedBranch)->toArray(),
                'Branch updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse('Invalid data', 422, $e->errors());
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Error updating branch', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error updating branch', 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $branch = $this->branchRepo->getById($id);

            if (!$branch) {
                return $this->errorResponse('Branch not found', 404);
            }

            EmployeeModel::where('branch_id', $id)
                ->update(['branch_id' => null]);

            $deleted = $this->branchRepo->delete($id);

            if (!$deleted) {
                return $this->errorResponse('Branch could not be deleted', 500);
            }

            return $this->successResponse(null, 'Branch deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting branch', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error deleting branch', 500);
        }
    }

    public function search(Request $request)
    {
        try {

            $request->validate(['q' => 'required|string|min:2']);


            $branches = $this->branchRepo->searchByNameOrCity($request->q);


            $result = array_map(function ($branch) {
                $dto = BranchDTO::fromEntity($branch);
                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'city' => $dto->city,
                    'country' => $dto->country,
                    'latitude' => $dto->latitude,
                    'longitude' => $dto->longitude,
                    'weather' => null,
                    'employees' => []
                ];
            }, $branches);

            return $this->successResponse($result);
        } catch (ValidationException $e) {
            return $this->errorResponse('Invalid search term', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Error searching branches', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error searching branches', 500);
        }
    }

    private function getEmployeesForBranch(int $branchId): array
    {
        $model = BranchModel::with('employees')->find($branchId);

        if (!$model || !$model->employees) {
            return [];
        }

        return $model->employees->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'email' => $emp->email,
                'branch_id' => $emp->branch_id
            ];
        })->toArray();
    }
    private function getWeatherForBranch(Branch $branch): ?array
    {
        if (!$branch->getLatitude() || !$branch->getLongitude()) {
            return null;
        }

        try {
            return Cache::remember(
                "weather.{$branch->getLatitude()}.{$branch->getLongitude()}",
                1800,
                fn() => $this->weatherService->getByCoordinates(
                    $branch->getLatitude(),
                    $branch->getLongitude()
                )
            );
        } catch (WeatherApiException $e) {
            Log::warning('Error fetching weather', [
                'branch' => $branch->getId(),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    private function formatBranchResponse(Branch $branch, array $employees, ?array $weather = null): array
    {
        $dto = BranchDTO::fromEntity($branch);

        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'city' => $dto->city,
            'country' => $dto->country,
            'latitude' => $dto->latitude,
            'longitude' => $dto->longitude,
            'weather' => $weather,
            'employees' => $employees
        ];
    }
}
