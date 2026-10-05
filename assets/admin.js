(function () {
    'use strict';
    const config = window.dwSwAdmin;
    const dialog = document.getElementById('dw-sw-history');
    const opener = document.querySelector('[data-dw-sw-history]');
    if (dialog && opener && typeof dialog.showModal === 'function') {
        opener.addEventListener('click', event => {event.preventDefault();dialog.showModal();});
        dialog.querySelector('[data-dw-sw-close]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('close', () => opener.focus());
        dialog.addEventListener('click', event => {if (event.target === dialog) dialog.close();});
    }
    if (!config) return;
    const text = (tag, content) => {const el = document.createElement(tag);el.textContent = content;return el;};
    const api = async path => {
        const response = await fetch(config.root + path, {credentials: 'same-origin', headers: {'X-WP-Nonce': config.nonce}});
        if (!response.ok) throw new Error(config.labels.error);
        return response.json();
    };
    document.querySelectorAll('.dw-sw-association-editor').forEach(editor => {
        const input = editor.querySelector('input');
        const mount = editor.querySelector('.dw-sw-association-mount');
        let items = JSON.parse(input.value || '[]');
        let sequence = 0, page = 1, timer;
        const titleCache = new Map();
        const list = document.createElement('ol');
        const search = document.createElement('input');
        search.type = 'search';search.placeholder = config.labels.search;search.setAttribute('aria-label', config.labels.search);
        const results = document.createElement('div');
        const status = text('p', '');status.setAttribute('aria-live', 'polite');
        const controls = document.createElement('div');
        const button = (label, action) => {
            const b = text('button', label);b.type = 'button';b.className = 'button button-small';b.addEventListener('click', action);return b;
        };
        function commit() {input.value = JSON.stringify(items);input.dispatchEvent(new Event('change', {bubbles: true}));renderList();}
        function renderList() {
            list.replaceChildren();
            items.forEach((item, index) => {
                const key = item.selectionMode + ':' + item.productId;
                const li = text('li', titleCache.get(key) || item.productId);
                const remove = button(config.labels.remove, () => {items.splice(index, 1);commit();});
                const up = button('↑', () => { [items[index - 1], items[index]] = [items[index], items[index - 1]];commit();});
                const down = button('↓', () => { [items[index + 1], items[index]] = [items[index], items[index + 1]];commit();});
                up.disabled = index === 0;down.disabled = index === items.length - 1;
                up.setAttribute('aria-label', config.labels.up);down.setAttribute('aria-label', config.labels.down);
                li.append(document.createElement('br'), up, down, remove);list.append(li);
                if (!titleCache.has(key)) {
                    // Mark pending so repeated list renders do not duplicate requests.
                    titleCache.set(key, item.productId);
                    api('products/' + item.productId + '?mode=' + item.selectionMode).then(p => {titleCache.set(key, label(p));renderList();}).catch(() => {});
                }
            });
        }
        function add(p, family) {
            if (items.length >= 50) return;
            const ref = {productId: family ? (p.parentId || p.id) : p.id, selectionMode: family ? 'family' : 'variant'};
            if (!items.some(x => x.productId === ref.productId && x.selectionMode === ref.selectionMode)) {items.push(ref);commit();}
        }
        function label(p) {return [p.title, p.productNumber, p.variantText].filter(Boolean).join(' · ');}
        function renderResults(products, variants) {
            results.replaceChildren();
            products.forEach(p => {
                const row = document.createElement('div');row.className = 'dw-sw-result';row.append(text('p', label(p)));
                row.append(button(p.isFamily ? config.labels.family : config.labels.variant, () => add(p, p.isFamily)));
                if (!variants && p.parentId) row.append(button(config.labels.family, () => add(p, true)));
                if (!variants && (p.parentId || p.isFamily)) row.append(button(config.labels.variants, async () => {
                    const request = ++sequence;
                    try {const data = await api('products/' + (p.parentId || p.id) + '/variants');if (request === sequence) {renderResults(data, true);controls.replaceChildren();}}
                    catch (err) {if (request === sequence) status.textContent = config.labels.error;}
                }));
                results.append(row);
            });
        }
        async function run() {
            const request = ++sequence;
            results.replaceChildren();controls.replaceChildren();status.textContent = '';
            if (!search.value.trim()) return;
            try {
                const data = await api('products?search=' + encodeURIComponent(search.value.trim()) + '&page=' + page);
                if (request !== sequence) return;
                renderResults(data.items, false);
                if (!data.items.length) status.textContent = config.labels.empty;
                const previous = button(config.labels.previous, () => {page--;run();});previous.disabled = page <= 1;
                const next = button(config.labels.next, () => {page++;run();});next.disabled = page * 20 >= data.total;
                controls.append(previous, next);
            } catch (err) {if (request === sequence) status.textContent = config.labels.error;}
        }
        search.addEventListener('input', () => {sequence++;page = 1;clearTimeout(timer);timer = setTimeout(run, 300);});
        mount.append(list, search, status, results, controls);renderList();
    });
})();
