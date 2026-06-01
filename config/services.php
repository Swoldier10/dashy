<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'codex' => [
        // ChatGPT-account-backed Codex uses different model slugs than the
        // platform API. `gpt-5.5` works for any Plus/Pro plan; alternatives
        // include `gpt-5.4`, `gpt-5.4-codex`, `gpt-5.2-codex`. The platform
        // names like `gpt-5-codex` are NOT valid against this backend.
        'model' => env('CODEX_MODEL', 'gpt-5.5'),

        // Wall-clock ceiling (seconds) for a streamed chat turn, plus the
        // connect timeout. Generous enough for a long multi-tool turn, while
        // still bounding a hung upstream so it can't tie up a web worker or
        // leak an abandoned stream indefinitely.
        'timeout' => (int) env('CODEX_TIMEOUT', 300),
        'connect_timeout' => (int) env('CODEX_CONNECT_TIMEOUT', 15),
    ],

    'openai' => [
        // Used by AudioTranscriptionService for voice-message transcription
        // AND by EmbedTextService for the semantic-search index. Optional —
        // when missing, voice messages still upload and play (without
        // transcription) and the semantic_search tool reports unavailable.
        'api_key' => env('OPENAI_API_KEY'),
        'transcription_model' => env('OPENAI_TRANSCRIPTION_MODEL', 'whisper-1'),
        'transcription_url' => env('OPENAI_TRANSCRIPTION_URL', 'https://api.openai.com/v1/audio/transcriptions'),
        'transcription_timeout' => (int) env('OPENAI_TRANSCRIPTION_TIMEOUT', 30),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'embedding_url' => env('OPENAI_EMBEDDING_URL', 'https://api.openai.com/v1/embeddings'),
        'embedding_timeout' => (int) env('OPENAI_EMBEDDING_TIMEOUT', 30),
    ],

];
