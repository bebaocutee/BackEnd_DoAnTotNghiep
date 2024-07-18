<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\TeacherInfo;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'full_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123123'),
            'role' => User::ROLE_ADMIN,
        ]);

        User::create([
            'full_name' => 'Phạm Thị Lan Anh',
            'email' => 'lananh@gmail.com',
            'password' => bcrypt('123123'),
            'role' => User::ROLE_STUDENT,
        ]);

        User::create([
            'full_name' => 'Hoàng Văn Kiên',
            'email' => 'hvkien@gmail.com',
            'password' => bcrypt('123123'),
            'role' => User::ROLE_TEACHER,
        ]);

        TeacherInfo::create([
            'teacher_id' => 1,
            'date_of_birth' => '1990-01-01',
            'experience' => '10 năm',
            'work_unit' => 'Trường Tiểu học Achimedes',
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

        collect([
            'Chương 1: Làm quen với một số hình',
            'Chương 2: Các số đến 10',
            'Chương 3: Phép cộng, phép trừ trong phạm vi 10',
            'Chương 4: Các số tới 20',
            'Chương 5: Các số tới 100'
        ])->map(function ($name) {
            Chapter::create([
                'chapter_name' => $name,
                'course_id' => 1,
                'teacher_id' => 1
            ]);
        });

        collect([
            'Bài 1: Vị trí',
            'Bài 2: Khối hộp chữ nhật - Khối lập phương',
            'Bài 3: Hình tròn - Hình tam giác - Hình vuông - Hình chữ nhật',
            'Bài 4: Xếp hình'
        ])->map(function ($name) {
            Lesson::create([
                'lesson_name' => $name,
                'chapter_id' => 1,
                'lesson_type' => Lesson::TYPE_LESSON,
                'teacher_id' => 1,
            ]);
        });

        Lesson::create([
            'lesson_name' => 'Kiểm tra chương 1',
            'chapter_id' => 1,
            'lesson_type' => Lesson::TYPE_EXERCISE,
            'teacher_id' => 1,
        ]);
    }
}
