<?php

namespace App\Livewire\Chat\Concerns;

use App\Domains\Chat\Services\ChatAttachmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

trait ManagesChatAttachments
{
    /** @var array<int, TemporaryUploadedFile> */
    public array $imageUploads = [];

    public ?TemporaryUploadedFile $voiceUpload = null;

    /** @var array<int, array<string, mixed>> */
    public array $persistedAttachments = [];

    /**
     * Temporary uploads for replacing a project logo from within the preview
     * card, keyed by message id.
     *
     * @var array<int, TemporaryUploadedFile|null>
     */
    public array $toolCallLogoUploads = [];

    public function updatedImageUploads(ChatAttachmentService $service): void
    {
        if ($this->imageUploads === []) {
            return;
        }

        $owner = Auth::user();

        foreach ($this->imageUploads as $upload) {
            if (! $upload instanceof TemporaryUploadedFile) {
                continue;
            }

            try {
                $payload = $service->storeImage($owner, $this->activeChatId, $upload);
            } catch (ValidationException $e) {
                $this->imageUploads = [];
                throw $e;
            }

            $this->persistedAttachments[] = $payload;
        }

        $this->imageUploads = [];
    }

    public function updatedVoiceUpload(ChatAttachmentService $service): void
    {
        if ($this->voiceUpload === null) {
            return;
        }

        $owner = Auth::user();

        // Drop any existing voice memo — only one per composer turn.
        $this->persistedAttachments = array_values(array_filter(
            $this->persistedAttachments,
            fn (array $att) => ($att['type'] ?? null) !== 'audio',
        ));

        try {
            $payload = $service->storeAudio($owner, $this->activeChatId, $this->voiceUpload);
        } catch (ValidationException $e) {
            $this->voiceUpload = null;
            throw $e;
        }

        $this->persistedAttachments[] = $payload;
        $this->voiceUpload = null;
    }

    public function removeAttachment(int $index): void
    {
        if (! isset($this->persistedAttachments[$index])) {
            return;
        }

        $att = $this->persistedAttachments[$index];
        $path = $att['path'] ?? null;
        if (is_string($path) && $path !== '') {
            try {
                Storage::disk('public')->delete($path);
            } catch (Throwable $e) {
                // Best-effort cleanup — still drop it from the composer below.
                report($e);
            }
        }

        unset($this->persistedAttachments[$index]);
        $this->persistedAttachments = array_values($this->persistedAttachments);
    }

    public function updatedToolCallLogoUploads(ChatAttachmentService $attachments): void
    {
        $owner = Auth::user();

        foreach ($this->toolCallLogoUploads as $messageId => $upload) {
            if (! $upload instanceof TemporaryUploadedFile) {
                continue;
            }

            $messageId = (int) $messageId;

            try {
                $payload = $attachments->storeImage($owner, $this->activeChatId, $upload);
            } catch (ValidationException $e) {
                $this->toolCallLogoUploads[$messageId] = null;
                throw $e;
            }

            if (! isset($this->toolCallEdits[$messageId])) {
                $this->toolCallEdits[$messageId] = [];
            }

            $this->toolCallEdits[$messageId]['logo_attachment'] = [
                'path' => $payload['path'],
                'url' => $payload['url'],
                'mime' => $payload['mime'],
                'name' => $payload['name'],
            ];

            $this->toolCallLogoUploads[$messageId] = null;
        }
    }

    public function clearToolCallLogo(int $messageId): void
    {
        if (! isset($this->toolCallEdits[$messageId])) {
            $this->toolCallEdits[$messageId] = [];
        }
        $this->toolCallEdits[$messageId]['logo_attachment'] = null;
    }

    protected function seedFromAttachments(): string
    {
        $first = $this->persistedAttachments[0] ?? null;
        if (! is_array($first)) {
            return (string) __('New chat');
        }

        return ($first['type'] ?? null) === 'audio'
            ? (string) __('Voice message')
            : (string) __('Image message');
    }
}
