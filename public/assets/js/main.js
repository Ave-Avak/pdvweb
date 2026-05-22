/* =====================================================================
   public/assets/js/main.js
   ---------------------------------------------------------------------
   JavaScript global du site.
   Volontairement minimal : le site est principalement piloté par PHP.
   ===================================================================== */

(function () {
    'use strict';

    // -----------------------------------------------------------------
    // Disparition automatique des messages flash après 5 secondes
    // -----------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        const messages = document.querySelectorAll('[role="alert"]');
        messages.forEach(function (msg) {
            setTimeout(function () {
                msg.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                msg.style.opacity = '0';
                msg.style.transform = 'translateY(-10px)';
                setTimeout(function () { msg.remove(); }, 500);
            }, 5000);
        });
    });

    // -----------------------------------------------------------------
    // Confirmation pour les actions destructives (data-confirm="message")
    // -----------------------------------------------------------------
    document.addEventListener('click', function (e) {
        const cible = e.target.closest('[data-confirm]');
        if (cible) {
            const message = cible.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        }
    });

})();
