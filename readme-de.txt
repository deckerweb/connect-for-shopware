=== Connect for Shopware ===
Contributors: daveshine
Tags: shopware, gutenberg, bricks, products
Requires at least: 6.6
Tested up to: 7.1.2
Requires PHP: 8.1
Stable tag: 0.5.1-dev
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shopware-Produkte direkt in Gutenberg und Bricks anzeigen.

Gliederung: Beschreibung · Installation · Bedienung · FAQ · Changelog

== Description ==

Produkte/Varianten, Galerie, Beschreibung, Datenblätter, Hersteller, Shopware-Preise, dynamische Kategorien, passende Produkte, CTA, Schnellansicht. Gemeinsamer Core mit Cache und Diagnose. Kein Warenkorb/Checkout und keine doppelte Produktpflege.

== Requirements ==

WordPress ≥ 6.6 · PHP ≥ 8.1 (Kunde/customer 8.3.x) · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

== Installation ==

Bestehendes Connect for Shopware mit dem ZIP aktualisieren. Unter Einstellungen → Connect for Shopware öffentliche HTTPS-Shop-URL eintragen, speichern und Verbindung testen. Eine separate Store-API-URL ist optional. Kein Shop ist voreingestellt. `DW_SW_ACCESS_KEY` weiterhin serverseitig in wp-config.php oder PHP-Umgebung hinterlegen. Zuordnungen bleiben erhalten. Beim Wechsel vom alten Entwicklungsnamen zuerst das Altplugin deaktivieren.

== Usage ==

Im Editor Produkt auswählen. Optional unter Einstellungen → Connect for Shopware Bricks Components und Beispiele installieren. Bestehende Components bleiben unangetastet.

== Frequently Asked Questions ==

= Berechnet WordPress Preise? =
Nein. Shopware liefert sämtliche Preise und Rabatte.

= Brauche ich Bricks Components? =
Nein. Native Elemente funktionieren sofort; Components sind freiwillig.

= Überschreiben Updates meine Components-Gestaltung? =
Nein. Bestehende Definitionen werden übersprungen; Versionskopien sind optional.

= Wie erhalte ich Updates? =
Derzeit das installierbare ZIP verwenden. GitHub-Release-Updates sind vorbereitet; Veröffentlichung und ein echtes Online-Update wurden in dieser Lieferung nicht verifiziert.

== Changelog ==

= 0.5.1-dev · 2026-10-06 =
* Verbessert: Vom Nutzer freigegebene Signal-Gestaltung in Mattblau, mit dezenterem Titel und passenden deutschen/englischen Bannern.
* Verbessert: Echt rekonstruiertes SVG-Icon und passende PNG-Icons in 128/256 Pixeln; Banner in 772×250 und 1544×500 Pixeln.
* Verbessert: Versionskennung in Grafik-URLs verhindert alte Icons/Banner aus dem Browser-Cache nach einem Update.
* Verbessert: Alle Banner als editierbare SVG und passende PNG; eigenes GitHub-Banner 1280×640 in Deutsch und Englisch.
* Sonstiges: Exportgrößen, Sprachpfade, Updater-Icons im Cache und Paketinhalt geprüft; Connector-Verhalten unverändert.


= 0.5.0-dev · 2026-10-06 =
* Neu: Konfigurierbare Shop-URL und optional gesonderter Store-API-Endpunkt; kein voreingestellter Kundenshop.
* Verbessert: Sales Channel, Sprache, Währung, Formatierung und Steuerhinweise kommen aus dem anonymen Store-API-Kontext; SEO-URLs verwenden diesen Kontext.
* Verbessert: Cache und Ausfallpause pro Shop; Shopwechsel leert Cache/Diagnose und entwertet Schnellansicht-Tickets.
* Behoben: Kundennamen, feste Domains, Kanal-/Sprach-/Produkt-/Kategorie-IDs und Auswahlen aus Quelltext, Dokumentation und Startvorlagen entfernt.
* Sonstiges: Serverseitige Konstante/Umgebungsvariable für Schlüssel beibehalten; generische Mehrshop-Testdaten und URL-Prüfung ergänzt.


= 0.4.0-dev · 2026-10-06 =
* Neu: Freiwillige Installation nativer Bricks Components mit Auswahl, befüllten Beispielen und zusätzlichen Versionskopien.
* Verbessert: Name Connect for Shopware, stabiler Slug, lokale Grafiken, zweisprachige Dokumentation und gemeinsamer Updater V2.
* Behoben: Bestehende Komponenten, Zuordnungen, Einstellungen und Gutenberg-Blocknamen bei der Umstellung erhalten.
* Behoben: Externe Bricks-CSS-Dateien nur einmal erzeugen; ein zweiter Durchlauf entfernt keine Styles durch Selektor-Deduplizierung.
* Sonstiges: Prüfung der deckerweb-Vorgaben und lokale Umstellungs-/Installer-Tests.


= 0.3.0-dev · 2026-10-05 =
* Neu: Verbindungstest und sichere Diagnose mit letztem Erfolg, Dauer und neutralem Fehlerstatus.
* Neu: Automatische, abschaltbare Seiten-Cache-Erkennung beim manuellen Refresh für WP Rocket, LiteSpeed Cache, WP Super Cache und W3 Total Cache.
* Neu: Produktspalte in Beitrags-/Seitenlisten ohne zusätzliche Shop-Abfragen und gemeinsame Darstellungsvorlagen für Gutenberg/Bricks.
* Neu: Eigenständiger Shopware-Produktbutton in Gutenberg und als natives Bricks-Element.
* Neu: Optionaler Kategorie-Shop-Link und Seitenzahlen im dynamischen Raster.
* Neu: Zuschaltbare Schnellansicht mit Galerie, Tabs, Shop-Button, signierten Lese-Tickets und Abruflimit.
* Verbessert: Ausfallschutz mit 60-Sekunden-Pause, Netzwerklimit und atomaren Cache-Sperren; vorhandene Inhalte ohne abgelaufene Preise oder Verfügbarkeit.
* Behoben: Galerie-/Tab-Kennungen bleiben auch in nachgeladenen Modals eindeutig.
* Sonstiges: Einrichtung, Cache-Verhalten, Grenzen und FAQ zweisprachig dokumentiert; lokale Regressionstests erweitert.


= 0.2.0-dev · 2026-10-04 =
* Neu: Gutenberg-Block „Shopware-Produktraster“ und natives Bricks-Element für dynamische Produktgruppen über aktive Shopware-Kategorien.
* Neu: Kategorieauswahl, maximale Produktanzahl, Shopware-Sortierung, Überschrift und responsive Spalten.
* Neu: Geschützte Editor-API und native Bricks-AJAX-Auswahl für Kategorien und Sortierungen.
* Verbessert: Gemeinsames Repository und Karten-Rendering; Shopware wertet Regeln und Preise aus, keine doppelte Regelpflege.
* Sonstiges: Dynamische Kategorien geprüft; Pagination und Preis-Sortierung erfolgreich. Umsetzung und Einrichtung zweisprachig dokumentiert.



Autor: David Decker – DECKERWEB · https://github.com/deckerweb
Hooks: dw_sw_post_types, dw_sw_cache_refreshed, dw_sw_page_cache_purge_requested.
Deutsche Übersetzung enthalten. Ausführliche Dokumentation: docs/wiki/Deutsch.md / docs/wiki/English.md.
