/*
 * Site-wide helper for tracking user actions (not just page views) in Google
 * Analytics. No-ops silently whenever GA isn't loaded — either because
 * tracking is disabled in the admin, or because the visitor hasn't accepted
 * the cookie-consent banner yet (see cookie_consent_controller.js) — so
 * callers never need to guard the call themselves.
 *
 * Two ways to record an action:
 *   1. Call trackEvent(name, params) from a Stimulus controller.
 *   2. Add data-ga-event="name" (plus optional data-ga-* params) to any
 *      element in a template; clicks on it are tracked automatically.
 */
export function trackEvent(name, params = {}) {
    if (typeof window.gtag !== 'function') {
        return;
    }
    window.gtag('event', name, params);
}

document.addEventListener('click', (event) => {
    const target = event.target.closest('[data-ga-event]');
    if (!target) {
        return;
    }

    const { gaEvent, ...rest } = target.dataset;
    const params = {};
    Object.entries(rest).forEach(([key, value]) => {
        if (key.startsWith('ga') && key !== 'gaEvent') {
            const paramName = key.slice(2, 3).toLowerCase() + key.slice(3);
            params[paramName] = value;
        }
    });

    trackEvent(gaEvent, params);
});
