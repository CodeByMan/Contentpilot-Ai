@extends('home.page.main_master')
@section('title', 'Use Cases · ContentPilot')
@section('page')
<section class="section cp-page-hero cp-usecase-hero">
    <div class="container">
        <div class="cp-page-hero-inner cp-reveal">
            <div class="cp-eyebrow"><span class="cp-eyebrow-dot"></span> Built for real content workflows</div>
            <h1 class="title">From first draft to private media library in one workspace</h1>
            <p class="lead">ContentPilot combines guided writing, AI image and audio generation, project organization, plan controls, and administrator review workflows.</p>
        </div>
    </div>
</section>

<section class="section cp-feature-page-section">
    <div class="container">
        <div class="cp-section-heading cp-reveal">
            <div>
                <div class="cp-eyebrow cp-eyebrow-light"><span class="cp-eyebrow-dot"></span> Practical use cases</div>
                <h2 class="title">Use the tools that match the work you actually need to finish</h2>
            </div>
            <p>Each workflow maps directly to an existing ContentPilot feature and keeps generated work tied to the authenticated account.</p>
        </div>
        <div class="cp-feature-page-grid cp-usecase-grid">
            @foreach ([
                ['Guided content generation', 'Choose an administrator-published template, complete its structured input fields, generate editable copy, and save the result as a document.', 'cp-icon-templates', 'cp-feature-tile-purple'],
                ['Campaign and marketing copy', 'Draft social captions, product descriptions, landing-page sections, email content, blog outlines, and other reusable marketing assets.', 'cp-icon-spark', 'cp-feature-tile-orange'],
                ['AI image creation', 'Generate images with an approved model, keep the files in a private library, and access them only through authorized account routes.', 'cp-icon-image', 'cp-feature-tile-cyan'],
                ['Text-to-speech audio', 'Turn approved scripts into generated audio, track usage against the active plan, and manage files from the private audio library.', 'cp-icon-audio', 'cp-feature-tile-pink'],
                ['Document and media organization', 'Review, rename, edit, download, or delete owned documents, generated images, and audio from one personal dashboard.', 'cp-icon-document', 'cp-feature-tile-blue'],
                ['Plan upgrades and payment review', 'Submit an offline payment request, upload proof securely, receive review notifications, and download invoices after approval.', 'cp-icon-billing', 'cp-feature-tile-green'],
                ['AI assistant administration', 'Create specialized assistants, configure their instructions, and maintain conversation workflows from the protected administrator area.', 'cp-icon-chat', 'cp-feature-tile-purple'],
                ['Website and product administration', 'Manage plans, templates, homepage content, FAQs, contact messages, users’ payment requests, and generation limits from the admin dashboard.', 'cp-icon-settings', 'cp-feature-tile-orange'],
            ] as $index => [$title, $description, $icon, $class])
                <article class="cp-feature-tile {{ $class }} cp-reveal" data-reveal-delay="{{ ($index % 4) * 55 }}">
                    <span class="cp-feature-tile-icon"><em class="cp-icon {{ $icon }}"></em></span>
                    <h2>{{ $title }}</h2>
                    <p>{{ $description }}</p>
                    <span class="cp-feature-arrow" aria-hidden="true">→</span>
                </article>
            @endforeach
        </div>
    </div>
</section>

@include('home.layout.productivity')
@endsection
