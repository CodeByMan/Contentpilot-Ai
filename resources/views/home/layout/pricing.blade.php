@php $heading = isset($headings) ? $headings->get(3) : null; @endphp
<section class="section cp-pricing-section" id="plans">
    <div class="container">
        <div class="section-head text-center cp-reveal">
            <div class="cp-eyebrow cp-eyebrow-light justify-content-center"><span class="cp-eyebrow-dot"></span> Plans that grow with you</div>
            <h2 class="title">{{ $heading?->title ?? 'Start free, then scale your creative capacity' }}</h2>
            <p class="lead">{{ $heading?->description ?? 'Choose the monthly limits that fit your workflow. Paid upgrades are reviewed before activation.' }}</p>
        </div>
        <div class="row g-gs justify-content-center cp-pricing-grid">
            @forelse ($plan as $index => $planItem)
                <div class="col-lg-4 col-md-6 cp-reveal" data-reveal-delay="{{ $index * 70 }}">
                    <article class="pricing h-100 {{ $index === 1 ? 'is-featured' : '' }}">
                        @if ($index === 1)<div class="cp-popular-label">Most popular</div>@endif
                        <div class="pricing-body p-5">
                            <div class="cp-plan-heading">
                                <span class="cp-plan-icon cp-plan-icon-{{ $index % 3 }}"><em class="cp-icon {{ ['cp-icon-spark', 'cp-icon-templates', 'cp-icon-dashboard'][$index % 3] }}"></em></span>
                                <div><h3>{{ $planItem->name }}</h3><p>{{ $index === 0 ? 'For exploring ideas' : ($index === 1 ? 'For consistent creators' : 'For high-volume teams') }}</p></div>
                            </div>
                            <div class="cp-plan-price"><strong>${{ number_format((float) $planItem->price, 2) }}</strong><span>/ month</span></div>
                            <a href="{{ auth()->check() ? route('user.checkout') : route('register') }}" class="btn {{ $index === 1 ? 'btn-primary' : 'btn-outline-secondary' }} btn-block">{{ (float) $planItem->price > 0 ? 'Choose '.$planItem->name : 'Get started free' }}</a>
                            <ul class="list cp-plan-list">
                                <li><em class="cp-icon cp-icon-check"></em><span>{{ number_format($planItem->monthly_word_limit) }} words per cycle</span></li>
                                <li><em class="cp-icon cp-icon-check"></em><span>{{ $planItem->templates }} guided templates</span></li>
                                <li><em class="cp-icon cp-icon-check"></em><span>{{ $planItem->image_generation_limit }} image generations</span></li>
                                <li><em class="cp-icon cp-icon-check"></em><span>{{ $planItem->audio_generation_limit }} audio generations</span></li>
                            </ul>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12 text-center"><p>No plans are currently available.</p></div>
            @endforelse
        </div>
    </div>
</section>
