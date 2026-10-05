# deckerweb audit · 0.5.1-dev

[Deutsch](AUDIT-de.md) · [Test report](TESTING.txt)

Date: 6 October 2026. Basis: local DECKERWEB Plugin Standards including updater V2 and parent AGENTS requirements.

- Metadata, stable slug/text domain, GPL file, four readmes with five latest versions, bilingual wiki/FAQ/history and local artwork preserved and checked.
- Complete structured escaped changelog in accessible footer dialog; language selection, Escape/focus return and text fallback retained.
- Unchanged shared updater V2 with own registration and package/HTTP safeguards retained.
- Public HTTPS shop URL and optional complete API base URL; no saved keys or customer defaults.
- Anonymous context supplies Sales Channel, language, currency, decimal precision and tax state. No prices are calculated. SEO links match channel/language.
- Shop/key fingerprint isolates shops; URL changes clear cache/diagnostics and invalidate Quick View tickets. Existing WordPress assignments and customized Components are retained.
- Full package scan confirms no customer names/domains or fixed channel/language/product/category IDs, including translations and starter templates.
- Optional Components 1.1.0 provide native elements without catalog selections.
- Local regressions and 28 additional configuration/security/multi-shop checks passed; admin saving/context probe browser-tested.

## Acceptance limits

One shop is configured per WordPress installation. The key remains in wp-config.php or PHP environment; .env is not loaded automatically. Enter the existing shop URL once after updating. Anonymous defaults belong to the Sales Channel; a storefront language path alone does not switch them.

PHP 8.3.x was user-confirmed for the previous version; this version was not locally executed on 8.3. Full licensed Bricks editing, responsive visual checks, live shop APIs and live updates require external acceptance. Repository/wiki addresses are prepared; this delivery does not publish external content. No customer shop or website change was made.

## Artwork selection addendum · 6 October 2026

The previously missing presentation of three artwork alternatives has been supplied. Three separate icon/banner concepts are available for selection. Design approval remains pending; the accepted plugin ZIP 0.5.0-dev is unchanged. Only the selected alternative will enter a subsequent package.

## Artwork round two · 6 October 2026

The user rejected all three initial designs. Three distinct banner/icon presentation concepts were generated with Imagegen: Editorial Bridge, Live Modules and Signal. Selection and subsequent production refinement remain pending. The working plugin and its ZIP remain unchanged.

## Artwork selection completed · 6 October 2026

The user explicitly approved Signal in matte blue. Earlier pending-selection notes are superseded. SVG/PNG icons and German/English banners are integrated in 0.5.1-dev. Genuine vector and raster sources are identified accurately. Required output dimensions and locale paths were checked. Rejected round-one and lilac concepts remain external proposal archives only.

Artwork completion 2026-10-06: All six banners now have genuine editable SVG sources and matching PNG exports. WordPress: 772×250 and 1544×500; GitHub: separate 1280×640, both DE/EN. Text, mark and product illustrations are vectors. XML, dimensions, external resources and ZIP contents checked; both composition formats visually checked. Public repository created; source and release not yet published.
