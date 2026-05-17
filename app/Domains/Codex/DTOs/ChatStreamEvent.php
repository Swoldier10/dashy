<?php

namespace App\Domains\Codex\DTOs;

final readonly class ChatStreamEvent
{
    public const TYPE_TEXT_DELTA = 'text_delta';

    public const TYPE_TOOL_CALL_STARTED = 'tool_call_started';

    public const TYPE_TOOL_CALL_ARGUMENTS_DELTA = 'tool_call_arguments_delta';

    public const TYPE_TOOL_CALL_COMPLETED = 'tool_call_completed';

    public function __construct(
        public string $type,
        public ?string $text = null,
        public ?string $callId = null,
        public ?string $name = null,
        public ?string $argsDelta = null,
        public ?string $argumentsJson = null,
    ) {}

    public static function textDelta(string $text): self
    {
        return new self(type: self::TYPE_TEXT_DELTA, text: $text);
    }

    public static function toolCallStarted(string $callId, string $name): self
    {
        return new self(type: self::TYPE_TOOL_CALL_STARTED, callId: $callId, name: $name);
    }

    public static function toolCallArgumentsDelta(string $callId, string $argsDelta): self
    {
        return new self(type: self::TYPE_TOOL_CALL_ARGUMENTS_DELTA, callId: $callId, argsDelta: $argsDelta);
    }

    public static function toolCallCompleted(string $callId, string $name, string $argumentsJson): self
    {
        return new self(
            type: self::TYPE_TOOL_CALL_COMPLETED,
            callId: $callId,
            name: $name,
            argumentsJson: $argumentsJson,
        );
    }
}
