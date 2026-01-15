<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index('group_id');
            $table->index('status');
            $table->index('full_name');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            //
        });
    }
};
