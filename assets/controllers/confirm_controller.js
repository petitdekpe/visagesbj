import { Controller } from '@hotwired/stimulus';

/*
 * Shows a native confirm() dialog before letting a form submit through.
 * Usage: data-controller="confirm" data-action="submit->confirm#check" data-confirm-message-value="Sure?"
 */
export default class extends Controller {
    static values = { message: String };

    check(event) {
        if (!window.confirm(this.messageValue || 'Confirmer ?')) {
            event.preventDefault();
        }
    }
}
