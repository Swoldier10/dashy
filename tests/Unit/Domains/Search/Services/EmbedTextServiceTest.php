<?php

namespace Tests\Unit\Domains\Search\Services;

use App\Domains\Search\Services\EmbedTextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class EmbedTextServiceTest extends TestCase
{
    use RefreshDatabase;

    private function configureOpenAi(): void
    {
        config([
            'services.openai.api_key' => 'test-key',
            'services.openai.embedding_url' => 'https://api.openai.com/v1/embeddings',
            'services.openai.embedding_model' => 'text-embedding-3-small',
        ]);
    }

    public function test_returns_a_float_vector_on_success(): void
    {
        $this->configureOpenAi();
        Http::fake([
            '*' => Http::response(['data' => [['embedding' => [0.1, 0.2, 0.3]]]]),
        ]);

        $vector = app(EmbedTextService::class)->embed('hello world');

        $this->assertSame([0.1, 0.2, 0.3], $vector);
    }

    public function test_throws_when_not_configured(): void
    {
        config(['services.openai.api_key' => '']);

        $this->expectException(RuntimeException::class);
        app(EmbedTextService::class)->embed('hello');
    }

    public function test_throws_on_an_empty_string(): void
    {
        $this->configureOpenAi();

        $this->expectException(RuntimeException::class);
        app(EmbedTextService::class)->embed('   ');
    }

    public function test_throws_when_the_api_responds_with_an_error(): void
    {
        $this->configureOpenAi();
        Http::fake(['*' => Http::response('rate limited', 429)]);

        $this->expectException(RuntimeException::class);
        app(EmbedTextService::class)->embed('hello');
    }

    public function test_is_configured_reflects_the_api_key_presence(): void
    {
        config(['services.openai.api_key' => '']);
        $this->assertFalse(app(EmbedTextService::class)->isConfigured());

        config(['services.openai.api_key' => 'k']);
        $this->assertTrue(app(EmbedTextService::class)->isConfigured());
    }
}
