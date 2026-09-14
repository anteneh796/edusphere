const ToastStore = {
    container: null,

    ensureContainer() {
        if (this.container) {
            return this.container;
        }

        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);

        return this.container;
    },

    push(message, type = 'info', title = null) {
        const container = this.ensureContainer();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.setAttribute('role', 'status');

        const iconName = type === 'success' ? 'check' : type === 'danger' ? 'alert' : type === 'warning' ? 'warn' : 'info';
        const svg = this.renderIcon(iconName);

        toast.appendChild(svg);

        const content = document.createElement('div');
        content.style.flex = '1';
        if (title) {
            const t = document.createElement('div');
            t.style.fontWeight = '700';
            t.textContent = title;
            content.appendChild(t);
        }
        const msg = document.createElement('div');
        msg.style.color = 'var(--color-text-muted)';
        msg.textContent = message;
        content.appendChild(msg);

        toast.appendChild(content);

        const close = document.createElement('button');
        close.className = 'btn btn-ghost btn-xs btn-icon';
        close.innerHTML = '&times;';
        close.onclick = () => toast.remove();
        toast.appendChild(close);

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 300ms, transform 300ms';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(12px)';
            setTimeout(() => toast.remove(), 320);
        }, 4000);
    },

    renderIcon(name) {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'icon');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '2');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');

        const paths = {
            check: '<path d="M20 6L9 17l-5-5" />',
            alert: '<circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" />',
            warn: '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" />',
            info: '<circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />',
        };

        svg.innerHTML = paths[name] ?? paths.info;

        return svg;
    },
};

window.EduToast = ToastStore;