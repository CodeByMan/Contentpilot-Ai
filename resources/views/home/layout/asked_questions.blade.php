<section class="section cp-faq-section">
    <div class="container">
        <div class="row g-gs align-items-start">
            <div class="col-lg-5">
                <div class="cp-faq-intro cp-reveal">
                    <div class="cp-eyebrow cp-eyebrow-light"><span class="cp-eyebrow-dot"></span> Questions, answered</div>
                    <h2 class="title">Everything you need to start creating confidently</h2>
                    <p class="lead">Learn how plans, privacy, generation, and account access work before you begin.</p>
                    <a href="{{ route('contact') }}" class="btn btn-ghost">Still need help? Contact us <span aria-hidden="true">→</span></a>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="accordion cp-faq-list" id="faq">
                    @forelse ($questions as $index => $question)
                        <div class="accordion-item cp-reveal" data-reveal-delay="{{ $index * 45 }}">
                            <h3 class="accordion-header"><button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $question->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">{{ $question->title }}</button></h3>
                            <div id="faq-{{ $question->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#faq"><div class="accordion-body">{{ $question->description }}</div></div>
                        </div>
                    @empty
                        <div class="cp-empty-state"><em class="cp-icon cp-icon-chat"></em><strong>No FAQs published yet</strong><p>Seed or add FAQ records from the CMS to display them here.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
