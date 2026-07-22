@extends('home.page.main_master')
@section('title', 'Contact · ContentPilot')
@section('page')
<section class="section cp-contact-section">
    <div class="container">
        <div class="row g-gs align-items-start">
            <div class="col-lg-5">
                <div class="cp-contact-intro cp-reveal">
                    <div class="cp-eyebrow"><span class="cp-eyebrow-dot"></span> Let’s create something useful</div>
                    <h1 class="title">Tell us what you are building</h1>
                    <p class="lead">Questions about the project, portfolio, workflow, or a potential collaboration are always welcome.</p>
                    <div class="cp-contact-points">
                        <article><span><em class="cp-icon cp-icon-chat"></em></span><div><strong>Thoughtful replies</strong><p>Share the context and desired outcome so your message can be handled clearly.</p></div></article>
                        <article><span><em class="cp-icon cp-icon-lock"></em></span><div><strong>Privacy-first contact</strong><p>Do not include passwords, API keys, payment secrets, or confidential credentials.</p></div></article>
                        <article><span><em class="cp-icon cp-icon-user"></em></span><div><strong>Muhammad Ali Nawaz</strong><p>Laravel developer · Karachi, Pakistan</p></div></article>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="cp-contact-card card cp-reveal" data-reveal-delay="100">
                    <div class="card-body">
                        <div class="cp-form-heading"><span>Send a message</span><small>Fields marked required must be completed.</small></div>
                        <form action="{{ route('store.contact') }}" method="POST">@csrf
                            <div class="row g-4">
                                <div class="col-md-6"><label class="form-label" for="name">Name</label><input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control form-control-lg" maxlength="120" placeholder="Your full name" required></div>
                                <div class="col-md-6"><label class="form-label" for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" maxlength="180" placeholder="you@example.com" required></div>
                                <div class="col-12"><label class="form-label" for="subject">Subject</label><input id="subject" type="text" name="subject" value="{{ old('subject') }}" class="form-control form-control-lg" maxlength="180" placeholder="How can we help?" required></div>
                                <div class="col-12"><label class="form-label" for="message">Message</label><textarea id="message" name="message" class="form-control form-control-lg" rows="7" maxlength="3000" placeholder="Tell us about your question, project, or goal..." required>{{ old('message') }}</textarea></div>
                                <div class="col-12"><button class="btn btn-primary btn-lg" type="submit">Send message <span aria-hidden="true">→</span></button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
