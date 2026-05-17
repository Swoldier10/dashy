<?php

namespace App\Domains\Search\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per embedded source (task / project / message). Identified by the
 * (source_type, source_id) tuple — the embedding job upserts against it so a
 * task being re-saved re-embeds in place rather than spawning duplicates.
 *
 * @property int $id
 * @property string $source_type   "task" | "project" | "message"
 * @property int    $source_id
 * @property int|null $team_id
 * @property string $text
 * @property list<float> $vector
 * @property array<string, mixed>|null $metadata
 */
class Embedding extends Model
{
    protected $table = 'chat_embeddings';

    protected $fillable = ['source_type', 'source_id', 'team_id', 'text', 'vector', 'metadata'];

    protected $casts = [
        'vector' => 'array',
        'metadata' => 'array',
    ];
}
