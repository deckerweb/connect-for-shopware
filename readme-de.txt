=== Connect for Shopware ===
Contributors: daveshine
Tags: shopware, gutenberg, bricks, products
Requires at least: 6.6
Tested up to: 7.1.2
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shopware-Produkte mit nativen Gutenberg-Blöcken und Bricks-Elementen in WordPress-Inhalten anzeigen. Shopware liefert Produktdaten und Preise.

== Description ==

- Produkte und konkrete Varianten, Galerie, Beschreibungen, Datenblätter und Hersteller.
- Shopware-Preise, Grund-/Streichpreise, Verfügbarkeit und direkte Produktlinks.
- Passende Artikelprodukte, dynamische Gruppen, Produktbuttons und optionale Schnellansicht.
- Gemeinsame Darstellung in Gutenberg und Bricks; optionale Bricks Components.
- Websitebezogene Verbindung, Diagnose und Cache für 15/30/60 Minuten.

**Version:** 1.0.1 · WordPress ≥ 6.6 · PHP ≥ 8.1 · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

== Installation ==

1. Das Plugin-ZIP aus dem aktuellen Release: https://github.com/deckerweb/connect-for-shopware/releases/latest herunterladen und in WordPress unter Plugins → Installieren → Plugin hochladen installieren.
2. Unter Einstellungen → Connect for Shopware die öffentliche HTTPS-Shop-Adresse speichern.
3. DW_SW_ACCESS_KEY in wp-config.php oder der PHP-Umgebung bereitstellen und die Verbindung testen.

== Frequently Asked Questions ==

= Berechnet WordPress Preise? =
Nein. Shopware liefert Preise und Rabatte; WordPress stellt sie dar.

= Was bedeutet die Cache-Dauer? =
Daten werden 15, 30 oder 60 Minuten verwendet. Nach Ablauf lädt der nächste Abruf frische Daten.

= Brauche ich Bricks Components? =
Nein. Native Elemente funktionieren direkt; Components sind optionale wiederverwendbare Layouts.

= Kann ich mehrere Shops verbinden? =
Pro WordPress-Website wird ein Shop konfiguriert. Jeder Shop benötigt seinen passenden serverseitigen Schlüssel.

= Funktioniert das Plugin in Multisite? =
Ja. Jede Website konfiguriert Shop, Cache und Artikelzuordnungen getrennt. Library-Einstellungen gelten je Netzwerk.

= Was passiert bei Deaktivierung oder Deinstallation? =
Einstellungen, Artikelzuordnungen und Components bleiben erhalten. Deinstallation leert temporäre Connector-Daten; gemeinsame Library-Daten folgen ihren eigenen Einstellungen.

= Wie erhalte ich Updates? =
Stabile GitHub-Releases erscheinen im normalen WordPress-Updatesystem. Automatische Updates bleiben deine Entscheidung.

Vollständige Fragen nach Themen: https://github.com/deckerweb/connect-for-shopware/wiki/FAQ-Deutsch

== Screenshots ==

1. Einstellungen: Verbindung, Cache, Diagnose und optionale Bricks Components.

== Changelog ==

= 1.0.1 · 2026-10-06 =
* Verbessert: Produkt-Styles nur für dargestellte Blöcke und Elemente laden.
* Behoben: Vollständige deutsche Sie-Übersetzung für Einstellungen, Editor-Bedienelemente und Update-Meldungen.
* Behoben: Temporäre Connector-Caches und Sperren bei Deinstallation entfernen; Einstellungen, Produktzuordnungen und installierte Components erhalten.
* Behoben: Explizite serverseitige Schlüssel je Multisite-Website zuordnen; globalen Schlüssel als Fallback erhalten.
* Sonstiges: Zweisprachige Dokumentation, Release-Metadaten und gemeinsame Komponenten vervollständigt.

= 1.0.0 · 2026-10-06 =
* Neu: Shopware-Produkte und Varianten lesend in nativen Gutenberg-Blöcken und Bricks-Elementen anzeigen.
* Neu: Produktgalerie, Beschreibungen, Datenblätter, Hersteller, Shopware-Preise und Verfügbarkeit.
* Neu: Passende Artikelprodukte, dynamische Produktraster, Produktbuttons und optionale Schnellansicht.
* Neu: Konfigurierbare Shop-Verbindung, Cache, Diagnose und optionale Bricks Components.
* Neu: WordPress-Updates von GitHub, optionaler deckerweb-Plugin-Katalog und deutsche Übersetzungen.


== Herkunft und Lizenzen ==

© 2026 David Decker – DECKERWEB. Gemeinsamer deckerweb Updater und Plugin Library sowie aktive Grafiken: GPL-2.0-or-later.

== Support ==

https://github.com/deckerweb/connect-for-shopware/issues
https://github.com/deckerweb/connect-for-shopware/security/advisories/new
https://ko-fi.com/deckerweb
https://buymeacoffee.com/daveshine
https://paypal.me/deckerweb
