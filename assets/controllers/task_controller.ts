import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'hiddenInput', 'display', 'suggestions', 'count', 'suggestion'];
    static values = {
        searchUrl: String,
        currentTaskId: Number
    }

    declare readonly inputTarget: HTMLInputElement;
    declare readonly hiddenInputTarget: HTMLInputElement;
    declare readonly displayTarget: HTMLElement;
    declare readonly suggestionsTarget: HTMLElement;
    declare readonly countTarget: HTMLElement;
    declare readonly hasCountTarget: boolean;
    declare readonly suggestionTargets: HTMLElement[];
    declare readonly searchUrlValue: string;
    declare readonly currentTaskIdValue: number;

    private selectedTasks: Map<number, string> = new Map();
    private timeout: number | null = null;
    private abortController: AbortController | null = null;

    connect() {
        // Initialize selected tasks from the DOM to preserve titles
        this.displayTarget.querySelectorAll('[data-task-id]').forEach(element => {
            const id = parseInt((element as HTMLElement).dataset.taskId || '0');
            const title = (element as HTMLElement).dataset.taskTitle || '';
            if (id > 0) {
                this.selectedTasks.set(id, title);
            }
        });

        // Also sync with hidden input just in case
        const hiddenIds = this.hiddenInputTarget.value.split(',')
            .filter(id => id.length > 0)
            .map(id => parseInt(id));

        hiddenIds.forEach(id => {
            if (!this.selectedTasks.has(id)) {
                this.selectedTasks.set(id, `Task #${id}`);
            }
        });

        // this.updateDisplay();
    }

    suggestionTargetConnected(element: HTMLElement) {
        const taskId = parseInt(element.dataset.taskId || '0');
        if (this.selectedTasks.has(taskId)) {
            element.classList.add('d-none');
        }
    }

    search(event: Event) {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        this.timeout = window.setTimeout(() => {
            const text = this.inputTarget.value;
            const lastHashIndex = text.lastIndexOf('#');

            if (lastHashIndex !== -1) {
                let searchQuery = text.substring(lastHashIndex + 1).trim();

                if (searchQuery.length >= 2) {
                    this.fetchSuggestions(searchQuery);
                } else if (searchQuery.length === 0) {
                    this.suggestionsTarget.innerHTML = '';
                }
            } else {
                this.suggestionsTarget.innerHTML = '';
            }
        }, 300);
    }

    async fetchSuggestions(query: string) {
        if (this.abortController) {
            this.abortController.abort();
        }
        this.abortController = new AbortController();

        this.suggestionsTarget.innerHTML = `
            <div class="text-center my-2">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        const url = `${this.searchUrlValue}?q=${encodeURIComponent(query)}&currentTaskId=${this.currentTaskIdValue}`;

        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'text/html, text/vnd.turbo-stream.html' },
                signal: this.abortController.signal
            });
            const html = await response.text();
            document.body.insertAdjacentHTML('beforeend', html);
        } catch (error) {
            if ((error as Error).name === 'AbortError') {
                console.log('Fetch aborted');
            } else {
                console.error('Error fetching suggestions:', error);
            }
        }
    }

    select(event: Event) {
        const element = event.currentTarget as HTMLElement;
        const taskId = parseInt(element.dataset.taskId || '0');
        const taskTitle = element.dataset.taskTitle || '';

        if (!this.selectedTasks.has(taskId)) {
            this.selectedTasks.set(taskId, taskTitle);
            this.updateDisplay();

            // Hide the selected suggestion
            this.toggleSuggestionVisibility(taskId, false);
        }
    }

    remove(event: Event) {
        const element = event.currentTarget as HTMLElement;
        const taskId = parseInt(element.dataset.taskId || '0');

        this.selectedTasks.delete(taskId);
        this.updateDisplay();

        // Show the suggestion again if it's in the list
        this.toggleSuggestionVisibility(taskId, true);
    }

    toggleSuggestionVisibility(taskId: number, visible: boolean) {
        const suggestion = this.suggestionTargets.find(el => parseInt(el.dataset.taskId || '0') === taskId);
        if (suggestion) {
            if (visible) {
                suggestion.classList.remove('d-none');
            } else {
                suggestion.classList.add('d-none');
            }
        }
    }

    updateDisplay() {
        const idsArray = Array.from(this.selectedTasks.keys());
        this.hiddenInputTarget.value = idsArray.join(',');

        if (this.hasCountTarget) {
            this.countTarget.textContent = `(${idsArray.length})`;
        }

        const header = `<p>Tâches sélectionnées : <span data-task-target="count">(${idsArray.length})</span></p>`;

        let badgesHtml = '';
        this.selectedTasks.forEach((title, id) => {
            badgesHtml += `
                <span class="badge bg-primary me-2 mb-1" data-task-id="${id}" data-task-title="${title}">
                    #${id} 
                    <a type="button" data-action="click->task#remove" data-task-id="${id}"><i class="bi bi-x-square"></i></a>
                </span>
            `;
        });

        this.displayTarget.innerHTML = header + badgesHtml;
    }
}
