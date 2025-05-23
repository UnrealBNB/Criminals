// Criminals Game - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Flash Messages Auto-hide
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Mobile Menu Toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', () => {
            mainNav.classList.toggle('active');
        });
    }

    // Select All Checkboxes
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="id["]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // Confirm Actions
    const confirmBtns = document.querySelectorAll('[data-confirm]');
    confirmBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-submit Forms
    const autoSubmitSelects = document.querySelectorAll('select[data-auto-submit]');
    autoSubmitSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Number Input Validation
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            const min = parseInt(this.min) || 0;
            const max = parseInt(this.max) || Infinity;
            let value = parseInt(this.value) || 0;

            if (value < min) this.value = min;
            if (value > max) this.value = max;
        });
    });

    // Copy to Clipboard
    const copyBtns = document.querySelectorAll('[data-copy]');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.copy;
            const target = document.getElementById(targetId);

            if (target) {
                target.select();
                document.execCommand('copy');

                const originalText = this.textContent;
                this.textContent = 'Copied!';
                setTimeout(() => {
                    this.textContent = originalText;
                }, 2000);
            }
        });
    });

    // AJAX Form Submission (for future use)
    const ajaxForms = document.querySelectorAll('[data-ajax]');
    ajaxForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const action = this.action;
            const method = this.method;

            try {
                const response = await fetch(action, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.message) {
                        showNotification(data.message, 'success');
                    }
                } else {
                    showNotification(data.message || 'An error occurred', 'error');
                }
            } catch (error) {
                console.error('Form submission error:', error);
                showNotification('Network error. Please try again.', 'error');
            }
        });
    });

    // Notification System
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transition = 'opacity 0.5s';
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }

    // Gambling Game Enhancements
    const rouletteNumbers = document.querySelectorAll('.roulette .number-option');
    rouletteNumbers.forEach(num => {
        num.addEventListener('click', function() {
            const input = this.querySelector('input');
            if (input) input.checked = true;
        });
    });

    // Real-time Form Validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateInput(this);
            });
        });

        form.addEventListener('submit', function(e) {
            let valid = true;
            inputs.forEach(input => {
                if (!validateInput(input)) {
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();
            }
        });
    });

    function validateInput(input) {
        const value = input.value.trim();
        const parent = input.closest('.form-group');
        let errorEl = parent.querySelector('.error');

        if (!errorEl) {
            errorEl = document.createElement('span');
            errorEl.className = 'error';
            parent.appendChild(errorEl);
        }

        if (!value && input.hasAttribute('required')) {
            errorEl.textContent = 'This field is required';
            input.classList.add('error');
            return false;
        }

        if (input.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                errorEl.textContent = 'Please enter a valid email';
                input.classList.add('error');
                return false;
            }
        }

        if (input.type === 'number' && value) {
            const num = parseFloat(value);
            const min = parseFloat(input.min);
            const max = parseFloat(input.max);

            if (min && num < min) {
                errorEl.textContent = `Minimum value is ${min}`;
                input.classList.add('error');
                return false;
            }

            if (max && num > max) {
                errorEl.textContent = `Maximum value is ${max}`;
                input.classList.add('error');
                return false;
            }
        }

        errorEl.textContent = '';
        input.classList.remove('error');
        return true;
    }

    // Theme Switcher (if implemented)
    const themeSwitcher = document.querySelector('[data-theme-switcher]');
    if (themeSwitcher) {
        themeSwitcher.addEventListener('change', function() {
            const theme = this.value;
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('game-theme', theme);
        });

        // Load saved theme
        const savedTheme = localStorage.getItem('game-theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            themeSwitcher.value = savedTheme;
        }
    }

    // Lazy Loading Images
    const lazyImages = document.querySelectorAll('img[data-lazy]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.lazy;
                    img.removeAttribute('data-lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.src = img.dataset.lazy;
        });
    }

    // Initialize tooltips (if using)
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(el => {
        el.addEventListener('mouseenter', function() {
            const text = this.dataset.tooltip;
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = text;
            tooltip.style.position = 'absolute';
            tooltip.style.background = '#333';
            tooltip.style.color = 'white';
            tooltip.style.padding = '5px 10px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '0.875rem';
            tooltip.style.zIndex = '9999';

            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';

            this.tooltipElement = tooltip;
        });

        el.addEventListener('mouseleave', function() {
            if (this.tooltipElement) {
                this.tooltipElement.remove();
                delete this.tooltipElement;
            }
        });
    });
});

// Global utility functions
window.CriminalsGame = {
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },

    formatNumber: function(num) {
        return new Intl.NumberFormat().format(num);
    },

    formatCurrency: function(num) {
        return '€' + this.formatNumber(num);
    },

    countdown: function(elementId, seconds, callback) {
        const element = document.getElementById(elementId);
        if (!element) return;

        let remaining = seconds;
        const timer = setInterval(() => {
            remaining--;
            const minutes = Math.floor(remaining / 60);
            const secs = remaining % 60;
            element.textContent = `${minutes}:${secs.toString().padStart(2, '0')}`;

            if (remaining <= 0) {
                clearInterval(timer);
                if (callback) callback();
            }
        }, 1000);
    }
};