@extends('admin.dashboard')
@section('title', 'Administration Dashboard')
@section('admin')
<div class="nk-content-inner"><div class="nk-content-body">
    <div class="cp-dashboard-hero cp-admin-dashboard-hero">
        <div><div class="cp-dashboard-kicker">Workspace overview</div><h1>Keep every creative workflow moving.</h1><p>Monitor content activity, publishing tools, payments, and platform growth from one clear view.</p></div>
        <div class="cp-dashboard-hero-actions"><a href="{{ route('admin.templates.create') }}" class="btn btn-light"><em class="cp-icon cp-icon-spark"></em> New template</a><a href="{{ route('admin.payments.index') }}" class="btn cp-glass-btn">Review payments</a></div>
    </div>
    <div class="cp-stat-grid">
        @foreach ([
            ['Users', number_format($stats['users']), 'Active creator accounts', 'cp-icon-user', 'cp-stat-purple'],
            ['Generated content', number_format($stats['documents']), 'Stored documents', 'cp-icon-document', 'cp-stat-cyan'],
            ['Templates', number_format($stats['templates']), 'Database templates', 'cp-icon-templates', 'cp-stat-blue'],
            ['Revenue', '$'.number_format($stats['revenue'], 0), 'Approved payments', 'cp-icon-billing', 'cp-stat-pink']
        ] as [$label,$value,$change,$icon,$class])
            <article class="cp-stat-card {{ $class }}"><span class="cp-stat-icon"><em class="cp-icon {{ $icon }}"></em></span><div><small>{{ $label }}</small><strong>{{ $value }}</strong><span>{{ $change }}</span></div></article>
        @endforeach
    </div>
    <div class="row g-gs cp-dashboard-main-grid">
        <div class="col-xl-7">
            <section class="card cp-panel-card h-100"><div class="card-header cp-panel-header"><div><h2>Popular templates</h2><p>Most-used creative starting points</p></div><a href="{{ route('admin.templates.index') }}" class="btn btn-ghost btn-sm">Manage all</a></div><div class="card-body"><div class="cp-dashboard-template-grid">
                @forelse($popularTemplates as $index => $item)
                    <a href="{{ route('admin.templates.show', $item) }}" class="cp-dashboard-template"><div class="cp-dashboard-template-art cp-thumb-{{ ['one','two','three','four'][$index % 4] }}"><span>{{ strtoupper(substr($item->title, 0, 2)) }}</span></div><div><strong>{{ $item->title }}</strong><small>{{ $item->generated_contents_count }} generated projects</small></div><span class="cp-template-menu">•••</span></a>
                @empty
                    <div class="cp-empty-state"><em class="cp-icon cp-icon-templates"></em><strong>No templates yet</strong><p>Create or seed templates to populate this section.</p></div>
                @endforelse
            </div></div></section>
        </div>
        <div class="col-xl-5">
            <section class="card cp-panel-card h-100"><div class="card-header cp-panel-header"><div><h2>Recent content</h2><p>Latest generated documents</p></div><a href="{{ route('admin.documents.index') }}">View all</a></div><div class="card-body cp-activity-list">
                @forelse($recentDocuments as $item)<a href="{{ route('admin.documents.edit', $item) }}" class="cp-activity-item"><span class="cp-activity-icon"><em class="cp-icon cp-icon-document"></em></span><span><strong>{{ $item->template?->title ?? 'Generated document' }}</strong><small>{{ $item->user?->name ?? 'Deleted user' }} · {{ $item->created_at->diffForHumans() }}</small></span><span class="cp-activity-arrow">→</span></a>
                @empty <div class="cp-empty-state"><em class="cp-icon cp-icon-document"></em><strong>No generated content yet</strong><p>Generated documents stored in MySQL will appear here.</p></div> @endforelse
            </div></section>
        </div>
    </div>
    <div class="cp-admin-insight-grid">
        <article class="card cp-insight-card"><span class="cp-insight-icon cp-insight-warning"><em class="cp-icon cp-icon-billing"></em></span><div><small>Pending payments</small><strong>{{ number_format($stats['pendingPayments']) }}</strong><a href="{{ route('admin.payments.index') }}">Review queue →</a></div></article>
        <article class="card cp-insight-card"><span class="cp-insight-icon cp-insight-image"><em class="cp-icon cp-icon-image"></em></span><div><small>Generated images</small><strong>{{ number_format($stats['images']) }}</strong><a href="{{ route('admin.images.index') }}">Open library →</a></div></article>
        <article class="card cp-insight-card"><span class="cp-insight-icon cp-insight-audio"><em class="cp-icon cp-icon-audio"></em></span><div><small>Generated audio</small><strong>{{ number_format($stats['audio']) }}</strong><a href="{{ route('admin.audio.index') }}">Open library →</a></div></article>
    </div>
</div></div>
@endsection
