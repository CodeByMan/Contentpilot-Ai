<section class="section cp-cta-section">
    <div class="container">
        <div class="cp-cta-card cp-reveal">
            <div class="cp-cta-orb cp-cta-orb-one"></div>
            <div class="cp-cta-orb cp-cta-orb-two"></div>
            <div class="cp-cta-content">
                <div class="cp-eyebrow cp-eyebrow-dark"><span class="cp-eyebrow-dot"></span> Your next idea is ready</div>
                <h2 class="title">Create something worth sharing today</h2>
                <p>Start with a guided template, add your perspective, and keep every result organized in one secure workspace.</p>
                <div class="cp-cta-actions">
                    <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard')) : route('register') }}" class="btn btn-light btn-lg">Open ContentPilot <span aria-hidden="true">→</span></a>
                    <a href="{{ route('contact') }}" class="cp-cta-link">Talk to us</a>
                </div>
            </div>
            <div class="cp-cta-cards" aria-hidden="true"><span></span><span></span><span></span></div>
        </div>
    </div>
</section>
