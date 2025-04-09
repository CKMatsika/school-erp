<?php

namespace App\Imports;

use App\Models\Accounting\Contact; // Correct namespace for your Contact model
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Use heading row to map data
use Maatwebsite\Excel\Concerns\WithValidation; // Enable row validation
use Maatwebsite\Excel\Concerns\SkipsOnFailure; // Skip rows that fail validation
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure; // To handle validation failures
use Illuminate\Validation\Rule; // For validation rules like unique, in

class ContactsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;

    private $rowCount = 0;
    private $importedCount = 0;
    private $skippedRows = []; // To store rows that failed validation

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $this->rowCount++;

        // Basic check for essential data to avoid creating empty rows if validation skipped
        if (empty($row['name']) || empty($row['contact_type'])) {
             $this->skippedRows[] = ['row' => $this->rowCount, 'data' => $row, 'errors' => ['Missing essential data (Name or Contact Type).']];
             return null; // Skip this row explicitly
        }

        $contact = Contact::create([
            'name'           => $row['name'], // Assumes 'name' column header in file
            'contact_type'   => strtolower($row['contact_type']), // Assumes 'contact_type', convert to lowercase
            'email'          => $row['email'] ?? null,       // Assumes 'email' column header
            'phone'          => $row['phone'] ?? null,       // Assumes 'phone' column header
            'tax_number'     => $row['tax_number'] ?? null,  // Assumes 'tax_number'
            'address'        => $row['address'] ?? null,     // Assumes 'address'
            'city'           => $row['city'] ?? null,        // Assumes 'city'
            'state'          => $row['state'] ?? null,       // Assumes 'state'
            'postal_code'    => $row['postal_code'] ?? null, // Assumes 'postal_code'
            'country'        => $row['country'] ?? null,     // Assumes 'country'
            // Add other fields from your Contact model if they are in the CSV/Excel
            // 'is_active' => true, // Default if not in file
        ]);

        // Optional: Link to student if 'student_id' column exists and contact_type is 'student'
        // Be careful with matching students robustly (e.g., using student_number)
        // if (strtolower($row['contact_type']) === 'student' && !empty($row['student_identifier'])) {
        //    $student = \App\Models\Student::where('student_number', $row['student_identifier'])->first();
        //    if ($student) {
        //        $contact->student_id = $student->id;
        //        $contact->save();
        //    } else {
        //        // Log or store error that student wasn't found?
        //        $this->skippedRows[] = ['row' => $this->rowCount, 'data' => $row, 'errors' => ['Student identifier not found.']];
        //    }
        // }

        $this->importedCount++;
        return $contact; // Return the created model
    }

    /**
     * Define validation rules for each row.
     * Keys should match the *exact* heading names in your CSV/Excel file.
     */
    public function rules(): array
    {
        return [
            // Use '*.column_header' for row validation
            '*.name' => ['required', 'string', 'max:255'],
            '*.contact_type' => ['required', 'string', Rule::in(['customer', 'vendor', 'student', 'Customer', 'Vendor', 'Student'])], // Allow case variations?
            '*.email' => ['nullable', 'email', 'max:255', Rule::unique('contacts', 'email')], // Must be unique in contacts table if provided
            '*.phone' => ['nullable', 'string', 'max:50'],
            '*.tax_number' => ['nullable', 'string', 'max:100'],
            // Add rules for other columns you expect
             '*.address' => ['nullable', 'string'],
             '*.city' => ['nullable', 'string', 'max:100'],
             // '*.student_identifier' => ['nullable', 'string', /* Rule::exists('students', 'student_number') */ ], // If importing students
        ];
    }

    /**
     * Handle validation failures.
     * Store skipped rows and their errors.
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->skippedRows[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(), // Column header
                'errors' => $failure->errors(), // Array of error messages
                'data' => $failure->values(), // Original row data
            ];
        }
    }

    // --- Helper methods to get import results ---
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedRows(): array
    {
        return $this->skippedRows;
    }
}