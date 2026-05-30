<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\Contracts\PresentsToolCard;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Models\User;

/**
 * STRUCTURAL. Emits a short checklist of steps the assistant is about to
 * take. No DB writes, no UI confirmation — purely a transparency surface so
 * the user can see the plan before the assistant runs read/write tools.
 *
 * The chat runtime persists this with status='executed' (it never blocks the
 * loop) and a result body of {"acknowledged": true} so subsequent LLM turns
 * see that the plan was rendered.
 */
final class PlanTool implements AiTool, PresentsToolCard
{
    private const MIN_STEPS = 1;

    private const MAX_STEPS = 10;

    private const MAX_STEP_LENGTH = 200;

    public function name(): string
    {
        return 'plan';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::Structural;
    }

    public function description(): string
    {
        return 'Announce the steps you are about to take, so the user can see your reasoning before '
            .'any tool runs. Use this whenever the request needs more than one tool call. Each step '
            .'is one short imperative sentence (max 200 chars), in the user\'s language. 1–10 steps.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'steps' => [
                    'type' => 'array',
                    'items' => ['type' => 'string', 'maxLength' => self::MAX_STEP_LENGTH],
                    'minItems' => self::MIN_STEPS,
                    'maxItems' => self::MAX_STEPS,
                ],
            ],
            'required' => ['steps'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $steps = $arguments['steps'] ?? null;
        if (! is_array($steps)) {
            return AiToolValidationResult::fail(['steps must be a non-empty array of strings.']);
        }

        $normalized = [];
        foreach ($steps as $step) {
            if (! is_string($step)) {
                continue;
            }
            $trimmed = trim($step);
            if ($trimmed === '') {
                continue;
            }
            if (mb_strlen($trimmed) > self::MAX_STEP_LENGTH) {
                return AiToolValidationResult::fail([
                    sprintf('Each step must be %d characters or fewer.', self::MAX_STEP_LENGTH),
                ]);
            }
            $normalized[] = $trimmed;
        }

        if (count($normalized) < self::MIN_STEPS) {
            return AiToolValidationResult::fail(['Provide at least one step.']);
        }
        if (count($normalized) > self::MAX_STEPS) {
            return AiToolValidationResult::fail([
                sprintf('Provide at most %d steps.', self::MAX_STEPS),
            ]);
        }

        return AiToolValidationResult::ok(['steps' => $normalized]);
    }

    /**
     * Structural tools never reach `execute()` through the normal confirm
     * path. The runtime calls `dispatchToolCall` in SendMessageService which
     * leaves status='pending' for structural tools — but a plan card has no
     * Apply/Discard buttons and resolves itself. Returning an acknowledgement
     * payload here is defensive insurance.
     */
    public function execute(User $user, array $arguments): array
    {
        return ['acknowledged' => true, 'steps' => $arguments['steps'] ?? []];
    }

    public function presentCard(array $toolCall, User $user): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $result = is_array($toolCall['result'] ?? null) ? $toolCall['result'] : [];

        // Steps may live under result.steps (post-execution) or arguments.steps
        // (failed validation). Prefer result for fidelity.
        $steps = is_array($result['steps'] ?? null)
            ? array_values(array_filter(array_map('strval', $result['steps'])))
            : array_values(array_filter(array_map('strval', (array) ($args['steps'] ?? []))));

        return [
            'name' => 'plan',
            'status' => (string) ($toolCall['status'] ?? 'executed'),
            'mode' => 'structural_auto',
            'steps' => $steps,
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
        ];
    }
}
