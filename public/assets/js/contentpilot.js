/*
 * ContentPilot AI interface behavior
 * Copyright (c) 2026 Muhammad Ali Nawaz
 * Licensed under the repository MIT License.
 */

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const sidebar = document.querySelector('.nk-sidebar');
    const publicHeader = document.querySelector('[data-public-header]');

    const setSidebarOpen = (open) => {
        body.classList.toggle('sidebar-open', open);
        sidebar?.classList.toggle('is-open', open);
        document.querySelectorAll('.sidebar-toggle').forEach((button) => {
            button.setAttribute('aria-expanded', String(open));
        });
    };

    document.querySelectorAll('.sidebar-toggle').forEach((button) => {
        button.setAttribute('aria-expanded', 'false');
        button.addEventListener('click', (event) => {
            event.preventDefault();
            setSidebarOpen(!body.classList.contains('sidebar-open'));
        });
    });

    document.querySelectorAll('.compact-toggle').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            if (window.innerWidth >= 992) {
                body.classList.toggle('sidebar-compact');
            } else {
                setSidebarOpen(!body.classList.contains('sidebar-open'));
            }
        });
    });

    const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
    document.querySelectorAll('.nk-sidebar a.nk-menu-link[href]').forEach((link) => {
        const rawHref = link.getAttribute('href')?.trim();
        if (!rawHref || rawHref === '#' || rawHref.startsWith('#') || rawHref.toLowerCase().startsWith('javascript:')) {
            return;
        }

        try {
            const linkPath = new URL(link.href, window.location.href).pathname.replace(/\/$/, '') || '/';
            if (linkPath === currentPath) {
                link.setAttribute('aria-current', 'page');
                link.closest('.nk-menu-item')?.classList.add('is-active');
                const parentSubmenu = link.closest('.nk-menu-sub');
                const parentItem = parentSubmenu?.closest('.nk-menu-item.has-sub');
                parentItem?.classList.add('is-open');
                parentItem?.querySelector(':scope > .nk-menu-toggle')?.setAttribute('aria-expanded', 'true');
            }
        } catch {
            // Ignore non-standard links.
        }
    });

    document.querySelectorAll('.nk-menu-toggle').forEach((toggle) => {
        const item = toggle.closest('.nk-menu-item');
        const submenu = item?.querySelector(':scope > .nk-menu-sub');
        if (!item || !submenu) return;

        const initiallyOpen = item.classList.contains('is-open') || Array.from(submenu.querySelectorAll('a[href]')).some((link) => {
            try {
                return (new URL(link.href, window.location.href).pathname.replace(/\/$/, '') || '/') === currentPath;
            } catch {
                return false;
            }
        });

        item.classList.toggle('is-open', initiallyOpen);
        toggle.setAttribute('aria-expanded', String(initiallyOpen));

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            const open = !item.classList.contains('is-open');
            item.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', String(open));
        });
    });

    const headerToggle = document.querySelector('.header-menu-toggle');
    const headerMenu = document.querySelector('.nk-header-main .nk-header-menu');
    if (headerToggle && headerMenu) {
        headerToggle.setAttribute('aria-expanded', 'false');
        headerToggle.addEventListener('click', (event) => {
            event.preventDefault();
            const open = !body.classList.contains('header-menu-open');
            body.classList.toggle('header-menu-open', open);
            headerToggle.setAttribute('aria-expanded', String(open));
        });

        headerMenu.querySelectorAll('a[href]').forEach((link) => {
            link.addEventListener('click', () => {
                body.classList.remove('header-menu-open');
                headerToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    const updateHeaderState = () => {
        publicHeader?.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    updateHeaderState();
    window.addEventListener('scroll', updateHeaderState, { passive: true });

    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach((button) => {
        const targetSelector = button.getAttribute('data-bs-target');
        if (!targetSelector || !targetSelector.startsWith('#')) return;
        const target = document.querySelector(targetSelector);
        if (!target) return;

        button.addEventListener('click', () => {
            const parentSelector = target.getAttribute('data-bs-parent');
            const willOpen = !target.classList.contains('show');

            if (willOpen && parentSelector) {
                document.querySelectorAll(`${parentSelector} .accordion-collapse.show`).forEach((openPanel) => {
                    if (openPanel === target) return;
                    openPanel.classList.remove('show');
                    const openButton = document.querySelector(`[data-bs-target="#${CSS.escape(openPanel.id)}"]`);
                    openButton?.classList.add('collapsed');
                    openButton?.setAttribute('aria-expanded', 'false');
                });
            }

            target.classList.toggle('show', willOpen);
            button.classList.toggle('collapsed', !willOpen);
            button.setAttribute('aria-expanded', String(willOpen));
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Continue with this action?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    document.querySelectorAll('input[type="password"]').forEach((input, index) => {
        let wrapper = input.closest('.cp-password-field, .cp-input-wrap');

        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'cp-password-field';
            input.parentNode?.insertBefore(wrapper, input);
            wrapper.append(input);
        } else {
            wrapper.classList.add('cp-password-field');
        }

        if (wrapper.querySelector('.cp-password-toggle')) return;

        if (!input.id) input.id = `password-field-${index + 1}`;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'cp-password-toggle';
        button.setAttribute('aria-controls', input.id);
        button.setAttribute('aria-label', 'Show password');
        button.setAttribute('aria-pressed', 'false');
        button.title = 'Show password';
        button.innerHTML = '<em class="cp-icon cp-icon-eye" aria-hidden="true"></em>';

        button.addEventListener('click', () => {
            const isVisible = input.type === 'text';
            input.type = isVisible ? 'password' : 'text';
            button.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
            button.setAttribute('aria-pressed', String(!isVisible));
            button.title = isVisible ? 'Show password' : 'Hide password';

            const icon = button.querySelector('.cp-icon');
            icon?.classList.toggle('cp-icon-eye', isVisible);
            icon?.classList.toggle('cp-icon-eye-off', !isVisible);
            input.focus({ preventScroll: true });
        });

        wrapper.append(button);
    });

    document.querySelectorAll('img[data-avatar-fallback]').forEach((image) => {
        const fallback = image.getAttribute('data-fallback-src');
        if (!fallback) return;

        const applyFallback = () => {
            if (image.src === new URL(fallback, window.location.href).href) return;
            image.src = fallback;
        };

        image.addEventListener('error', applyFallback, { once: true });
        if (!image.getAttribute('src') || (image.complete && image.naturalWidth === 0)) applyFallback();
    });


    document.querySelectorAll('[data-image-preview-target]').forEach((input) => {
        const selector = input.getAttribute('data-image-preview-target');
        const preview = selector ? document.querySelector(selector) : null;
        if (!preview) return;

        const originalSource = preview.getAttribute('src') || '';
        let objectUrl = null;

        input.addEventListener('change', () => {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            const [file] = input.files || [];
            if (!file) {
                preview.src = originalSource;
                return;
            }

            if (!file.type.startsWith('image/')) {
                input.value = '';
                preview.src = originalSource;
                return;
            }

            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
        });
    });

    document.querySelectorAll('[data-auto-dismiss]').forEach((toast) => {
        const duration = Number.parseInt(toast.getAttribute('data-auto-dismiss') || '5000', 10);
        const closeButton = toast.querySelector('[data-toast-close]');
        let timer = null;

        const dismiss = () => {
            if (timer) window.clearTimeout(timer);
            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.closest('.cp-toast-stack')?.remove(), 360);
        };

        toast.style.setProperty('--cp-toast-duration', `${Math.max(1000, duration)}ms`);
        window.requestAnimationFrame(() => toast.classList.add('is-visible'));
        timer = window.setTimeout(dismiss, Math.max(1000, duration));
        closeButton?.addEventListener('click', dismiss);
    });

    document.querySelectorAll('.cp-user-menu').forEach((menu) => {
        menu.addEventListener('toggle', () => {
            if (!menu.open) return;
            document.querySelectorAll('.cp-user-menu[open]').forEach((otherMenu) => {
                if (otherMenu !== menu) otherMenu.removeAttribute('open');
            });
        });
    });

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealItems = document.querySelectorAll('.cp-reveal');
    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    } else {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const delay = Number.parseInt(entry.target.getAttribute('data-reveal-delay') || '0', 10);
                window.setTimeout(() => entry.target.classList.add('is-visible'), Math.min(delay, 400));
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px' });
        revealItems.forEach((item) => revealObserver.observe(item));
    }

    document.addEventListener('click', (event) => {
        document.querySelectorAll('.cp-user-menu[open]').forEach((menu) => {
            if (!menu.contains(event.target)) menu.removeAttribute('open');
        });

        if (body.classList.contains('sidebar-open') && sidebar) {
            const clickedToggle = event.target.closest('.sidebar-toggle, .compact-toggle');
            const clickedSidebar = event.target.closest('.nk-sidebar');
            if (!clickedToggle && !clickedSidebar) setSidebarOpen(false);
        }

        if (body.classList.contains('header-menu-open')) {
            const clickedHeader = event.target.closest('.nk-header-main');
            if (!clickedHeader) {
                body.classList.remove('header-menu-open');
                headerToggle?.setAttribute('aria-expanded', 'false');
            }
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.cp-user-menu[open]').forEach((menu) => menu.removeAttribute('open'));
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            setSidebarOpen(false);
            body.classList.remove('header-menu-open');
            headerToggle?.setAttribute('aria-expanded', 'false');
        }
    });
});
