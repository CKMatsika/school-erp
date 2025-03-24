<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddStudentManagementModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get the actual columns from your modules table
        $columns = DB::getSchemaBuilder()->getColumnListing('modules');
        
        // Base data for the module
        $data = [
            'name' => 'Student Management',
            'key' => 'student',
            'description' => 'Manage students, applications, enrollments, guardians and academic records',
            'route' => 'student.dashboard',
            'active' => true,
            'order' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Remove any keys that don't exist in the actual table
        $filteredData = array_intersect_key($data, array_flip($columns));
        
        DB::table('modules')->insert($filteredData);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('modules')->where('key', 'student')->delete();
    }
}