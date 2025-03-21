<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentImportController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::where('school_id', auth()->user()->school_id)->get();
        return view('students.import', compact('classes'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'class_id' => 'required|exists:school_classes,id',
        ]);
        
        $file = $request->file('file');
        $path = $file->getRealPath();
        $records = array_map('str_getcsv', file($path));
        
        // Validate headers
        $headers = array_shift($records);
        $requiredColumns = ['name', 'admission_number'];
        $missingColumns = array_diff($requiredColumns, $headers);
        
        if (!empty($missingColumns)) {
            return back()->with('error', 'CSV file is missing required columns: ' . implode(', ', $missingColumns));
        }
        
        DB::beginTransaction();
        try {
            $count = 0;
            $errors = [];
            
            foreach ($records as $index => $record) {
                if (count($headers) !== count($record)) {
                    $errors[] = "Row " . ($index + 2) . " has an incorrect number of columns.";
                    continue;
                }
                
                $data = array_combine($headers, $record);
                
                // Validate required fields
                if (empty($data['name']) || empty($data['admission_number'])) {
                    $errors[] = "Row " . ($index + 2) . " is missing required data.";
                    continue;
                }
                
                // Check for duplicate admission number
                $existingStudent = Student::where('admission_number', $data['admission_number'])
                    ->where('school_id', auth()->user()->school_id)
                    ->first();
                    
                if ($existingStudent) {
                    $errors[] = "Row " . ($index + 2) . ": Student with admission number '{$data['admission_number']}' already exists.";
                    continue;
                }
                
                // Create student
                Student::create([
                    'school_id' => auth()->user()->school_id,
                    'name' => $data['name'],
                    'admission_number' => $data['admission_number'],
                    'class_id' => $request->class_id,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'parent_name' => $data['parent_name'] ?? null,
                    'parent_phone' => $data['parent_phone'] ?? null,
                    'parent_email' => $data['parent_email'] ?? null,
                    'status' => 'active',
                ]);
                
                $count++;
            }
            
            if (count($errors) > 0) {
                // If we have some successful imports but also errors
                if ($count > 0) {
                    DB::commit();
                    return back()->with('warning', $count . " students imported successfully, but the following errors occurred: " . implode(" ", $errors));
                } else {
                    DB::rollBack();
                    return back()->with('error', "Import failed. " . implode(" ", $errors));
                }
            }
            
            DB::commit();
            return redirect()->route('students.index')
                ->with('success', "{$count} students imported successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Error importing students: {$e->getMessage()}");
        }
    }
    
    public function downloadTemplate()
    {
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=student_import_template.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = ['name', 'admission_number', 'email', 'phone', 'parent_name', 'parent_phone', 'parent_email'];
        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['John Doe', 'ADM-001', 'john@example.com', '1234567890', 'Parent Name', '0987654321', 'parent@example.com']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}