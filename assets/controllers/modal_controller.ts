import { Controller } from "@hotwired/stimulus";
import { renderStreamMessage } from "@hotwired/turbo";
import speRouting from "../js/utils/routing";

export default class extends Controller<HTMLElement> {
    static targets = ["modal", "openModalButton"];

    declare readonly openModalButtonTargets: HTMLButtonElement[];

    connect() {
        this.openModalButtonTargets.forEach((button) => {
            button.addEventListener("click", () => {
                this.startAction(button);
            });
        });
    }

    startAction(button: HTMLButtonElement) {
        const route = button.getAttribute("data-route");
        const parameters = button.getAttribute("data-parameters");

        if (route) {
            this.fetchController(route, parameters ? JSON.parse(parameters) : {});
        }
    }

    clickActionTable(event: Event) {
        this.startAction(event.currentTarget as HTMLButtonElement);
    }

    fetchController(route: string, parameters: Record<string, any>) {
        const url = speRouting.generate(route, parameters);
        console.log(url);
        
        fetch(url, {
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
}