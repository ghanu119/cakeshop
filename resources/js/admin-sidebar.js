const DESKTOP_BREAKPOINT = 1024;

function isDesktopViewport() {
    return window.matchMedia(`(min-width: ${DESKTOP_BREAKPOINT}px)`).matches;
}

function initAdminSidebar() {
    const root = document.documentElement;
    if (!root.classList.contains('admin-area')) {
        return;
    }

    const sidebar = document.querySelector('[data-admin-sidebar]');
    const backdrop = document.querySelector('[data-admin-sidebar-backdrop]');
    const toggleButtons = document.querySelectorAll('[data-admin-sidebar-toggle]');
    const closeButtons = document.querySelectorAll('[data-admin-sidebar-close]');

    if (!sidebar || toggleButtons.length === 0) {
        return;
    }

    const openIcons = document.querySelectorAll('[data-admin-sidebar-icon-open]');
    const closeIcons = document.querySelectorAll('[data-admin-sidebar-icon-close]');

    function isOpen() {
        return root.classList.contains('admin-sidebar-open');
    }

    function updateToggleState(open) {
        toggleButtons.forEach((button) => {
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            button.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        });

        openIcons.forEach((icon) => {
            icon.classList.toggle('hidden', open);
        });
        closeIcons.forEach((icon) => {
            icon.classList.toggle('hidden', !open);
        });

        if (backdrop) {
            backdrop.toggleAttribute('hidden', !open);
            backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
    }

    function openSidebar() {
        if (isDesktopViewport()) {
            return;
        }

        root.classList.add('admin-sidebar-open');
        updateToggleState(true);
    }

    function closeSidebar() {
        root.classList.remove('admin-sidebar-open');
        updateToggleState(false);
    }

    function toggleSidebar() {
        if (isOpen()) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    toggleButtons.forEach((button) => {
        button.addEventListener('click', toggleSidebar);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeSidebar);
    });

    backdrop?.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', () => {
            if (!isDesktopViewport()) {
                closeSidebar();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            closeSidebar();
        }
    });

    window.addEventListener('resize', () => {
        if (isDesktopViewport()) {
            closeSidebar();
        }
    });

    updateToggleState(false);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminSidebar);
} else {
    initAdminSidebar();
}
