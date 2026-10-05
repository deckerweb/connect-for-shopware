# Connect for Shopware · Anleitung

[English](English.md) · [FAQ](FAQ-Deutsch.md) · [Changelog](Changelog-Deutsch.md)

## Zweck und Voraussetzungen

Version 0.5.1-dev zeigt Shopware-Produkte in WordPress ohne Produktimport. Pro WordPress-Installation einen Shopware-6-Shop konfigurieren. Keine Domain, kein Sales Channel, keine Währung und kein Kundenprodukt sind voreingestellt. Die API-Anbindung wird gegen Shopware 6.7 geprüft. WordPress ab 6.6, PHP ab 8.1 und die Store API sind erforderlich. Die vorherige Fassung ist vom Nutzer unter PHP 8.3.x bestätigt; lokal wird PHP 8.4.5 getestet. Die native Bricks-Anbindung wird mit 2.4.2 geprüft. Gutenberg funktioniert ohne Bricks. Elementor-Seiten können WordPress-Ausgaben anzeigen; ein eigenes Elementor-Widget ist nicht enthalten.

## Installation und Umstellung

Zuerst das alte deckerweb Shopware Connector deaktivieren. Das neue ZIP connect-for-shopware installieren und aktivieren. Beide Einstiegsdateien dürfen nicht gleichzeitig laufen; das Plugin schützt gegen das aktive Altplugin. DW_SW_ACCESS_KEY, Einstellungen, Artikelzuordnungen, native Bricks-Elementnamen und vorhandene Gutenberg-Blockkennungen bleiben kompatibel. Das inaktive Altplugin anschließend entfernen. Keine Deinstallationsroutine löscht Inhalte.

Den Sales-Channel-Key serverseitig in wp-config.php als DW_SW_ACCESS_KEY oder als gleichnamige Umgebungsvariable setzen. Niemals im Block oder Template hinterlegen. Einstellungen → Connect for Shopware zeigt den Konfigurationsstatus ohne Schlüsselanzeige. Dort die Verbindung testen. Der anonyme Kontext muss zur gewünschten Währung, Sprache und Besteuerung passen.

## Produkte und Darstellung

Im Gutenberg-Block oder nativen Bricks-Element nach Name oder Produktnummer suchen. Produktfamilie oder konkrete Variante mit Gebinde/Farbe auswählen. Ein Familienwechsel leert die Variantenauswahl; der Server prüft zusätzlich die Familienzugehörigkeit. Die Produktkarte kann Bild/Galerie, Titel, Kurztext/Beschreibung/eigenen Text, Variantentext, aktuellen Preis, Listenpreis, Rabatt, Grundpreis, Verfügbarkeit und Shop-Link zeigen. Preise und Rabatte liefert Shopware; WordPress rechnet sie niemals neu. Familienpreise tragen die Kennzeichnung „ab“.

Bild und wichtigste Produktangaben sind links, rechts oder darunter anordbar. Beschreibung und Tabs folgen diesen Angaben. Cover oder vollständige verfügbare Galerie sind optional. Video-Vorschaubilder verlinken zum Anbieter, ohne automatisch einen fremden Player zu laden. Tabs zeigen wahlweise Beschreibung, verfügbare PDF-Datenblätter und Herstellerdaten. Medien und Dokumente hängen von den Associations und tatsächlichen Daten des Sales Channels ab. Vererbte Cover können eine andere Gebindegröße zeigen.

Textlink oder nativ gestalteten Button sowie bestehenden/neuen Tab wählen. Neue Tabs erhalten noopener noreferrer. Produkt-URLs bevorzugen Shopware-SEO-URLs; fehlen diese, wird ein technischer Produktlink verwendet. Die optionale Schnellansicht lädt Details erst beim Öffnen, mit signiertem Lese-Ticket und Abruflimit. Escape schließt den Dialog, der Fokus kehrt zum Auslöser zurück. Der Shop-Link bleibt nutzbar, wenn die Schnellansicht nicht lädt.

## Artikelprodukte und dynamische Raster

Geordnete Produkte im Dokumentbereich Shopware-Produkte oder im Inspector des passenden Blocks zuordnen; den Artikel für die Frontend-Ausgabe speichern. Passende Produkte liest diese Zuordnungen. Im Bricks-Single-Template einen zugeordneten Beitrag als Vorschau wählen. Produktbutton gibt einen eigenständigen Shop-CTA aus.

Dynamische Raster wählen eine aktive Shopware-Kategorie mit dynamischer Produktgruppe. Die Regeln wertet Shopware aus; WordPress kopiert sie nicht. Maximale Anzahl und verfügbare Shopware-Sortierung wählen; ohne Sortierauswahl gilt der Shop-Standard. Seitenzahlen und Kategorie-Shop-Link sind optional. Mehrere Raster behalten unabhängige Seitenparameter. Kompakt-, Beschreibungs- und Detail-Vorlagen sind in Gutenberg und Bricks gemeinsam verfügbar.

## Freiwillige Bricks Components

Bei aktivem Bricks ab 2.4.2 erscheint der Components-Installer in den Einstellungen. Einzelne Components auswählen: Produktkarte, Produktdetails, passende Produkte, dynamisches Raster und Produktbutton. Optionale Startvorlagen mit nativen Elementen ohne Produktauswahl machen Slots sofort nutzbar. Es wird nichts automatisch installiert.

Bestehende Komponenten-IDs werden übersprungen. Bearbeitete Definitionen und Beispielvorlagen bleiben erhalten. Zum separaten Ausprobieren einer mitgelieferten Version die zusätzliche Versionskopie wählen. Dieselbe Version erzeugt bei wiederholter Installation keine weiteren Kopien. Plugin-Updates ersetzen keine installierten Definitionen. Beim Kopieren werden Root-/Slot-Verweise angepasst. Der Installer respektiert Bricks-Berechtigungen für Components/Vorlagen und native Importsperren. Bei Multisite mit gemeinsamen Hauptseiten-Components ist die Installation nur auf der Hauptseite verfügbar.

Vier Definitionen verwenden native Slots. Im Strukturbaum den Slot öffnen und sein Shopware-Element auswählen, um die normale Produktsuche/Kategoriesuche zu nutzen. Ein neu eingefügter leerer Slot benötigt ein Element; alternativ eine installierte Startvorlage einsetzen und ihr Produkt auswählen. Gemeinsame Rahmen-Einstellungen werden zentral gepflegt; die Einstellungen des Slot-Elements gehören zur Instanz. Passende Produkte stellt Überschrift, Buttontext und Linkziel als verbundene Komponenten-Eigenschaften bereit. Startvorlagen enthalten keine Produkt-/Kategorie-IDs. Die Auswahl aus dem eigenen Shop vornehmen.

## Cache, Diagnose und Sicherheit

Standard sind 30 Minuten Cache; auswählbar sind 15/30/60. Nach Ablauf fragt der nächste Zugriff frische Daten ab. Es gibt keinen zeitgesteuerten Hintergrundabruf. Manueller Refresh leert den Connector-Cache. Die optionale Anbindung leert den Seiten-Cache der gesamten Website bei erkanntem WP Rocket, LiteSpeed, WP Super Cache oder W3 Total Cache. Ohne diese Plugins wird kein Seiten-Cache geleert. Server-/CDN-Caches separat abstimmen, damit sichtbare Preise nicht länger als gewünscht zwischengespeichert bleiben.

Der Core verwendet Abruflimits pro Anfrage, atomare Sperren für Cache-Erneuerung, 60 Sekunden Pause nach Fehlern und vorübergehend gespeicherte Inhalte bei Ausfällen. Solche Inhalte zeigen keine alten Preise oder Verfügbarkeiten; ein ausdrücklich fehlendes Produkt entfernt seine Sicherung. Diagnose speichert neutrale Statuswerte und Dauer, keine Schlüssel, Kontext-Tokens oder rohen Fehlermeldungen. Kein WordPress-Warenkorb/Checkout, kein Produktimport, keine schreibende Shop-Anfrage und keine Shopware-Erweiterung sind erforderlich.

## Dokumentation, Updates und Erweiterungs-Hooks

Englische Quelltexte und deutsche PHP-/JavaScript-Übersetzung sind enthalten. Der Standard-Footer öffnet den vollständigen lokalen strukturierten Changelog; ohne JavaScript führt der Link zur lokalen Textdatei. Dokumentationslinks folgen der Adminsprache.

Das vorgesehene Repository ist https://github.com/deckerweb/connect-for-shopware. Diese lokale Lieferung veröffentlicht weder Repository noch Wiki oder Release. Bis dahin gelten die mitgelieferten Wiki-Quellen und ZIP-Updates. Nach einem öffentlichen Release bindet der gemeinsame GitHub-Updater V2 an das reguläre WordPress-Updatesystem an. Identität, angebotene Version und WordPress-/PHP-Anforderungen werden vor Ersetzen geprüft. Automatische Updates aktiviert das Plugin nicht.

Öffentliche Hooks: dw_sw_post_types filtert unterstützte Inhaltstypen (Standard Beitrag/Seite); dw_sw_cache_refreshed signalisiert manuellen Cache-Refresh; dw_sw_page_cache_purge_requested ermöglicht zusätzliche Seiten-Cache-Anbindungen. Vorhandene Hook-Namen bleiben kompatibel.

## Prüfung und verbleibende Abnahme

Lokale Mapping-, WordPress-/Bricks-Ausgabe-, AJAX-Rechte-, Cache-Ausfall-, Galerie-/Tab-, CTA-, Pagination- und Schnellansicht-Tests bestanden. Components-Installation, Wiederholung, Erhalt eigener Anpassungen, Versionskopien, Beispiel-Verweise und externe CSS-Erzeugung sind lokal geprüft. Admin-Installationsablauf sowie Escape/Fokusrückgabe des Changelogs wurden im Browser getestet. Vollständige Bearbeitung im lizenzierten Bricks-Builder und responsive Sichtprüfung bleiben Schritte auf der Testseite. PHP 8.3 wurde für diese Fassung nicht lokal ausgeführt. Keine Kundeninstallation oder Shopänderung wurde vorgenommen.

© 2026 [David Decker – DECKERWEB](https://github.com/deckerweb). GPL v2 or later · SPDX GPL-2.0-or-later.

## Nutzerabnahme vom 6. Oktober 2026

Der Nutzer bestätigt erfolgreiche Installation und volle Funktion von 0.4.0-dev unter PHP 8.3.x sowie die Deaktivierung des Altplugins. Die zuvor offene PHP-8.3-Abnahme ist damit durch den Nutzer bestätigt; ein lokaler PHP-8.3-Test wurde weiterhin nicht durchgeführt. Einzelne Bricks-Builder-/responsive Prüfschritte wurden nicht gesondert beschrieben.

## Shop-URL und serverseitiger Schlüssel ab 0.5.0

Nach dem Update die öffentliche Shop-URL unter Einstellungen → Connect for Shopware eintragen. Auch bestehende Installationen müssen dies einmal tun; es gibt keine alte Domain als Rückfallwert. Speichern, danach Verbindung testen. Shop-URL und Sales-Channel-Key sind beide erforderlich. Standardmäßig ist die Store-API-URL die Shop-URL plus /store-api. Bei Sprachpfaden wie /en oder separatem API-Host im optionalen Feld die tatsächlich vollständige API-Basisadresse angeben. Beide URLs benötigen öffentliches HTTPS auf dem Standardport, ohne Zugangsdaten, Query-Parameter oder Fragment. Lokale/private HTTP-Ziele und Weiterleitungen werden nicht unterstützt; der sichere WordPress-HTTP-Transport prüft das Ziel beim Abruf.

Die anonymen Sales-Channel-Standardwerte bestimmen Sprache, Währung und Steuerart. Die Shop-URL oder ihr Sprachpfad schaltet diesen Kontext nicht selbst um. Den gemeldeten Kontext mit der gewünschten Shop-Darstellung abgleichen. Produkt-/Suchabrufe bleiben ausschließlich lesend; kein Kontext-PATCH und keine Kundenanmeldung. Die Formatierung verwendet Währungssymbol und Dezimalstellen aus Shopware; Netto-/steuerfrei-/Bruttohinweise folgen der tatsächlichen Steuerart. Ohne bekannte SEO-Sprachkennung wird der technische Produktlink verwendet, statt die URL einer anderen Sprache zu wählen.

Ein Wechsel der Shop-/API-URL leert Connector-Cache und Diagnose und entwertet bisherige Schnellansicht-Tickets. Die optionale Leerung erkannter Seiten-Caches läuft ebenfalls. Produktzuordnungen und installierte Components bleiben bewusst erhalten; beim Wechsel des Katalogs die Auswahl prüfen. Shop und Schlüssel-Fingerprint trennen Cache-Einträge. Bearbeitete Components werden nie heimlich geändert; mitgelieferte Components 1.1.0 bieten neutrale Startvorlagen als optionale Zusatzkopien.

DW_SW_ACCESS_KEY ist auf zwei unterstützten Wegen möglich: als WordPress-Konstante, normalerweise in wp-config.php definiert, oder als PHP-Umgebungsvariable vom Hosting/Server. Eine nicht leere Konstante hat Vorrang. Der Hoster muss die Variable an PHP weiterreichen; eine beliebige .env-Datei reicht nicht aus und wird vom Plugin nicht geladen. Optional kann eine außerhalb des öffentlichen Webverzeichnisses liegende PHP-Datei dieselbe Konstante definieren und ausdrücklich von wp-config.php eingebunden werden. Zugriffsrechte einschränken. Dies ist Serverkonfiguration, kein vom Plugin verwalteter Schlüsselspeicher. Der Key wird weder in WordPress-Optionen gespeichert noch im Admin angezeigt. Keine Variante schützt vor einem offengelegten Server oder Backup; Zugriffe entsprechend absichern. Ein Secret-Manager kann dieselbe Konstante/Umgebungsvariable bereitstellen.

[Shopware: Preise und Steuerkontext](https://developer.shopware.com/frontends/frontends-recipes/catalog/prices.html)

## Freigegebene Grafiken ab 0.5.1

Der Nutzer hat Signal in Mattblau gewählt. Passende echte SVG-/PNG-Icons und deutsche/englische Banner sind lokal enthalten und werden in Readmes und Updater verwendet. Diese Fassung ändert Grafik-/Versionsdokumentation; Shop-Konfiguration und Connector-Verhalten bleiben gleich. Banner wurden mit dem integrierten Imagegen-Werkzeug ausgearbeitet und übersetzt. Das Icon wurde als Vektor rekonstruiert; Rastergrößen wurden aus diesen Quellen exportiert.
