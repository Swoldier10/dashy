<?php

namespace App\Domains\Chat\Ai\Services;

use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Message;
use Illuminate\Support\Facades\Storage;

/**
 * Translates the persisted chat history into the OpenAI Responses-API `input`
 * shape. Every message row is forwarded — full thread memory is the contract.
 *
 * Item types emitted:
 * - `{type: 'message', role, content: [...]}` — user/assistant text turns
 * - `{type: 'function_call', call_id, name, arguments}` — prior tool calls
 * - `{type: 'function_call_output', call_id, output}` — paired result/status
 */
final class LlmInputBuilder
{
    /**
     * @param  iterable<int, Message>  $messages
     * @return array<int, array<string, mixed>>
     */
    public function build(iterable $messages): array
    {
        $list = $messages instanceof \IteratorAggregate ? iterator_to_array($messages) : (array) $messages;

        // History compaction: if a message with is_summary=true exists, every
        // message before it has already been folded into that summary — drop
        // the head and replay only the summary + the trailing window.
        $cutoff = -1;
        foreach ($list as $index => $message) {
            if ($message instanceof Message && $message->is_summary) {
                $cutoff = $index;
            }
        }
        if ($cutoff >= 0) {
            $list = array_slice($list, $cutoff);
        }

        $items = [];
        foreach ($list as $message) {
            foreach ($this->itemsFor($message) as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function itemsFor(Message $message): array
    {
        $items = [];

        $messageItem = $this->messageItem($message);
        if ($messageItem !== null) {
            $items[] = $messageItem;
        }

        if ($message->role === MessageRole::Assistant && is_array($message->tool_call)) {
            foreach ($this->functionCallItems($message->tool_call) as $callItem) {
                $items[] = $callItem;
            }
        }

        return $items;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function messageItem(Message $message): ?array
    {
        $role = $message->role->value;
        $isAssistant = $message->role === MessageRole::Assistant;
        $hasToolCall = $isAssistant && is_array($message->tool_call);

        $atts = $message->attachments ?? [];

        $imageDataUrls = collect($atts)
            ->where('type', 'image')
            ->map(fn (array $att): ?string => $this->buildDataUrl($att))
            ->filter()
            ->values()
            ->all();

        $transcripts = collect($atts)
            ->where('type', 'audio')
            ->pluck('transcript')
            ->filter(fn ($t) => is_string($t) && trim($t) !== '')
            ->values()
            ->all();

        $content = (string) $message->content;
        if ($transcripts !== []) {
            $voiceBlock = '[Voice note] '.implode(' ', $transcripts);
            $content = trim($content) === ''
                ? $voiceBlock
                : $content."\n\n".$voiceBlock;
        }

        // Pure tool-call assistant rows (no text, no transcripts) contribute
        // only the function_call pair — no empty `message` envelope.
        if ($hasToolCall && trim($content) === '' && $imageDataUrls === []) {
            return null;
        }

        $type = $isAssistant ? 'output_text' : 'input_text';

        $contentBlocks = [];
        foreach ($imageDataUrls as $dataUrl) {
            $contentBlocks[] = ['type' => 'input_image', 'image_url' => $dataUrl];
        }
        $contentBlocks[] = ['type' => $type, 'text' => $content];

        return [
            'type' => 'message',
            'role' => $role,
            'content' => $contentBlocks,
        ];
    }

    /**
     * @param  array<string, mixed>  $toolCall
     * @return array<int, array<string, mixed>>
     */
    private function functionCallItems(array $toolCall): array
    {
        $callId = $toolCall['tool_call_id'] ?? null;
        if (! is_string($callId) || $callId === '') {
            return [];
        }

        $name = (string) ($toolCall['name'] ?? '');
        $arguments = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $argumentsJson = json_encode($arguments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($argumentsJson)) {
            $argumentsJson = '{}';
        }

        return [
            [
                'type' => 'function_call',
                'call_id' => $callId,
                'name' => $name,
                'arguments' => $argumentsJson,
            ],
            [
                'type' => 'function_call_output',
                'call_id' => $callId,
                'output' => $this->synthesizeOutput($toolCall),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $toolCall
     */
    private function synthesizeOutput(array $toolCall): string
    {
        $status = $toolCall['status'] ?? null;

        return match ($status) {
            'pending' => 'Awaiting user confirmation in the UI.',
            'executed' => $this->executedOutput($toolCall),
            'created' => $this->createdOutput($toolCall),
            'answered' => $this->answeredOutput($toolCall),
            'discarded' => 'User discarded this tool call. Do not retry the same call — wait for new instructions.',
            'failed' => $this->failedOutput($toolCall),
            default => 'Tool call status unknown.',
        };
    }

    /**
     * For auto_read tools that ran inline. The model sees the actual JSON
     * result so it can incorporate it into its next reasoning step. The output
     * is capped at ~4 KB so a huge list doesn't blow the input budget.
     *
     * @param  array<string, mixed>  $toolCall
     */
    private function executedOutput(array $toolCall): string
    {
        $result = $toolCall['result'] ?? null;
        $json = json_encode($result ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($json)) {
            $json = '{}';
        }

        if (strlen($json) > 4000) {
            $json = substr($json, 0, 4000).'…(truncated)';
        }

        return $json;
    }

    /**
     * @param  array<string, mixed>  $toolCall
     */
    private function createdOutput(array $toolCall): string
    {
        $result = $toolCall['result'] ?? null;
        $name = (string) ($toolCall['name'] ?? '');

        return match ($name) {
            'create_task' => is_array($result) && isset($result['task_id'])
                ? sprintf('Task created. Confirmed by the user. task_id=%s.', $result['task_id'])
                : 'Task created. Confirmed by the user.',
            'create_project' => is_array($result) && isset($result['project_id'])
                ? sprintf('Project created. Confirmed by the user. project_id=%s.', $result['project_id'])
                : 'Project created. Confirmed by the user.',
            default => 'Tool call confirmed by the user.',
        };
    }

    /**
     * @param  array<string, mixed>  $toolCall
     */
    private function answeredOutput(array $toolCall): string
    {
        $result = $toolCall['result'] ?? null;
        if (is_array($result) && is_string($result['choice_label'] ?? null) && $result['choice_label'] !== '') {
            return sprintf('User chose: %s.', $result['choice_label']);
        }

        return 'User answered the choice.';
    }

    /**
     * @param  array<string, mixed>  $toolCall
     */
    private function failedOutput(array $toolCall): string
    {
        $errors = $toolCall['validation_errors'] ?? [];
        $joined = is_array($errors)
            ? implode('; ', array_map(fn ($e) => (string) $e, $errors))
            : (string) $errors;

        if ($joined === '') {
            return 'Tool call validation failed. Do not retry the same call.';
        }

        return 'Tool call validation failed: '.$joined.'. Do not retry the same call.';
    }

    /**
     * @param  array<string, mixed>  $att
     */
    private function buildDataUrl(array $att): ?string
    {
        $path = $att['path'] ?? null;
        if (! is_string($path) || $path === '') {
            return null;
        }

        $bytes = Storage::disk('public')->get($path);
        if ($bytes === null) {
            return null;
        }

        $mime = is_string($att['mime'] ?? null) ? $att['mime'] : 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($bytes);
    }
}
