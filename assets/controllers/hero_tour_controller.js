import { Controller } from '@hotwired/stimulus';
import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

/*
 * One-time hint pointing at a face in the homepage hero grid, nudging visitors
 * to click through to a personality's page. Not a blocking modal: the
 * highlighted tile stays clickable and the tip can be dismissed without
 * interacting with it. Shown once per browser via localStorage.
 */
const STORAGE_KEY = 'htd_hero_tour_seen';

export default class extends Controller {
    connect() {
        if (window.localStorage.getItem(STORAGE_KEY)) {
            return;
        }

        const tile = this.element.querySelector('.hero__tile');
        if (!tile) {
            return;
        }

        window.setTimeout(() => this.showHint(tile), 900);
    }

    showHint(tile) {
        window.localStorage.setItem(STORAGE_KEY, '1');

        driver({
            allowClose: true,
            overlayOpacity: 0.45,
            stagePadding: 6,
            stageRadius: 12,
        }).highlight({
            element: tile,
            popover: {
                title: 'Découvrez les 66 visages',
                description: 'Cliquez sur une personnalité pour découvrir son portrait.',
                side: 'bottom',
                align: 'start',
                showButtons: ['close'],
            },
        });
    }
}
