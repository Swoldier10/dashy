<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_embeddings', function (Blueprint $table) {
            $table->id();
            // Source identifies what was embedded — one of "task" | "project"
            // | "message". Combined with source_id it forms the unique key
            // we upsert against.
            $table->string('source_type', 16);
            $table->unsignedBigInteger('source_id');
            // Team scoping. Tasks/projects always have a team; messages may
            // not, in which case team_id is null and the row is reachable
            // only by the message's owner via the chat lookup. Indexed for
            // the candidate-set filter in SemanticSearchService.
            $table->unsignedBigInteger('team_id')->nullable()->index();
            // The chunked text we actually embedded. Kept verbatim so we can
            // re-embed on model upgrade without re-fetching the source.
            $table->text('text');
            // The vector itself — JSON-encoded list of floats. MySQL on Sail
            // doesn't have first-class vector support; cosine similarity is
            // computed in PHP over the (small) candidate set.
            $table->json('vector');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['source_type', 'source_id'], 'chat_embeddings_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_embeddings');
    }
};
