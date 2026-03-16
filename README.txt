# Haus-Energiemonitoring

**Haus-Energiemonitoring** ist ein komplettes, **lokales Energiemonitoring-System für Privathaushalte**, mit dem du den Stromverbrauch, die Einspeisung und die Leistung von Geräten live überwachen und analysieren kannst. Das Projekt ist **vollständig lokal**, verwendet **keine externen Ressourcen** und ist **abmahnsicher**. Ideal für DIY-Projekte, private Haushalte, Bildungszwecke oder als Referenz für Bewerbungen im Bereich Smart Home, IoT oder Energie-Management.

---

## 📌 Features

- **Live-Messung:** Spannung, Strom, Leistung, Netzbezug, Einspeisung
- **Historische Auswertung:** Diagramme für Tag, Woche, Monat, Jahr
- **Geräteverwaltung:** CSV-basierte Übersicht mit Jahresverbrauch-Berechnung
- **Lokale Ressourcen:** Fonts und Chart.js lokal, keine externen Links
- **Responsive Frontend:** Desktop, Tablet, mobil
- **Automatische Aktualisierung:** Live-Daten ohne Seiten-Reload
- **Erweiterbar:** Mehrere Zähler, Solaranlagen oder Batteriespeicher integrierbar

---

## 🏗 Projektstruktur

Haus-Energiemonitoring/
├─ fonts/                 # Lokale Schriftarten (Orbitron)
│  ├─ Orbitron-Regular.ttf
│  └─ Orbitron-Bold.ttf
├─ js/                    # Lokale JavaScript-Bibliotheken
│  └─ chart.min.js
├─ index.php              # Haupt-Frontend-Datei
├─ geraete.csv            # Geräteübersicht / Beispiel
├─ data.json              # Aktuelle Messwerte
├─ history.json           # Historische Messwerte
├─ monitor.py             # Python-Skript für Stromzähler-Auslesung
└─ README.md              # Dieses Dokument

---

## 🛠 Installation

1. Repository klonen:

```bash
git clone https://github.com/dein-benutzername/Haus-Energiemonitoring.git


Ordnerstruktur prüfen (siehe oben).

Beispiel-Gerätedatei geraete.csv prüfen oder eigene Geräte eintragen.

PHP-Server starten (XAMPP, MAMP, LAMP, Raspberry Pi) oder auf deinem Webserver bereitstellen.

Python-Skript für Live-Daten starten (siehe Nutzung unten).

Tipp: Auf einem Raspberry Pi kann das Projekt 24/7 autark laufen.



⚡ Python-Skript (monitor.py)

Das Skript liest die Daten vom Stromzähler (z. B. Smart Meter via USB) aus und erstellt die JSON-Dateien für das Frontend:

data.json → aktuelle Messwerte (Spannung, Strom, Leistung, Netzbezug, Einspeisung)

history.json → historische Messwerte für Diagramme

Berechnet Leistung (W) und Strom (A) aus kWh-Differenzen

Läuft automatisch in Intervallen (z. B. jede Minute)


Beispiel-Ausführung:

python3 monitor.py



📊 Geräteübersicht

Die Geräteübersicht wird aus geraete.csv gelesen.

Spalte	Beschreibung
Geraet	Name des Geräts
Anzahl	Anzahl identischer Geräte
Leistung_W	Leistung pro Gerät in Watt
Std_Tag	Betriebsstunden pro Tag
Tage_Woche	Betriebstage pro Woche
Wochen_Jahr	Wochen pro Jahr, an denen das Gerät läuft
Jahresverbrauch_kWh	Automatisch berechneter Jahresverbrauch

Eine Beispiel-Datei geraete.csv ist enthalten.



💻 Frontend

Technologien: PHP, HTML, CSS, JS

Design: Karten für Messwerte, interaktive Diagramme, Hintergrundanimation

Diagramme: Tages-, Wochen-, Monats- und Jahresansicht auswählbar

Live-Update: alle 10 Sekunden aktualisierte Messwerte

Lokale Fonts: /fonts/Orbitron-Regular.ttf, /fonts/Orbitron-Bold.ttf

Lokale Chart.js Bibliothek: /js/chart.min.js

Abmahnsicher: alles lokal, keine externen Ressourcen



🔧 Voraussetzungen

PHP 7+

Python 3

USB-Zugang zu Stromzähler oder kompatiblem Smart Meter

Lokaler Webserver oder Raspberry Pi



👨‍💻 Nutzung

Python-Skript starten:

python3 monitor.py

PHP-Frontend aufrufen:

http://localhost/index.php

Messwerte werden automatisch angezeigt, Diagramme interaktiv wählbar.



🌱 Erweiterungsmöglichkeiten

Mehrere Stromzähler unterstützen

Solar- oder Batteriespeicher integrieren

Alarm bei Überschreitung bestimmter Verbrauchswerte

Automatische Statistikberichte (E-Mail, CSV)

Mobile App Interface (PWA)



📝 Lizenz

MIT License – frei nutzbar, auch kommerziell.
Siehe LICENSE
 für Details.#   H a u s - E n e r g i e m o n i t o r i n g 
 
 
