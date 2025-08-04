#include <stdio.h>
#include <stdlib.h>
#ifdef _WIN32
    #include <windows.h>
    #define sleep(x) Sleep((x) * 1000)  // Convert seconds to milliseconds
#else
    #include <unistd.h>
#endif
#include <iostream>
#include <algorithm>
#include <ctime>
#include <cstring>

// ******************************************************************


#include "../include/pcanFunctions.h"
#include "../include/databaseFunctions.h"
#include "../include/mainFunctions.h"
#include "../include/cJSON/cJSON.h"

using namespace std;

#define MAX_NUM_FLOORREQUESTS 10

// To play an MP3 file on Raspberry Pi (Linux)
void playMP3(const char* filename) {
    char command[256];
    sprintf(command, "mpg123 \"%s\" &", filename); // or use mpg321
    system(command);
}

// Function to sort array: non-zero values first, then zeros (using STL)
void sortNonZeroFirst(int arr[], int size) {
    std::sort(arr, arr + size, [](int a, int b) {
        if (a == 0 && b != 0) return false;  // b comes before a
        if (a != 0 && b == 0) return true;   // a comes before b
        return a < b;  // If both non-zero or both zero, sort by value
    });
}

// ******************************************************************
int main()
{
    int choice; 
	int ID; 
	int data; 
	int numRx;
	int floorNumber = 1, prev_floorNumber = 1;

    int floorRequests[MAX_NUM_FLOORREQUESTS] = {0}; // Array to store floor requests
	int temp[90] = {0}; // Temporary array to hold floor requests
    int index = 0;
	

	int distance = 0;
	int priority = 0; // for FIFO of floor requests

					
	// Add current date and time to JSON
	time_t now = time(0);
	char* dt = ctime(&now);

	
	int *message;

	int diagnosticArray[3][30] = {0}; // 3 rows for each floor, 30 columns for 30 distances each floor
	int floorCounters[3] = {0, 0, 0}; // for each floor, to keep track of how many distances have been recorded
	int floorIndex = 0; // to keep track of which floor we are currently recording distances for

	// create a cJSON object
	cJSON *json = cJSON_CreateObject();
	char *json_str;
	cJSON *floorArray;
	char floorKey[8];

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
				db_setFloorNum(1, FloorFromHex(data)); 		// change floor number in database ** NEW **
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
				db_setFloorNum(1, 1); //loop from 0 to 89
				prev_floorNumber = 1;
				while (true) {
					
					sleep(5);
					// Step 1: Get requests from DB
					db_getAllFloorRequests(floorRequests);

					// Sort array to put non-zero values first
					sortNonZeroFirst(floorRequests, MAX_NUM_FLOORREQUESTS);

					for (int i = 0; i < MAX_NUM_FLOORREQUESTS; i++) {
						if (floorRequests[i] != 0 && floorRequests[i] != prev_floorNumber)
						{
							pcanTx(ID_SC_TO_EC, HexFromFloor(floorRequests[i]));
							db_setFloorNum(1, floorRequests[i]); // Update current floor number in DB
							sleep(3);
							
						}
						else {
							printf("No floor requests\n");
							
						}
						floorRequests[i] = 0; // Reset the array after processing
						prev_floorNumber = db_getFloorNum(1); // Update current floor number from DB

					}
				}

				break;
				
			case 4:
				
				printf("\nDemo Mode - loop from floor to floor - press ctrl-z to cancel\n");
				while(1) {
					pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
					db_setFloorNum(1, 1);
					sleep(20);
					pcanTx(ID_SC_TO_EC, GO_TO_FLOOR2);
					db_setFloorNum(1, 2);
					sleep(20);
					pcanTx(ID_SC_TO_EC, GO_TO_FLOOR3);
					db_setFloorNum(1, 3);
					sleep(20);
				}
				break;
			case 5:

				// Synchronize elevator db and CAN (start at 1st floor)
				pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
				db_setFloorNum(1, 1);

				for (int i = 0; i < 90; i++) 
				{
					if (i % 3 == 0)
					{
						pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
						db_setFloorNum(1, FloorFromHex(0x05));
						floorIndex = 0;
					}
					if (i % 3 == 1)
					{
						pcanTx(ID_SC_TO_EC, GO_TO_FLOOR2);
						db_setFloorNum(1, FloorFromHex(0x06));
						floorIndex = 1;
					}
					if (i % 3 == 2)
					{
						pcanTx(ID_SC_TO_EC, GO_TO_FLOOR3);
						db_setFloorNum(1, FloorFromHex(0x07));
						floorIndex = 2;
					}
					
					sleep(4);


					data = pCanRx_db();
					printf("Received data: %d\n", data);
					

					if (data == 0x05 || data == 0x06 || data == 0x07) {
					} else {
						if (floorCounters[floorIndex] < 30) { // Prevent overflow
							diagnosticArray[floorIndex][floorCounters[floorIndex]] = data;
							floorCounters[floorIndex]++;
						}
					}
					
					floorNumber = db_getFloorNum(1);
					//distance = db_getDistance();
					
					printf("Current floor number: %d, Distance: %d\n", floorNumber, data);

					sleep(1);
				
				}

				printf("\nDiagnostic Data:\n");

				// Remove newline from ctime string
				dt[strlen(dt) - 1] = '\0';
				cJSON_AddStringToObject(json, "timestamp", dt);
				
				for (int j = 0; j < 3; j++) {
					printf("Floor %d:\n",j+1);
					sprintf(floorKey, "floor%d", j + 1); // "floor1", "floor2", "floor3"
					floorArray = cJSON_CreateIntArray(diagnosticArray[j], 30);
					cJSON_AddItemToObject(json, floorKey, floorArray);

					for (int k = 0; k < 30; k++) {

						/*if (diagnosticArray[j][k] != 0) { // Print only non-zero values
							printf("	Distance %d: %d\n",k + 1, diagnosticArray[j][k]);
						}*/
					}
				}
			

				// convert the cJSON object to a JSON string
				json_str = cJSON_Print(json);

				// write the JSON string to a file
				fp = fopen("/var/www/html/Elevator/Amys_Work/Elevator/json/diagnostics.json", "w");
				if (fp == NULL) {
					printf("Error: Unable to open the file.\n");
					return 1;
				}
				printf("%s\n", json_str);
				fputs(json_str, fp);
				fclose(fp);

				// free the JSON string and cJSON object
				cJSON_free(json_str);
				cJSON_Delete(json);


				// Send elevator back to floor 1 after diagnostic test
				pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
				db_setFloorNum(1, 1);

				break;
			case 6:
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
