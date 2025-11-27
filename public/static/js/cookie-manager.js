// ============================================================================
// COOKIE MANAGER - GDPR Compliant Cookie Script Loading
// ============================================================================
// This module handles conditional loading of analytics and marketing scripts
// based on user consent, ensuring GDPR compliance.

(function() {
    'use strict';

    const STORAGE_KEY = 'cookieConsent';
    const SCRIPT_LOADED_FLAG = 'cookieScriptsLoaded';

    /**
     * Read user consent from localStorage
     * @returns {Object|null} Consent object or null
     */
    function readConsent() {
        try {
            const value = window.localStorage.getItem(STORAGE_KEY);
            return value ? JSON.parse(value) : null;
        } catch (error) {
            return null;
        }
    }

    /**
     * Check if analytics scripts are allowed
     * @returns {boolean}
     */
    function isAnalyticsAllowed() {
        const consent = readConsent();
        return consent && consent.analytics === true;
    }

    /**
     * Check if marketing scripts are allowed
     * @returns {boolean}
     */
    function isMarketingAllowed() {
        const consent = readConsent();
        return consent && consent.marketing === true;
    }

    /**
     * Dynamically load a script
     * @param {string} src - Script source URL
     * @param {Object} options - Options (async, defer, id, etc.)
     * @returns {Promise}
     */
    function loadScript(src, options = {}) {
        return new Promise((resolve, reject) => {
            // Check if script already exists
            const existingScript = options.id 
                ? document.getElementById(options.id) 
                : document.querySelector(`script[src="${src}"]`);
            
            if (existingScript) {
                resolve(existingScript);
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            
            if (options.async !== false) script.async = true;
            if (options.defer) script.defer = true;
            if (options.id) script.id = options.id;
            if (options.type) script.type = options.type;
            
            script.onload = () => resolve(script);
            script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
            
            document.head.appendChild(script);
        });
    }

    /**
     * Load analytics scripts if consent is given
     */
    function loadAnalyticsScripts() {
        if (!isAnalyticsAllowed()) {
            return;
        }

        // Mark as loaded to prevent duplicate loading
        if (window[SCRIPT_LOADED_FLAG]) {
            return;
        }
        window[SCRIPT_LOADED_FLAG] = true;

        // Check if scripts are configured via window.cookieConfig
        const config = window.cookieConfig || {};
        
        // Google Analytics
        if (config.googleAnalyticsId) {
            loadScript(`https://www.googletagmanager.com/gtag/js?id=${config.googleAnalyticsId}`, {
                id: 'ga-script',
                async: true
            }).then(() => {
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                window.gtag = gtag;
                gtag('js', new Date());
                gtag('config', config.googleAnalyticsId, {
                    'anonymize_ip': true, // GDPR compliance
                    'cookie_flags': 'SameSite=None;Secure'
                });
            }).catch(() => {
                // Failed to load Google Analytics
            });
        }

        // Matomo Analytics
        if (config.matomoUrl && config.matomoSiteId) {
            loadScript(`${config.matomoUrl}/matomo.js`, {
                id: 'matomo-script',
                async: true
            }).then(() => {
                window._paq = window._paq || [];
                window._paq.push(['trackPageView']);
                window._paq.push(['enableLinkTracking']);
                (function() {
                    window._paq.push(['setTrackerUrl', `${config.matomoUrl}/matomo.php`]);
                    window._paq.push(['setSiteId', config.matomoSiteId]);
                })();
            }).catch(() => {
                // Failed to load Matomo
            });
        }

        // Custom analytics scripts
        if (config.customAnalytics && Array.isArray(config.customAnalytics)) {
            config.customAnalytics.forEach((scriptConfig, index) => {
                loadScript(scriptConfig.src, {
                    id: scriptConfig.id || `custom-analytics-${index}`,
                    async: scriptConfig.async !== false,
                    defer: scriptConfig.defer || false
                }).then(() => {
                    if (scriptConfig.onLoad && typeof scriptConfig.onLoad === 'function') {
                        scriptConfig.onLoad();
                    }
                }).catch(() => {
                    // Failed to load custom analytics script
                });
            });
        }

    }

    /**
     * Load marketing scripts if consent is given
     */
    function loadMarketingScripts() {
        if (!isMarketingAllowed()) {
            return;
        }

        const config = window.cookieConfig || {};

        // Facebook Pixel
        if (config.facebookPixelId) {
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            window.fbq('init', config.facebookPixelId);
            window.fbq('track', 'PageView');
        }

        // Custom marketing scripts
        if (config.customMarketing && Array.isArray(config.customMarketing)) {
            config.customMarketing.forEach((scriptConfig, index) => {
                loadScript(scriptConfig.src, {
                    id: scriptConfig.id || `custom-marketing-${index}`,
                    async: scriptConfig.async !== false,
                    defer: scriptConfig.defer || false
                }).then(() => {
                    if (scriptConfig.onLoad && typeof scriptConfig.onLoad === 'function') {
                        scriptConfig.onLoad();
                    }
                }).catch(() => {
                    // Failed to load custom marketing script
                });
            });
        }

    }

    /**
     * Remove analytics scripts (when consent is withdrawn)
     */
    function removeAnalyticsScripts() {
        // Remove Google Analytics
        const gaScript = document.getElementById('ga-script');
        if (gaScript) {
            gaScript.remove();
        }

        // Clear dataLayer if it exists
        if (window.dataLayer) {
            window.dataLayer = [];
        }

        // Clear gtag if it exists
        if (window.gtag) {
            delete window.gtag;
        }

        // Remove Matomo
        const matomoScript = document.getElementById('matomo-script');
        if (matomoScript) {
            matomoScript.remove();
        }
        if (window._paq) {
            window._paq = [];
        }

        // Remove custom analytics scripts
        const customScripts = document.querySelectorAll('[id^="custom-analytics-"]');
        customScripts.forEach(script => {
            script.remove();
        });

        // Reset loaded flag
        window[SCRIPT_LOADED_FLAG] = false;
    }

    /**
     * Remove marketing scripts (when consent is withdrawn)
     */
    function removeMarketingScripts() {
        // Remove Facebook Pixel
        const fbScript = document.querySelector('script[src*="fbevents.js"]');
        if (fbScript) {
            fbScript.remove();
        }

        // Clear fbq if it exists
        if (window.fbq) {
            delete window.fbq;
        }

        // Remove custom marketing scripts
        const customScripts = document.querySelectorAll('[id^="custom-marketing-"]');
        customScripts.forEach(script => {
            script.remove();
        });
    }

    /**
     * Initialize scripts based on current consent
     */
    function initializeScripts() {
        const consent = readConsent();
        
        if (consent) {
            // User has made a choice, load scripts accordingly
            if (consent.analytics) {
                loadAnalyticsScripts();
            } else {
                removeAnalyticsScripts();
            }

            if (consent.marketing) {
                loadMarketingScripts();
            } else {
                removeMarketingScripts();
            }
        } else {
            // No consent yet, don't load any non-essential scripts
            removeAnalyticsScripts();
            removeMarketingScripts();
        }
    }

    /**
     * Handle consent update event
     */
    function handleConsentUpdate(event) {
        const consent = event.detail;
        
        // Reset loaded flag to allow reloading
        window[SCRIPT_LOADED_FLAG] = false;

        if (consent.analytics) {
            loadAnalyticsScripts();
        } else {
            removeAnalyticsScripts();
        }

        if (consent.marketing) {
            loadMarketingScripts();
        } else {
            removeMarketingScripts();
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeScripts);
    } else {
        initializeScripts();
    }

    // Listen for consent updates
    document.addEventListener('cookie:consent-updated', handleConsentUpdate);

    // Export public API
    window.CookieManager = {
        readConsent,
        isAnalyticsAllowed,
        isMarketingAllowed,
        loadAnalyticsScripts,
        loadMarketingScripts,
        removeAnalyticsScripts,
        removeMarketingScripts,
        initializeScripts
    };
})();

