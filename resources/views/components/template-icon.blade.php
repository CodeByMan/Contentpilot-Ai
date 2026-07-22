@props(['template' => null, 'fallback' => 'document'])
@php
    $allowed = ['spark', 'document', 'templates', 'chat', 'image', 'audio', 'billing', 'plan'];
    $rawIcon = strtolower(trim((string) ($template?->icon ?? '')));
    $context = strtolower(trim(implode(' ', [
        (string) ($template?->title ?? ''),
        (string) ($template?->category ?? ''),
        $rawIcon,
    ])));

    $selected = null;
    foreach ($allowed as $candidate) {
        if (str_contains($rawIcon, 'cp-icon-'.$candidate)) {
            $selected = $candidate;
            break;
        }
    }

    if ($selected === null) {
        $keywordMap = [
            'audio' => ['audio', 'voice', 'speech', 'podcast'],
            'image' => ['image', 'visual', 'photo', 'graphic'],
            'chat' => ['email', 'message', 'conversation', 'assistant'],
            'billing' => ['billing', 'invoice', 'payment'],
            'templates' => ['template', 'outline', 'framework'],
            'spark' => ['social', 'campaign', 'creative', 'idea'],
            'document' => ['blog', 'article', 'copy', 'content', 'product', 'document'],
        ];

        foreach ($keywordMap as $icon => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($context, $keyword)) {
                    $selected = $icon;
                    break 2;
                }
            }
        }
    }

    $selected ??= in_array($fallback, $allowed, true) ? $fallback : 'document';
@endphp
<em {{ $attributes->class(['cp-icon', 'cp-icon-'.$selected]) }} aria-hidden="true"></em>
