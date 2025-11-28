import { Controller } from "@hotwired/stimulus";
import { renderStreamMessage } from "@hotwired/turbo";
import speRouting from "../js/utils/routing";

export default class ModalController extends Controller<HTMLElement> {
    static readonly targets = ["modal", "openModalButton"];

    declare readonly openModalButtonTargets: HTMLButtonElement[];

    async startAction(button: HTMLButtonElement) {
        const route = button.dataset.route;
        const parameters = button.dataset.parameters

        if (route) {
            await this.fetchController(route, parameters ? JSON.parse(parameters) : {});
        }
    }

    clickActionTable(event: Event) {
        this.startAction(event.currentTarget as HTMLButtonElement);
    }

    async fetchController(route: string, parameters: Record<string, any>) {
        const url = speRouting.generate(route, parameters);

        await fetch(url, {
            headers: {
                Accept: "text/vnd.turbo-stream.html",
            },
        })
        .then((response) => response.text())
        .then((html) => {
            renderStreamMessage(html);
        })
        .catch((error) => {
            console.error("Error fetching modal content:", error);
        });
    }

    /**
     * Used to close the modal after form submission
     * 
     * A mettre sur le bouton de fermeture de modal.
     * Exemple : 
     * <div class="modal-footer" {{ stimulus_controller('modal') }}>
     *   <button type="submit"
     *       class="btn btn-primary"
     *       {{ stimulus_action('modal', 'closeModal') }}
     *       data-action-form-id="commentForm"
     *       data-action-modal-id="commentTaskModal">{% trans %}Publish{% endtrans %}</button>
     * </div>
     * */ 
    closeModal(event: Event) {
        const target = event.target as HTMLButtonElement;
        let form = document.getElementById(target.dataset.actionFormId) as HTMLFormElement;
        form.addEventListener('turbo:submit-end', () => {
            form.innerHTML = '';
            $('#' + target.dataset.actionModalId).modal('hide');
        });
    }

    /**
     * Used to form after submission
     * 
     * A mettre sur le bouton de fermeture de modal.
     * Exemple : 
     * <div class="modal-footer" {{ stimulus_controller('modal') }}>
     *   <button type="submit"
     *       class="btn btn-primary"
     *       {{ stimulus_action('modal', 'resetForm') }}
     *       data-action-form-id="commentForm">{% trans %}Publish{% endtrans %}</button>
     * </div>
     * */ 
    resetForm(event: Event) {
        const target = event.target as HTMLButtonElement;
        let form = document.getElementById(target.dataset.actionFormId) as HTMLFormElement;
        form.addEventListener('turbo:submit-end', () => {
            form.reset();
        });
    }
}