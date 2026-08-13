import { Controller } from '@hotwired/stimulus';

/*
 * Gates Google Analytics behind an explicit accept/decline choice — gtag.js
 * is never requested from the browser, and no dataLayer/cookie exists, until
 * the visitor clicks "Accepter". The choice is remembered in localStorage so
 * it survives across visits without needing a cookie of its own.
 *
 * The banner is data-turbo-permanent so it (and any pending consent) isn't
 * lost across Turbo navigations. Turbo still detaches/reattaches permanent
 * elements on each visit, which re-runs connect() every time even though the
 * DOM node and any consent already granted are unchanged — so connect() must
 * stay idempotent (checking window.gaLoaded) rather than assuming it only
 * runs once, and it doubles as the hook for sending a page_view on each
 * Turbo-driven navigation since there is no full page reload to trigger one.
 */
const STORAGE_KEY = 'htd_ga_consent';

export default class extends Controller {
    static values = { measurementId: String };

    connect() {
        const consent = window.localStorage.getItem(STORAGE_KEY);

        if (consent === 'granted') {
            this.loadAnalytics();
        } else if (consent !== 'denied') {
            this.element.classList.add('is-visible');
        }
    }

    accept() {
        window.localStorage.setItem(STORAGE_KEY, 'granted');
        this.element.classList.remove('is-visible');
        this.loadAnalytics();
    }

    decline() {
        window.localStorage.setItem(STORAGE_KEY, 'denied');
        this.element.classList.remove('is-visible');
    }

    loadAnalytics() {
        if (window.gaLoaded) {
            this.trackPageview();
            return;
        }
        window.gaLoaded = true;

        window.dataLayer = window.dataLayer || [];
        window.gtag = function gtag() {
            window.dataLayer.push(arguments);
        };
        window.gtag('js', new Date());
        window.gtag('config', this.measurementIdValue);

        const script = document.createElement('script');
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${this.measurementIdValue}`;
        document.head.appendChild(script);
    }

    trackPageview() {
        window.gtag('event', 'page_view', {
            page_path: window.location.pathname + window.location.search,
            page_title: document.title,
        });
    }
}
