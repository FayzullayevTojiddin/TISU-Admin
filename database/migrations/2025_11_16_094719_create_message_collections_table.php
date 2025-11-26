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
        Schema::create('message_collections', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->foreignId('template_id')
                ->constrained()
                ->onDelete('cascade');

            $table->integer('success_count')->default(0);

            $table->integer('failed_count')->default(0);

            $table->string('description')->nullable();

            $table->dateTime('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_collections');
    }
};
