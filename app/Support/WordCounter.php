<?php

namespace App\Support;

final class WordCounter
{
    public static function count(string $content): int
    {
        $plainText = trim(strip_tags($content));

        if ($plainText === '') {
            return 0;
        }

        return preg_match_all(
            "/[\\p{L}\\p{N}]+(?:['’\\-][\\p{L}\\p{N}]+)*/u",
            $plainText,
        ) ?: 0;
    }
}
