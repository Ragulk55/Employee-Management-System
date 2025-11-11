<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    // Use the 'attendance' database connection
    protected $connection = 'attendance';

    // Specify the table name (if same, optional)
    protected $table = 'employees';

    // Primary key (optional if 'id')
    protected $primaryKey = 'id';

    // If no timestamps in that DB table
    public $timestamps = false;


    protected $fillable = [
        'name',
        'email',
        'position',
        'phone',
    ];

    /**
     * Get all submodule assignments for this employee
     */
    public function submoduleAssignments()
    {
        return $this->hasMany(Submodule::class, 'employee_id');
    }

    /**
     * Get dynamic submodule assignments for this employee
     */
    public function dynamicSubmoduleAssignments()
    {
        return $this->hasMany(DynamicSubmodule::class, 'employee_id');
    }

    /**
     * Get the count of assignments for this employee
     */
    public function getAssignmentCountAttribute()
    {
        return $this->submoduleAssignments()->count();
    }
}