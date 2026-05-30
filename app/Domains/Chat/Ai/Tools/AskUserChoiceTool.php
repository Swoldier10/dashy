<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\Contracts\PresentsToolCard;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use RuntimeException;

/**
 * Surfaces a multiple-choice picker in the chat UI when the assistant needs the
 * user to disambiguate between a fixed, small set of options. Unlike the other
 * tools, this one is NOT routed through ConfirmToolCallService — the user
 * resolves it by clicking an option (see AnswerChoiceService + ChatPanel
 * ::answerChoice). execute() is therefore a defensive trap, not a happy path.
 */
final class AskUserChoiceTool implements AiTool, PresentsToolCard
{
    private const MIN_OPTIONS = 2;

    private const MAX_OPTIONS = 6;

    private const MAX_OPTION_LENGTH = 80;

    public function name(): string
    {
        return 'ask_user_choice';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::Structural;
    }

    public function description(): string
    {
        return 'Ask the user to choose between a fixed, short list of options (2–6). '
            .'Use this whenever the next step needs the user to pick from a known set '
            .'(e.g. which team, which project) — never write the question as plain text. '
            .'The chat UI renders each option as a clickable button.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'question' => ['type' => 'string', 'maxLength' => 300],
                'options' => [
                    'type' => 'array',
                    'minItems' => self::MIN_OPTIONS,
                    'maxItems' => self::MAX_OPTIONS,
                    'items' => ['type' => 'string', 'maxLength' => self::MAX_OPTION_LENGTH],
                ],
            ],
            'required' => ['question', 'options'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $errors = [];

        $question = $arguments['question'] ?? null;
        if (! is_string($question) || trim($question) === '') {
            $errors[] = 'question is required.';
        } elseif (mb_strlen($question) > 300) {
            $errors[] = 'question must be 300 characters or less.';
        }

        $rawOptions = $arguments['options'] ?? null;
        $options = [];
        if (! is_array($rawOptions)) {
            $errors[] = 'options must be an array of strings.';
        } else {
            foreach ($rawOptions as $opt) {
                if (! is_string($opt) || trim($opt) === '') {
                    $errors[] = 'each option must be a non-empty string.';
                    $options = [];
                    break;
                }
                if (mb_strlen($opt) > self::MAX_OPTION_LENGTH) {
                    $errors[] = sprintf('each option must be %d characters or less.', self::MAX_OPTION_LENGTH);
                    $options = [];
                    break;
                }
                $options[] = trim($opt);
            }

            if ($options !== []) {
                $unique = array_values(array_unique($options));
                if (count($unique) !== count($options)) {
                    $errors[] = 'options must be unique.';
                } else {
                    $options = $unique;
                }
            }

            if ($errors === []) {
                $count = count($options);
                if ($count < self::MIN_OPTIONS) {
                    $errors[] = sprintf('at least %d options are required.', self::MIN_OPTIONS);
                } elseif ($count > self::MAX_OPTIONS) {
                    $errors[] = sprintf('at most %d options are allowed.', self::MAX_OPTIONS);
                }
            }
        }

        if ($errors !== []) {
            return AiToolValidationResult::fail($errors);
        }

        return AiToolValidationResult::ok([
            'question' => trim((string) $question),
            'options' => $options,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        // ask_user_choice is resolved by the user clicking an option in the UI,
        // not by the standard confirm/discard flow. If something routes here,
        // surface it instead of silently no-op-ing.
        throw new RuntimeException(
            'ask_user_choice is resolved via AnswerChoiceService, not the confirm flow.'
        );
    }

    public function presentCard(array $toolCall, User $user): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];

        $options = array_values(array_filter(array_map(
            static fn ($o): ?string => is_string($o) ? $o : null,
            is_array($args['options'] ?? null) ? $args['options'] : [],
        )));

        $result = $toolCall['result'] ?? null;
        $chosenIndex = null;
        $chosenLabel = null;
        if (is_array($result)) {
            $idx = $result['choice_index'] ?? null;
            if (is_int($idx) || (is_string($idx) && ctype_digit($idx))) {
                $chosenIndex = (int) $idx;
            }
            if (is_string($result['choice_label'] ?? null)) {
                $chosenLabel = $result['choice_label'];
            }
        }

        return [
            'name' => 'ask_user_choice',
            'status' => (string) ($toolCall['status'] ?? 'pending'),
            'question' => (string) ($args['question'] ?? ''),
            'options' => $options,
            'chosen_index' => $chosenIndex,
            'chosen_label' => $chosenLabel,
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
        ];
    }
}
