import { Controller } from '@hotwired/stimulus';
import { trackEvent } from '../analytics.js';

/*
 * Copy-link button on the personality detail page's share row. Falls back to
 * a hidden-textarea + execCommand copy when the Clipboard API is unavailable
 * (older mobile browsers, or a non-secure context) rather than just failing
 * silently.
 */
export default class extends Controller {
    static targets = ['copyButton', 'copyLabel'];

    connect() {
        this.originalLabel = this.hasCopyLabelTarget ? this.copyLabelTarget.textContent : '';
    }

    disconnect() {
        clearTimeout(this.resetTimeout);
    }

    copyLink(event) {
        const url = event.currentTarget.dataset.clipboardText;

        this.copyToClipboard(url).then(() => {
            this.showCopied();
            trackEvent('share_link_copy', { link_url: url });
        });
    }

    copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).catch(() => this.legacyCopy(text));
        }

        return Promise.resolve(this.legacyCopy(text));
    }

    legacyCopy(text) {
        const input = document.createElement('textarea');
        input.value = text;
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.focus();
        input.select();
        try {
            document.execCommand('copy');
        } catch {
            // Nothing more we can do — the URL is still visible in the address bar.
        }
        document.body.removeChild(input);
    }

    showCopied() {
        clearTimeout(this.resetTimeout);
        this.copyButtonTarget.classList.add('is-copied');
        this.copyLabelTarget.textContent = 'Lien copié !';
        this.resetTimeout = setTimeout(() => {
            this.copyButtonTarget.classList.remove('is-copied');
            this.copyLabelTarget.textContent = this.originalLabel;
        }, 2000);
    }
}
