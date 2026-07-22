@extends('home.page.main_master')
@section('title', 'Features · ContentPilot')
@section('page')
<section class="section cp-page-hero cp-features-hero">
    <div class="container"><div class="cp-page-hero-inner cp-reveal"><div class="cp-eyebrow"><span class="cp-eyebrow-dot"></span> Everything in one place</div><h1 class="title">A creative workspace that stays simple as you grow</h1><p class="lead">Generate content, organize projects, protect ownership, and manage plan usage without adding complexity to your day.</p></div></div>
</section>
<section class="section cp-feature-page-section"><div class="container"><div class="cp-feature-page-grid">
@foreach ([
    ['Write with guided AI', 'Move from blank page to structured, editable copy with clear prompt fields and reusable templates.', 'cp-icon-spark', 'cp-feature-tile-purple'],
    ['Create visual concepts', 'Generate and organize original images using controlled models and private media delivery.', 'cp-icon-image', 'cp-feature-tile-cyan'],
    ['Turn scripts into audio', 'Create voice-ready audio from approved models and keep every file inside your private library.', 'cp-icon-audio', 'cp-feature-tile-pink'],
    ['Manage every project', 'Review documents, generated media, invoices, notifications, and usage from one dashboard.', 'cp-icon-dashboard', 'cp-feature-tile-blue'],
    ['Work with confidence', 'Ownership policies, secure uploads, verified accounts, and rate limits protect core workflows.', 'cp-icon-lock', 'cp-feature-tile-green'],
    ['Scale through templates', 'Administrators can publish templates, manage plans, review payments, and control website content.', 'cp-icon-templates', 'cp-feature-tile-orange']
] as $index => [$title, $description, $icon, $class])
<article class="cp-feature-tile {{ $class }} cp-reveal" data-reveal-delay="{{ ($index % 3) * 60 }}"><span class="cp-feature-tile-icon"><em class="cp-icon {{ $icon }}"></em></span><h2>{{ $title }}</h2><p>{{ $description }}</p><span class="cp-feature-arrow">→</span></article>
@endforeach
</div></div></section>
@include('home.layout.productivity')
@endsection
