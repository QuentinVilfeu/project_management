// Ensure jQuery is globally available for legacy plugins like DataTables
import $ from 'jquery';
window.$ = window.jQuery = $;
global.$ = global.jQuery = $;

import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

/**
 * Fonction pour gérer la fermeture automatique des alertes (méthode de gestionnaire).
 * Note: Cette fonction suppose que 'bootstrap' est disponible globalement.
 */
function closeAlert(alertElement) {
    const delay = 5000;

    // Si l'élément a déjà un timer, on s'arrête (voir 2. Améliorations)
    if (alertElement.dataset.timerInitialized) {
        return;
    }

    alertElement.dataset.timerInitialized = 'true';

    setTimeout(function () {
        // Utilisation de l'API Bootstrap pour une fermeture propre
        // Note : Si vous n'utilisez pas TypeScript/ESM, utilisez window.bootstrap
        const alertInstance = bootstrap.Alert.getInstance(alertElement) || new bootstrap.Alert(alertElement);
        alertInstance.close();

    }, delay);
}

/**
 * Démarre le MutationObserver pour détecter l'ajout de nouvelles alertes.
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Définir le conteneur à observer (souvent l'endroit où les messages flash sont injectés)
    const targetNode = document.body;

    // 2. Options de l'observateur
    const config = {
        childList: true, // Observer l'ajout/suppression d'enfants directs
        subtree: true    // Observer les changements dans toute l'arborescence
    };

    // 3. Callback qui sera exécuté lors d'une mutation
    const callback = (mutationsList, observer) => {
        for (const mutation of mutationsList) {
            // Nous nous intéressons uniquement aux nœuds qui ont été ajoutés
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {

                mutation.addedNodes.forEach(node => {
                    // Vérifier si le nœud ajouté est un élément HTML
                    if (node instanceof HTMLElement) {

                        // Si le nœud ajouté correspond à notre alerte
                        if (node.classList.contains('alert-autoclose')) {
                            closeAlert(node);
                        }

                        // S'assurer de vérifier également les enfants du nœud ajouté
                        node.querySelectorAll('.alert-autoclose').forEach(alertElement => {
                            closeAlert(alertElement);
                        });
                    }
                });
            }
        }
    };

    // 4. Créer et démarrer l'observateur
    const observer = new MutationObserver(callback);
    observer.observe(targetNode, config);

    // 5. Exécuter une première fois la fonction pour les alertes déjà présentes
    document.querySelectorAll('.alert-autoclose').forEach(closeAlert);
});    
