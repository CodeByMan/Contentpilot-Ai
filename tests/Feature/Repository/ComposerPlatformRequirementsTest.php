<?php

use Illuminate\Support\Facades\File;

it('declares directly required PHP extensions in Composer metadata', function () {
    $composer = json_decode(File::get(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
    $lock = json_decode(File::get(base_path('composer.lock')), true, flags: JSON_THROW_ON_ERROR);

    $requiredExtensions = [
        'ext-curl' => '*',
        'ext-fileinfo' => '*',
        'ext-gd' => '*',
    ];

    foreach ($requiredExtensions as $extension => $constraint) {
        expect($composer['require'][$extension] ?? null)
            ->toBe($constraint, "{$extension} must be declared in composer.json")
            ->and($lock['platform'][$extension] ?? null)
            ->toBe($constraint, "{$extension} must be recorded in composer.lock");
    }
});
