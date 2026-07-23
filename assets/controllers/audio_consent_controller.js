import { Controller } from '@hotwired/stimulus';

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
 */
export default class extends Controller {
    static targets = ['audio', 'button', 'label'];

    connect() {
        this.onPlay = this.onPlay.bind(this);
        this.onPause = this.onPause.bind(this);
        this.audioTarget.addEventListener('play', this.onPlay);
        this.audioTarget.addEventListener('pause', this.onPause);
        this.syncState();
    }

    disconnect() {
        this.audioTarget.removeEventListener('play', this.onPlay);
        this.audioTarget.removeEventListener('pause', this.onPause);
    }

    toggle() {
        if (this.audioTarget.paused) {
            this.audioTarget.play().catch(() => {});
        } else {
            this.audioTarget.pause();
        }
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
