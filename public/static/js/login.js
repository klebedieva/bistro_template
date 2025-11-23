/**
 * Login page functionality
 * Handles password visibility toggle
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.querySelector('[data-toggle-password]');
        const passwordInput = document.getElementById('inputPassword');

        if (!toggleBtn || !passwordInput) {
            return;
        }

        const icon = toggleBtn.querySelector('i');

        toggleBtn.addEventListener('click', function () {
            const isVisible = passwordInput.getAttribute('type') === 'text';

            // Toggle input type
            passwordInput.setAttribute('type', isVisible ? 'password' : 'text');

            // Toggle icon
            if (icon) {
                icon.classList.toggle('bi-eye', isVisible);
                icon.classList.toggle('bi-eye-slash', !isVisible);
            }

            // Update aria attributes
            const labelText = isVisible ? 'Afficher le mot de passe' : 'Masquer le mot de passe';
            toggleBtn.setAttribute('aria-pressed', String(!isVisible));
            toggleBtn.setAttribute('aria-label', labelText);
            toggleBtn.setAttribute('title', labelText);
        });
    });
})();
