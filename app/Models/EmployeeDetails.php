<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDetails extends Model
{

    protected $table = 'employeedetails';
    protected $primaryKey = 'emp_id';
    public $incrementing = true;
    
    protected $fillable = [
        'name', 
        'date_of_birth', 
        'qualification', 
        'contact_number',
        'blood_group', 
        'father_no', 
        'native_address', 
        'local_address',
        'mother_no', 
        'date_of_joining', 
        'driving_license_no', 
        'aadhar_id', 
        'salary'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'salary' => 'decimal:2'
    ];
}