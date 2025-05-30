// CardReader.h
#ifndef CARD_READER_H
#define CARD_READER_H

#include <SPI.h>
#include <MFRC522.h>

#define RFID_SS_PIN A0     // SDA -> Analog 0 (Digital 14)
#define RFID_RST_PIN A1    // RST -> Analog 1 (Digital 15)

class CardReader {
private:
    MFRC522 rfid;

public:
    CardReader() : rfid(RFID_SS_PIN, RFID_RST_PIN) {}

    void setup() {
        SPI.begin();         // Start SPI bus
        rfid.PCD_Init();     // Init RFID reader
        Serial.println("RFID reader ready. Tap a card...");
    }

    void checkCard() {
        if (!rfid.PICC_IsNewCardPresent()) return;
        if (!rfid.PICC_ReadCardSerial()) return;

        Serial.print("UID:");
        for (byte i = 0; i < rfid.uid.size; i++) {
            Serial.print(rfid.uid.uidByte[i] < 0x10 ? " 0" : " ");
            Serial.print(rfid.uid.uidByte[i], HEX);
        }
        Serial.println();

        rfid.PICC_HaltA();         // Halt communication
        rfid.PCD_StopCrypto1();    // Stop encryption
    }
};

#endif

