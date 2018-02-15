/**
 * A re-modal wrapper to simplify usage.
 */

function openModalDialog(remodalId) {
    const dlgElement = $(`[data-remodal-id=${remodalId}]`);
    const modal = $.remodal.lookup[dlgElement.data('remodal')];
    modal.open();

    return {
        show: function () {
            modal.open();
        },
        close: function () {
            modal.close();
        },
        /**
         * Replaces a message listener
         * @param selector
         * @param message
         * @param callback
         */
        on: function (selector, message, callback) {
            const found = dlgElement.find(selector);
            found.off(message);
            found.on(message, callback);
        },
        /**
         * 
         * @param selector {string}
         * @returns {element} The HTML element
         */
        get: function (selector) {
            const found = dlgElement.find(selector);
            return found[0];
        },
        /**
         * 
         * @param selector {string}
         * @returns {jquery element}
         */
        jget: function (selector) {
            const found = dlgElement.find(selector);
            return found;
        }
    }
    // For getting/setting values
    // var $modal = modal.$modal;
}