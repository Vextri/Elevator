/*!
 * @file ElevatorControl_OOP.ino
 * @brief Michael Galle's Elevator Controller API - Object Oriented Version
 * @copyright Michael Galle
 * @author [Michael Galle]
 * references: https://paulmurraycbr.github.io/ArduinoTheOOWay.html, http://arduinoetcetera.blogspot.com/2011/01/classes-within-classes-initialiser.html
 * @version V1.1
 * REQUIRED: LIBRARY FROM ARDUINO LIBRARY MANAGER: mcp_can by coryjfowler (You can see examples in File > Examples > mcp_can)
 * REQUIRED: LIBRARY LiquidCrystal.zip --- To install slect:  Sketch -> Include Library -> Add .ZIP Library (add .zip)         
 */

#include <SoftwareSerial.h>
#include <DFRobotDFPlayerMini.h>

#include "ElevatorController.h"
#include "CardReader.h"


SoftwareSerial mySoftwareSerial(10, 11); // RX, TX
DFRobotDFPlayerMini myDFPlayer;

ElevatorController EC;                                     // Instantiate an Elevator controller object

void setup() { 
  EC.setup(); 
  CR.setup();  // Initialize card reader
  attachInterrupt(digitalPinToInterrupt(INT_PIN), CAN_MSGRCVD_ISR, FALLING);      // Interrupt on falling edge of INT_PIN and call CAN_MSGRCVD() method of the ElevatorControl object  
  // Do not need to attach anything to the timer-based interrupt. It will automatically call ISR(TIMER1_COMPA_vect) when triggered. It is on a register external to the microcontroller.
  //start the virtual serial port for the DFPlayer Mini
  mySoftwareSerial.begin(9600);
  if (!myDFPlayer.begin(mySoftwareSerial)) {
    Serial.println("Unable to begin DFPlayer Mini:");
    while(true);
  }
  myDFPlayer.volume(10); // Set volume (0~30)
}

// For playing the audio files corresponding to the floor number
void playFloorAudio(byte floor) {
    switch(floor) {
        case FLOOR1:
            myDFPlayer.play("floor1.mp3"); // Plays 0001.mp3
            break;
        case FLOOR2:
            myDFPlayer.play("floor2.mp3"); // Plays 0002.mp3
            break;
        case FLOOR3:
            myDFPlayer.play("floor3.mp3"); // Plays 0003.mp3
            break;
        default:
            break;
    }
}

// Timer-based Interrupt routine for timer1 (occurs at 0.25 Hz 0r 4 seconds) - This ISR is called when the timer-based interrupt is triggered and is exteral to the ElevatorController Object
ISR(TIMER1_COMPA_vect) {
    EC.flagTx = true;
}

// When message is received and the INT_PIN is triggered LOW, the interrupt calls this function
void CAN_MSGRCVD_ISR() {
    EC.flagRecv = true;                                                           // Set received flag to true - dealt with inside the loop
}

void loop() 
{
  EC.loop();
  CR.checkCard();  // Poll RFID reader
}
