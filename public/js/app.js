/* ==========================================================================
   One-Time Link — application JavaScript (vanilla, no build step)
   Provides:
     window.otlToast(message)      Bootstrap toast helper
     [data-copy="#selector"]       copy an input's value to the clipboard
     [data-loading-text="..."]     swap submit buttons to a spinner on submit
   ========================================================================== */

(function () {
    'use strict';

    /* --- Toast helper ------------------------------------------------------ */

    var toastEl = document.getElementById('otl-toast');
    var toastBody = document.getElementById('otl-toast-body');
    var toastInstance = null;

    window.otlToast = function (message) {
        if (!toastEl || !toastBody || typeof bootstrap === 'undefined') {
            return;
        }
        toastBody.textContent = message;
        toastInstance = toastInstance || bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2500 });
        toastInstance.show();
    };

    /* --- Copy-to-clipboard buttons ------------------------------------------ */

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-copy]');
        if (!button) {
            return;
        }

        var target = document.querySelector(button.getAttribute('data-copy'));
        if (!target) {
            return;
        }

        var text = 'value' in target && target.value !== '' ? target.value : target.textContent.trim();

        var confirm = function () {
            var original = button.textContent;
            button.textContent = 'Copied!';
            window.otlToast('Copied to clipboard.');
            window.setTimeout(function () {
                button.textContent = original;
            }, 1600);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(confirm);
        } else {
            // Legacy fallback for non-secure contexts.
            if (target.select) {
                target.select();
                document.execCommand('copy');
                confirm();
            }
        }
    });

    /* --- Loading state on submit buttons -------------------------------------- */

    document.addEventListener('submit', function (event) {
        var button = event.target.querySelector('button[type="submit"][data-loading-text]');
        if (!button || button.disabled) {
            return;
        }

        var label = button.getAttribute('data-loading-text');
        button.disabled = true;
        button.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
            label.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        // Safety valve: if the navigation is blocked (e.g. validation handled
        // client-side by the browser after our handler), re-enable shortly.
        window.setTimeout(function () {
            if (document.body.contains(button)) {
                button.disabled = false;
            }
        }, 15000);
    });
})();
