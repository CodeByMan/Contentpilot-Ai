@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-inner">
        <div class="nk-content-body">
            <div class="nk-block-head nk-page-head">
                <div class="nk-block-head-between flex-wrap gap g-3">
                    <div class="nk-block-head-content">
                        <div class="badge text-bg-primary-soft rounded-pill mb-2 px-3 py-2">ContentPilot Admin</div>
                        <h2 class="display-6 mb-1">Welcome back, {{ auth()->user()->name }} 👋</h2>
                        <p class="text-light mb-0">Monitor plans, AI templates, content activity, and SaaS revenue from one clean dashboard.</p>
                    </div>
                    <div class="nk-block-head-content">
                        <a href="{{ route('add.template') }}" class="btn btn-primary"><em class="icon ni ni-plus"></em><span>New Template</span></a>
                    </div>
                </div>
            </div>

            <div class="nk-block">
                <div class="row g-gs">
                    @foreach ([
                        ['label' => 'Customers', 'value' => $stats['users'], 'icon' => 'ni-users', 'tone' => 'primary'],
                        ['label' => 'Templates', 'value' => $stats['templates'], 'icon' => 'ni-layers', 'tone' => 'info'],
                        ['label' => 'Documents', 'value' => $stats['documents'], 'icon' => 'ni-file-docs', 'tone' => 'success'],
                        ['label' => 'Revenue', 'value' => '$'.number_format($stats['revenue'], 2), 'icon' => 'ni-growth', 'tone' => 'warning'],
                    ] as $card)
                        <div class="col-sm-6 col-xxl-3">
                            <div class="card card-full border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="media media-rg media-middle media-circle text-{{ $card['tone'] }} bg-{{ $card['tone'] }} bg-opacity-10">
                                            <em class="icon ni {{ $card['icon'] }}"></em>
                                        </div>
                                        <span class="badge text-bg-{{ $card['tone'] }}-soft rounded-pill">Live</span>
                                    </div>
                                    <div class="fs-6 text-light mb-1">{{ $card['label'] }}</div>
                                    <h3 class="mb-0">{{ $card['value'] }}</h3>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="nk-block">
                <div class="row g-gs">
                    <div class="col-xl-7">
                        <div class="card card-full shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h4 class="mb-1">Popular AI Templates</h4>
                                        <p class="small text-light mb-0">Recruiter-friendly view of active SaaS features and usage.</p>
                                    </div>
                                    <a href="{{ route('all.template') }}" class="link">Manage all</a>
                                </div>
                                <div class="row g-3">
                                    @forelse ($popularTemplates as $item)
                                        <div class="col-md-6">
                                            <a href="{{ route('details.template', $item->id) }}" class="card border-0 bg-light bg-opacity-25 h-100">
                                                <div class="card-body">
                                                    <div class="media media-rg media-middle media-circle text-primary bg-primary bg-opacity-20 mb-3">
                                                        <em class="{{ $item->icon }}"></em>
                                                    </div>
                                                    <h5 class="fs-5 fw-semibold mb-1">{{ $item->title }}</h5>
                                                    <p class="small text-light line-clamp-2 mb-3">{{ $item->description }}</p>
                                                    <span class="badge text-bg-success-soft rounded-pill">{{ $item->generated_contents_count }} generations</span>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-info mb-0">No templates yet. Add your first template to make the demo dashboard come alive.</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card card-full shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h4 class="mb-3">Subscription Plans</h4>
                                @forelse ($plans as $plan)
                                    <div class="d-flex align-items-center justify-content-between border-bottom border-light py-3">
                                        <div>
                                            <h6 class="mb-1">{{ $plan->name }}</h6>
                                            <p class="small text-light mb-0">{{ number_format($plan->monthly_word_limit) }} words · {{ $plan->templates }} templates</p>
                                        </div>
                                        <span class="badge text-bg-primary-soft rounded-pill">${{ $plan->price }}</span>
                                    </div>
                                @empty
                                    <p class="text-light mb-0">No plans seeded yet.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="card card-full shadow-sm border-0">
                            <div class="card-body">
                                <h4 class="mb-3">Recent Documents</h4>
                                @forelse ($recentDocuments as $document)
                                    <div class="d-flex align-items-start gap-3 border-bottom border-light py-3">
                                        <div class="media media-md media-middle media-circle text-success bg-success bg-opacity-10">
                                            <em class="icon ni ni-file-text"></em>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $document->template?->title ?? 'AI Document' }}</h6>
                                            <p class="small text-light mb-0">{{ $document->user?->name }} · {{ $document->word_count }} words</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-light mb-0">No documents generated yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
