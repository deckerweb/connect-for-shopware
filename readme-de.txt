=== Connect for Shopware ===
Contributors: daveshine
Tags: shopware, gutenberg, bricks, products
Requires at least: 6.6
Tested up to: 7.1.2
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shopware-Produkte direkt in Gutenberg und Bricks anzeigen.

Gliederung: Beschreibung · Installation · Bedienung · FAQ · Changelog

== Description ==

Produkte/Varianten, Galerie, Beschreibung, Datenblätter, Hersteller, Shopware-Preise, dynamische Kategorien, passende Produkte, CTA, Schnellansicht. Gemeinsamer Core mit Cache und Diagnose. Kein Warenkorb/Checkout und keine doppelte Produktpflege.

== Requirements ==

WordPress ≥ 6.6 · PHP ≥ 8.1 · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

== Installation ==

Das Plugin-ZIP aus dem aktuellen GitHub-Release unter Plugins → Installieren → Plugin hochladen installieren und aktivieren. Unter Einstellungen → Connect for Shopware die öffentliche HTTPS-Shop-Adresse speichern. DW_SW_ACCESS_KEY serverseitig in wp-config.php oder der PHP-Umgebung bereitstellen und die Verbindung testen. Bei einem Update bleiben Einstellungen und Produktzuordnungen erhalten.

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
Das ZIP aus dem GitHub-Release installieren. Veröffentlichte stabile Updates erscheinen im normalen WordPress-Updatesystem. Automatische Updates bleiben deine Entscheidung.

== Screenshots ==

1. Einstellungen: Shop-Verbindung, Cache und Diagnose.

== Changelog ==

= 1.0.0 · 2026-10-06 =
* Neu: Shopware-Produkte und Varianten lesend in nativen Gutenberg-Blöcken und Bricks-Elementen anzeigen.
* Neu: Produktgalerie, Beschreibungen, Datenblätter, Hersteller, Shopware-Preise und Verfügbarkeit.
* Neu: Passende Artikelprodukte, dynamische Produktraster, Produktbuttons und optionale Schnellansicht.
* Neu: Konfigurierbare Shop-Verbindung, Cache, Diagnose und optionale Bricks Components.
* Neu: WordPress-Updates von GitHub, optionaler deckerweb-Plugin-Katalog und deutsche Übersetzungen.


Autor: David Decker – DECKERWEB · https://github.com/deckerweb
Hooks: dw_sw_post_types, dw_sw_cache_refreshed, dw_sw_page_cache_purge_requested.
Deutsche Übersetzung enthalten. Ausführliche Dokumentation: docs/wiki/Deutsch.md / docs/wiki/English.md.
