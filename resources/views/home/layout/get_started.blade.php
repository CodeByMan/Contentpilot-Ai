@php
    $heading = $headings->get(1);
@endphp
<section class="section cp-template-section">
    <div class="container">
        <div class="cp-section-heading cp-reveal">
            <div>
                <div class="cp-eyebrow cp-eyebrow-light"><span class="cp-eyebrow-dot"></span> Start with inspiration</div>
                <h2 class="title">{{ $heading?->title ?? 'Create faster with a template for every idea' }}</h2>
                <p class="lead">{{ $heading?->description ?? 'Choose a starting point, add your details, and turn it into something uniquely yours.' }}</p>
            </div>
            <a href="{{ route('usecase') }}" class="btn btn-ghost">Browse all templates <span aria-hidden="true">→</span></a>
        </div>
        <div class="cp-template-grid">
            @forelse ($templates as $index => $item)
                <article class="cp-template-card cp-reveal" data-reveal-delay="{{ min(240, $index * 45) }}">
                    <div class="cp-template-visual cp-template-{{ ['social', 'product', 'email', 'blog', 'image', 'audio'][$index % 6] }}">
                        <span class="cp-template-icon"><x-template-icon :template="$item" fallback="spark" /></span>
                        <div class="cp-template-lines"><i></i><i></i><i></i></div>
                    </div>
                    <div class="cp-template-meta"><h3>{{ $item->title }}</h3><p>{{ $item->description }}</p></div>
                </article>
            @empty
                <div class="cp-empty-state"><em class="cp-icon cp-icon-templates"></em><strong>No templates published yet</strong><p>Seed or create active templates to display them on the homepage.</p></div>
            @endforelse
        </div>
    </div>
</section>
