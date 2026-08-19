function initPopup(popup) {
    const id = popup.dataset.popupId;
    const showOnce = popup.dataset.showOnce === '1';
    const storageKey = 'tht-popup-dismissed-' + id;
    const delay = Math.max(0, Number(popup.dataset.delay || 0));

    if (showOnce) {
        try {
            if (window.localStorage.getItem(storageKey) === '1') {
                return;
            }
        } catch (error) {
            // Storage may be disabled; the popup can still be used for this visit.
        }
    }

    const timer = window.setTimeout(() => {
        popup.hidden = false;
        window.requestAnimationFrame(() => {
            popup.classList.add('is-visible');
        });
        document.body.classList.add('tht-popup-open');

        if (showOnce) {
            try {
                window.localStorage.setItem(storageKey, '1');
            } catch (error) {
                // Ignore storage errors and keep the current interaction usable.
            }
        }

        const closeButton = popup.querySelector('.tht-popup__close');
        closeButton?.focus({ preventScroll: true });
    }, delay);

    function close() {
        window.clearTimeout(timer);
        popup.classList.remove('is-visible');
        document.body.classList.remove('tht-popup-open');
        window.setTimeout(() => {
            popup.hidden = true;
        }, 240);
        document.removeEventListener('keydown', onKeydown);
    }

    function onKeydown(event) {
        if (event.key === 'Escape') {
            close();
        }
    }

    popup.querySelectorAll('[data-popup-dismiss]').forEach(element => {
        element.addEventListener('click', close);
    });
    document.addEventListener('keydown', onKeydown);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-tht-popup]').forEach(initPopup);
    }, { once: true });
} else {
    document.querySelectorAll('[data-tht-popup]').forEach(initPopup);
}
