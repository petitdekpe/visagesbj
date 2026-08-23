import { Controller } from '@hotwired/stimulus';
import { trackEvent } from '../analytics.js';

/*
 * A play button that only starts audio once the user explicitly clicks it —
 * browsers block autoplay with sound anyway, so this doubles as both the
 * consent gate and the technical requirement for playback to be allowed.
 *
 * The player is data-turbo-permanent so it keeps playing across page visits;
 * Turbo detaches/reattaches permanent elements on each visit, which re-runs
 * connect()/disconnect() even though the DOM node is reused — so listeners
 * added here must be torn down in disconnect(), or repeated visits stack up
 * duplicate handlers and the button state drifts out of sync with reality.
 *
 * True silent autoplay on page load isn't possible (browsers require a user
 * gesture before allowing sound), so landing on the homepage arms a one-shot
 * listener that starts playback on the visitor's very first interaction
 * anywhere on the page — not just a click on this button specifically — so
 * it feels immediate rather than requiring them to find and press play.
 *
 * The card promoting the anthem (cover photo + dismiss button) is only
 * useful once, at arrival — kept up while scrolling, it just sits on top of
 * whatever's underneath. So it collapses to the bare icon+label pill (same
 * toggle button, cover photo dropped) as soon as the visitor scrolls or
 * dismisses it, and it never expands in the first place on a page like the
 * quiz where the full card would sit over the answer options.
 *
 * The whole floating widget also hides once the footer scrolls into view —
 * being fixed-position, it would otherwise sit on top of the footer's links
 * for as long as the visitor stays scrolled to the bottom of the page. The
 * footer itself isn't a turbo-permanent element (unlike this player), so its
 * reference is re-looked-up on every connect().
 */
const VOLUME = 0.5;
const COLLAPSE_SCROLL_THRESHOLD = 120;

export default class extends Controller {
    static targets = ['audio', 'button', 'label'];

    connect() {
        this.audioTarget.volume = VOLUME;

        this.onPlay = this.onPlay.bind(this);
        this.onPause = this.onPause.bind(this);
        this.onMediaPlaying = this.onMediaPlaying.bind(this);
        this.onScroll = this.onScroll.bind(this);
        this.onFooterIntersect = this.onFooterIntersect.bind(this);
        this.audioTarget.addEventListener('play', this.onPlay);
        this.audioTarget.addEventListener('pause', this.onPause);
        document.addEventListener('media:playing', this.onMediaPlaying);
        this.syncState();

        if (document.querySelector('.quiz-stage')) {
            this.collapse();
        } else if (!this.element.classList.contains('is-collapsed')) {
            window.addEventListener('scroll', this.onScroll, { passive: true });
        }

        const footer = document.querySelector('.site-footer');
        if (footer) {
            this.footerObserver = new IntersectionObserver(this.onFooterIntersect);
            this.footerObserver.observe(footer);
        }

        this.armAutoplayOnHome();
    }

    disconnect() {
        this.audioTarget.removeEventListener('play', this.onPlay);
        this.audioTarget.removeEventListener('pause', this.onPause);
        document.removeEventListener('media:playing', this.onMediaPlaying);
        window.removeEventListener('scroll', this.onScroll);
        if (this.footerObserver) {
            this.footerObserver.disconnect();
        }
        this.disarmAutoplay();
    }

    onFooterIntersect([entry]) {
        this.element.classList.toggle('is-hidden', entry.isIntersecting);
    }

    onScroll() {
        if (window.scrollY > COLLAPSE_SCROLL_THRESHOLD) {
            this.collapse();
        }
    }

    collapse() {
        this.element.classList.add('is-collapsed');
        window.removeEventListener('scroll', this.onScroll);
    }

    toggle() {
        if (this.audioTarget.paused) {
            this.audioTarget.play().catch(() => {});
            trackEvent('anthem_play');
        } else {
            this.audioTarget.pause();
        }
    }

    armAutoplayOnHome() {
        if (window.location.pathname !== '/' || !this.audioTarget.paused) {
            return;
        }

        this.autoplayHandler = (event) => {
            // The button already starts/stops playback itself via toggle() —
            // only step in when the first interaction was something else, so
            // we don't immediately re-toggle (and stop) the audio it just started.
            if (!this.buttonTarget.contains(event.target) && this.audioTarget.paused) {
                this.audioTarget.play().catch(() => {});
                trackEvent('anthem_play');
            }
            this.disarmAutoplay();
        };

        ['pointerdown', 'keydown'].forEach((type) => {
            document.addEventListener(type, this.autoplayHandler, { once: true });
        });
    }

    disarmAutoplay() {
        if (!this.autoplayHandler) {
            return;
        }
        ['pointerdown', 'keydown'].forEach((type) => {
            document.removeEventListener(type, this.autoplayHandler);
        });
        this.autoplayHandler = null;
    }

    syncState() {
        if (this.audioTarget.paused) {
            this.onPause();
        } else {
            this.onPlay();
        }
    }

    onPlay() {
        this.buttonTarget.setAttribute('aria-pressed', 'true');
        this.buttonTarget.classList.add('is-playing');
        this.buttonTarget.setAttribute('aria-label', "Couper le son de l'hymne de la campagne Hisse ton Drapeau");
        this.labelTarget.textContent = 'Couper le son';
        document.dispatchEvent(new CustomEvent('media:playing', { detail: { source: 'audio' } }));
    }

    onMediaPlaying(event) {
        if (event.detail.source !== 'audio' && !this.audioTarget.paused) {
            this.audioTarget.pause();
        }
    }

    onPause() {
        this.buttonTarget.setAttribute('aria-pressed', 'false');
        this.buttonTarget.classList.remove('is-playing');
        this.buttonTarget.setAttribute('aria-label', "Écouter l'hymne officiel de la campagne Hisse ton Drapeau");
        this.labelTarget.textContent = "Écouter l'hymne";
    }
}
