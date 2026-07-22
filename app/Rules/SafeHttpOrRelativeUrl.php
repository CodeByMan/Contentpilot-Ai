<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeHttpOrRelativeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || $this->containsUnsafeCharacters($value)) {
            $fail('The :attribute must be a safe HTTP(S) URL or an application-relative path.');

            return;
        }

        if ($this->isSafeRelativePath($value) || $this->isSafeHttpUrl($value)) {
            return;
        }

        $fail('The :attribute must be a safe HTTP(S) URL or an application-relative path.');
    }

    private function containsUnsafeCharacters(string $value): bool
    {
        return preg_match('/[\x00-\x1F\x7F\\\\]/u', $value) === 1;
    }

    private function isSafeRelativePath(string $value): bool
    {
        return str_starts_with($value, '/') && ! str_starts_with($value, '//');
    }

    private function isSafeHttpUrl(string $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), ['http', 'https'], true);
    }
}
