<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Submodule;
use App\Models\EmployeeDetails;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeController extends Controller
{
    /**
     * Display a listing of all employees
     */
    public function index()
    {
        $employees = Employee::orderBy('name', 'asc')->get();
        
        return view('employees.index', [
            'employees' => $employees
        ]);
    }

    /**
     * Display the specified employee with their assignments
     */
    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        
        // Get employee details from employeedetails table
        $employeeDetails = EmployeeDetails::where('emp_id', $id)->first();
        
        // If no employee details found, create an empty object to avoid errors
        if (!$employeeDetails) {
            $employeeDetails = new EmployeeDetails();
        }
        
        // Get all assignments for this employee
        $assignments = Submodule::where('employee_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($assignment) {
                // Add human-readable module and submodule names
                $assignment->module_name = ucfirst(str_replace('-', ' ', $assignment->module));
                
                // Get submodule/product name
                if ($assignment->module === 'production') {
                    $product = \App\Models\Product::find($assignment->submodule);
                    $assignment->submodule_name = $product ? $product->name : 'Unknown Product';
                } else {
                    $assignment->submodule_name = $this->getSubmoduleName($assignment->module, $assignment->submodule);
                }
                
                return $assignment;
            });

        return view('employees.show', [
            'employee' => $employee,
            'employeeDetails' => $employeeDetails,
            'assignments' => $assignments
        ]);
    }

    /**
     * Get submodule name from slug
     */
    /**
     * Export employees data to Excel
     */
    public function exportToExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Position');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Phone');
        $sheet->setCellValue('E1', 'Available Resources');
        $sheet->setCellValue('F1', 'Trainable Resources');
        
        // Style the header
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        
        // Get all employees with their assignments
        $employees = Employee::all();
        $row = 2;
        
        foreach ($employees as $employee) {
            $availableResources = [];
            $trainableResources = [];
            
            // Get all assignments for this employee
            $assignments = Submodule::where('employee_id', $employee->id)->get();
            
            foreach ($assignments as $assignment) {
                $moduleName = ucfirst(str_replace('-', ' ', $assignment->module));
                $submoduleName = '';
                
                if ($assignment->module === 'production') {
                    $product = \App\Models\Product::find($assignment->submodule);
                    $submoduleName = $product ? $product->name : 'Unknown Product';
                } else {
                    $submoduleName = $this->getSubmoduleName($assignment->module, $assignment->submodule);
                }
                
                $resourceString = $moduleName . ' - ' . $submoduleName;
                
                if ($assignment->is_trained) {
                    $trainableResources[] = $resourceString;
                } else {
                    $availableResources[] = $resourceString;
                }
            }
            
            // Write employee data
            $sheet->setCellValue('A' . $row, $employee->name);
            $sheet->setCellValue('B' . $row, $employee->position);
            $sheet->setCellValue('C' . $row, $employee->email);
            $sheet->setCellValue('D' . $row, $employee->phone);
            $sheet->setCellValue('E' . $row, implode("\n", $availableResources));
            $sheet->setCellValue('F' . $row, implode("\n", $trainableResources));
            
            // Auto-wrap text for resources columns
            $sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            
            $row++;
        }
        
        // Auto-size columns
        foreach(range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create the Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'employees_report_' . date('Y-m-d') . '.xlsx';
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    private function getSubmoduleName($module, $subSlug)
    {
        $submodulesList = [
            'onsite-service' => [
                'add-fire-alarm' => 'Add Fire Alarm',
                'add-pa' => 'Add PA',
                'add-talkback' => 'Add Talkback',
                'conv-fire-alarm' => 'Conv Fire Alarm',
                'conv-lpg' => 'Conv LPG System',
                'add-lpg' => 'Add LPG System',
                'networkable-fire-alarm' => 'Networkable Fire Alarm with Repeator Panel',
            ],
            'materials' => [
                'planning' => 'Materials Planning',
                'store' => 'Store Process',
                'purchase' => 'Purchase',
            ],
            'it' => [
                'amudhu-app' => 'Amudhu App',
                'website' => 'Website Design and Deployment',
                'erp' => 'ERP using PHP',
            ],
            'marketing' => [
                'datasheet' => 'Datasheet Preparation',
                'manual' => 'User Manual Preparation',
                'catalog' => 'Catalog Preparation',
            ],
            'sales' => [
                'quotation' => 'Quotation Preparation',
                'bill' => 'Bill Preparation',
                'payment' => 'Payment Follow-up',
            ],
            'accounts' => [
                'entry' => 'Accounts Entry',
                'gst' => 'GST Filing',
            ],
            'rnd' => [
                'schematic' => 'Schematic Preparation',
                'pcb' => 'PCB Designing',
            ],
        ];

        return $submodulesList[$module][$subSlug] ?? 'Unknown';
    }
}