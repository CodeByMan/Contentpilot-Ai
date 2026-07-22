@extends('client.client_dashboard')
@section('title', 'Dashboard')
@section('client')
<div class="nk-content-inner"><div class="nk-content-body">
    <div class="cp-dashboard-welcome"><div><div class="cp-dashboard-kicker">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}</div><h1>What will you create today, {{ Str::before($user->name, ' ') }}?</h1><p>Turn an idea into polished content, imagery, or audio with a guided workflow.</p></div><a href="{{ route('user.templates.index') }}" class="btn btn-primary btn-lg"><em class="cp-icon cp-icon-spark"></em> Create a design</a></div>
    <section class="cp-quick-create"><div class="cp-quick-create-head"><div><h2>Start creating</h2><p>Choose a tool to begin a new project</p></div><a href="{{ route('user.templates.index') }}">See all</a></div><div class="cp-quick-create-grid">
        <a href="{{ route('user.templates.index') }}" class="cp-quick-create-card cp-quick-purple"><span><em class="cp-icon cp-icon-spark"></em></span><strong>AI writer</strong><small>Draft content</small></a>
        <a href="{{ route('user.images.create') }}" class="cp-quick-create-card cp-quick-cyan"><span><em class="cp-icon cp-icon-image"></em></span><strong>Image studio</strong><small>Create visuals</small></a>
        <a href="{{ route('user.audio.create') }}" class="cp-quick-create-card cp-quick-pink"><span><em class="cp-icon cp-icon-audio"></em></span><strong>Voice lab</strong><small>Generate audio</small></a>
        <a href="{{ route('user.documents.index') }}" class="cp-quick-create-card cp-quick-blue"><span><em class="cp-icon cp-icon-document"></em></span><strong>My projects</strong><small>Continue editing</small></a>
    </div></section>
    <div class="cp-stat-grid cp-user-stat-grid">
        @foreach ([
            ['Words used', number_format($wordsUsed), number_format($wordLimit).' monthly limit', 'cp-icon-document', 'cp-stat-purple'],
            ['Words available', number_format($wordsLeft), number_format($wordsReserved).' reserved', 'cp-icon-spark', 'cp-stat-cyan'],
            ['Projects', number_format($documentsCount), 'Saved documents', 'cp-icon-templates', 'cp-stat-blue'],
            ['Media library', number_format($imagesCount + $audioCount), $imagesCount.' images · '.$audioCount.' audio', 'cp-icon-image', 'cp-stat-pink']
        ] as [$label,$value,$caption,$icon,$class])
            <article class="cp-stat-card {{ $class }}"><span class="cp-stat-icon"><em class="cp-icon {{ $icon }}"></em></span><div><small>{{ $label }}</small><strong>{{ $value }}</strong><span>{{ $caption }}</span></div></article>
        @endforeach
    </div>
    <section class="card cp-usage-panel"><div class="card-body"><div class="cp-usage-panel-copy"><div><span>Monthly word usage</span><strong>{{ $usagePercent }}%</strong></div><p>You have {{ number_format($wordsLeft) }} words available in your current cycle.</p></div><div class="progress cp-large-progress"><div class="progress-bar progress-width-{{ $usagePercent }}"></div></div></div></section>
    <div class="row g-gs cp-dashboard-main-grid">
        <div class="col-xl-7"><section class="card cp-panel-card h-100"><div class="card-header cp-panel-header"><div><h2>Recommended templates</h2><p>Database-backed starting points for your next idea</p></div><a href="{{ route('user.templates.index') }}">View all</a></div><div class="card-body"><div class="cp-dashboard-template-grid">
            @forelse($templates as $index => $item)<a href="{{ route('user.templates.show', $item) }}" class="cp-dashboard-template"><div class="cp-dashboard-template-art cp-thumb-{{ ['one','two','three','four'][$index % 4] }}"><span>{{ strtoupper(substr($item->title,0,2)) }}</span></div><div><strong>{{ $item->title }}</strong><small>{{ Str::limit($item->description, 64) }}</small></div><span class="cp-template-menu">→</span></a>
            @empty <div class="cp-empty-state"><em class="cp-icon cp-icon-templates"></em><strong>No templates in your plan</strong><p>Ask an administrator to activate templates or update your plan.</p></div> @endforelse
        </div></div></section></div>
        <div class="col-xl-5"><section class="card cp-panel-card h-100"><div class="card-header cp-panel-header"><div><h2>Recent projects</h2><p>Continue where you left off</p></div><a href="{{ route('user.documents.index') }}">View all</a></div><div class="card-body cp-project-list">
            @forelse($recentDocuments as $index => $item)<a href="{{ route('user.documents.edit', $item) }}" class="cp-project-row"><span class="cp-project-row-art cp-thumb-{{ ['one','two','three','four'][$index % 4] }}">{{ strtoupper(substr($item->template?->title ?? 'Document',0,2)) }}</span><span><strong>{{ $item->template?->title ?? 'Generated document' }}</strong><small>{{ $item->created_at->diffForHumans() }} · {{ $item->word_count }} words</small></span><em>•••</em></a>
            @empty <div class="cp-empty-state"><em class="cp-icon cp-icon-document"></em><strong>No saved projects yet</strong><p>Your generated and edited documents will appear here.</p></div> @endforelse
        </div></section></div>
    </div>
</div></div>
@endsection
