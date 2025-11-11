<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submodule extends Model
{
    // Use the default connection instead of 'attendance'
    protected $connection = 'mysql';

    protected $table = 'submodules';

    protected $fillable = [
        'module_id',
        'module',
        'submodule',
        'employee_id',
        'is_trained',
    ];

    protected $casts = [
        'is_trained' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function dynamicModule(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}