(function (wp) {
    'use strict';
    const e = wp.element.createElement;
    const {useState, useEffect, useRef, Fragment} = wp.element;
    const {__, sprintf} = wp.i18n;
    const domain = 'connect-for-shopware';
    const {Button, TextControl, TextareaControl, RangeControl, SelectControl, ToggleControl, PanelBody, Notice, Spinner} = wp.components;
    const {InspectorControls, useBlockProps} = wp.blockEditor;
    const api = (path) => wp.apiFetch({path: '/deckerweb-shopware/v1/' + path});
    const ServerSideRender = wp.serverSideRender.default || wp.serverSideRender;
    const reference = (p) => ({productId: p.id, selectionMode: p.isFamily ? 'family' : 'variant'});
    const label = (p) => [p.title, p.productNumber, p.variantText].filter(Boolean).join(' · ');

    function ProductPicker({onChoose}) {
        const [term, setTerm] = useState('');
        const [page, setPage] = useState(1);
        const [result, setResult] = useState({items: [], total: 0});
        const [variants, setVariants] = useState(null);
        const [busy, setBusy] = useState(false);
        const [error, setError] = useState('');
        const sequence = useRef(0);
        useEffect(() => {
            const request = ++sequence.current;
            setError(''); setVariants(null); setResult({items: [], total: 0});
            if (!term.trim()) {setBusy(false); return;}
            setBusy(true);
            const timer = setTimeout(() => {
                api('products?search=' + encodeURIComponent(term.trim()) + '&page=' + page)
                    .then(data => {if (request === sequence.current) setResult(data);})
                    .catch(() => {if (request === sequence.current) setError(__('Shop data unavailable. Check the connector configuration.', domain));})
                    .finally(() => {if (request === sequence.current) setBusy(false);});
            }, 300);
            return () => {clearTimeout(timer); sequence.current++;};
        }, [term, page]);
        async function loadVariants(p) {
            const request = ++sequence.current; setBusy(true); setError('');
            try {
                const items = await api('products/' + (p.parentId || p.id) + '/variants');
                if (request === sequence.current) setVariants(items);
            } catch (err) {
                if (request === sequence.current) setError(__('Shop data unavailable.', domain));
            } finally {if (request === sequence.current) setBusy(false);}
        }
        const items = variants || result.items;
        return e('div', {className: 'dw-sw-picker'},
            e(TextControl, {label: __('Search products', domain), value: term, onChange: value => {setPage(1); setTerm(value);}, __nextHasNoMarginBottom: true}),
            busy && e(Spinner),
            error && e(Notice, {status: 'error', isDismissible: false}, error),
            variants && e(Button, {variant: 'secondary', onClick: () => {sequence.current++;setVariants(null);setBusy(false);}}, __('Back to search results', domain)),
            e('div', {'aria-live': 'polite'}, !busy && term && !items.length && !error ? __('No products found.', domain) : ''),
            e('ul', {style: {padding: 0, listStyle: 'none', maxHeight: '360px', overflow: 'auto'}}, items.map(p => e('li', {key: p.id, style: {padding: '10px 0', borderBottom: '1px solid #ddd'}},
                e('div', null, label(p)),
                e(Button, {variant: 'secondary', onClick: () => onChoose(reference(p))}, p.isFamily ? __('Select product family', domain) : __('Select product / variant', domain)),
                !variants && p.parentId && e(Button, {variant: 'tertiary', onClick: () => onChoose({productId: p.parentId, selectionMode: 'family'})}, __('Select product family', domain)),
                !variants && (p.parentId || p.isFamily) && e(Button, {variant: 'tertiary', onClick: () => loadVariants(p)}, __('Choose variant', domain))
            ))),
            !variants && e('div', null,
                e(Button, {disabled: busy || page <= 1, onClick: () => setPage(page - 1)}, __('Previous results', domain)),
                e(Button, {disabled: busy || page * 20 >= result.total, onClick: () => setPage(page + 1)}, __('Next results', domain))
            )
        );
    }
    const flags = {
        showImage: 'Image', showTitle: 'Title', showText: 'Text', showPrice: 'Price',
        showListPrice: 'List price', showAvailability: 'Availability', showVariant: 'Variant text', showButton: 'Button', showTabs: 'Product details tabs', showDescriptionTab: 'Description tab', showDocumentsTab: 'Data sheets tab', showManufacturerTab: 'Manufacturer tab', showQuickView: 'Quick view'
    };
    function Controls({attributes, setAttributes}) {
        return e(PanelBody, {title: __('Product display', domain), initialOpen: true},
            e('p', null, __('Apply a preset, then adjust individual settings.', domain)),
            [['compact', __('Compact recommendation', domain)], ['description', __('Product description', domain)], ['details', __('Full product details', domain)]].map(([key, title]) =>
                e(Button, {key, variant: 'secondary', onClick: () => setAttributes(window.dwSwEditor.presets[key])}, title)),
            Object.entries(flags).map(([key, title]) => e(ToggleControl, {key, label: __(title, domain), checked: !!attributes[key], onChange: value => setAttributes({[key]: value})})),
            e(SelectControl, {label: __('Heading tag', domain), value: attributes.headingTag, options: ['h2', 'h3', 'h4', 'h5', 'h6', 'p'].map(value => ({value, label: value.toUpperCase()})), onChange: headingTag => setAttributes({headingTag})}),
            e(SelectControl, {label: __('Product information position', domain), value: attributes.infoPosition, options: [{value: 'right', label: __('Right of image', domain)}, {value: 'left', label: __('Left of image', domain)}, {value: 'below', label: __('Below image', domain)}], onChange: infoPosition => setAttributes({infoPosition})}),
            e(SelectControl, {label: __('Images', domain), value: attributes.imageMode, options: [{value: 'cover', label: __('Cover image', domain)}, {value: 'gallery', label: __('Product gallery', domain)}], onChange: imageMode => setAttributes({imageMode})}),
            e(SelectControl, {label: __('Text source', domain), value: attributes.textMode, options: [{value: 'summary', label: __('SEO summary', domain)}, {value: 'description', label: __('Description', domain)}, {value: 'custom', label: __('Custom text', domain)}], onChange: textMode => setAttributes({textMode})}),
            attributes.textMode === 'custom' && e(TextareaControl, {label: __('Custom text', domain), value: attributes.customText, onChange: customText => setAttributes({customText})}),
            e(SelectControl, {label: __('Product link style', domain), value: attributes.linkStyle, options: [{value: 'link', label: __('Text link', domain)}, {value: 'button', label: __('Button', domain)}], onChange: linkStyle => setAttributes({linkStyle})}),
            e(SelectControl, {label: __('Open product link in', domain), value: attributes.linkTarget, options: [{value: 'same', label: __('Current tab', domain)}, {value: 'new', label: __('New tab', domain)}], onChange: linkTarget => setAttributes({linkTarget})}),
            e(TextControl, {label: __('Button text', domain), value: attributes.buttonText, onChange: buttonText => setAttributes({buttonText})}),
            e(TextControl, {label: __('Custom button URL (optional)', domain), value: attributes.buttonUrl, onChange: buttonUrl => setAttributes({buttonUrl})})
        );
    }
    const common = {showImage: true, showTitle: true, showText: false, showPrice: true, showListPrice: true, showAvailability: true, showVariant: true, showButton: true, showTabs: false, showDescriptionTab: true, showDocumentsTab: true, showManufacturerTab: true, showQuickView: false};
    const attributes = Object.fromEntries(Object.entries(common).map(([key, value]) => [key, {type: 'boolean', default: value}]));
    Object.entries({buttonText: '', buttonUrl: '', headingTag: 'h3', textMode: 'summary', customText: '', linkStyle: 'link', linkTarget: 'same', infoPosition: 'right', imageMode: 'cover'}).forEach(([key, value]) => {attributes[key] = {type: 'string', default: value};});
    const supports = {html: false, align: ['wide', 'full'], spacing: {margin: true, padding: true, blockGap: true}, color: {text: true, background: true}, typography: {fontSize: true, lineHeight: true}, __experimentalBorder: {color: true, radius: true, width: true}};
    wp.blocks.registerBlockType('deckerweb/shopware-product', {
        apiVersion: 3, title: __('Shopware Product', domain), icon: 'products', category: 'widgets', supports,
        attributes: {...attributes, productId: {type: 'string', default: ''}, selectionMode: {type: 'string', default: 'variant', enum: ['family', 'variant']}},
        edit: props => e(Fragment, null,
            e(InspectorControls, null,
                e(PanelBody, {title: __('Product', domain)}, e(ProductPicker, {onChoose: ref => props.setAttributes(ref)}),
                    props.attributes.productId && e(Button, {isDestructive: true, onClick: () => props.setAttributes({productId: ''})}, __('Clear selection', domain))),
                e(Controls, props)),
            e('div', useBlockProps(), props.attributes.productId
                ? e(ServerSideRender, {block: 'deckerweb/shopware-product', attributes: props.attributes, httpMethod: 'POST'})
                : e('p', null, __('Select a Shopware product in the block settings.', domain)))),
        save: () => null
    });
    wp.blocks.registerBlockType('deckerweb/shopware-related-products', {
        apiVersion: 3, title: __('Shopware Related Products', domain), icon: 'products', category: 'widgets', supports,
        attributes: {...attributes, heading: {type: 'string', default: ''}}, usesContext: ['postId', 'postType'],
        edit: RelatedEdit,
        save: () => null
    });
    function ButtonControls({attributes, setAttributes}) {
        return e(PanelBody, {title: __('Product display', domain)},
            e(SelectControl, {label: __('Product link style', domain), value: attributes.linkStyle, options: [{value: 'link', label: __('Text link', domain)}, {value: 'button', label: __('Button', domain)}], onChange: linkStyle => setAttributes({linkStyle})}),
            e(SelectControl, {label: __('Open product link in', domain), value: attributes.linkTarget, options: [{value: 'same', label: __('Current tab', domain)}, {value: 'new', label: __('New tab', domain)}], onChange: linkTarget => setAttributes({linkTarget})}),
            e(TextControl, {label: __('Button text', domain), value: attributes.buttonText, onChange: buttonText => setAttributes({buttonText})}),
            e(TextControl, {label: __('Custom button URL (optional)', domain), value: attributes.buttonUrl, onChange: buttonUrl => setAttributes({buttonUrl})}));
    }
    wp.blocks.registerBlockType('deckerweb/shopware-product-button', {
        apiVersion: 3, title: __('Shopware Product Button', domain), icon: 'button', category: 'widgets', supports,
        attributes: {productId: {type: 'string', default: ''}, selectionMode: {type: 'string', default: 'variant'},
            buttonText: {type: 'string', default: ''}, buttonUrl: {type: 'string', default: ''}, linkStyle: {type: 'string', default: 'button'}, linkTarget: {type: 'string', default: 'same'}},
        edit: props => e(Fragment, null,
            e(InspectorControls, null, e(PanelBody, {title: __('Product', domain)}, e(ProductPicker, {onChoose: ref => props.setAttributes(ref)}),
                props.attributes.productId && e(Button, {isDestructive: true, onClick: () => props.setAttributes({productId: ''})}, __('Clear selection', domain))), e(ButtonControls, props)),
            e('div', useBlockProps(), props.attributes.productId ? e(ServerSideRender, {block: 'deckerweb/shopware-product-button', attributes: props.attributes, httpMethod: 'POST'}) : e('p', null, __('Select a Shopware product in the block settings.', domain)))),
        save: () => null
    });
    function GridEdit(props) {
        const duplicateId = wp.data.useSelect(select => {
            if (!props.attributes.gridId) return false;
            let first = '';
            function walk(blocks) {blocks.forEach(block => {
                if (!first && block.name === 'deckerweb/shopware-product-grid' && block.attributes.gridId === props.attributes.gridId) first = block.clientId;
                if (block.innerBlocks) walk(block.innerBlocks);
            });}
            walk(select('core/block-editor').getBlocks());
            return first !== '' && first !== props.clientId;
        }, [props.attributes.gridId, props.clientId]);
        useEffect(() => {if (!props.attributes.gridId || duplicateId) props.setAttributes({gridId: props.clientId});}, [props.attributes.gridId, duplicateId]);
        const [categories, setCategories] = useState([]);
        const [listing, setListing] = useState(null);
        const [error, setError] = useState('');
        const [loading, setLoading] = useState(true);
        useEffect(() => {
            let active = true;
            api('categories').then(items => {if (active) {setCategories(items); setLoading(false);}})
                .catch(() => {if (active) {setError(__('Shop data unavailable. Check the connector configuration.', domain)); setLoading(false);}});
            return () => {active = false;};
        }, []);
        useEffect(() => {
            let active = true;
            setListing(null);
            if (!props.attributes.categoryId) return;
            setError('');
            api('categories/' + props.attributes.categoryId + '/listing?limit=' + props.attributes.limit)
                .then(result => {if (active) setListing(result);})
                .catch(() => {if (active) setError(__('Dynamic product listing unavailable. Check the category and connector configuration.', domain));});
            return () => {active = false;};
        }, [props.attributes.categoryId, props.attributes.limit]);
        return e(Fragment, null,
            e(InspectorControls, null,
                e(PanelBody, {title: __('Product source', domain), initialOpen: true},
                    e('p', null, __('Only active categories using a dynamic product group are available. Rules remain in Shopware.', domain)),
                    loading && e(Spinner),
                    e(SelectControl, {label: __('Dynamic Shopware category', domain), value: props.attributes.categoryId,
                        options: [{value: '', label: __('Select a category', domain)}, ...categories.map(c => ({value: c.id, label: c.name}))],
                        onChange: categoryId => props.setAttributes({categoryId, order: ''})}),
                    !loading && !categories.length && !error && e(Notice, {status: 'info', isDismissible: false}, __('No active dynamic categories found. Assign a product group to an active category in Shopware.', domain)),
                    e(ToggleControl, {label: __('Pagination', domain), checked: props.attributes.showPagination, onChange: showPagination => props.setAttributes({showPagination})}),
                    e(ToggleControl, {label: __('More products link', domain), checked: props.attributes.showShopLink, onChange: showShopLink => props.setAttributes({showShopLink})}),
                    props.attributes.showShopLink && e(TextControl, {label: __('More products link text', domain), value: props.attributes.shopLinkText, onChange: shopLinkText => props.setAttributes({shopLinkText})}),
                    e(RangeControl, {label: __('Maximum products', domain), value: props.attributes.limit, min: 1, max: 48, onChange: limit => props.setAttributes({limit: limit || 6})}),
                    e(SelectControl, {label: __('Shopware sorting', domain), value: props.attributes.order,
                        options: [{value: '', label: __('Shopware default', domain)}, ...(listing?.sortings || []).map(sort => ({value: sort.key, label: sort.label}))],
                        onChange: order => props.setAttributes({order})}),
                    e(SelectControl, {label: __('Columns', domain), value: String(props.attributes.gridColumns),
                        options: [{value: '0', label: __('Automatic', domain)}, ...[1,2,3,4,5,6].map(n => ({value: String(n), label: String(n)}))],
                        onChange: gridColumns => props.setAttributes({gridColumns: Number(gridColumns)})}),
                    e(TextControl, {label: __('Section heading', domain), value: props.attributes.heading, onChange: heading => props.setAttributes({heading})}),
                    listing && e('p', null, sprintf(__('Products matching in Shopware: %d', domain), listing.total))),
                e(Controls, props)),
            e('div', useBlockProps({className: 'dw-sw-related dw-sw-dynamic-grid'}),
                error && e(Notice, {status: 'error', isDismissible: false}, error),
                props.attributes.categoryId
                    ? e(ServerSideRender, {block: 'deckerweb/shopware-product-grid', attributes: props.attributes, httpMethod: 'POST'})
                    : e('p', null, __('Select a dynamic Shopware category.', domain))));
    }
    wp.blocks.registerBlockType('deckerweb/shopware-product-grid', {
        apiVersion: 3, title: __('Shopware Product Grid', domain), icon: 'screenoptions', category: 'widgets', supports,
        attributes: {...attributes, categoryId: {type: 'string', default: ''}, heading: {type: 'string', default: ''},
            limit: {type: 'number', default: 6}, order: {type: 'string', default: ''}, gridColumns: {type: 'number', default: 0}, gridId: {type: 'string', default: ''}, showPagination: {type: 'boolean', default: false}, showShopLink: {type: 'boolean', default: false}, shopLinkText: {type: 'string', default: ''}},
        edit: GridEdit, save: () => null
    });
    function AssociationRow({item, index, count, onMove, onRemove}) {
        const [title, setTitle] = useState(item.productId);
        useEffect(() => {
            let active = true;
            api('products/' + item.productId + '?mode=' + item.selectionMode)
                .then(p => {if (active) setTitle(label(p));}).catch(() => {if (active) setTitle(item.productId);});
            return () => {active = false;};
        }, [item.productId, item.selectionMode]);
        return e('li', {style: {overflowWrap: 'anywhere'}}, e('p', null, title),
            e(Button, {disabled: index === 0, onClick: () => onMove(index, -1), 'aria-label': __('Move up', domain)}, '↑'),
            e(Button, {disabled: index === count - 1, onClick: () => onMove(index, 1), 'aria-label': __('Move down', domain)}, '↓'),
            e(Button, {isDestructive: true, onClick: () => onRemove(index)}, __('Remove', domain)));
    }
    function useAssignments() {
        const data = wp.data.useSelect(select => ({
            meta: select('core/editor').getEditedPostAttribute('meta') || {},
            postId: select('core/editor').getCurrentPostId(),
            type: select('core/editor').getCurrentPostType()
        }), []);
        const dispatch = wp.data.useDispatch('core/editor');
        const items = data.meta._dw_sw_products || [];
        return {...data, items, update: next => dispatch.editPost({meta: {...data.meta, _dw_sw_products: next}})};
    }
    function AssignmentControls({items, update}) {
        return e(Fragment, null,
            e('p', null, __('These assignments feed Related Products in your Single template.', domain)),
            e('ol', {style: {paddingLeft: '20px'}}, items.map((item, index) => e(AssociationRow, {
                key: item.selectionMode + ':' + item.productId, item, index, count: items.length,
                onRemove: i => update(items.filter((_, n) => n !== i)),
                onMove: (i, delta) => {const next = items.slice();[next[i], next[i + delta]] = [next[i + delta], next[i]];update(next);}
            }))),
            items.length < 50 && e(ProductPicker, {onChoose: ref => {
                if (!items.some(item => item.productId === ref.productId && item.selectionMode === ref.selectionMode)) update([...items, ref]);
            }})
        );
    }
    function RelatedEdit(props) {
        const assignments = useAssignments();
        const [html, setHtml] = useState('');
        const [error, setError] = useState('');
        const [loading, setLoading] = useState(false);
        const identity = JSON.stringify([assignments.postId, assignments.items, props.attributes]);
        useEffect(() => {
            let active = true;
            setHtml(''); setError('');
            if (!assignments.items.length || !assignments.postId) {setLoading(false); return;}
            setLoading(true);
            const timer = setTimeout(() => wp.apiFetch({path: '/deckerweb-shopware/v1/related-preview', method: 'POST',
                data: {postId: assignments.postId, products: assignments.items, settings: props.attributes}})
                .then(result => {if (active) {setHtml(result.html); setLoading(false);}})
                .catch(() => {if (active) {setError(__('Shop data unavailable. Check the connector configuration.', domain)); setLoading(false);}}), 300);
            return () => {active = false; clearTimeout(timer);};
        }, [identity]);
        return e(Fragment, null,
            e(InspectorControls, null,
                e(PanelBody, {title: __('Related products', domain)},
                    e(TextControl, {label: __('Section heading', domain), value: props.attributes.heading, onChange: heading => props.setAttributes({heading})}),
                    e(AssignmentControls, assignments)),
                e(Controls, props)),
            e('div', useBlockProps({className: 'dw-sw-related'}),
                !assignments.items.length && e(Notice, {status: 'info', isDismissible: false}, __('No products assigned yet. Select this block and add products in its settings, then save the article.', domain)),
                loading && e(Spinner),
                error && e(Notice, {status: 'error', isDismissible: false}, error),
                html && e('div', {dangerouslySetInnerHTML: {__html: html}})));
    }
    function DocumentProducts() {
        const data = useAssignments();
        if (!window.dwSwEditor || !window.dwSwEditor.postTypes.includes(data.type)) return null;
        return e(wp.editor.PluginDocumentSettingPanel, {name: 'dw-sw-products', title: __('Shopware products', domain)},
            e(AssignmentControls, data));
    }
    wp.plugins.registerPlugin('dw-sw-document-products', {render: DocumentProducts});
})(window.wp);
