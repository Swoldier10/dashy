<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_calendar_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')
                ->constrained('google_calendar_connections')
                ->cascadeOnDelete();
            $table->morphs('syncable');
            $table->string('google_event_id');
            $table->string('etag')->nullable();
            $table->timestamp('last_synced_at');
            $table->timestamps();

            $table->unique(['connection_id', 'google_event_id']);
            $table->unique(['connection_id', 'syncable_type', 'syncable_id'], 'gcal_links_connection_syncable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_calendar_links');
    }
};
