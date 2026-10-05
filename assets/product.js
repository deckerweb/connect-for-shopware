(function () {
    'use strict';
    let dialog, content, opener, controller, sequence = 0;
    function createDialog() {
        if (dialog) return;
        const config = window.dwSwProduct;
        dialog = document.createElement('dialog');dialog.className = 'dw-sw-modal';
        dialog.setAttribute('aria-label', config.title);
        const close = document.createElement('button');close.type = 'button';close.className = 'dw-sw-modal-close';close.textContent = config.close;
        close.addEventListener('click', () => dialog.close());
        content = document.createElement('div');content.className = 'dw-sw-modal-content';
        dialog.append(close, content);document.body.append(dialog);
        dialog.addEventListener('close', () => {sequence++;if (controller) controller.abort();if (opener?.isConnected) opener.focus();});
        dialog.addEventListener('click', event => {
            const rect = dialog.getBoundingClientRect();
            if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
        });
    }
    async function openQuickView(button) {
        const config = window.dwSwProduct;
        if (!config || typeof HTMLDialogElement === 'undefined') return;
        createDialog();opener = button;
        if (controller) controller.abort();controller = new AbortController();const abort = controller;const request = ++sequence;
        content.replaceChildren();
        const status = document.createElement('p');status.setAttribute('role', 'status');status.textContent = config.loading;
        const shop = document.createElement('a');shop.href = button.dataset.shopUrl;shop.textContent = config.error;
        content.append(status);
        dialog.showModal();dialog.querySelector('.dw-sw-modal-close').focus();
        const timeout = setTimeout(() => abort.abort(), 12000);
        try {
            const response = await fetch(config.endpoint, {method: 'POST', credentials: 'omit', headers: {'Content-Type': 'application/json'}, signal: abort.signal,
                body: JSON.stringify({id: button.dataset.product, mode: button.dataset.mode, ticket: button.dataset.ticket})});
            if (!response.ok) throw new Error('Unavailable');
            const result = await response.json();
            if (request !== sequence) return;
            // The site endpoint returns server-sanitized HTML, never raw Shopware data.
            content.innerHTML = result.html;initialize(content);
        } catch (error) {if (request === sequence) {status.textContent = config.error;shop.textContent = button.closest('.dw-sw-info')?.querySelector('.dw-sw-button')?.textContent || config.title;content.append(shop);}}
        finally {clearTimeout(timeout);}
    }
    function initialize(root) {
        if (window.dwSwProduct && typeof HTMLDialogElement !== 'undefined') root.querySelectorAll('[data-dw-sw-quick]:not([data-ready])').forEach(button => {
            button.dataset.ready = 'true';button.hidden = false;button.addEventListener('click', () => openQuickView(button));
        });
        root.querySelectorAll('.dw-sw-tabs:not([data-ready])').forEach(container => {
            container.dataset.ready = 'true';
            const list = container.querySelector('.dw-sw-tablist');
            const tabs = Array.from(list.children);
            const panels = Array.from(container.querySelectorAll('.dw-sw-tabpanel'));
            list.setAttribute('role', 'tablist');
            const select = index => {
                tabs.forEach((tab, i) => {
                    tab.setAttribute('aria-selected', String(i === index));
                    tab.tabIndex = i === index ? 0 : -1;
                    panels[i].hidden = i !== index;
                });
            };
            tabs.forEach((tab, i) => {
                tab.setAttribute('role', 'tab');
                tab.setAttribute('aria-controls', panels[i].id);
                panels[i].setAttribute('role', 'tabpanel');
                panels[i].tabIndex = 0;
                tab.addEventListener('click', event => {event.preventDefault(); select(i);});
                tab.addEventListener('keydown', event => {
                    let next;
                    if (event.key === 'ArrowRight') next = (i + 1) % tabs.length;
                    if (event.key === 'ArrowLeft') next = (i + tabs.length - 1) % tabs.length;
                    if (event.key === 'Home') next = 0;
                    if (event.key === 'End') next = tabs.length - 1;
                    if (event.key === ' ' || event.key === 'Enter') next = i;
                    if (next !== undefined) {event.preventDefault(); select(next); tabs[next].focus();}
                });
            });
            select(0);
            container.classList.add('dw-sw-tabs-ready');
        });
        root.querySelectorAll('.dw-sw-media:not([data-ready])').forEach(container => {
            container.dataset.ready = 'true';
            const images = Array.from(container.children).filter(el => el.matches('[data-gallery-item]'));
            const links = Array.from(container.querySelectorAll('[data-gallery-index]'));
            if (!links.length) return;
            const select = index => {
                images.forEach((img, i) => {img.hidden = i !== index;});
                links.forEach((link, i) => {link.setAttribute('aria-current', i === index ? 'true' : 'false');});
            };
            links.forEach((link, i) => link.addEventListener('click', event => {event.preventDefault(); select(i);}));
            select(0);
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initialize(document));
    else initialize(document);
    // Builder preview fragments can be replaced after initial page load.
    new MutationObserver(() => initialize(document)).observe(document.documentElement, {childList: true, subtree: true});
})();
