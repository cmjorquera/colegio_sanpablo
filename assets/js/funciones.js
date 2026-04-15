/**
 * Funciones reutilizables del proyecto.
 * Centraliza helpers que luego puedan usarse en distintas paginas o modulos.
 */
window.SPabloUtils = window.SPabloUtils || {
    toggleClass: function (selector, className) {
        const element = document.querySelector(selector);

        if (element) {
            element.classList.toggle(className);
        }
    }
};
