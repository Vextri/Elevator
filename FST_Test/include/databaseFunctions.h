#ifndef DB_FUNCTIONS_H
#define DB_FUNCTIONS_H

#ifdef __cplusplus
extern "C" {
#endif

int db_getFloorNum(int nodeID);
int db_setFloorNum(int nodeID, int floorNum);
int db_getDistance();
int db_setDistance(int distance);
void db_getAllFloorRequests(int floorRequests[90]);
int db_updateFloorRequests(int floorRequests[90], int count);

#ifdef __cplusplus
}
#endif

#endif // DB_FUNCTIONS_H
