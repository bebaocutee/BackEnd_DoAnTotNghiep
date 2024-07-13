<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Course;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        User::create([
            'full_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123123'),
            'role' => User::ROLE_ADMIN,
        ]);
        collect([
            'Toán lớp 1',
            'Toán lớp 2',
            'Toán lớp 3',
            'Toán lớp 4',
            'Toán lớp 5',
            'Ôn tập hè lớp 1',
            'Ôn tập hè lớp 2',
            'Ôn tập hè lớp 3',
        ])->map(function ($name) {
            Course::create([
                'course_name' => $name,
                'description' => 'Cùng học, cùng đam mê Toán học, đồng thời phát triển tư duy và năng lực toán học',
                'admin_id' => 1,
            ]);
        });
    }
}
