import { Controller } from '@hotwired/stimulus';

/*
 * Drag-and-drop row reordering for the admin personnalités table.
 * Usage: data-controller="admin-reorder" on the table wrapper, data-admin-reorder-target="row"
 * (+ draggable="true") on each <tr>, and a sibling <form data-admin-reorder-target="form">
 * holding the CSRF token — submitted with the new order once a drag ends.
 */
export default class extends Controller {
    static targets = ['row', 'form'];

    dragStart(event) {
        this.dragging = event.currentTarget;
        event.dataTransfer.effectAllowed = 'move';
        this.dragging.classList.add('is-dragging');
    }

    dragOver(event) {
        event.preventDefault();
        const target = event.currentTarget;
        if (!this.dragging || target === this.dragging) {
            return;
        }
        const rect = target.getBoundingClientRect();
        const before = (event.clientY - rect.top) < rect.height / 2;
        target.parentNode.insertBefore(this.dragging, before ? target : target.nextSibling);
    }

    dragEnd(event) {
        event.currentTarget.classList.remove('is-dragging');
        this.dragging = null;
        this.submitOrder();
    }

    submitOrder() {
        const form = this.formTarget;
        form.querySelectorAll('input[name="order[]"]').forEach((input) => input.remove());

        this.rowTargets.forEach((row) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order[]';
            input.value = row.dataset.id;
            form.appendChild(input);
        });

        form.requestSubmit();
    }
}
