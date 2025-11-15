import { Controller } from "@hotwired/stimulus";
import { renderStreamMessage } from "@hotwired/turbo";
import speRouting from "../js/utils/routing";

export default class extends Controller<HTMLElement> {
    static targets = ["modal", "openModalButton"];

    declare readonly openModalButtonTargets: HTMLButtonElement[];

    connect() {
        this.openModalButtonTargets.forEach((button) => {
            button.addEventListener("click", () => {
                const route = button.getAttribute("data-route");
                if (route) {
                    this.fetchController(route);
                }
            });
        });
    }

    fetchController(route: string) {
        const url = speRouting.generate(route);
        
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