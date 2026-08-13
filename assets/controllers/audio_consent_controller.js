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
 */
const VOLUME = 0.5;

export default class extends Controller {
    static targets = ['audio', 'button', 'label'];

    connect() {
        this.audioTarget.volume = VOLUME;

        this.onPlay = this.onPlay.bind(this);
        this.onPause = this.onPause.bind(this);
        this.audioTarget.addEventListener('play', this.onPlay);
        this.audioTarget.addEventListener('pause', this.onPause);
        this.syncState();

        this.armAutoplayOnHome();
    }

    disconnect() {
        this.audioTarget.removeEventListener('play', this.onPlay);
        this.audioTarget.removeEventListener('pause', this.onPause);
        this.disarmAutoplay();
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
        this.labelTarget.textContent = 'Couper le son';
    }

    onPause() {
        this.buttonTarget.setAttribute('aria-pressed', 'false');
        this.buttonTarget.classList.remove('is-playing');
        this.labelTarget.textContent = "Écouter l'hymne";
    }
}
