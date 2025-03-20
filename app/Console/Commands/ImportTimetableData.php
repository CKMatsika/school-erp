<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTimetableData extends Command
{
    protected $signature = 'import:timetable-data {school_id?}';
    protected $description = 'Import data from the timetable database to the ERP database';

    public function handle()
    {
        $this->info('Starting timetable data import...');

        // Check for available schools
        $schools = DB::table('schools')->get();
        
        if ($schools->isEmpty()) {
            $this->error('No schools found in the database. Please create a school first.');
            return 1;
        }
        
        // Display available schools
        $this->info('Available schools:');
        foreach ($schools as $school) {
            $this->line(" - ID: {$school->id}, Name: {$school->name}");
        }
        
        // Get school ID from argument or ask for it
        $schoolId = $this->argument('school_id');
        if (!$schoolId) {
            $schoolId = $this->ask('Enter the ID of the school to import data for:');
        }
        
        // Validate school ID
        $schoolExists = $schools->firstWhere('id', $schoolId);
        if (!$schoolExists) {
            $this->error("School with ID {$schoolId} not found.");
            return 1;
        }
        
        $this->info("Importing data for school: {$schoolExists->name} (ID: {$schoolId})");

        // Set up temporary connection to timetable database
        config(['database.connections.timetable_source' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => 'school_timetable', 
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]]);
        
        try {
            DB::connection('timetable_source')->getPdo();
            $this->info('Successfully connected to timetable database.');
        } catch (\Exception $e) {
            $this->error('Failed to connect to timetable database: ' . $e->getMessage());
            return 1;
        }
        
        // Migrate periods
        $this->info('Importing periods...');
        try {
            $periods = DB::connection('timetable_source')->table('periods')->get();
            foreach ($periods as $period) {
                DB::table('timetable_periods')->insert([
                    'id' => $period->id,
                    'name' => $period->name,
                    'start_time' => $period->start_time,
                    'end_time' => $period->end_time,
                    'description' => $period->description,
                    'is_break' => $period->is_break,
                    'order' => $period->order,
                    'active' => $period->active,
                    'school_id' => $schoolId,
                    'created_at' => $period->created_at,
                    'updated_at' => $period->updated_at,
                    'deleted_at' => $period->deleted_at,
                ]);
            }
            $this->info('Imported ' . count($periods) . ' periods.');
        } catch (\Exception $e) {
            $this->error('Error importing periods: ' . $e->getMessage());
        }
        
        // Migrate school classes
        $this->info('Importing school classes...');
        try {
            $classes = DB::connection('timetable_source')->table('school_classes')->get();
            foreach ($classes as $class) {
                DB::table('timetable_school_classes')->insert([
                    'id' => $class->id,
                    'name' => $class->name,
                    'level' => $class->level,
                    'capacity' => $class->capacity,
                    'home_room' => $class->home_room,
                    'notes' => $class->notes,
                    'active' => $class->active,
                    'school_id' => $schoolId,
                    'created_at' => $class->created_at,
                    'updated_at' => $class->updated_at,
                    'deleted_at' => $class->deleted_at,
                ]);
            }
            $this->info('Imported ' . count($classes) . ' school classes.');
        } catch (\Exception $e) {
            $this->error('Error importing school classes: ' . $e->getMessage());
        }
        
        // Migrate subjects
        $this->info('Importing subjects...');
        try {
            $subjects = DB::connection('timetable_source')->table('subjects')->get();
            foreach ($subjects as $subject) {
                DB::table('timetable_subjects')->insert([
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'description' => $subject->description,
                    'color_code' => $subject->color_code,
                    'active' => $subject->active,
                    'school_id' => $schoolId,
                    'created_at' => $subject->created_at,
                    'updated_at' => $subject->updated_at,
                    'deleted_at' => $subject->deleted_at,
                ]);
            }
            $this->info('Imported ' . count($subjects) . ' subjects.');
        } catch (\Exception $e) {
            $this->error('Error importing subjects: ' . $e->getMessage());
        }
        
        // Migrate teachers
        $this->info('Importing teachers...');
        try {
            $teachers = DB::connection('timetable_source')->table('teachers')->get();
            foreach ($teachers as $teacher) {
                DB::table('timetable_teachers')->insert([
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'specialty' => $teacher->specialty,
                    'max_weekly_hours' => $teacher->max_weekly_hours,
                    'phone_number' => $teacher->phone_number,
                    'notes' => $teacher->notes,
                    'active' => $teacher->active,
                    'school_id' => $schoolId,
                    'created_at' => $teacher->created_at,
                    'updated_at' => $teacher->updated_at,
                    'deleted_at' => $teacher->deleted_at,
                ]);
            }
            $this->info('Imported ' . count($teachers) . ' teachers.');
        } catch (\Exception $e) {
            $this->error('Error importing teachers: ' . $e->getMessage());
        }
        
        // Migrate timetable templates
        $this->info('Importing timetable templates...');
        try {
            $templates = DB::connection('timetable_source')->table('timetable_templates')->get();
            foreach ($templates as $template) {
                DB::table('timetable_templates')->insert([
                    'id' => $template->id,
                    'name' => $template->name,
                    'academic_year' => $template->academic_year,
                    'term' => $template->term,
                    'start_date' => $template->start_date,
                    'end_date' => $template->end_date,
                    'description' => $template->description,
                    'is_active' => $template->is_active,
                    'days_of_week' => $template->days_of_week,
                    'school_id' => $schoolId,
                    'created_at' => $template->created_at,
                    'updated_at' => $template->updated_at,
                    'deleted_at' => $template->deleted_at,
                ]);
            }
            $this->info('Imported ' . count($templates) . ' timetable templates.');
        } catch (\Exception $e) {
            $this->error('Error importing timetable templates: ' . $e->getMessage());
        }
        
        // Migrate timetable entries
        $this->info('Importing timetable entries...');
        try {
            $entries = DB::connection('timetable_source')->table('timetable_entries')->get();
            foreach ($entries as $entry) {
                DB::table('timetable_entries')->insert([
                    'id' => $entry->id,
                    'timetable_template_id' => $entry->timetable_template_id,
                    'school_class_id' => $entry->school_class_id,
                    'subject_id' => $entry->subject_id,
                    'teacher_id' => $entry->teacher_id,
                    'period_id' => $entry->period_id,
                    'day_of_week' => $entry->day_of_week,
                    'room' => $entry->room,
                    'notes' => $entry->notes,
                    'created_at' => $entry->created_at,
                    'updated_at' => $entry->updated_at,
                    'deleted_at' => $entry->deleted_at,
                ]);
            }
            $this->info('Imported ' . count($entries) . ' timetable entries.');
        } catch (\Exception $e) {
            $this->error('Error importing timetable entries: ' . $e->getMessage());
        }
        
        // Migrate timetable exceptions
        $this->info('Importing timetable exceptions...');
        try {
            $exceptions = DB::connection('timetable_source')->table('timetable_exceptions')->get();
            foreach ($exceptions as $exception) {
                DB::table('timetable_exceptions')->insert([
                    'id' => $exception->id,
                    'timetable_entry_id' => $exception->timetable_entry_id,
                    'exception_date' => $exception->exception_date,
                    'is_canceled' => $exception->is_canceled,
                    'replacement_teacher_id' => $exception->replacement_teacher_id,
                    'replacement_room' => $exception->replacement_room,
                    'reason' => $exception->reason,
                    'note' => $exception->note,
                    'created_at' => $exception->created_at,
                    'updated_at' => $exception->updated_at,
                    'deleted_at' => $exception->deleted_at,
                ]);
            }
            $this->info('Imported ' . count($exceptions) . ' timetable exceptions.');
        } catch (\Exception $e) {
            $this->error('Error importing timetable exceptions: ' . $e->getMessage());
        }
        
        $this->info('Timetable data import completed successfully!');
        
        return 0;
    }
}