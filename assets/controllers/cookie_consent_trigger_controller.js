import { Controller } from '@hotwired/stimulus';

/*
 * Lets a link/button anywhere on the page (e.g. the footer) reopen the
 * cookie consent banner, which lives in its own data-turbo-permanent
 * subtree elsewhere in the DOM. Communicates via a window event rather
 * than a direct Stimulus target since the two elements share no
 * controller-scoped ancestor.
 */
export default class extends Controller {
    reopen() {
        window.dispatchEvent(new CustomEvent('cookie-consent:reopen'));
    }
}
