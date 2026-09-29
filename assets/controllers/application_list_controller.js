import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['table'];

    connect() {
        this.sortDirection = {};
    }

    filter() {
        // El live component maneja el filtrado automáticamente.
        // Añadimos un indicador visual de carga.
        this.element.classList.add('opacity-50');

        // Quitar la opacidad después de un breve delay (el live component re-renderiza)
        setTimeout(() => {
            this.element.classList.remove('opacity-50');
        }, 150);
    }

    clear(event) {
        event.preventDefault();

        const inputs = this.element.querySelectorAll('[data-model]');
        inputs.forEach((input) => {
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });

        const selects = this.element.querySelectorAll('select[data-model]');
        selects.forEach((select) => {
            select.value = '';
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    sort(event) {
        const column = event.currentTarget.dataset.applicationListColumnParam;
        const table = this.element.querySelector('table tbody');

        if (!table) return;

        const rows = Array.from(table.querySelectorAll('tr'));

        // Toggle direction
        this.sortDirection[column] = this.sortDirection[column] === 'asc' ? 'desc' : 'asc';
        const direction = this.sortDirection[column];

        const columnIndex = this.#getColumnIndex(column);

        rows.sort((a, b) => {
            const aVal = a.children[columnIndex]?.textContent?.trim() ?? '';
            const bVal = b.children[columnIndex]?.textContent?.trim() ?? '';

            // Intentar parsear como número
            const aNum = parseFloat(aVal);
            const bNum = parseFloat(bVal);

            if (!isNaN(aNum) && !isNaN(bNum) && aVal !== '' && bVal !== '') {
                return direction === 'asc' ? aNum - bNum : bNum - aNum;
            }

            return direction === 'asc'
                ? aVal.localeCompare(bVal)
                : bVal.localeCompare(aVal);
        });

        rows.forEach((row) => table.appendChild(row));

        // Actualizar indicadores visuales
        this.#updateSortIndicators(event.currentTarget, direction);
    }

    #getColumnIndex(column) {
        const map = {
            fullName: 0,
            email: 1,
            position: 2,
            status: 3,
            score: 4,
            appliedAt: 5,
        };

        return map[column] ?? 0;
    }

    #updateSortIndicators(th, direction) {
        // Resetear todos los indicadores
        this.element.querySelectorAll('th span').forEach((span) => {
            span.textContent = '↕';
            span.classList.remove('text-blue-600', 'font-bold');
            span.classList.add('text-gray-400');
        });

        // Actualizar indicador activo
        const span = th.querySelector('span');
        if (span) {
            span.textContent = direction === 'asc' ? '↑' : '↓';
            span.classList.remove('text-gray-400');
            span.classList.add('text-blue-600', 'font-bold');
        }
    }
}
