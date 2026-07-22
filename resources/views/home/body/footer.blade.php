<footer class="nk-footer cp-footer">
    <div class="container">
        <div class="cp-footer-grid">
            <div class="cp-footer-brand">
                <a href="{{ route('home') }}" class="logo-link"><img class="logo-img logo-img--light" src="{{ asset('assets/images/contentpilot-logo-light.svg') }}" alt="ContentPilot"></a>
                <p>AI-assisted content creation with a simple workflow, private libraries, and controls you can trust.</p>
                <div class="cp-footer-owner">Laravel portfolio project · Karachi, Pakistan</div>
            </div>
            <div class="cp-footer-links"><h3>Product</h3><a href="{{ route('features') }}">Features</a><a href="{{ route('usecase') }}">Use cases</a><a href="{{ route('pricing') }}">Pricing</a></div>
            <div class="cp-footer-links"><h3>Company</h3><a href="{{ route('home') }}#about">About</a><a href="{{ route('contact') }}">Contact</a><a href="{{ route('login') }}">Sign in</a></div>
            <div class="cp-footer-newsletter"><h3>Start creating</h3><p>Bring your next campaign, article, image, or script to life.</p><a href="{{ route('register') }}" class="btn btn-primary cp-footer-cta">Create free account</a></div>
        </div>
        <div class="cp-footer-bottom"><span>© {{ now()->year }} ContentPilot. All rights reserved.</span><span>Designed &amp; developed by Muhammad Ali Nawaz</span></div>
    </div>
</footer>
