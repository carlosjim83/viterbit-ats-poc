import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);

        // Efecto visual: opacidad reducida mientras re-renderiza
        this.component.on('render:started', () => {
            this.element.classList.add('opacity-50');
        });

        this.component.on('render:finished', () => {
            this.element.classList.remove('opacity-50');
        });
    }
}
