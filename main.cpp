#include "../include/pcanFunctions.h"
#include "../include/databaseFunctions.h"
#include "../include/mainFunctions.h"

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h> 
#include <iostream>

using namespace std;


// ******************************************************************

int main() {

	int choice; 
	int ID = 0x100;
	int data; 
	int numRx;
	int floorNumber = 1, prev_floorNumber = 1;
	int i = 0;

	while(i < 20) {
		printf("Awaiting Input...\n\n");
		data = pcanRx(1);
		pcanTx(ID, data);		// transmit ID and data 
		sleep(1);					// delay between send/receive
		i++;
	}
	
	return(0);
}






	
