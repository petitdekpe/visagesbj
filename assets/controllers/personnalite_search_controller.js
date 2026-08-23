import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'card', 'category', 'count', 'reset', 'empty', 'emptyMessage'];

    connect() {
        this.activeCategory = '';

        const params = new URLSearchParams(window.location.search);
        const query = params.get('q');
        const category = params.get('categorie');

        if (query) {
            this.inputTarget.value = query;
        }
        if (category && this.categoryTargets.some((button) => button.dataset.category === category)) {
            this.activeCategory = category;
        }

        this.apply();
    }

    filter() {
        this.apply();
    }

    toggleCategory(event) {
        const { category } = event.currentTarget.dataset;
        this.activeCategory = this.activeCategory === category ? '' : category;
        this.apply();
    }

    reset() {
        this.inputTarget.value = '';
        this.activeCategory = '';
        this.apply();
        this.inputTarget.focus();
    }

    apply() {
        const query = this.inputTarget.value.trim().toLowerCase();
        const activeCategory = this.activeCategory;
        let visibleCount = 0;

        this.cardTargets.forEach((card) => {
            const matchesQuery = !query || card.dataset.name.includes(query);
            const matchesCategory = !activeCategory || card.dataset.category === activeCategory;
            const matches = matchesQuery && matchesCategory;
            card.hidden = !matches;
            if (matches) {
                visibleCount += 1;
            }
        });

        this.categoryTargets.forEach((button) => {
            const isActive = button.dataset.category === activeCategory;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });

        const total = this.cardTargets.length;

        if (this.hasCountTarget) {
            this.countTarget.textContent = visibleCount === total
                ? `${total} personnalités`
                : `${visibleCount} résultat${visibleCount > 1 ? 's' : ''} sur ${total}`;
        }

        const hasActiveFilter = query.length > 0 || activeCategory.length > 0;

        if (this.hasResetTarget) {
            this.resetTarget.hidden = !hasActiveFilter;
        }

        this.emptyTarget.hidden = visibleCount > 0;
        if (visibleCount === 0 && this.hasEmptyMessageTarget) {
            this.emptyMessageTarget.textContent = this.buildEmptyMessage(query, activeCategory);
        }

        this.syncUrl(query, activeCategory);
    }

    buildEmptyMessage(query, activeCategory) {
        const categoryButton = activeCategory
            ? this.categoryTargets.find((button) => button.dataset.category === activeCategory)
            : null;
        const categoryLabel = categoryButton ? categoryButton.textContent.trim() : '';

        if (query && categoryLabel) {
            return `Aucun résultat pour « ${this.inputTarget.value.trim()} » dans « ${categoryLabel} ».`;
        }
        if (query) {
            return `Aucun résultat pour « ${this.inputTarget.value.trim()} ».`;
        }
        if (categoryLabel) {
            return `Aucune personnalité classée dans « ${categoryLabel} » pour le moment.`;
        }

        return 'Aucun résultat.';
    }

    syncUrl(query, activeCategory) {
        const url = new URL(window.location.href);

        if (query) {
            url.searchParams.set('q', query);
        } else {
            url.searchParams.delete('q');
        }

        if (activeCategory) {
            url.searchParams.set('categorie', activeCategory);
        } else {
            url.searchParams.delete('categorie');
        }

        window.history.replaceState(window.history.state, '', url);
    }
}
