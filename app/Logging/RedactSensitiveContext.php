<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\LogRecord;

final class RedactSensitiveContext
{
    private const REDACTED = '[REDACTED]';

    /** @var list<string> */
    private const SENSITIVE_KEY_FRAGMENTS = [
        'authorization',
        'cookie',
        'password',
        'passphrase',
        'secret',
        'token',
        'api_key',
        'apikey',
        'prompt',
        'input',
        'output',
        'message',
        'payment_reference',
        'account_holder',
        'card',
        'cvv',
    ];

    public function __invoke(Logger $logger): void
    {
        $logger->pushProcessor(fn (LogRecord $record): LogRecord => $record->with(
            message: $this->sanitizeValue($record->message),
            context: $this->sanitizeArray($record->context),
            extra: $this->sanitizeArray($record->extra),
        ));
    }

    /**
     * @param  array<string|int, mixed>  $values
     * @return array<string|int, mixed>
     */
    private function sanitizeArray(array $values): array
    {
        foreach ($values as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $values[$key] = self::REDACTED;

                continue;
            }

            $values[$key] = is_array($value)
                ? $this->sanitizeArray($value)
                : $this->sanitizeValue($value);
        }

        return $values;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(str_replace(['-', '.'], '_', $key));

        foreach (self::SENSITIVE_KEY_FRAGMENTS as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = preg_replace('/\bBearer\s+[A-Za-z0-9._~+\/-]+=*/i', 'Bearer '.self::REDACTED, $value) ?? $value;

        return preg_replace('/\bsk-[A-Za-z0-9_-]{12,}\b/', self::REDACTED, $value) ?? $value;
    }
}
