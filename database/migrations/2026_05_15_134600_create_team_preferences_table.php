<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('key', 64);
            $table->json('value');
            $table->timestamps();

            $table->unique(['team_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_preferences');
    }
};
