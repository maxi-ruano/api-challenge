<?php

namespace App\Infrastructure\Http\Controllers;

use App\Core\Domain\Ports\Repositories\EmployeeRepositoryPort;
use App\Core\Domain\Ports\Repositories\BranchRepositoryPort;
use App\Core\Domain\Entities\Employee;
use App\Core\Domain\ValueObjects\Email;
use App\Shared\DTOs\EmployeeDTO;
use App\Shared\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private EmployeeRepositoryPort $employeeRepo,
        private BranchRepositoryPort $branchRepo
    ) {}


    public function index(Request $request)
    {
        try {
            $branchId = $request->get('branch_id');

            $employees = $this->employeeRepo->getAll(
                $branchId ? (int) $branchId : null
            );

            $result = array_map(
                fn($e) => EmployeeDTO::fromEntity($e)->toArray(),
                $employees
            );

            return $this->successResponse($result);
        } catch (\Exception $e) {
            Log::error('Error listing employees', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error fetching employees', 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:employees,email',
                'branch_id' => 'required|integer|exists:branches,id'
            ]);

            $branch = $this->branchRepo->getById($validated['branch_id']);
            if (!$branch) {
                return $this->errorResponse('Branch not found', 404);
            }

            $employee = new Employee(
                id: null,
                name: $validated['name'],
                email: new Email($validated['email']),
                branchId: $validated['branch_id']
            );

            $saved = $this->employeeRepo->save($employee);

            return $this->successResponse(
                EmployeeDTO::fromEntity($saved)->toArray(),
                'Employee created successfully',
                201
            );
        } catch (ValidationException $e) {
            return $this->errorResponse('Invalid data', 422, $e->errors());
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Error creating employee', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error creating employee', 500);
        }
    }


    public function show(int $id)
    {
        try {
            $employee = $this->employeeRepo->getById($id);

            if (!$employee) {
                return $this->errorResponse('Employee not found', 404);
            }

            return $this->successResponse(
                EmployeeDTO::fromEntity($employee)->toArray()
            );
        } catch (\Exception $e) {
            Log::error('Error fetching employee', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error fetching employee', 500);
        }
    }


    public function update(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:employees,email,' . $id,
                'branch_id' => 'sometimes|integer|exists:branches,id'
            ]);

            $existing = $this->employeeRepo->getById($id);

            if (!$existing) {
                return $this->errorResponse('Employee not found', 404);
            }

            if (isset($validated['branch_id'])) {
                $branch = $this->branchRepo->getById($validated['branch_id']);
                if (!$branch) {
                    return $this->errorResponse('Branch not found', 404);
                }
            }

            $employee = new Employee(
                id: $id,
                name: $validated['name'] ?? $existing->getName(),
                email: isset($validated['email'])
                    ? new Email($validated['email'])
                    : new Email($existing->getEmail()),
                branchId: $validated['branch_id'] ?? $existing->getBranchId()
            );

            $updated = $this->employeeRepo->update($id, $employee);

            return $this->successResponse(
                EmployeeDTO::fromEntity($updated)->toArray(),
                'Employee updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse('Invalid data', 422, $e->errors());
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Error updating employee', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error updating employee', 500);
        }
    }


    public function destroy(int $id)
    {
        try {
            $employee = $this->employeeRepo->getById($id);

            if (!$employee) {
                return $this->errorResponse('Employee not found', 404);
            }

            $deleted = $this->employeeRepo->delete($id);

            if (!$deleted) {
                return $this->errorResponse('Employee could not be deleted', 500);
            }

            return $this->successResponse(null, 'Employee deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting employee', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error deleting employee', 500);
        }
    }
}
