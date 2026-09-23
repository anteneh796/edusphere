<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            AcademicSeeder::class,
            GradeCapacitySeeder::class,
            ContentBlockSeeder::class,
        ]);

        if (app()->environment(['local'])) {
            $this->call([
                DevSeeder::class,
                HrSeeder::class,
                DemoTeacherSeeder::class,
                DemoParentSeeder::class,
                DemoAttendanceSeeder::class,
            ]);
        }
    }
}
