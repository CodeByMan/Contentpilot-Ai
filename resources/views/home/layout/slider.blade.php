@php
    $heroTitle = $slider?->title ?? 'Create remarkable content in one intelligent workspace';
    $heroDescription = $slider?->description ?? 'Turn ideas into polished copy, visuals, and audio with guided AI tools, private libraries, and a workflow designed to keep you moving.';
    $heroLink = $slider?->link ?: (auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard')) : route('register'));
@endphp
<section class="nk-hero cp-hero">
    <div class="cp-hero-glow cp-hero-glow-a"></div>
    <div class="cp-hero-glow cp-hero-glow-b"></div>
    <div class="container">
        <div class="row align-items-center g-gs cp-hero-grid">
            <div class="col-lg-6">
                <div class="nk-hero-content cp-reveal">
                    <div class="cp-eyebrow"><span class="cp-eyebrow-dot"></span> Your AI content studio</div>
                    <h1 class="title">{{ $heroTitle }}</h1>
                    <p class="lead">{{ $heroDescription }}</p>
                    <div class="cp-hero-actions">
                        <a href="{{ $heroLink }}" class="btn btn-primary btn-lg">Start creating free <span aria-hidden="true">→</span></a>
                        <a href="{{ route('features') }}" class="btn btn-outline-light btn-lg">Explore features</a>
                    </div>
                    <div class="cp-trust-row" aria-label="Platform highlights">
                        <span><em class="cp-icon cp-icon-check"></em> Guided templates</span>
                        <span><em class="cp-icon cp-icon-check"></em> Private workspace</span>
                        <span><em class="cp-icon cp-icon-check"></em> No card required</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="cp-workspace-preview cp-reveal" data-reveal-delay="120">
                    <div class="cp-preview-topbar">
                        <div class="cp-window-dots"><i></i><i></i><i></i></div>
                        <div class="cp-preview-search"><em class="cp-icon cp-icon-search"></em><span>What will you create today?</span></div>
                        <div class="cp-preview-avatar">MN</div>
                    </div>
                    <div class="cp-preview-body">
                        <aside class="cp-preview-sidebar">
                            <span class="is-active"><em class="cp-icon cp-icon-dashboard"></em></span>
                            <span><em class="cp-icon cp-icon-templates"></em></span>
                            <span><em class="cp-icon cp-icon-document"></em></span>
                            <span><em class="cp-icon cp-icon-image"></em></span>
                            <span><em class="cp-icon cp-icon-audio"></em></span>
                        </aside>
                        <div class="cp-preview-canvas">
                            <div class="cp-preview-heading"><div><small>Good morning, Muhammad</small><strong>Design your next idea</strong></div><button type="button">Create</button></div>
                            <div class="cp-create-grid">
                                <article class="cp-create-card cp-create-purple"><span class="cp-card-icon"><em class="cp-icon cp-icon-spark"></em></span><b>AI Writer</b><small>Draft content</small></article>
                                <article class="cp-create-card cp-create-cyan"><span class="cp-card-icon"><em class="cp-icon cp-icon-image"></em></span><b>Image Studio</b><small>Create visuals</small></article>
                                <article class="cp-create-card cp-create-pink"><span class="cp-card-icon"><em class="cp-icon cp-icon-audio"></em></span><b>Voice Lab</b><small>Generate audio</small></article>
                            </div>
                            <div class="cp-preview-projects">
                                <div class="cp-preview-section-title"><strong>Recent projects</strong><span>View all</span></div>
                                <div class="cp-project-strip">
                                    <article class="cp-project-thumb cp-thumb-one"><span>Summer<br>Campaign</span></article>
                                    <article class="cp-project-thumb cp-thumb-two"><span>Product<br>Launch</span></article>
                                    <article class="cp-project-thumb cp-thumb-three"><span>Weekly<br>Newsletter</span></article>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
