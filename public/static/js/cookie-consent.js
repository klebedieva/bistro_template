document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'cookieConsent';
    const banner = document.querySelector('[data-cookie-banner]');
    if (!banner) {
        return;
    }

    const acceptAllBtn = banner.querySelector('[data-cookie-accept="all"]');
    const acceptEssentialBtn = banner.querySelector('[data-cookie-accept="essential"]');
    const preferenceTriggers = document.querySelectorAll('[data-cookie-preferences]');

    function readConsent() {
        try {
            const value = window.localStorage.getItem(STORAGE_KEY);
            return value ? JSON.parse(value) : null;
        } catch (error) {
            console.warn('Impossible de lire les préférences cookies', error);
            return null;
        }
    }

    function persistConsent(choice) {
        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(choice));
        } catch (error) {
            console.warn('Impossible d’enregistrer les préférences cookies', error);
        }
    }

    function hideBanner() {
        banner.classList.remove('is-visible');
        banner.setAttribute('aria-hidden', 'true');
    }

    function showBanner(force = false) {
        if (force || !readConsent()) {
            banner.classList.add('is-visible');
            banner.removeAttribute('aria-hidden');
            banner.focus?.();
        }
    }

    function handleChoice(type) {
        const consent = {
            necessary: true,
            analytics: type === 'all',
            marketing: false,
            updatedAt: new Date().toISOString(),
        };

        persistConsent(consent);
        hideBanner();
        document.dispatchEvent(new CustomEvent('cookie:consent-updated', { detail: consent }));
    }

    acceptAllBtn?.addEventListener('click', () => handleChoice('all'));
    acceptEssentialBtn?.addEventListener('click', () => handleChoice('essential'));

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

