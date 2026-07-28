import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'card', 'empty'];

    filter() {
        const query = this.inputTarget.value.trim().toLowerCase();
        let visibleCount = 0;

        this.cardTargets.forEach((card) => {
            const matches = card.dataset.name.includes(query);
            card.hidden = !matches;
            if (matches) {
                visibleCount += 1;
            }
        });

        this.emptyTarget.hidden = visibleCount > 0;
    }
}
