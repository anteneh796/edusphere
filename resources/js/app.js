import Alpine from 'alpinejs';

import './modules/toast';
import './modules/offline';
import './modules/charts';

window.Alpine = Alpine;

Alpine.store('ui', {
    sidebarCollapsed: false,
    mobileSidebarOpen: false,

    get bodyClasses() {
        return {
            'sidebar-collapsed': this.sidebarCollapsed,
            'mobile-sidebar-open': this.mobileSidebarOpen,
        };
    },

    collapseSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
    },

    closeMobileSidebar() {
        this.mobileSidebarOpen = false;
    },

    go(link = null) {
        if (link) {
            window.location.href = link;
        }
    },
});

Alpine.store('confirm', {
    open: false,
    title: 'Are you sure?',
    message: 'This action cannot be undone.',
    actionUrl: '#',
    method: 'DELETE',
    confirmText: 'Confirm',

    ask({ title = 'Are you sure?', message = 'This action cannot be undone.', action, method = 'DELETE', confirmText = 'Confirm' }) {
        this.title = title;
        this.message = message;
        this.actionUrl = action;
        this.method = method;
        this.confirmText = confirmText;
        this.open = true;
    },

    close() {
        this.open = false;
    },

    submit() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = this.actionUrl;
        form.style.display = 'none';

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = this.method;
        form.appendChild(method);

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        form.appendChild(token);

        document.body.appendChild(form);
        form.submit();
    },
});

Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
}));

Alpine.data('dismissable', () => ({
    show: true,
}));

document.addEventListener('alpine:init', () => {
    Alpine.directive('modal-blur', (el) => {
        el._modalBlurCleanup = () => {
            const body = document.body;
            body.classList.toggle('modal-open', Boolean(document.querySelector('.modal-backdrop.open')));
        };
    });

    const observer = new MutationObserver(() => {
        document.body.classList.toggle('modal-open', Boolean(document.querySelector('.modal-backdrop.open')));
    });
    observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
});

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-auto-close]').forEach((el) => {
        el.addEventListener('auto-close', () => el.style.display = 'none');
    });
});