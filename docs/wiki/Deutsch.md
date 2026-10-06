# Connect for Shopware · Anleitung

[English](English.md) · [FAQ](FAQ-Deutsch.md) · [Änderungsverlauf](Changelog-Deutsch.md)

## Zweck und Voraussetzungen

Shopware-Produkte ohne Produktimport in WordPress anzeigen. Shopware bleibt die Quelle für Produktdaten und Preise. Pro WordPress-Installation wird ein Shopware-6-Shop konfiguriert. Voraussetzungen: WordPress ab 6.6, PHP ab 8.1 und ein Sales Channel mit Shopware-6.7-Store-API. Gutenberg funktioniert ohne Bricks; optionale Bricks Components benötigen Bricks ab 2.4.2. Elementor-Seiten können WordPress-Ausgaben anzeigen; ein eigenes Elementor-Widget ist nicht enthalten.

## Installation und Verbindung

Das Plugin-ZIP aus dem [aktuellen GitHub-Release](https://github.com/deckerweb/connect-for-shopware/releases/latest) unter Plugins → Installieren → Plugin hochladen installieren und aktivieren. Unter Einstellungen → Connect for Shopware die öffentliche HTTPS-Shop-Adresse speichern. Kein Shop ist voreingestellt.

Den Sales-Channel-Schlüssel serverseitig als `DW_SW_ACCESS_KEY` bereitstellen: Konstante in wp-config.php definieren oder eine gleichnamige PHP-Umgebungsvariable einrichten. Eine nicht leere Konstante hat Vorrang. Das Plugin lädt keine .env-Dateien, speichert den Schlüssel nicht in WordPress-Optionen und zeigt ihn nicht in den Einstellungen an. Eine ausdrücklich eingebundene geheime PHP-Datei außerhalb des Webverzeichnisses kann dieselbe Konstante definieren. Zugangsdaten nicht in Blöcken, Templates oder öffentlichen Dateien hinterlegen.

Nach dem Speichern die Verbindung testen. Die Store-API-Adresse entspricht standardmäßig der Shop-Adresse plus /store-api. Bei Sprachpfaden oder einem anderen API-Host unter Erweiterte Verbindungseinstellungen die vollständige API-Basisadresse eintragen. Beide Adressen benötigen öffentliches HTTPS mit Standardport, ohne Zugangsdaten, Query-Parameter oder Fragmente. Weiterleitungen und private/lokale HTTP-Ziele werden nicht unterstützt.

Die Vorgaben des anonymen Sales-Channel-Kontexts bestimmen Sprache, Währung und Steuerart. Ein Sprachpfad in der Shop-Adresse schaltet diesen Kontext nicht selbst um. Den angezeigten Kontext mit dem gewünschten Storefront-Kontext abgleichen. Preise und Rabatte kommen aus Shopware; WordPress berechnet sie nicht neu. Währungsformatierung und Netto-/Brutto-/Steuerfrei-Hinweise folgen Shopware.

Ein Wechsel der Shop-/API-Adresse leert Connector-Cache und Diagnose und macht alte Schnellansicht-Tickets ungültig. Artikelzuordnungen und installierte Components bleiben erhalten; beim Katalogwechsel die Produktauswahl prüfen.

## Einstellungen

![Verbindung, Cache und Diagnose](https://raw.githubusercontent.com/deckerweb/connect-for-shopware/main/assets/screenshots/settings-de.png)

Der kompakte Statusbereich zeigt Shop-Adresse und letzten bekannten erfolgreichen Abruf. Es gibt keine laufende Live-Überwachung: Für den aktuellen Zustand die Verbindung testen. API-Sonderfälle und technische Diagnose sind aufklappbar. Verbindung, Cache und optionale Bricks Components besitzen getrennte Bereiche.

## Produkte und Darstellung

Im Gutenberg-Block oder nativen Bricks-Element nach Produktname oder Artikelnummer suchen. Eine Produktfamilie oder eine konkrete Variante mit Gebindegröße/Farbe wählen. Ein Familienwechsel leert die Variante; der Server prüft die Zuordnung zusätzlich. Produktkarten können Bild/Galerie, Titel, Kurztext/Beschreibung/eigenen Text, Variantentext, aktuellen Preis, Streichpreis, Rabatt, Grundpreis, Verfügbarkeit und Shop-Link zeigen. Familienpreise sind mit „ab“ gekennzeichnet.

Bild und Hauptangaben links, rechts oder untereinander anordnen. Beschreibung und Tabs folgen den Hauptangaben. Coverbild oder vorhandene Galerie wählen. Videovorschauen führen zum Anbieter, ohne automatisch einen externen Player zu laden. Optionale Tabs zeigen Beschreibung, vorhandene PDF-Dokumente und Herstellerdaten. Bilder und Dokumente hängen von den Sales-Channel-Daten ab; geerbte Cover können eine andere Gebindegröße zeigen.

Textlink oder gestalteten Button sowie bestehenden/neuen Tab wählen. Links in neuen Tabs verwenden noopener noreferrer. Produktlinks bevorzugen Shopware-SEO-Adressen und nutzen ersatzweise technische Produktadressen. Die optionale Schnellansicht lädt Details bei Bedarf und unterstützt Escape sowie Fokusrückgabe. Shop-Links bleiben bei einem Ladefehler nutzbar.

## Artikelprodukte und dynamische Raster

Produkte geordnet im Dokumentbereich Shopware-Produkte oder im Inspector des Blocks Passende Produkte zuordnen; den Artikel für die Frontend-Ausgabe speichern. Passende Produkte liest diese Zuordnungen. Im Bricks-Single-Template einen Vorschauartikel mit zugeordneten Produkten wählen. Produktbutton stellt einen eigenständigen Shop-CTA bereit.

Für ein dynamisches Raster eine aktive Shopware-Kategorie mit dynamischer Produktgruppe auswählen. Shopware wertet deren Regeln aus. Produktanzahl und verfügbare Sortierung wählen; eine leere Sortierung verwendet den Shop-Standard. Seitennavigation und Kategorie-Shop-Link sind optional. Mehrere Raster behalten getrennte Seitenparameter. Darstellungsvorgaben werden von Gutenberg und Bricks gemeinsam genutzt.

## Optionale Bricks Components

Mit aktivem Bricks ab 2.4.2 lassen sich Components für Produktkarte, Produktdetails, passende Produkte, dynamisches Raster und Produktbutton einzeln installieren. Optionale Startvorlagen enthalten native Elemente ohne Katalogauswahl. Es wird nichts automatisch installiert. Vorhandene Definitionen und Vorlagen bleiben erhalten; zusätzliche Versionskopien erlauben das separate Ausprobieren mitgelieferter Designs. Wiederholen derselben Version erzeugt keine doppelten Kopien.

Den Component-Slot in der Strukturansicht aufklappen und sein natives Shopware-Element für die Produkt-/Kategoriensuche auswählen. Ein leerer Slot benötigt ein Element; Startvorlagen enthalten bereits eines. Wrapper-Einstellungen sind gemeinsam, Einstellungen der Slot-Elemente gehören zur jeweiligen Instanz. Passende Produkte stellt Überschrift, Buttontext und Ziel als verbundene Eigenschaften bereit. Der Installer berücksichtigt Bricks-Berechtigungen und Import-Sperren. Bei Multisite mit gemeinsamen Hauptsite-Components auf der Hauptsite installieren.

## Cache und Verfügbarkeit

Die Cache-Dauer beträgt standardmäßig 30 Minuten; wählbar sind 15, 30 oder 60 Minuten. Nach Ablauf lädt der nächste Abruf frische Daten. Es gibt keine Aktualisierung nach Zeitplan. Ein manueller Refresh leert den Connector-Cache. Optional werden die Seiten-Caches der gesamten Website von erkanntem WP Rocket, LiteSpeed Cache, WP Super Cache oder W3 Total Cache geleert. Ablaufzeiten von Server-/CDN-Caches separat abstimmen, damit dargestellte Preise nicht länger als vorgesehen gespeichert bleiben.

Bei einer vorübergehenden Shop-Störung können gespeicherte Produktinhalte ohne veraltete Preise oder Verfügbarkeit erscheinen. Ohne gespeicherte Daten wird keine Produktkarte ausgegeben. Eine Wiederholpause von 60 Sekunden und Abrufgrenzen schützen beide Systeme. Die Diagnose enthält Status und Dauer ohne rohe Zugangsdaten oder Kontext-Tokens. Produktimport, Shopware-Erweiterung, schreibende Shop-Abfragen, WordPress-Warenkorb oder Checkout sind nicht erforderlich.

## Updates, Übersetzungen und Plugin-Katalog

Stabile GitHub-Releases erscheinen im regulären WordPress-Updatesystem. Automatische Updates bleiben deine Entscheidung. Einstellungen, Artikelzuordnungen und installierte Components bleiben beim Update erhalten. Englische Oberfläche und deutsche Übersetzungen sind enthalten. Der Footer verlinkt die Anleitung in der Adminsprache und öffnet den strukturierten lokalen Änderungsverlauf; ohne JavaScript führt der Link zur Textdatei.

Der optionale gemeinsame deckerweb-Plugin-Katalog ist unter Plugins → Installieren → deckerweb erreichbar. Seine Einstellungen werden mit anderen installierten Library-Hosts geteilt. Der Connector läuft unabhängig von anderen deckerweb-Plugins. Öffentliche Updates benötigen keine GitHub-Zugangsdaten.

## Erweiterungs-Hooks

| Hook | Zweck |
| --- | --- |
| `dw_sw_post_types` | Unterstützte redaktionelle Inhaltstypen filtern; Standard: Beiträge und Seiten. |
| `dw_sw_cache_refreshed` | Aktion nach manuellem Connector-Cache-Refresh. |
| `dw_sw_page_cache_purge_requested` | Aktion für zusätzliche Seiten-Cache-Anbindungen. |

© 2026 [David Decker – DECKERWEB](https://github.com/deckerweb). GPL v2 or later · SPDX GPL-2.0-or-later.
