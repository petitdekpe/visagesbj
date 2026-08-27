import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'row', 'count', 'empty', 'pagination', 'pageInfo', 'prevButton', 'nextButton'];
    static values = { perPage: { type: Number, default: 10 } };

    connect() {
        this.page = 1;
        this.filter();
    }

    filter() {
        this.page = 1;
        this.render();
    }

    prevPage() {
        this.page -= 1;
        this.render();
    }

    nextPage() {
        this.page += 1;
        this.render();
    }

    render() {
        const query = this.inputTarget.value.trim().toLowerCase();
        const matches = this.rowTargets.filter((row) => !query || row.dataset.search.includes(query));
        const total = this.rowTargets.length;
        const pageCount = Math.max(1, Math.ceil(matches.length / this.perPageValue));

        this.page = Math.min(Math.max(this.page, 1), pageCount);
        const start = (this.page - 1) * this.perPageValue;
        const end = start + this.perPageValue;
        const visibleOnPage = new Set(matches.slice(start, end));

        this.rowTargets.forEach((row) => {
            row.hidden = !visibleOnPage.has(row);
        });

        if (this.hasCountTarget) {
            this.countTarget.textContent = matches.length === total
                ? `${total} personnalités`
                : `${matches.length} / ${total} personnalités`;
        }

        if (this.hasEmptyTarget) {
            this.emptyTarget.hidden = matches.length > 0;
        }

        if (this.hasPaginationTarget) {
            this.paginationTarget.hidden = pageCount <= 1;
        }

        if (this.hasPageInfoTarget) {
            this.pageInfoTarget.textContent = `Page ${this.page} / ${pageCount}`;
        }

        if (this.hasPrevButtonTarget) {
            this.prevButtonTarget.disabled = this.page <= 1;
        }

        if (this.hasNextButtonTarget) {
            this.nextButtonTarget.disabled = this.page >= pageCount;
        }
    }
}
