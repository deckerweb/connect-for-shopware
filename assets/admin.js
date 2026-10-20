(function () {
    'use strict';
    const config = window.dwSwAdmin;
    const dialog = document.getElementById('dw-sw-history');
    const opener = document.querySelector('[data-dw-sw-history]');
    // Bind once and use native methods so instance-level overrides cannot strand the dialog.
    if (dialog && opener && !dialog.dataset.dwSwBound && typeof HTMLDialogElement !== 'undefined') {
        dialog.dataset.dwSwBound = 'true';
        const native = HTMLDialogElement.prototype;
        const close = () => {
            try {native.close.call(dialog);} catch (error) {dialog.removeAttribute('open');}
            dialog.hidden = true;
            opener.focus();
        };
        dialog.addEventListener('close', () => {dialog.hidden = true;opener.focus();});
        dialog.addEventListener('cancel', event => {event.preventDefault();close();});
        document.addEventListener('click', event => {
            if (event.target.closest?.('[data-dw-sw-close]') && dialog.contains(event.target)) {
                event.preventDefault();event.stopImmediatePropagation();close();return;
            }
            if (event.target.closest?.('[data-dw-sw-history]') === opener) {
                if (typeof native.showModal !== 'function') return;
                try {
                    dialog.hidden = false;
                    native.showModal.call(dialog);
                    if (!dialog.open) throw new Error('Dialog did not open');
                } catch (error) {dialog.hidden = true;dialog.removeAttribute('open');return;}
                event.preventDefault();event.stopImmediatePropagation();
                dialog.querySelector('[data-dw-sw-close]').focus();
            }
        }, true);
        dialog.addEventListener('click', event => {
            const rect = dialog.getBoundingClientRect();
            if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) close();
        });
    }
    if (!config) return;
    const text = (tag, content) => {const el = document.createElement(tag);el.textContent = content;return el;};
    // Plain WordPress permalinks already use a rest_route query parameter.
    const endpoint = path => {
        const url = new URL(config.root, window.location.origin);
        const divider = path.indexOf('?');
        const route = divider < 0 ? path : path.slice(0, divider);
        if (url.searchParams.has('rest_route')) url.searchParams.set('rest_route', url.searchParams.get('rest_route') + route);
        else url.pathname += route;
        if (divider >= 0) new URLSearchParams(path.slice(divider + 1)).forEach((value, key) => url.searchParams.append(key, value));
        return url.href;
    };
    const request = async (path, method = 'GET', data) => {
        let response, result;
        try {
            response = await fetch(endpoint(path), {method, credentials: 'same-origin', headers: {'X-WP-Nonce': config.nonce, 'Content-Type': 'application/json'}, body: data ? JSON.stringify(data) : undefined});
            result = await response.json();
        } catch (error) {throw new Error(config.labels.error);}
        if (!response.ok) throw new Error(result.message || config.labels.error);
        return result;
    };
    const l = config.labels;
    document.querySelectorAll('[data-dw-sw-check]').forEach(button => {
        if (button.dataset.dwSwBound) return;button.dataset.dwSwBound = 'true';
        button.addEventListener('click', async () => {
            const output = button.parentElement.querySelector('.dw-sw-check-results');
            button.disabled = true;output.replaceChildren(text('p', l.checking));
            const list = document.createElement('ul');output.append(list);
            let cursor = 0;
            try {
                while (true) {
                    const result = await request('usage/check', 'POST', {postId: Number(button.dataset.dwSwCheck), cursor});
                    result.items.forEach(item => list.append(text('li', item.title + ' · ' + (l[item.status] || l.unreachable))));
                    if (result.done) break;
                    if (result.next <= cursor) throw new Error(l.error);
                    cursor = result.next;
                }
                output.firstElementChild.textContent = l.checked;
            } catch (error) {output.firstElementChild.textContent = error.message;}
            finally {button.disabled = false;}
        });
    });
    const transfer = document.querySelector('.dw-sw-transfer');
    if (transfer && !transfer.dataset.dwSwBound) {
        transfer.dataset.dwSwBound = 'true';
        const input = transfer.querySelector('textarea'), status = transfer.querySelector('.dw-sw-transfer-status');
        const area = transfer.querySelector('.dw-sw-transfer-preview'), apply = transfer.querySelector('[data-dw-sw-apply]');
        const buttons = [...transfer.querySelectorAll('button')];let preview = null, busy = false;
        const reset = () => {preview = null;apply.disabled = true;area.replaceChildren();};
        const lock = value => {busy = value;buttons.forEach(button => {button.disabled = value || (button === apply && !preview);});input.disabled = value;};
        input.addEventListener('input', reset);
        transfer.querySelector('[data-dw-sw-export]').addEventListener('click', async () => {
            if (busy) return;lock(true);
            try {
                const bundle = await request('settings/export');const href = URL.createObjectURL(new Blob([JSON.stringify(bundle, null, 2)], {type: 'application/json'}));
                const a = document.createElement('a');a.href = href;a.download = 'connect-for-shopware-settings.json';a.click();setTimeout(() => URL.revokeObjectURL(href), 1000);
                status.textContent = l.exported;
            } catch (error) {status.textContent = error.message;}
            finally {lock(false);}
        });
        transfer.querySelector('[data-dw-sw-preview]').addEventListener('click', async () => {
            if (busy) return;reset();lock(true);status.textContent = '';
            try {
                preview = await request('settings/preview', 'POST', {bundle: JSON.parse(input.value)});
                const table = document.createElement('table');table.className = 'widefat striped';
                const head = document.createElement('thead'), tr = document.createElement('tr');[l.setting, l.before, l.after].forEach(value => {const cell = text('th', value);cell.scope = 'col';tr.append(cell);});head.append(tr);table.append(head);
                const body = document.createElement('tbody');
                preview.changes.forEach(change => {const row = document.createElement('tr');[l[change.key] || change.key, typeof change.before === 'boolean' ? (change.before ? l.enabled : l.disabled) : String(change.before), typeof change.after === 'boolean' ? (change.after ? l.enabled : l.disabled) : String(change.after)].forEach(value => row.append(text('td', value)));body.append(row);});table.append(body);area.append(table);
                status.textContent = l.previewReady;
            } catch (error) {reset();status.textContent = error instanceof SyntaxError ? l.invalidImport : error.message;}
            finally {lock(false);}
        });
        apply.addEventListener('click', async () => {
            if (busy || !preview) return;lock(true);
            try {await request('settings/apply', 'POST', preview);reset();status.textContent = l.imported;const next = new URL(window.location.href);next.searchParams.delete('settings-updated');next.searchParams.set('dw_sw_imported', '1');window.location.assign(next.href);}
            catch (error) {reset();status.textContent = error.message;}
            finally {lock(false);}
        });
    }

    const api = async path => {
        const response = await fetch(endpoint(path), {credentials: 'same-origin', headers: {'X-WP-Nonce': config.nonce}});
        if (!response.ok) throw new Error(config.labels.error);
        return response.json();
    };
    document.querySelectorAll('.dw-sw-association-editor').forEach(editor => {
        if (editor.dataset.dwSwBound) return;editor.dataset.dwSwBound = 'true';
        const input = editor.querySelector('input');
        const mount = editor.querySelector('.dw-sw-association-mount');
        let items = JSON.parse(input.value || '[]');
        let sequence = 0, page = 1, timer;
        const titleCache = new Map(), products = new Map();
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
                if (products.get(key)?.images?.[0]?.url) {const image = document.createElement('img');image.src = products.get(key).images[0].url;image.alt = '';image.width = 44;image.height = 44;image.loading = 'lazy';image.className = 'dw-sw-picker-image';li.prepend(image);}
                const refresh = button(l.refresh, async () => {
                    refresh.disabled = true;
                    try {await request('selection/refresh', 'POST', {kind: 'product', id: item.productId});status.textContent = l.refreshed;titleCache.delete(key);products.delete(key);renderList();}
                    catch (error) {status.textContent = error.message;refresh.disabled = false;}
                });
                const remove = button(config.labels.remove, () => {items.splice(index, 1);commit();});
                const up = button('↑', () => { [items[index - 1], items[index]] = [items[index], items[index - 1]];commit();});
                const down = button('↓', () => { [items[index + 1], items[index]] = [items[index], items[index + 1]];commit();});
                up.disabled = index === 0;down.disabled = index === items.length - 1;
                up.setAttribute('aria-label', config.labels.up);down.setAttribute('aria-label', config.labels.down);
                li.append(document.createElement('br'), up, down, remove, refresh);list.append(li);
                if (!titleCache.has(key)) {
                    // Mark pending so repeated list renders do not duplicate requests.
                    titleCache.set(key, item.productId);
                    api('products/' + item.productId + '?mode=' + item.selectionMode).then(p => {titleCache.set(key, label(p));products.set(key, p);renderList();}).catch(() => {});
                }
            });
        }
        function add(p, family) {
            if (items.length >= 50) return;
            const ref = {productId: family ? (p.parentId || p.id) : p.id, selectionMode: family ? 'family' : 'variant'};
            if (!items.some(x => x.productId === ref.productId && x.selectionMode === ref.selectionMode)) {items.push(ref);commit();}
        }
        function label(p) {return [p.isFamily ? config.labels.family : config.labels.variant, p.title, p.productNumber, p.variantText].filter(Boolean).join(' · ');}
        function renderResults(products, variants) {
            results.replaceChildren();
            products.forEach(p => {
                const row = document.createElement('div');row.className = 'dw-sw-result';
                if (p.images?.[0]?.url) {const image = document.createElement('img');image.src = p.images[0].url;image.alt = '';image.width = 44;image.height = 44;image.loading = 'lazy';image.className = 'dw-sw-picker-image';row.append(image);}
                row.append(text('p', label(p)));
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
