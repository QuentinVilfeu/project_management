import Routing from "fos-router";
import routes from "/public/js/fos_js_routes.json";

Routing.setRoutingData(routes);

const speRouting = {
    generate: (name, params = {}, absolute = false) => {
        const p = Object.assign({}, params);

        // If no locale provided, try to infer it from the current URL or <html lang="">
        if (!('_locale' in p)) {
            let locale = null;
            try {
                const pathname = window.location && window.location.pathname ? window.location.pathname : '';
                // Match first segment like /en/ or /fr-FR/
                const match = pathname.match(/^\/([a-z]{2}(?:-[A-Za-z]{2})?)(?:\/|$)/);
                if (match && match[1]) {
                    locale = match[1];
                } else if (document.documentElement && document.documentElement.lang) {
                    locale = document.documentElement.lang;
                }
            } catch (e) {
                // ignore
            }

            if (locale) {
                // Normalize underscore to dash
                p._locale = locale.replace('_', '-');
            }
        }

        return Routing.generate(name, p, absolute);
    },
};

export default speRouting;