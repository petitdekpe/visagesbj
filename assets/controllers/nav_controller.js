import { Controller } from '@hotwired/stimulus';

/*
 * Toggles the mobile navigation menu.
 * Usage: data-controller="nav" on the header, data-nav-toggle on the button.
 */
export default class extends Controller {
    connect() {
        this.toggle = this.element.querySelector('[data-nav-toggle]');
        this.nav = this.element.querySelector('.site-nav');
        this.toggle.addEventListener('click', () => this.onToggle());
    }

    onToggle() {
        const isOpen = this.nav.classList.toggle('is-open');
        this.toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }
}
