<?php

use App\Logging\RedactSensitiveContext;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;

it('redacts secrets prompts and payment data from log records', function () {
    $logger = new Logger('test');
    $handler = new TestHandler(Level::Debug);
    $logger->pushHandler($handler);
    (new RedactSensitiveContext)($logger);

    $logger->error('Provider failure using Bearer secret-token-value', [
        'api_key' => 'sk-secret-value-1234567890',
        'prompt' => 'private prompt',
        'payment_reference' => 'BANK-123',
        'nested' => ['password' => 'private-password'],
        'safe' => 'visible',
    ]);

    $record = $handler->getRecords()[0];

    expect($record->message)->not->toContain('secret-token-value')
        ->and($record->context['api_key'])->toBe('[REDACTED]')
        ->and($record->context['prompt'])->toBe('[REDACTED]')
        ->and($record->context['payment_reference'])->toBe('[REDACTED]')
        ->and($record->context['nested']['password'])->toBe('[REDACTED]')
        ->and($record->context['safe'])->toBe('visible');
});
