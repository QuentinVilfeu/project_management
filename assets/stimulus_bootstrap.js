import { startStimulusApp } from '@symfony/stimulus-bridge';
import { StreamActions } from "@hotwired/turbo";
import speRouting from "./js/utils/routing.js";

export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers/',
    true,
    /\.[jt]sx?$/
));

StreamActions.visit = function () {
    const location = this.getAttribute("target");
    if (location) {
        const url = speRouting.generate(location);
        window.location.href = url;
    }
};

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
