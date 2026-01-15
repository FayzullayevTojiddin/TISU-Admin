<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->boolean('status')->default(true);
            $table->json('details')->nullable();
            $table->timestamps();
            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};