import { Controller } from "@hotwired/stimulus";
import { renderStreamMessage } from "@hotwired/turbo";
import speRouting from "../js/utils/routing";

export default class extends Controller<HTMLElement> {
    static readonly targets = ["taskAction"];

    declare readonly taskActionTargets: HTMLElement[];

    updatePriority(event: Event) {
        event.preventDefault();
        const item = event.currentTarget as HTMLElement;
        const url = speRouting.generate('app_task_action_edit_priority', {
            id: item.dataset.actionTaskId,
            priorityId: item.dataset.actionPriorityId
        });

        fetch(url, {
            method: 'POST',
            headers: {
                Accept: "text/vnd.turbo-stream.html",
            },
        })
        .then((response) => response.text())
        .then((html) => {
            renderStreamMessage(html);
        })
        .catch((error) => {
            console.error("Error fetching content:", error);
        });
    }

    updateState(event: Event) {
        event.preventDefault();
        const item = event.currentTarget as HTMLElement;
        const url = speRouting.generate('app_task_action_edit_state', {
            id: item.dataset.actionTaskId,
            stateId: item.dataset.actionStateId
        });

        fetch(url, {
            method: 'POST',
            headers: {
                Accept: "text/vnd.turbo-stream.html",
            },
        })
        .then((response) => response.text())
        .then((html) => {
            renderStreamMessage(html);
        })
        .catch((error) => {
            console.error("Error fetching content:", error);
        });
    }

    updateAssignee(event: Event) {
        event.preventDefault();
        const item = event.currentTarget as HTMLElement;
        const url = speRouting.generate('app_task_action_edit_assignee', {
            id: item.dataset.actionTaskId,
            assigneeId: item.dataset.actionAssigneeId
        });

        fetch(url, {
            method: 'POST',
            headers: {
                Accept: "text/vnd.turbo-stream.html",
            },
        })
        .then((response) => response.text())
        .then((html) => {
            renderStreamMessage(html);
        })
        .catch((error) => {
            console.error("Error fetching content:", error);
        });
    }

    updateEndDate(event: Event) {
        event.preventDefault();
        const item = event.currentTarget as HTMLInputElement;
        const value = item.value + " 23:59:59";
        const url = speRouting.generate('app_task_action_edit_enddate', {
            id: item.dataset.actionTaskId,
            dateEnd: value
        });

        fetch(url, {
            method: 'POST',
            headers: {
                Accept: "text/vnd.turbo-stream.html",
            },
        })
        .then((response) => response.text())
        .then((html) => {
            renderStreamMessage(html);
        })
        .catch((error) => {
            console.error("Error fetching content:", error);
        });
    }
}