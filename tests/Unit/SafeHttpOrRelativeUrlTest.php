<?php

use App\Rules\SafeHttpOrRelativeUrl;

function validateCmsLink(mixed $value): array
{
    $messages = [];

    (new SafeHttpOrRelativeUrl)->validate(
        'link',
        $value,
        function (string $message) use (&$messages): void {
            $messages[] = $message;
        },
    );

    return $messages;
}

it('accepts safe application-relative and HTTP links', function () {
    expect(validateCmsLink('/register'))->toBeEmpty()
        ->and(validateCmsLink('/pricing?plan=creator'))->toBeEmpty()
        ->and(validateCmsLink('https://example.com/register'))->toBeEmpty()
        ->and(validateCmsLink('http://example.test'))->toBeEmpty();
});

it('rejects unsafe CMS links', function (mixed $value) {
    expect(validateCmsLink($value))->not->toBeEmpty();
})->with([
    'protocol-relative URL' => '//evil.example/redirect',
    'javascript scheme' => 'javascript:alert(1)',
    'data scheme' => 'data:text/html,unsafe',
    'backslash path' => '/\\evil.example',
    'control character' => "/safe\nunsafe",
]);
