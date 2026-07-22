<?php

use App\Support\WordCounter;

it('counts English words and contractions consistently', function (): void {
    expect(WordCounter::count("A secure user's well-tested workflow."))->toBe(5);
});

it('ignores markup and returns zero for empty content', function (): void {
    expect(WordCounter::count('<p>Hello <strong>world</strong>.</p>'))->toBe(2)
        ->and(WordCounter::count('   '))->toBe(0);
});
