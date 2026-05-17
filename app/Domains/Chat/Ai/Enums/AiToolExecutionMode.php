<?php

namespace App\Domains\Chat\Ai\Enums;

/**
 * How a tool's `execute()` is invoked by the chat runtime.
 *
 * The runtime collects every tool call the LLM emits in one turn, then routes
 * each by its mode: AutoRead runs immediately and feeds the result back to the
 * LLM in the same turn; ConfirmWrite persists a pending card and pauses the
 * loop until the user resolves it; Structural tools don't run at all — they
 * just shape the conversation (plan, ask_user_choice).
 */
enum AiToolExecutionMode: string
{
    case AutoRead = 'auto_read';
    case ConfirmWrite = 'confirm_write';
    case Structural = 'structural';

    /**
     * Reserved for long-running tools that should be queued and polled rather
     * than blocking the chat turn (e.g. generate_tasks_from_brief,
     * summarize_range). Tools declaring this mode persist as `queued` and a
     * dispatched job runs `execute()` in the background; the card polls and
     * re-renders when the job updates the row to `created`/`failed`.
     *
     * The runtime path that fans these out lives in SendMessageService::
     * dispatchToolCall — see the TODO in that branch for the wiring.
     */
    case AsyncWrite = 'async_write';
}
