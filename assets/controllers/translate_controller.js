import { Controller } from '@hotwired/stimulus';
import { trackEvent } from '../analytics.js';

/*
 * FR/fongbe toggle on the personality detail page. Swaps every fr/fon target
 * pair (bio, achievements, sources) in one click rather than reloading or
 * fetching a translation — the fongbe markup is already server-rendered and
 * simply hidden until toggled.
 */
export default class extends Controller {
    static targets = ['fr', 'fon', 'label'];

    connect() {
        this.showingFongbe = false;
    }

    toggle() {
        this.showingFongbe = !this.showingFongbe;

        this.frTargets.forEach((el) => { el.hidden = this.showingFongbe; });
        this.fonTargets.forEach((el) => { el.hidden = !this.showingFongbe; });

        if (this.hasLabelTarget) {
            this.labelTarget.textContent = this.showingFongbe ? 'Lire en français' : 'Lire en fongbe';
        }

        trackEvent('personnalite_translate_toggle', { lang: this.showingFongbe ? 'fon' : 'fr' });
    }
}
