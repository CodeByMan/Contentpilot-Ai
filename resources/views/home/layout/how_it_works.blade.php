@php $heading = $headings->get(2); @endphp
<section class="section cp-how-section">
    <div class="container">
        <div class="section-head text-center cp-reveal">
            <div class="cp-eyebrow cp-eyebrow-light justify-content-center"><span class="cp-eyebrow-dot"></span> Simple by design</div>
            <h2 class="title">{{ $heading?->title ?? 'From first thought to finished content in three steps' }}</h2>
            <p class="lead">{{ $heading?->description ?? 'A clear workflow helps you stay creative without losing control of the details.' }}</p>
        </div>
        <div class="cp-step-grid">
            @foreach ([
                ['Choose your starting point', 'Pick a guided template or a media tool that matches your goal.', 'cp-icon cp-icon-templates'],
                ['Shape the direction', 'Add your source details, tone, audience, and desired outcome.', 'cp-icon cp-icon-settings'],
                ['Refine and organize', 'Review, edit, save, and manage every result in your private workspace.', 'cp-icon cp-icon-check']
            ] as $index => [$title, $description, $icon])
                <article class="cp-step-card cp-reveal" data-reveal-delay="{{ $index * 80 }}">
                    <div class="cp-step-number">0{{ $index + 1 }}</div>
                    <span class="cp-step-icon"><em class="{{ $icon }}"></em></span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $description }}</p>
                </article>
            @endforeach
        </div>
        <div class="section-actions text-center cp-reveal">
            <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.templates.index')) : route('register') }}" class="btn btn-primary btn-lg">Create your first project</a>
        </div>
    </div>
</section>
