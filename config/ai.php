<?php

return [
    'content_models' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AI_CONTENT_MODELS', 'gpt-4o-mini')),
    ))),
    'default_content_model' => env('AI_DEFAULT_CONTENT_MODEL', 'gpt-4o-mini'),
    'image_models' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AI_IMAGE_MODELS', 'gpt-image-2')),
    ))),
    'image_model' => env('AI_IMAGE_MODEL', 'gpt-image-2'),
    'audio_models' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AI_AUDIO_MODELS', 'tts-1,tts-1-hd')),
    ))),
    'audio_model' => env('AI_AUDIO_MODEL', 'tts-1'),
    'audio_voices' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AI_AUDIO_VOICES', 'nova')),
    ))),
    'audio_voice' => env('AI_AUDIO_VOICE', 'nova'),
    'image_max_characters' => (int) env('AI_IMAGE_MAX_CHARACTERS', 1500),
    'audio_max_characters' => (int) env('AI_AUDIO_MAX_CHARACTERS', 2500),
    'max_prompt_characters' => (int) env('AI_MAX_PROMPT_CHARACTERS', 6000),
    'max_input_field_characters' => (int) env('AI_MAX_INPUT_FIELD_CHARACTERS', 1500),
    'max_output_words' => (int) env('AI_MAX_OUTPUT_WORDS', 800),
    'chat_max_characters' => (int) env('AI_CHAT_MAX_CHARACTERS', 2000),
    'chat_max_tokens' => (int) env('AI_CHAT_MAX_TOKENS', 500),
    'chat_max_words' => (int) env('AI_CHAT_MAX_WORDS', 300),
    'chat_history_messages' => (int) env('AI_CHAT_HISTORY_MESSAGES', 12),
    'assistant_categories' => ['Business', 'Education', 'Health', 'Marketing', 'Productivity'],
    'request_timeout' => (int) env('OPENAI_REQUEST_TIMEOUT', 30),
    'retry_attempts' => (int) env('AI_RETRY_ATTEMPTS', 2),
    'retry_delay_ms' => (int) env('AI_RETRY_DELAY_MS', 250),
    'reservation_ttl_minutes' => (int) env('AI_RESERVATION_TTL_MINUTES', 10),
    'rate_limits' => [
        'content_per_minute' => (int) env('AI_CONTENT_RATE_LIMIT', 6),
        'media_per_minute' => (int) env('AI_MEDIA_RATE_LIMIT', 3),
        'chat_per_minute' => (int) env('AI_CHAT_RATE_LIMIT', 8),
    ],
];
