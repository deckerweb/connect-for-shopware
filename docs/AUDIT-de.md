# deckerweb-Prüfung · 0.5.1-dev

[English](AUDIT.md) · [Testbericht](TESTING.txt)

Stand: 6. Oktober 2026. Grundlage: lokale DECKERWEB Plugin Standards einschließlich Updater V2 und übergeordnete AGENTS-Vorgaben.

- Metadaten, stabiler Slug/Textdomain, GPL-Datei, vier Readmes mit fünf jüngsten Versionen, zweisprachige Wiki-/FAQ-/Changelog-Dateien und lokale Grafiken erhalten und geprüft.
- Vollständiger strukturierter, maskierter Changelog im zugänglichen Footer-Dialog; Sprachwahl, Escape/Fokusrückgabe und Text-Fallback erhalten.
- Unveränderte gemeinsame Updater-V2-Library mit eigener Registrierung und Paket-/HTTP-Grenzen erhalten.
- Öffentliches HTTPS-Shop-URL-Feld und optionale vollständige API-Basisadresse; keine gespeicherten Schlüssel, keine Kunden-Defaults.
- Anonymer Kontext liefert Sales Channel, Sprache, Währung, Dezimalstellen und Steuerart. Preiswerte werden nicht berechnet. SEO-Links sind kanal-/sprachbezogen.
- URL-/Schlüssel-Fingerprint trennt Shops; Shopwechsel leert Cache/Diagnose und entwertet Schnellansicht-Tickets. Bestehende WordPress-Zuordnungen und bearbeitete Components bleiben erhalten.
- Vollständiger Paket-Scan bestätigt: keine Kundennamen/-domains oder festen Kanal-/Sprach-/Produkt-/Kategorie-IDs, auch nicht in Übersetzungen oder Startvorlagen.
- Components 1.1.0 sind freiwillig installierbar. Vorlagen enthalten native Elemente ohne Katalogauswahl.
- Lokale Regressionstests und 28 zusätzliche Konfigurations-/Sicherheits-/Mehrshop-Prüfungen bestanden; Admin-Speichern und Kontext-Test im Browser geprüft.

## Abnahmegrenzen

Pro WordPress-Installation wird ein Shop konfiguriert. Der Key bleibt in wp-config.php oder der PHP-Umgebung; .env wird nicht automatisch geladen. Nach dem Update muss die bisherige Shop-URL einmal eingetragen werden. Der Sales Channel bestimmt seine anonymen Standardwerte; ein Sprachpfad allein schaltet sie nicht um.

PHP 8.3.x ist für die vorherige Fassung durch den Nutzer bestätigt, für diese Fassung nicht lokal ausgeführt. Vollständiger lizenzierter Bricks-Builder, responsive Sichtprüfung, tatsächliche Shop-API und Online-Updates bleiben Abnahmeschritte. Repository-/Wiki-Adressen sind vorbereitet; diese Lieferung veröffentlicht keine externen Inhalte. Kundenshop und Kundenseite wurden nicht verändert.

## Ergänzung zur Grafikauswahl · 6. Oktober 2026

Die bislang fehlende Präsentation dreier Grafikentwürfe wurde nachgeholt. Drei separate Icon-/Banner-Varianten stehen zur Auswahl. Die Grafikentscheidung ist noch offen; der bestätigte Plugin-ZIP-Stand 0.5.0-dev wurde nicht verändert. Erst die ausgewählte Variante wird in einem folgenden Paket übernommen.

## Zweite Grafikrunde · 6. Oktober 2026

Der Nutzer hat alle drei ersten Entwürfe verworfen. Drei neue, eigenständige Banner-/Icon-Präsentationsentwürfe wurden mit Imagegen erstellt: Editorial Bridge, Live Modules und Signal. Die visuelle Auswahl und anschließende Produktionsausarbeitung stehen aus. Die funktionierende Plugin-Version und ihr ZIP bleiben unverändert.

## Grafikauswahl abgeschlossen · 6. Oktober 2026

Der Nutzer hat Signal in Mattblau ausdrücklich freigegeben. Die bisherigen Angaben zur ausstehenden Auswahl sind damit überholt. SVG-/PNG-Icon und deutsche/englische Banner sind in 0.5.1-dev integriert. Die reine Vektorquelle wird als solche geführt, Rasterbanner nicht. Alle geforderten Exportgrößen und Sprachpfade wurden geprüft. Die erste verworfene Runde und das lila Muster bleiben ausschließlich externe Entwurfsstände.

Grafik-Ergänzung 2026-10-06: Alle sechs Banner sind nun echte editierbare SVG mit identischen PNG-Exporten. WordPress: 772×250 und 1544×500; GitHub: separates 1280×640, jeweils DE/EN. Texte, Signet und Produktillustrationen bestehen aus Vektoren. XML, Maße, externe Ressourcen und ZIP-Inhalte geprüft; beide Layoutformate visuell geprüft. Öffentliches Repository angelegt, Quellcode und Release noch nicht veröffentlicht.
