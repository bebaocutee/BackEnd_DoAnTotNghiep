<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id')->unique();
            $table->date('date_of_birth');
            $table->string('experience');
            $table->string('qualification');
            $table->string('introduction');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_infos');
    }
};
