

// ******************************************************************
#include "../include/pcanFunctions.h"
#include "../include/databaseFunctions.h"
#include "../include/mainFunctions.h"

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h> 
#include <iostream>

using namespace std;

// To play an MP3 file on Raspberry Pi (Linux)
void playMP3(const char* filename) {
    char command[256];
    sprintf(command, "mpg123 \"%s\" &", filename); // or use mpg321
    system(command);
}


// ******************************************************************

int main() {

	int choice; 
	int ID; 
	int data; 
	int numRx;
	int floorNumber = 1, prev_floorNumber = 1;

	

	int distance = 0;

	
	
	int *message;

	int diagnosticArray[3][30] = {0}; // 3 rows for each floor, 30 columns for 30 distances each floor
	int floorCounters[3] = {0, 0, 0}; // for each floor, to keep track of how many distances have been recorded
	int floorIndex = 0; // to keep track of which floor we are currently recording distances for

	FILE *fp; // file pointer for writing JSON data to file
	


	while(1) {
		//system("@cls||clear");
		choice = menu(); 
		switch (choice) {
			case 1: 
				ID = chooseID();		// user to select ID depending on intended recipient
				data = chooseMsg();		// user to select message data
				printf("\nTransmitting message with ID: %x and data: %d\n", ID, data);
				pcanTx(ID, data);		// transmit ID and data 
				db_setFloorNum(FloorFromHex(data)); 		// change floor number in database ** NEW **
				break; 
				
			case 2:
				printf("\nHow many messages to receive?");
				scanf("%d", &numRx);
				pcanRx(numRx);
				break;
				
			case 3:
				printf("\nNow listening to commands from the website - press ctrl-z to cancel\n");
				// Synchronize elevator db and CAN (start at 1st floor)
				pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
				db_setFloorNum(1);
				
				while(1){			
					floorNumber = db_getFloorNum();
					distance = db_getDistance();
					if (prev_floorNumber != floorNumber) {								// If floor number changes in database
						pcanTx(ID_SC_TO_EC, HexFromFloor(floorNumber));					// change floor number in elevator - send command over CAN
										
					
						switch (floorNumber) {												// play floor announcement
							case 1:
								playMP3("../audio/Floor1.mp3");
								break;
							case 2:
								playMP3("../audio/Floor2.mp3");
								break;
							case 3:
								playMP3("../audio/Floor3.mp3");
								break;
							default:
								break;
						}
						
							
					}

					prev_floorNumber = floorNumber; 
					printf("Current floor number: %d, Distance: %d\n", floorNumber, distance);
					sleep(1);															// poll database once every second to check for change in floor number
				}
				break;
				
			case 4:
				
				printf("\nDemo Mode - loop from floor to floor - press ctrl-z to cancel\n");
				while(1) {
					pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
					db_setFloorNum(1);
					sleep(20);
					pcanTx(ID_SC_TO_EC, GO_TO_FLOOR2);
					db_setFloorNum(2);
					sleep(20);
					pcanTx(ID_SC_TO_EC, GO_TO_FLOOR3);
					db_setFloorNum(3);
					sleep(20);
				}
				break;
			case 5:
				printf("\nNow listening to commands from the website - press ctrl-z to cancel\n");
				// Synchronize elevator db and CAN (start at 1st floor)
				pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
				db_setFloorNum(1);

				while (1)
				{
					data = pCanRx_db();
					printf("Received data: %d\n", data);

					if (data == 5 || data == 6 || data == 7) {
						db_setFloorNum(FloorFromHex(data));
					} else {
						db_setDistance(data);
							
					}

					sleep(1);
					
					floorNumber = db_getFloorNum();
					distance = db_getDistance();
					
					printf("Current floor number: %d, Distance: %d\n", floorNumber, distance);
				}
				
				break;
			case 6:

				printf("In development - not implemented yet\n");

				break;
			case 7:
				printf("\nExiting program...\n");
				return(0);
				break;
			default:
				printf("Error on input values");
				sleep(3);
				break;
		}
		sleep(1);					// delay between send/receive
	}
	
	return(0);
}






	
