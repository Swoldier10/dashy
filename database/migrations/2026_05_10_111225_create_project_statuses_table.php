<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('category', 16);
            $table->string('name', 60);
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->index(['project_id', 'category', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_statuses');
    }
};
