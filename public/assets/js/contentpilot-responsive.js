// ContentPilot minimal dashboard responsive behavior.
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const sidebar = document.getElementById('sidebar') || document.querySelector('.nk-sidebar');
    const sidebarToggles = document.querySelectorAll('.sidebar-toggle');
    const compactToggles = document.querySelectorAll('.compact-toggle');

    sidebarToggles.forEach((button) => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            body.classList.toggle('sidebar-open');
            if (sidebar) sidebar.classList.toggle('is-open', body.classList.contains('sidebar-open'));
        });
    });

    compactToggles.forEach((button) => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            if (window.innerWidth >= 992) {
                body.classList.toggle('sidebar-compact');
            } else {
                body.classList.toggle('sidebar-open');
                if (sidebar) sidebar.classList.toggle('is-open', body.classList.contains('sidebar-open'));
            }
        });
    });

    document.addEventListener('click', function (event) {
        if (!body.classList.contains('sidebar-open') || !sidebar) return;
        const clickedToggle = event.target.closest('.sidebar-toggle, .compact-toggle');
        const clickedSidebar = event.target.closest('.nk-sidebar');
        if (!clickedToggle && !clickedSidebar) {
            body.classList.remove('sidebar-open');
            sidebar.classList.remove('is-open');
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            body.classList.remove('sidebar-open');
            if (sidebar) sidebar.classList.remove('is-open');
        }
    });
});
