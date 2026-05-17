<?php

namespace App\Domains\Chat\Ai\Contracts;

use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Models\User;

interface AiTool
{
    public function name(): string;

    public function description(): string;

    /**
     * How the chat runtime should route a call to this tool. Drives whether a
     * card is rendered, whether `execute()` runs synchronously inside the LLM
     * turn, and whether the loop pauses for user confirmation.
     */
    public function executionMode(): AiToolExecutionMode;

    /**
     * JSON-schema for the tool's `parameters` field, as required by the Codex Responses API.
     *
     * @return array<string, mixed>
     */
    public function parameters(): array;

    /**
     * @param  array<string, mixed>  $arguments
     * @param  Chat|null  $chat  the chat the tool call originated in — tools that
     *                           need it (e.g. to grab attachments from the prior
     *                           user message) can read; tools that don't can ignore.
     */
    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult;

    /**
     * Persist the tool's effect. Called only after validation has succeeded and the
     * user has confirmed the preview card.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>  payload stored under `tool_call.result`
     */
    public function execute(User $user, array $arguments): array;
}
