<?php

namespace App\Domains\Chat\Ai\Services;

use App\Domains\Chat\Ai\Contracts\AiTool;
use RuntimeException;

final class AiToolRegistry
{
    /** @var array<string, AiTool> */
    private array $tools = [];

    public function register(AiTool $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    /**
     * @return array<int, AiTool>
     */
    public function all(): array
    {
        return array_values($this->tools);
    }

    public function find(string $name): ?AiTool
    {
        return $this->tools[$name] ?? null;
    }

    public function get(string $name): AiTool
    {
        $tool = $this->find($name);
        if ($tool === null) {
            throw new RuntimeException("AI tool [{$name}] is not registered.");
        }

        return $tool;
    }

    /**
     * Tool definitions in the shape the Codex Responses API expects under the
     * top-level `tools` array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function schemas(): array
    {
        return array_map(
            fn (AiTool $tool): array => [
                'type' => 'function',
                'name' => $tool->name(),
                'description' => $tool->description(),
                'parameters' => $tool->parameters(),
            ],
            $this->all(),
        );
    }
}
