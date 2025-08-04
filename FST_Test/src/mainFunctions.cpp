#include "../include/pcanFunctions.h"
#include "../include/mainFunctions.h"

#include <stdio.h>
#include <stdlib.h>
#include <stdlib.h>
#include <unistd.h>
#include "../include/databaseFunctions.h"


int menu(){
	
	int usrchoice = 0;
	//system("@cls||clear");
	while(1) {
		printf("\n\nMenu - Transmit/Receive CAN Messages\n");
		printf("1. Transmit CAN message using this program\n");
		printf("2. Receive CAN message(s) using this program\n");
		printf("3. Control elevator from website\n");
		printf("4. Sabbath Mode - loop\n");
		printf("5. Run Height Diagnostics\n");
		printf("6. Exit program\n");
		printf("\nYour choice: ");
		scanf("%d", &usrchoice);

		if (usrchoice >=1 && usrchoice <= 6) {	
			return usrchoice;
		} else {
			printf("\nPLEASE SELECT FROM CHOICES 1-5 ONLY!\n\n");
			sleep(3);
			system("@cls||clear");
		}
	}
	
}


int chooseID(){

	int IdChoice = 0;		// Menu item number
	int IDvalue = 0 ;		// ID value in HEX
	while(1) {
		system("@cls||clear");
		printf("\nChoose sender and receiver for message\n");
		printf("1. Message from Supervisory controller (i.e. this node) to Elevator Controller\n");
		printf("2. Message from Elevator controller to all other nodes\n");
		printf("3. Message from Car controller to supervisory controller (this node)\n");
		printf("4. Message from floor 1 controller to supervisory controller (this node)\n");
		printf("5. Message from floor 2 controller to supervisory controller (this node)\n");
		printf("6. Message from floor 3 controller to supervisory controller (this node)\n");

		printf("\nYour choice: ");
		scanf("%d", &IdChoice);

		if (IdChoice >=1 && IdChoice <= 6) {	
			switch(IdChoice) {
				case 1:
					IDvalue = ID_SC_TO_EC; 
					return(IDvalue);
				case 2:
					IDvalue = ID_EC_TO_ALL; 
					return(IDvalue);
				case 3:
					IDvalue = ID_CC_TO_SC; 
					return(IDvalue);
				case 4:
					IDvalue = ID_F1_TO_SC; 
					return(IDvalue);
				case 5:
					IDvalue = ID_F2_TO_SC; 
					return(IDvalue);
				case 6:
					IDvalue = ID_F3_TO_SC; 
					return(IDvalue);
			}

		} else {
			printf("\nPLEASE SELECT FROM CHOICES 1-6 ONLY!\n\n");
			sleep(3);
		}

	}
}

int chooseMsg(){
	int messageChoice = 0; 
	int messageValue = 0;
	
	while(1) {
		system("@cls||clear");
		printf("\nChoose Message\n");
		printf("1. Go to floor 1\n");
		printf("2. Go to floor 2\n");
		printf("3. Go to floor 3\n");
		printf("\nYour choice: ");
		scanf("%d", &messageChoice);

		if (messageChoice >=1 && messageChoice <= 3) {	
			switch(messageChoice) {
				case 1:
					messageValue = GO_TO_FLOOR1; 
					return(messageValue);
					break;
				case 2:
					messageValue = GO_TO_FLOOR2; 
					return(messageValue);
					break;
				case 3:
					messageValue = GO_TO_FLOOR3; 
					return(messageValue);
					break;
			}

		} else {
			printf("PLEASE SELECT FROM CHOICES 1-3 ONLY!\n\n");
			sleep(3);
		}
	}
}


int HexFromFloor(int floorVal) {

	switch(floorVal) {
		case 1:
			return(GO_TO_FLOOR1);
			break;
		case 2:
			return(GO_TO_FLOOR2);
			break;
		case 3: 
			return(GO_TO_FLOOR3);
			break;
		default:
			return(GO_TO_FLOOR1);			// Default is to reset to floor 1 on bad input
		}
}

int FloorFromHex(int Hex){
		
	switch(Hex) {
		case GO_TO_FLOOR1:
			return(1);
			break;
		case GO_TO_FLOOR2:
			return(2);
			break;
		case GO_TO_FLOOR3:
			return(3);
			break;
		default:
			return(1);							// Default is to reset to floor 1 on bad input
		}
}

/*int diagnosticArrayFill(int diagnosticArray[3][30], int data, int floorNumber, int distance) {
				
	int floorCounters[3] = {0, 0, 0}; // for each floor, to keep track of how many distances have been recorded
	int floorIndex = 0; // to keep track of which floor we are currently recording distances for
	
	// create a cJSON object
   	cJSON *json = cJSON_CreateObject();

	// Synchronize elevator db and CAN (start at 1st floor)
	pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
	db_setFloorNum(1);

	for (int i = 0; i < 90; i++) 
	{
		if (i % 3 == 0)
		{
			pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
			db_setFloorNum(FloorFromHex(0x05));
			floorIndex = 0;
		}
		if (i % 3 == 1)
		{
			pcanTx(ID_SC_TO_EC, GO_TO_FLOOR2);
			db_setFloorNum(FloorFromHex(0x06));
			floorIndex = 1;
		}
		if (i % 3 == 2)
		{
			pcanTx(ID_SC_TO_EC, GO_TO_FLOOR3);
			db_setFloorNum(FloorFromHex(0x07));
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
		
		floorNumber = db_getFloorNum();
		//distance = db_getDistance();
		
		printf("Current floor number: %d, Distance: %d\n", floorNumber, data);

		sleep(1);
	
	}

	printf("\nDiagnostic Data:\n");
	for (int j = 0; j < 3; j++) {
		printf("Floor %d:\n",j+1);

		char floorKey[8];
		sprintf(floorKey, "floor%d", j + 1); // "floor1", "floor2", "floor3"
		cJSON *floorArray = cJSON_CreateIntArray(diagnosticArray[j], 30);
		cJSON_AddItemToObject(json, floorKey, floorArray);

		for (int k = 0; k < 30; k++) {

			if (diagnosticArray[j][k] != 0) { // Print only non-zero values
				printf("	Distance %d: %d\n",k + 1, diagnosticArray[j][k]);
			}
		}
	}

	// convert the cJSON object to a JSON string
	char *json_str = cJSON_Print(json);

	// write the JSON string to a file
	FILE *fp = fopen("/var/www/html/Elevator/Amys_Work/Elevator/json/diagnostics.json", "w");
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
	db_setFloorNum(1);

}*/

void sortArrayAscending(int arr[], int size) {
	for (int i = 0; i < size - 1; i++) {
		for (int j = 0; j < size - i - 1; j++) {
			if (arr[j] > arr[j + 1]) {
				// Swap arr[j] and arr[j + 1]
				int temp = arr[j];
				arr[j] = arr[j + 1];
				arr[j + 1] = temp;
			}
		}
	}
}

void sortArrayDescending(int arr[], int size) {
	for (int i = 0; i < size - 1; i++) {
		for (int j = 0; j < size - i - 1; j++) {
			if (arr[j] < arr[j + 1]) {
				// Swap arr[j] and arr[j + 1]
				int temp = arr[j];
				arr[j] = arr[j + 1];
				arr[j + 1] = temp;
			}
		}
	}
}
