import './bootstrap';

/**
 * Wire up a disclosure: a trigger button that shows and hides a panel.
 *
 * Used for the mobile navigation drawer and the mobile search field. The
 * panel is hidden with the `hidden` attribute so it never traps focus or
 * renders off-screen, and the trigger reports its state with
 * `aria-expanded` for assistive technology.
 *
 * @param {string} triggerSelector
 * @param {string} panelSelector
 * @param {{ lockScroll?: boolean, closeSelector?: string }} options
 */
function bindDisclosure(triggerSelector, panelSelector, options = {}) {
    const { lockScroll = false, closeSelector = null } = options;

    const trigger = document.querySelector(triggerSelector);
    const panel = document.querySelector(panelSelector);

    if (!trigger || !panel) {
        return;
    }

    const open = () => {
        panel.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');

        if (lockScroll) {
            document.body.classList.add('overflow-hidden');
        }
    };

    const close = () => {
        panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');

        if (lockScroll) {
            document.body.classList.remove('overflow-hidden');
        }
    };

    const toggle = () => {
        if (panel.hidden) {
            open();
        } else {
            close();
        }
    };

    trigger.addEventListener('click', toggle);

    if (closeSelector) {
        panel.querySelectorAll(closeSelector).forEach((button) => {
            button.addEventListener('click', close);
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || panel.hidden) {
            return;
        }

        close();
        trigger.focus();
    });

    // Following a link inside the drawer should close it, otherwise the
    // overlay stays on screen while the next page loads behind it.
    panel.addEventListener('click', (event) => {
        if (lockScroll && event.target.closest('a')) {
            close();
        }
    });
}

bindDisclosure('[data-nav-toggle]', '[data-nav-panel]', {
    lockScroll: true,
    closeSelector: '[data-nav-close]',
});

bindDisclosure('[data-search-toggle]', '[data-search-panel]');

/**
 * Wire up the admin sidebar drawer on small screens.
 *
 * The drawer is a native pattern in the layout already, but this adds a
 * controlled toggle with focus handling and body scroll locking.
 */
function bindAdminSidebar() {
    const toggle = document.querySelector('[data-admin-sidebar-toggle]');
    const panel = document.querySelector('[data-admin-sidebar]');
    const closeButtons = document.querySelectorAll('[data-admin-sidebar-close]');
    const backdrop = document.querySelector('[data-admin-sidebar-backdrop]');

    if (!toggle || !panel) {
        return;
    }

    const open = () => {
        panel.hidden = false;
        document.body.classList.add('overflow-hidden');
        toggle.setAttribute('aria-expanded', 'true');

        window.requestAnimationFrame(() => {
            panel.querySelector('[data-admin-sidebar-close]')?.focus();
        });
    };

    const close = () => {
        panel.hidden = true;
        document.body.classList.remove('overflow-hidden');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
    };

    toggle.addEventListener('click', () => {
        if (panel.hidden) {
            open();
        } else {
            close();
        }
    });

    closeButtons.forEach((button) => button.addEventListener('click', close));
    backdrop?.addEventListener('click', close);

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || panel.hidden) {
            return;
        }

        close();
    });

    panel.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            close();
        }
    });
}

bindAdminSidebar();

/**
 * Ask for confirmation before a destructive admin action runs.
 *
 * The trigger carries the confirmation text in `data-confirm`. Without
 * JavaScript the form submits normally, so the confirmation is an
 * enhancement and never a security control — the server still validates
 * every request.
 */
function bindConfirmDialogs() {
    const dialog = document.querySelector('[data-confirm-dialog]');

    if (!dialog || typeof dialog.showModal !== 'function') {
        return;
    }

    const message = dialog.querySelector('[data-confirm-message]');
    const accept = dialog.querySelector('[data-confirm-accept]');
    const cancel = dialog.querySelector('[data-confirm-cancel]');

    if (!message || !accept || !cancel) {
        return;
    }

    let pendingForm = null;

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-confirm]');

        if (!trigger) {
            return;
        }

        const form = trigger.closest('form');

        if (!form) {
            return;
        }

        event.preventDefault();

        pendingForm = form;
        message.textContent = trigger.dataset.confirm || 'Are you sure?';
        dialog.showModal();
    });

    cancel.addEventListener('click', () => {
        pendingForm = null;
        dialog.close();
    });

    accept.addEventListener('click', () => {
        const form = pendingForm;

        pendingForm = null;
        dialog.close();

        if (!form) {
            return;
        }

        if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
            return;
        }

        form.submit();
    });

    dialog.addEventListener('close', () => {
        pendingForm = null;
    });
}

bindConfirmDialogs();

/**
 * Keep every cart badge in sync after an item is added without a reload.
 *
 * @param {number} count
 */
function updateCartCounts(count) {
    document.querySelectorAll('[data-cart-count]').forEach((badge) => {
        badge.textContent = String(count);
        badge.classList.toggle('hidden', count < 1);
    });
}

/**
 * Bind the product gallery: thumbnails, the enlarged image dialog and
 * keyboard navigation.
 *
 * The markup works without this script: the main image is a link to the
 * original file, and the dialog is only opened when supported.
 */
function bindProductGallery() {
    const gallery = document.querySelector('[data-gallery]');

    if (!gallery) {
        return;
    }

    const mainImage = gallery.querySelector('[data-gallery-image]');
    const openLink = gallery.querySelector('[data-gallery-open]');
    const status = gallery.querySelector('[data-gallery-status]');
    const dialog = gallery.querySelector('[data-gallery-dialog]');
    const dialogImage = gallery.querySelector('[data-gallery-dialog-image]');
    const dialogCounter = gallery.querySelector('[data-gallery-dialog-counter]');
    const thumbs = Array.from(gallery.querySelectorAll('[data-gallery-thumb]'));

    if (!mainImage || !openLink) {
        return;
    }

    const slides = thumbs.length > 0
        ? thumbs.map((thumb) => ({
            src: thumb.dataset.src,
            alt: thumb.dataset.alt,
            placeholder: thumb.dataset.placeholder === 'true',
        }))
        : [{
            src: mainImage.getAttribute('src'),
            alt: mainImage.getAttribute('alt'),
            placeholder: mainImage.classList.contains('object-contain'),
        }];

    let index = 0;
    let dialogOpen = false;

    const render = (nextIndex) => {
        index = (nextIndex + slides.length) % slides.length;

        const slide = slides[index];

        mainImage.src = slide.src;
        mainImage.alt = slide.alt;
        mainImage.classList.toggle('object-contain', slide.placeholder);
        mainImage.classList.toggle('object-cover', !slide.placeholder);
        mainImage.classList.toggle('p-10', slide.placeholder);
        mainImage.classList.toggle('opacity-80', slide.placeholder);

        openLink.href = slide.src;
        openLink.setAttribute('aria-label', `Enlarge image ${index + 1} of ${slides.length}`);

        thumbs.forEach((thumb, thumbIndex) => {
            const active = thumbIndex === index;

            thumb.setAttribute('aria-current', active ? 'true' : 'false');
            thumb.classList.toggle('border-charcoal', active);
            thumb.classList.toggle('border-sand', !active);
            thumb.classList.toggle('hover:border-charcoal/40', !active);
        });

        if (status) {
            status.textContent = `Image ${index + 1} of ${slides.length}`;
        }

        if (dialogCounter) {
            dialogCounter.textContent = `Image ${index + 1} of ${slides.length}`;
        }

        if (dialogOpen && dialogImage) {
            dialogImage.src = slide.src;
            dialogImage.alt = slide.alt;
        }
    };

    thumbs.forEach((thumb, thumbIndex) => {
        thumb.addEventListener('click', () => render(thumbIndex));
    });

    openLink.addEventListener('click', (event) => {
        if (!dialog || typeof dialog.showModal !== 'function') {
            return;
        }

        event.preventDefault();
        dialogOpen = true;

        if (dialogImage) {
            dialogImage.src = slides[index].src;
            dialogImage.alt = slides[index].alt;
        }

        dialog.showModal();
    });

    if (!dialog) {
        return;
    }

    gallery.querySelectorAll('[data-gallery-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    gallery.querySelector('[data-gallery-prev]')?.addEventListener('click', () => render(index - 1));
    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => render(index + 1));

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            render(index + 1);
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            render(index - 1);
        } else if (event.key === 'Home') {
            event.preventDefault();
            render(0);
        } else if (event.key === 'End') {
            event.preventDefault();
            render(slides.length - 1);
        }
    });

    dialog.addEventListener('close', () => {
        dialogOpen = false;
    });
}

/**
 * Step the quantity input with the visible minus and plus controls,
 * respecting the stock-derived min and max already on the input.
 */
function bindQuantitySteppers() {
    document.querySelectorAll('[data-qty-step]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.getAttribute('aria-controls') ?? '');

            if (!input) {
                return;
            }

            const min = Number.parseInt(input.min, 10) || 1;
            const max = Number.parseInt(input.max, 10) || min;
            const current = Number.parseInt(input.value, 10) || min;
            const offset = Number.parseInt(button.dataset.qtyStep, 10) || 0;

            input.value = String(Math.min(max, Math.max(min, current + offset)));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
}

/**
 * Submit the add to cart form over fetch so the page can show loading,
 * success and error states without a reload.
 *
 * Without JavaScript the form posts normally and the server redirects
 * back with a status message.
 */
function bindAddToCartForms() {
    document.querySelectorAll('[data-add-to-cart]').forEach((form) => {
        const button = form.querySelector('[data-add-to-cart-button]');
        const label = form.querySelector('[data-add-to-cart-label]');
        const spinner = form.querySelector('[data-add-to-cart-spinner]');
        const status = form.querySelector('[data-add-to-cart-status]');

        if (!button || !label) {
            return;
        }

        const originalLabel = label.textContent.trim();

        const setStatus = (message, tone) => {
            if (!status) {
                return;
            }

            status.textContent = message;
            status.dataset.tone = tone;
        };

        form.addEventListener('submit', async (event) => {
            if (!window.fetch) {
                return;
            }

            event.preventDefault();

            if (button.disabled) {
                return;
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            label.textContent = 'Adding…';
            spinner?.classList.remove('hidden');
            setStatus('Adding to cart…', 'pending');

            let added = false;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (response.ok) {
                    added = true;

                    updateCartCounts(Number(payload.count) || 0);
                    setStatus(payload.message || 'Added to cart.', 'success');
                } else {
                    const firstError = payload.errors
                        ? Object.values(payload.errors).flat()[0]
                        : null;

                    setStatus(
                        firstError || payload.message || 'We could not add this item to your cart. Please try again.',
                        'error',
                    );
                }
            } catch {
                setStatus('We could not reach the store. Check your connection and try again.', 'error');
            } finally {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                spinner?.classList.add('hidden');

                if (added) {
                    label.textContent = 'Added';

                    window.setTimeout(() => {
                        label.textContent = originalLabel;
                    }, 2500);
                } else {
                    label.textContent = originalLabel;
                }
            }
        });
    });
}

bindProductGallery();
bindQuantitySteppers();
bindAddToCartForms();
