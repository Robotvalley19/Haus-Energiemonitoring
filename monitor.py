#!/usr/bin/env python3
import serial
import time
import json
import os
import re
from datetime import datetime

# --- Konfiguration ---
USB_PORT = "/dev/ttyUSB0"
DATA_FILE = "/home/basti/Energiemonitoring/data.json"
HISTORY_FILE = "/home/basti/Energiemonitoring/history.json"
LOG_FILE  = "/home/basti/Energiemonitoring/logs/error.log"
INTERVAL = 60  # Sekunden zwischen den Telegrammen
DEFAULT_VOLTAGE = 230  # angenommene Netzspannung in V
POWER_FACTOR = 0.95    # angenommener Leistungsfaktor (cos f)
MAX_HISTORY_ENTRIES = 1440  # z.B. 24h bei 1-Minuten-Intervall

# Ordner automatisch erstellen
os.makedirs(os.path.dirname(DATA_FILE), exist_ok=True)
os.makedirs(os.path.dirname(HISTORY_FILE), exist_ok=True)
os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)

def log_error(msg):
    with open(LOG_FILE, "a") as f:
        f.write(f"{datetime.now()} {msg}\n")

def extract_obis(code, telegram):
    """OBIS-Wert aus Telegramm extrahieren"""
    match = re.search(rf"{code}\(([\d\.]+)", telegram)
    return float(match.group(1)) if match else 0.0

def read_telegram():
    """Fuehrt Handshake aus und liest ein vollstaendiges Telegramm"""
    try:
        ser = serial.Serial(USB_PORT, 300, bytesize=7, parity='E', stopbits=1, timeout=2)
        ser.write(b"/?!\r\n")
        ser.read(100)
        ser.write(b"\x06050\r\n")
        ser.baudrate = 9600

        buffer = ""
        start_time = time.time()
        while True:
            byte = ser.read(1)
            if byte:
                buffer += byte.decode(errors="ignore")
                if "!\r\n" in buffer:
                    break
            if time.time() - start_time > 10:
                raise TimeoutError("Keine Daten vom Zaehler empfangen.")
        ser.close()
        return buffer.lstrip("\x00")
    except Exception as e:
        log_error(f"Fehler beim Lesen des Telegramms: {e}")
        return None

# --- Hilfsfunktion fuer Power und Current ---
def calculate_power_current(prev_data, new_grid_import, interval_sec):
    """Leistung (W) aus kWh-Differenz berechnen und Strom schaetzen"""
    delta_kwh = 0
    if prev_data:
        delta_kwh = new_grid_import - prev_data.get("grid_import", 0)
        if delta_kwh < 0:
            delta_kwh = 0  # Zaehler zurueckgesetzt
    delta_hours = interval_sec / 3600
    power_w = (delta_kwh / delta_hours) * 1000  # kWh -> W
    current_a = power_w / (DEFAULT_VOLTAGE * POWER_FACTOR)
    return power_w, current_a

def parse_obis_values(telegram, prev_data=None, interval_sec=INTERVAL):
    """OBIS-Werte extrahieren und Dictionary erstellen, inklusive Power/Current"""
    grid_import = extract_obis("1-0:1.8.0", telegram)
    grid_export = extract_obis("1-0:2.8.0", telegram)

    power, current = calculate_power_current(prev_data, grid_import, interval_sec)

    return {
        "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "voltage": DEFAULT_VOLTAGE,
        "current": round(current, 2),
        "power": round(power, 2),
        "grid_import": grid_import,
        "grid_export": grid_export,
        "grid_import_tarif1": extract_obis("1-0:1.8.1", telegram),
        "grid_import_tarif2": extract_obis("1-0:1.8.2", telegram),
        "grid_export_tarif1": extract_obis("1-0:2.8.1", telegram),
        "grid_export_tarif2": extract_obis("1-0:2.8.2", telegram)
    }

# --- Hauptschleife ---
print(f"[INFO] Energiemonitoring gestartet. Telegramme werden alle {INTERVAL} Sekunden gelesen.")

prev_data = None

# Vorherige Daten laden
if os.path.exists(DATA_FILE):
    with open(DATA_FILE, "r") as f:
        prev_data = json.load(f)

# Historie laden oder leeres Array erstellen
if os.path.exists(HISTORY_FILE):
    with open(HISTORY_FILE, "r") as f:
        history = json.load(f)
else:
    history = []

try:
    while True:
        telegram = read_telegram()
        if telegram:
            data = parse_obis_values(telegram, prev_data)
            # Aktuelle Werte speichern
            with open(DATA_FILE, "w") as f:
                json.dump(data, f, indent=2)
            print(f"[DATA] {datetime.now()} -> data.json aktualisiert")

            # Historie aktualisieren
            history.append(data)
            if len(history) > MAX_HISTORY_ENTRIES:
                history = history[-MAX_HISTORY_ENTRIES:]  # nur letzte N Eintraege behalten
            with open(HISTORY_FILE, "w") as f:
                json.dump(history, f, indent=2)
            print(f"[HISTORY] {datetime.now()} -> history.json aktualisiert")

            prev_data = data
        else:
            print("[WARN] Kein Telegramm empfangen.")
        time.sleep(INTERVAL)
except KeyboardInterrupt:
    print("[INFO] Beendet durch Benutzer")
