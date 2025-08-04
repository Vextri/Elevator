// Includes required (headers located in /usr/include) 
#include "../include/databaseFunctions.h"
#include <stdlib.h>
#include <iostream>
#include <mysql_connection.h>
#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>
 
using namespace std; 
 
int db_getFloorNum(int nodeID) {
    sql::Driver *driver;
    sql::Connection *con;
    sql::PreparedStatement *pstmt;
    sql::ResultSet *res;
    int floorNum = -1;  // Default/fallback value

    // Connect to the database
    driver = get_driver_instance();
    con = driver->connect("tcp://127.0.0.1:3306", "ese", "ese");
    con->setSchema("elevator");

    // Use a prepared statement instead of Statement
    pstmt = con->prepareStatement("SELECT currentFloor FROM elevatorNetwork WHERE nodeID = ?");
    pstmt->setInt(1, nodeID);
    res = pstmt->executeQuery();

    // Fetch the floor number (only one row expected)
    if (res->next()) {
        floorNum = res->getInt("currentFloor");
    }

    // Clean up
    delete res;
    delete pstmt;
    delete con;

    return floorNum;
}

int db_setFloorNum(int nodeID, int floorNum) {
	sql::Driver *driver; 				// Create a pointer to a MySQL driver object
	sql::Connection *con; 				// Create a pointer to a database connection object
	sql::Statement *stmt;				// Create a pointer to a Statement object to hold statements 
	sql::PreparedStatement *pstmt; 		// Create a pointer to a prepared statement	

	// Create a connection 
	driver = get_driver_instance();
	con = driver->connect("tcp://127.0.0.1:3306", "ese", "ese");	
	con->setSchema("elevator");										

	// Insert into database
	// *****************************
	pstmt = con->prepareStatement("INSERT INTO elevatorNetwork (nodeID, currentFloor) VALUES (?, ?) ON DUPLICATE KEY UPDATE currentFloor = ?");
	pstmt->setInt(1, nodeID);
	pstmt->setInt(2, floorNum);
	pstmt->setInt(3, floorNum); // Update currentFloor if it already exists
	pstmt->executeUpdate();
		
	// Clean up pointers 
	delete pstmt;
	delete con;

	return 0; // Return success indicator
}


int db_getDistance() {
	sql::Driver *driver; 			// Create a pointer to a MySQL driver object
	sql::Connection *con; 			// Create a pointer to a database connection object
	sql::Statement *stmt;			// Crealte a pointer to a Statement object to hold statements 
	sql::ResultSet *res;			// Create a pointer to a ResultSet object to hold results 
	int distance;					// Floor number 
	
	// Create a connection 
	driver = get_driver_instance();
	con = driver->connect("tcp://127.0.0.1:3306", "ese", "ese");	
	con->setSchema("elevator");		
	
	// Query database
	// ***************************** 
	stmt = con->createStatement();
	res = stmt->executeQuery("SELECT distance FROM elevatorNetwork WHERE nodeID = 1");	// message query
	while(res->next()){
		distance = res->getInt("distance");
	}
	
	// Clean up pointers 
	delete res;
	delete stmt;
	delete con;
	
	return distance;
}
 
 
int db_setDistance(int distance) {
	sql::Driver *driver; 				// Create a pointer to a MySQL driver object
	sql::Connection *con; 				// Create a pointer to a database connection object
	sql::Statement *stmt;				// Crealte a pointer to a Statement object to hold statements 
	sql::ResultSet *res;				// Create a pointer to a ResultSet object to hold results 
	sql::PreparedStatement *pstmt; 		// Create a pointer to a prepared statement	
	
	// Create a connection 
	driver = get_driver_instance();
	con = driver->connect("tcp://127.0.0.1:3306", "ese", "ese");	
	con->setSchema("elevator");										
	
	// Query database (possibly not necessary)
	// ***************************** 
	stmt = con->createStatement();
	res = stmt->executeQuery("SELECT distance FROM elevatorNetwork WHERE nodeID = 1");	// message query
	while(res->next()){
		res->getInt("distance");
	}
		
	// Update database
	// *****************************
	pstmt = con->prepareStatement("UPDATE elevatorNetwork SET distance = ? WHERE nodeID = 1");
	pstmt->setInt(1, distance);
	pstmt->executeUpdate();
		
	// Clean up pointers 
	delete res;
	delete pstmt;
	delete stmt;
	delete con;

	return 0;
} 

void db_getAllFloorRequests(int floorRequests[10]) {
    sql::Driver *driver;
    sql::Connection *con;
    sql::PreparedStatement *pstmt;
    sql::ResultSet *res;

    driver = get_driver_instance();
    con = driver->connect("tcp://127.0.0.1:3306", "ese", "ese");
    con->setSchema("elevator");

    pstmt = con->prepareStatement("SELECT requestedFloor FROM elevatorNetwork ORDER BY nodeID ASC LIMIT 90");
    res = pstmt->executeQuery();

    int i = 0;
    while (res->next() && i < 90) {
        floorRequests[i++] = res->getInt("requestedFloor");
    }

    // Fill remaining with a default value (e.g., -1)
    for (; i < 10; ++i) {
        floorRequests[i] = -1;
    }

    delete res;
    delete pstmt;
    delete con;
}

int db_updateFloorRequests(int floorRequests[10], int count) {
    sql::Driver *driver;
    sql::Connection *con;
    sql::PreparedStatement *pstmt;
    int affectedRows = 0;

    try {
        driver = get_driver_instance();
        con = driver->connect("tcp://127.0.0.1:3306", "ese", "ese");
        con->setSchema("elevator");

        pstmt = con->prepareStatement(
            "INSERT INTO elevatorNetwork (nodeID, requestedFloor) VALUES (?, ?)"
            " ON DUPLICATE KEY UPDATE requestedFloor = VALUES(requestedFloor)"
        );

        for (int i = 0; i < count; i++) {
            int nodeID = i + 1; // nodeID starts at 1
            int floor = floorRequests[i];

            pstmt->setInt(1, nodeID);
            pstmt->setInt(2, floor);

            affectedRows += pstmt->executeUpdate();
        }

        delete pstmt;
        delete con;
    } catch (sql::SQLException &e) {
        std::cerr << "Error updating floor requests: " << e.what() << std::endl;
        return -1;
    }

    return affectedRows;
}

