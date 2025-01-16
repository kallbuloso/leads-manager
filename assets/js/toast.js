class LMToast {
    constructor() {
        this.init();
    }

    init() {
        if (!document.querySelector('.lm-toast-container')) {
            const container = document.createElement('div');
            container.className = 'lm-toast-container';
            document.body.appendChild(container);
        }
    }

    show(message, type = 'success', duration = 3000) {
        const container = document.querySelector('.lm-toast-container');
        const toast = document.createElement('div');
        toast.className = `lm-toast ${type}`;
        toast.dataset.type = type; // Adiciona o tipo como dataset
        
        const messageSpan = document.createElement('span');
        messageSpan.className = 'lm-toast-message';
        messageSpan.textContent = message;
        
        const closeButton = document.createElement('span');
        closeButton.className = 'lm-toast-close';
        closeButton.innerHTML = '&times;';
        closeButton.onclick = () => this.hide(toast);

        toast.appendChild(messageSpan);
        toast.appendChild(closeButton);
        container.appendChild(toast);

        // Força um reflow para garantir que a animação funcione
        toast.offsetHeight;

        // Mostra o toast
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Remove o toast após a duração especificada
        if (duration) {
            setTimeout(() => {
                this.hide(toast);
            }, duration);
        }
    }

    hide(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300); // Tempo da animação
    }

    // Novo método para remover toasts por tipo
    removeByType(type) {
        const toasts = document.querySelectorAll(`.lm-toast[data-type="${type}"]`);
        toasts.forEach(toast => this.hide(toast));
    }
}
