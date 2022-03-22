import Sortable from 'sortablejs';
import { Controller } from 'stimulus';

export default class extends Controller {
    sortable: Sortable;

    connect() {
        const element: HTMLElement = this.element as HTMLElement;
        this.sortable = Sortable.create(element, {
            onSort: () => {
                let position = 1;
                this.element
                    .querySelectorAll('input[id$="_position"]')
                    .forEach((sub: HTMLInputElement) => {
                        sub.value = position.toString();
                        position++;
                });
            },
        });
    }

    disconnect() {
        this.sortable.destroy();
    }
}





