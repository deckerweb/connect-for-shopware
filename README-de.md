# Connect for Shopware

![Connect for Shopware](assets-github/banner-github-de-1280x640.png)

Shopware-Produkte direkt in Gutenberg und Bricks anzeigen.

[English](README.md) · [Ausführliche Anleitung](docs/wiki/Deutsch.md) · [FAQ](docs/wiki/FAQ-Deutsch.md)

[Funktionen](#0) · [Voraussetzungen](#1) · [Installation](#2) · [Bedienung](#3) · [FAQ](#4) · [Änderungen](#5)

<a id="0"></a>

## Funktionen

Produkte/Varianten, Galerie, Beschreibung, Datenblätter, Hersteller, Shopware-Preise, dynamische Kategorien, passende Produkte, CTA, Schnellansicht. Gemeinsamer Core mit Cache und Diagnose. Kein Warenkorb/Checkout und keine doppelte Produktpflege.

Kompakte Connect-Einstellungen mit sichtbarem Verbindungsstatus, aufklappbaren technischen Details und gemeinsamer deckerweb Plugin Library 0.6.0.


<a id="1"></a>

## Voraussetzungen

WordPress ≥ 6.6 · PHP ≥ 8.1 · Bricks Components ≥ 2.4.2 · Shopware 6.7 Store API.

<a id="2"></a>

## Installation

Bestehendes Connect for Shopware mit dem ZIP aktualisieren. Unter Einstellungen → Connect for Shopware öffentliche HTTPS-Shop-URL eintragen, speichern und Verbindung testen. Eine separate Store-API-URL ist optional. Kein Shop ist voreingestellt. `DW_SW_ACCESS_KEY` weiterhin serverseitig in wp-config.php oder PHP-Umgebung hinterlegen. Zuordnungen bleiben erhalten. Beim Wechsel vom alten Entwicklungsnamen zuerst das Altplugin deaktivieren.

<a id="3"></a>

## Bedienung

Im Editor Produkt auswählen. Optional unter Einstellungen → Connect for Shopware Bricks Components und Beispiele installieren. Bestehende Components bleiben unangetastet.

<a id="4"></a>

## FAQ

**Preise lokal berechnet?** Nein. **Components erforderlich?** Nein. **Updates überschreiben Designs?** Nein. **Updates?** Veröffentlichte stabile Releases erscheinen unter WordPress-Updates. Entwicklungsversionen werden per ZIP installiert.

<a id="5"></a>

## Änderungen

### 1.0.0 · 2026-10-06
- **Neu:** Erstes stabiles Release: gemeinsamer lesender Shopware-Core für native Gutenberg-Blöcke und Bricks-Elemente, optionale Bricks Components und konfigurierbare Shop-Adressen.
- **Verbessert:** Kompakte Connect-Einstellungen, Verbindungsstatus, aufklappbare technische Details und freigegebene mattblaue Vektorgrafiken.
- **Verbessert:** Unveränderter stabiler deckerweb Updater V2.1.0 und letztgültige deckerweb Plugin Library 0.6.0 integriert.
- **Sonstiges:** Zweisprachige Dokumentation und FAQ, installierbares ZIP mit stabilem Slug, vollständige Changelogs und Release-Prüfungen; bestehende Einstellungen und Produktzuordnungen erhalten.


### 0.6.0-dev · 2026-10-06
- **Neu:** Unveränderte deckerweb Plugin Library 0.6.0 mit gemeinsamer Host-Registrierung integriert.
- **Verbessert:** Kompakter Connect-Kopf, sichtbarer Verbindungsstatus und getrennte Bereiche für Verbindung, Cache und Bricks.
- **Verbessert:** Erweiterte Verbindungseinstellungen und technische Diagnose aufklappbar; native Bedienelemente und dezente mattblaue Akzente.
- **Verbessert:** Gemeinsamer Updater V2.1.0-dev.3 mit übersetzten Host-Meldungen und bisherigen Paketprüfungen.
- **Sonstiges:** Bestehende Shopware-Einstellungen, Produktzuordnungen, Blöcke und native Bricks-Elemente erhalten.


### 0.5.1-dev · 2026-10-06
- **Verbessert:** Vom Nutzer freigegebene Signal-Gestaltung in Mattblau, mit dezenterem Titel und passenden deutschen/englischen Bannern.
- **Verbessert:** Echt rekonstruiertes SVG-Icon und passende PNG-Icons in 128/256 Pixeln; Banner in 772×250 und 1544×500 Pixeln.
- **Verbessert:** Versionskennung in Grafik-URLs verhindert alte Icons/Banner aus dem Browser-Cache nach einem Update.
- **Verbessert:** Alle Banner als editierbare SVG und passende PNG; eigenes GitHub-Banner 1280×640 in Deutsch und Englisch.
- **Sonstiges:** Exportgrößen, Sprachpfade, Updater-Icons im Cache und Paketinhalt geprüft; Connector-Verhalten unverändert.


### 0.5.0-dev · 2026-10-06
- **Neu:** Konfigurierbare Shop-URL und optional gesonderter Store-API-Endpunkt; kein voreingestellter Kundenshop.
- **Verbessert:** Sales Channel, Sprache, Währung, Formatierung und Steuerhinweise kommen aus dem anonymen Store-API-Kontext; SEO-URLs verwenden diesen Kontext.
- **Verbessert:** Cache und Ausfallpause pro Shop; Shopwechsel leert Cache/Diagnose und entwertet Schnellansicht-Tickets.
- **Behoben:** Kundennamen, feste Domains, Kanal-/Sprach-/Produkt-/Kategorie-IDs und Auswahlen aus Quelltext, Dokumentation und Startvorlagen entfernt.
- **Sonstiges:** Serverseitige Konstante/Umgebungsvariable für Schlüssel beibehalten; generische Mehrshop-Testdaten und URL-Prüfung ergänzt.


### 0.4.0-dev · 2026-10-06
- **Neu:** Freiwillige Installation nativer Bricks Components mit Auswahl, befüllten Beispielen und zusätzlichen Versionskopien.
- **Verbessert:** Name Connect for Shopware, stabiler Slug, lokale Grafiken, zweisprachige Dokumentation und gemeinsamer Updater V2.
- **Behoben:** Bestehende Komponenten, Zuordnungen, Einstellungen und Gutenberg-Blocknamen bei der Umstellung erhalten.
- **Behoben:** Externe Bricks-CSS-Dateien nur einmal erzeugen; ein zweiter Durchlauf entfernt keine Styles durch Selektor-Deduplizierung.
- **Sonstiges:** Prüfung der deckerweb-Vorgaben und lokale Umstellungs-/Installer-Tests.



© 2026 [David Decker – DECKERWEB](https://github.com/deckerweb) · GPL v2 or later (SPDX GPL-2.0-or-later).
