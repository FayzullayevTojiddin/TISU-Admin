<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('lesson_id');

            $table->boolean('came')->default(false);

            $table->timestamps();

            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('cascade');

            $table->foreign('lesson_id')
                ->references('id')->on('lessons')
                ->onDelete('cascade');

            $table->unique(['student_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};