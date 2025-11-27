document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'cookieConsent';
    const banner = document.querySelector('[data-cookie-banner]');
    if (!banner) {
        return;
    }

    const acceptAllBtn = banner.querySelector('[data-cookie-accept="all"]');
    const acceptEssentialBtn = banner.querySelector('[data-cookie-accept="essential"]');
    const revokeBtn = banner.querySelector('[data-cookie-revoke]');
    const preferenceTriggers = document.querySelectorAll('[data-cookie-preferences]');

    function readConsent() {
        try {
            const value = window.localStorage.getItem(STORAGE_KEY);
            return value ? JSON.parse(value) : null;
        } catch (error) {
            return null;
        }
    }

    function persistConsent(choice) {
        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(choice));
        } catch (error) {
            // Failed to persist consent
        }
    }

    function hideBanner() {
        // Remove focus from any focused element inside the banner
        const focusedElement = banner.querySelector(':focus');
        if (focusedElement) {
            focusedElement.blur();
        }
        banner.classList.remove('is-visible');
        banner.setAttribute('inert', '');
        banner.setAttribute('aria-hidden', 'true');
    }

    function showBanner(force = false) {
        if (force || !readConsent()) {
            banner.classList.add('is-visible');
            banner.removeAttribute('inert');
            banner.removeAttribute('aria-hidden');
            banner.focus?.();
            
            // Show revoke button if consent already exists
            const consent = readConsent();
            if (consent && (consent.analytics || consent.marketing)) {
                revokeBtn?.style.setProperty('display', 'block');
            } else {
                revokeBtn?.style.setProperty('display', 'none');
            }
        }
    }

    function handleChoice(type) {
        const consent = {
            necessary: true,
            analytics: type === 'all',
            marketing: type === 'all', // Marketing only if all accepted
            updatedAt: new Date().toISOString(),
        };

        persistConsent(consent);
        hideBanner();
        document.dispatchEvent(new CustomEvent('cookie:consent-updated', { detail: consent }));
    }

    /**
     * Revoke consent (remove all non-essential cookies)
     */
    function revokeConsent() {
        try {
            window.localStorage.removeItem(STORAGE_KEY);
            const consent = {
                necessary: true,
                analytics: false,
                marketing: false,
                updatedAt: new Date().toISOString(),
            };
            document.dispatchEvent(new CustomEvent('cookie:consent-updated', { detail: consent }));
            showBanner(true);
        } catch (error) {
            // Failed to revoke consent
        }
    }

    // Export revoke function for use in other scripts
    window.revokeCookieConsent = revokeConsent;

    acceptAllBtn?.addEventListener('click', () => handleChoice('all'));
    acceptEssentialBtn?.addEventListener('click', () => handleChoice('essential'));
    
    revokeBtn?.addEventListener('click', () => {
        revokeConsent();
    });

    preferenceTriggers.forEach(trigger => {
        trigger.addEventListener('click', event => {
            event.preventDefault();
            showBanner(true);
        });
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            hideBanner();
        }
    });

    // Initialisation
    showBanner();
});

