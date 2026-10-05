# FAQ

[Anleitung](Deutsch.md) · [English](FAQ-English.md)

## Daten und Betrieb

**Wer pflegt Produkte und Preise?** Ausschließlich Shopware. WordPress speichert Auswahl und Darstellung. Keine lokale Preisberechnung.

**Was bedeutet 30 Minuten Cache?** Daten werden bis zu 30 Minuten wiederverwendet. Erst der nächste Zugriff nach Ablauf fragt neu ab; kein periodischer Hintergrundabruf. Seiten-/CDN-Caches können sichtbare Preise länger behalten.

**Was passiert bei Shop-Ausfall?** Vorhandener Inhalt kann vorübergehend ohne alte Preise und Verfügbarkeit erscheinen. Fehlen Daten vollständig, gibt es keine Produktkarte.

## Bricks

**Brauche ich Components?** Nein. Die nativen Elemente funktionieren sofort. Components sind freiwillige wiederverwendbare Gestaltungen.

**Wie nutze ich die Produktsuche?** Im Strukturbaum den Slot und das darin liegende native Shopware-Element auswählen.

**Warum ist eine Component leer?** Ein neuer Slot enthält noch kein Element. Befüllte Beispiele installieren oder selbst ein natives Element hineinziehen. Passende Produkte benötigen Artikelzuordnungen.

**Überschreibt ein Update meine Gestaltung?** Nein. Vorhandene Definitionen/Beispiele werden erhalten; zusätzliche Versionskopien sind optional.

## Installation

**Wie wechsel ich vom alten Namen?** Altplugin deaktivieren, neues ZIP installieren und aktivieren. Danach Altplugin löschen; die Daten bleiben erhalten.

**Sind GitHub-Updates schon verfügbar?** Erst nach Veröffentlichung des Repositorys und eines passenden Releases. Derzeit dient das lokale ZIP als Update.

## Shop-Zuordnung und Schlüssel

**Reicht der Key?** Nein. Auch die Shop-URL einstellen. Die optionale API-URL berücksichtigt eine andere API-Basisadresse.

**Wird .env automatisch gelesen?** Nein. Das Hosting muss DW_SW_ACCESS_KEY an PHP übergeben, oder wp-config.php definiert die Konstante. Eine ausdrücklich eingebundene externe Geheimnisdatei kann diese ebenfalls definieren.

**Mehrere Shops gleichzeitig?** Ein konfigurierter Shop pro WordPress-Installation. Verschiedene Installationen können verschiedene Shops verwenden.
