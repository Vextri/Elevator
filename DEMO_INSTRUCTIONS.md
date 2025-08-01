# Quick Setup Guide for Database Deliverables Demo

## 1. Run Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "SQL" tab
3. Copy and paste the contents of `sql/elevator_network_database.sql`
4. Click "Go" to execute

## 2. Test the Implementation
1. Login to your elevator system
2. Go to Dashboard
3. Click "🏢 Elevator Network Management"
4. You should see the members.php page with sample data

## 3. Demo Each Deliverable

### [4 marks] Database Structure ✅
- Show the tables in phpMyAdmin:
  - `elevatorNetwork` (parent table with nodeID primary key)
  - `canComponents` (child table with foreign key to nodeID)
- Point out unique keys (ipAddress, canAddress) and indexes (status, nodeType)

### [8 marks] Update Function ✅
- Edit any network node (click Edit button)
- Change some values and click "Update Node (Transaction)"
- Show it prevents primary key updates by trying to modify nodeID

### [4 marks] Transaction Function ✅
- The update uses transactions automatically
- Try entering invalid data (like floor = 15) to see exception handling
- Show it rolls back on errors

### [4 marks] Insert/Display ✅
- Add a new network node using the form
- See it appear in the table immediately

### [8 marks] Modify/Delete ✅
- Edit existing records (shows modify functionality)
- Delete a record (with confirmation popup)
- See child CAN components get deleted automatically (foreign key cascade)

## 4. Quick Screenshots to Take
1. phpMyAdmin showing the two tables with relationships
2. members.php page showing the data
3. Adding a new record
4. Editing a record
5. The deliverables summary at the bottom of the page

## 5. What to Highlight
- Real elevator-related data (not generic examples)
- Foreign key relationships working
- Transaction error handling
- Professional interface integrated with your existing system
- All 28 marks worth of functionality clearly labeled
