@extends('client.client_dashboard')
@section('client')
    <div class="nk-content-inner">
        <div class="nk-content-body">
            <div class="nk-block-head nk-page-head">
                <div class="nk-block-head-between flex-wrap gap g-3">
                    <div class="nk-block-head-content">
                        <div class="badge text-bg-primary-soft rounded-pill mb-2 px-3 py-2">{{ $user->plan?->name ?? 'Starter' }} Workspace</div>
                        <h2 class="display-6 mb-1">Create faster, {{ $user->name }} 🚀</h2>
                        <p class="text-light mb-0">Generate English content, images, and audio from one polished ContentPilot dashboard.</p>
                    </div>
                    <div class="nk-block-head-content">
                        <a href="{{ route('user.template') }}" class="btn btn-primary"><em class="icon ni ni-spark"></em><span>Generate Content</span></a>
                    </div>
                </div>
            </div>

            <div class="nk-block">
                <div class="row g-gs">
                    <div class="col-md-6 col-xxl-3">
                        <div class="card card-full bg-primary bg-opacity-10 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="fs-6 text-light mb-1">Words Remaining</div>
                                <h3 class="mb-2">{{ number_format($wordsLeft) }}</h3>
                                <div class="progress progress-md mb-2">
                                    <div class="progress-bar" style="width: {{ $usagePercent }}%"></div>
                                </div>
                                <p class="small text-light mb-0">{{ number_format($wordsUsed) }} / {{ number_format($wordLimit) }} words used</p>
                            </div>
                        </div>
                    </div>
                    @foreach ([
                        ['label' => 'Templates', 'value' => $templates->count().' / '.$templateLimit, 'icon' => 'ni-layers', 'tone' => 'info'],
                        ['label' => 'Documents', 'value' => $documentsCount, 'icon' => 'ni-file-docs', 'tone' => 'success'],
                        ['label' => 'Images', 'value' => $imagesCount, 'icon' => 'ni-img', 'tone' => 'warning'],
                    ] as $card)
                        <div class="col-md-6 col-xxl-3">
                            <div class="card card-full border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="media media-rg media-middle media-circle text-{{ $card['tone'] }} bg-{{ $card['tone'] }} bg-opacity-10 mb-3">
                                        <em class="icon ni {{ $card['icon'] }}"></em>
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
                    <div class="col-xl-8">
                        <div class="card card-full shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h4 class="mb-1">Recommended AI Templates</h4>
                                        <p class="small text-light mb-0">Demo-ready content generators available on your current plan.</p>
                                    </div>
                                    <a href="{{ route('user.template') }}" class="link">Explore all</a>
                                </div>
                                <div class="row g-3">
                                    @forelse ($templates as $item)
                                        <div class="col-md-6 col-xxl-4">
                                            <a href="{{ route('user.details.template', $item->id) }}" class="card border-0 bg-light bg-opacity-25 h-100">
                                                <div class="card-body">
                                                    <div class="media media-rg media-middle media-circle text-primary bg-primary bg-opacity-20 mb-3">
                                                        <em class="{{ $item->icon }}"></em>
                                                    </div>
                                                    <h5 class="fs-5 fw-semibold mb-1">{{ $item->title }}</h5>
                                                    <p class="small text-light line-clamp-2 mb-0">{{ $item->description }}</p>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="col-12"><div class="alert alert-info mb-0">No active templates available yet.</div></div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-full shadow-sm border-0">
                            <div class="card-body">
                                <h4 class="mb-3">Recent Drafts</h4>
                                @forelse ($recentDocuments as $document)
                                    <div class="border-bottom border-light py-3">
                                        <h6 class="mb-1">{{ $document->template?->title ?? 'AI Document' }}</h6>
                                        <p class="small text-light mb-0">{{ $document->word_count }} words · {{ $document->created_at->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <p class="text-light mb-0">Generate your first document to show activity here.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
