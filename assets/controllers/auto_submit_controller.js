import { Controller } from '@hotwired/stimulus';

/*
 * Submits the enclosing form as soon as a field inside it changes.
 * Usage: data-controller="auto-submit" on the <form>, data-action="auto-submit#submit" on the field.
 */
export default class extends Controller {
    submit() {
        this.element.requestSubmit();
    }
}
