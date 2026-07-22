<?php

use Illuminate\Support\Facades\File;

it('uses only the project-owned frontend entry points in application layouts', function () {
    $layouts = [
        resource_path('views/home/home_master.blade.php'),
        resource_path('views/home/page/main_master.blade.php'),
        resource_path('views/auth/layout.blade.php'),
        resource_path('views/admin/dashboard.blade.php'),
        resource_path('views/client/client_dashboard.blade.php'),
    ];

    foreach ($layouts as $layout) {
        $contents = File::get($layout);

        expect($contents)
            ->toContain("asset('assets/css/contentpilot.css")
            ->toContain("asset('assets/js/contentpilot.js")
            ->not->toContain('frontend/assets')
            ->not->toContain('backend/assets')
            ->not->toContain('bundle.js');
    }
});

it('does not distribute removed commercial theme or isotope assets', function () {
    expect(File::isDirectory(public_path('frontend')))->toBeFalse()
        ->and(File::isDirectory(public_path('backend')))->toBeFalse()
        ->and(File::exists(public_path('assets/css/contentpilot.css')))->toBeTrue()
        ->and(File::exists(public_path('assets/js/contentpilot.js')))->toBeTrue();

    $publicText = collect(File::allFiles(public_path()))
        ->filter(fn (SplFileInfo $file): bool => in_array(strtolower($file->getExtension()), [
            'css', 'js', 'json', 'md', 'php', 'svg', 'txt', 'xml', 'yml', 'yaml',
        ], true))
        ->map(fn (SplFileInfo $file): string => File::get($file->getPathname()))
        ->implode("\n");

    expect($publicText)
        ->not->toContain('Author: Softnio')
        ->not->toContain('Template Name: NioBoard')
        ->not->toContain('Isotope PACKAGED')
        ->not->toContain('Nioicon');
});
