<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\BranchModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchModel extends Model
{
    use HasFactory; 
    protected $table = 'branches';

    protected $fillable = [
        'name',
        'city',
        'country',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(EmployeeModel::class, 'branch_id');
    }
    protected static function newFactory()
    {
        return BranchModelFactory::new();
    }
}