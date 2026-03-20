<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\EmployeeModelFactory;


class EmployeeModel extends Model
{
    use HasFactory; 
    protected $table = 'employees';

    protected $fillable = [
        'name',
        'email',
        'branch_id'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }
        protected static function newFactory()
    {
        return EmployeeModelFactory::new();
    }
}