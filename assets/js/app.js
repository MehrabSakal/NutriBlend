// FreshSip JMS - small front-end helpers (vanilla JS, no jQuery required)
document.addEventListener('DOMContentLoaded', function () {

    // Auto-submit quantity changes on the cart page
    document.querySelectorAll('[data-cart-qty]').forEach(function (input) {
        input.addEventListener('change', function () {
            this.closest('form').submit();
        });
    });

    // Confirm destructive actions. Delegated on the document so it also works
    // for elements added later by the auto-refresh (e.g. kitchen tickets).
    document.addEventListener('click', function (ev) {
        var el = ev.target.closest('[data-confirm]');
        if (el && !window.confirm(el.getAttribute('data-confirm'))) {
            ev.preventDefault();
        }
    }, true);

    // Auto-refresh any container that declares a polling endpoint.
    // Used by the kitchen board and the customer "My Orders" live tracker.
    document.querySelectorAll('[data-endpoint]').forEach(function (el) {
        initAutoRefresh(el, el.dataset.endpoint);
    });
});

/**
 * Poll an endpoint and swap the container's HTML with the response.
 * The first load is skipped when the container was already rendered
 * server-side (so there is no visible flicker on page load).
 */
function initAutoRefresh(container, endpoint) {
    if (!container || !endpoint) return;

    var interval = parseInt(container.dataset.interval, 10) || 5000;
    var hasServerContent = container.textContent.trim().length > 0;

    async function load() {
        try {
            const res = await fetch(endpoint, { headers: { 'X-Requested-With': 'fetch' } });
            const html = await res.text();
            container.innerHTML = html;
        } catch (e) {
            console.error('Auto-refresh failed for', endpoint, e);
        }
    }

    if (!hasServerContent) {
        load();
    }
    setInterval(load, interval);
}
