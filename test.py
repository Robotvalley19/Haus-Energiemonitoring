import serial
import time

ser = serial.Serial("/dev/ttyUSB0",300,bytesize=7,parity='E',stopbits=1,timeout=2)

ser.write(b"/?!\r\n")

time.sleep(1)

print(ser.read(100))

ser.write(b"\x06050\r\n")

time.sleep(1)

ser.baudrate = 9600

buffer = ""

while True:
    data = ser.read(1).decode(errors="ignore")
    if not data:
        continue

    buffer += data

    if "!\r\n" in buffer:
        print("Telegramm:")
        print(buffer)
        break
