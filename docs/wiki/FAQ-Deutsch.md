# Fragen nach Themen

[Anleitung](Deutsch.md) · [English](FAQ-English.md)

## Installation und Updates

**Wie installiere ich das Plugin?** Das Plugin-ZIP aus dem aktuellen GitHub-Release unter Plugins → Installieren → Plugin hochladen installieren und aktivieren.

**Wie erhalte ich Updates?** Stabile Releases erscheinen im normalen WordPress-Updatesystem. Automatische Updates bleiben deine Entscheidung.

## Daten und Betrieb

**Wer pflegt Produkte und Preise?** Ausschließlich Shopware. WordPress speichert Auswahl und Darstellung. Keine lokale Preisberechnung.

**Was bedeutet 30 Minuten Cache?** Daten werden bis zu 30 Minuten wiederverwendet. Erst der nächste Zugriff nach Ablauf fragt neu ab; kein periodischer Hintergrundabruf. Seiten-/CDN-Caches können sichtbare Preise länger behalten.

**Was passiert bei Shop-Ausfall?** Vorhandener Inhalt kann vorübergehend ohne alte Preise und Verfügbarkeit erscheinen. Fehlen Daten vollständig, gibt es keine Produktkarte.

## Shop-Zuordnung und Schlüssel

**Reicht der Key?** Nein. Auch die Shop-URL einstellen. Die optionale API-URL berücksichtigt eine andere API-Basisadresse.

**Wird .env automatisch gelesen?** Nein. Das Hosting muss DW_SW_ACCESS_KEY an PHP übergeben, oder wp-config.php definiert die Konstante. Eine ausdrücklich eingebundene externe Geheimnisdatei kann diese ebenfalls definieren.

**Mehrere Shops gleichzeitig?** Ein konfigurierter Shop pro WordPress-Installation. Verschiedene Installationen können verschiedene Shops verwenden.

## Bricks

**Brauche ich Components?** Nein. Die nativen Elemente funktionieren sofort. Components sind freiwillige wiederverwendbare Gestaltungen.

**Wie nutze ich die Produktsuche?** Im Strukturbaum den Slot und das darin liegende native Shopware-Element auswählen.

**Warum ist eine Component leer?** Ein neuer Slot enthält noch kein Element. Befüllte Beispiele installieren oder selbst ein natives Element hineinziehen. Passende Produkte benötigen Artikelzuordnungen.

**Überschreibt ein Update meine Gestaltung?** Nein. Vorhandene Definitionen/Beispiele werden erhalten; zusätzliche Versionskopien sind optional.


## Berechtigungen und Multisite

**Wer darf die Verbindung ändern?** Administratoren mit manage_options. Autoren können Produkte auswählen, wenn sie redaktionelle Berechtigungen haben. Bricks Components benötigen zusätzlich die passenden Bricks-Berechtigungen.

**Kann jede Netzwerk-Website einen anderen Shop verwenden?** Ja. Verbindung, Cache und Artikelzuordnungen sind websitebezogen. Die Library wird je Netzwerk konfiguriert.

## Daten und Deinstallation

**Werden Artikelzuordnungen bei Deinstallation entfernt?** Nein. Shop-Einstellungen, Produktzuordnungen und installierte Components bleiben erhalten; temporäre Connector-Daten werden bereinigt.

**Was geschieht mit der gemeinsamen Library?** Andere installierte Hosts, auch deaktivierte, erhalten ihre gemeinsam benötigten Daten. Beim letzten Host gelten die Library-Einstellungen zur Datenlöschung.
