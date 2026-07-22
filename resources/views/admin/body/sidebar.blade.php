<aside class="nk-sidebar nk-sidebar-fixed" id="sidebar">
    <div class="nk-sidebar-element nk-sidebar-head">
        <div class="nk-sidebar-brand">
            <a href="{{ route('admin.dashboard') }}" class="logo-link" aria-label="ContentPilot administration dashboard">
                <div class="logo-wrap">
                    <img class="logo-img logo-light" src="{{ asset('assets/images/contentpilot-logo.svg') }}" alt="ContentPilot">
                    <img class="logo-img logo-icon" src="{{ asset('assets/images/contentpilot-icon.svg') }}" alt="ContentPilot">
                </div>
            </a>
        </div>
    </div>
    <div class="nk-sidebar-element nk-sidebar-body">
        <div class="nk-sidebar-content h-100">
            <nav class="nk-sidebar-menu" aria-label="Administration navigation">
                <ul class="nk-menu">
                    <li class="nk-menu-item"><a href="{{ route('admin.dashboard') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-dashboard"></em></span><span class="nk-menu-text">Home</span></a></li>
                    <li class="nk-menu-heading"><h6 class="overline-title">Workspace</h6></li>
                    <li class="nk-menu-item"><a href="{{ route('admin.templates.index') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-templates"></em></span><span class="nk-menu-text">Templates</span></a></li>
                    <li class="nk-menu-item"><a href="{{ route('admin.documents.index') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-document"></em></span><span class="nk-menu-text">Documents</span></a></li>
                    <li class="nk-menu-item"><a href="{{ route('admin.assistants.index') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-chat"></em></span><span class="nk-menu-text">AI assistants</span></a></li>
                    <li class="nk-menu-item has-sub">
                        <a href="#" class="nk-menu-link nk-menu-toggle"><span class="nk-menu-icon"><em class="cp-icon cp-icon-spark"></em></span><span class="nk-menu-text">AI media</span></a>
                        <ul class="nk-menu-sub">
                            <li><a href="{{ route('admin.images.create') }}" class="nk-menu-link">Generate image</a></li>
                            <li><a href="{{ route('admin.images.index') }}" class="nk-menu-link">Image library</a></li>
                            <li><a href="{{ route('admin.audio.create') }}" class="nk-menu-link">Generate audio</a></li>
                            <li><a href="{{ route('admin.audio.index') }}" class="nk-menu-link">Audio library</a></li>
                        </ul>
                    </li>
                    <li class="nk-menu-heading"><h6 class="overline-title">Manage</h6></li>
                    <li class="nk-menu-item"><a href="{{ route('admin.plans.index') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-plan"></em></span><span class="nk-menu-text">Plans</span></a></li>
                    <li class="nk-menu-item"><a href="{{ route('admin.payments.index') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-billing"></em></span><span class="nk-menu-text">Payments</span></a></li>
                    <li class="nk-menu-item has-sub">
                        <a href="#" class="nk-menu-link nk-menu-toggle"><span class="nk-menu-icon"><em class="cp-icon cp-icon-settings"></em></span><span class="nk-menu-text">Website CMS</span></a>
                        <ul class="nk-menu-sub">
                            <li><a href="{{ route('admin.cms.slider.edit') }}" class="nk-menu-link">Hero content</a></li>
                            <li><a href="{{ route('admin.cms.headings.index') }}" class="nk-menu-link">Headings</a></li>
                            <li><a href="{{ route('admin.cms.questions.index') }}" class="nk-menu-link">FAQs</a></li>
                            <li><a href="{{ route('admin.contacts.index') }}" class="nk-menu-link">Messages</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <div class="cp-sidebar-create cp-sidebar-create-bottom">
                <a href="{{ route('admin.templates.create') }}" class="btn btn-primary w-100">
                    <em class="cp-icon cp-icon-spark"></em><span>Create template</span>
                </a>
            </div>
        </div>
    </div>
</aside>
