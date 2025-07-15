# 🚁 Elevator Control System with Lockout/Tagout Safety

## 📋 Project Overview

This is a **web-based elevator control system** with comprehensive safety features, built using PHP, MySQL, JavaScript, and CSS. The system provides real-time elevator control, user authentication, access management, and critical **lockout/tagout (LOTO)** safety functionality for maintenance and emergency situations.

---

## 🏗️ System Architecture

### 📊 **Database Structure**

#### **Primary Databases:**
1. **`access_requests1`** - User Management & Authentication
2. **`elevator_lockout_db`** - Lockout/Tagout Safety System
3. **`elevator`** - Elevator Movement & Status

#### **Database Details:**

**1. `access_requests1` Database:**
```sql
Table: requests
- id (Primary Key)
- username 
- email
- password (hashed)
- student_card
- reason
- created_at
- approved
```
*Purpose: Stores all user accounts, handles login authentication*

**2. `elevator_lockout_db` Database:**
```sql
Table: elevator_lockout
- id (Primary Key)
- elevator_id (Default: 1)
- is_locked_out (BOOLEAN)
- locked_by_user_id
- locked_by_username
- lockout_reason (TEXT)
- lockout_timestamp
- unlock_timestamp
- created_at
```
*Purpose: Manages elevator lockout status and maintains complete audit trail*

**3. `elevator` Database:**
```sql
Table: elevatorNetwork
- nodeID
- currentFloor
```
*Purpose: Tracks actual elevator position and movement*

---

## 🔐 Authentication System

### **How We Connect to `access_requests1` Database:**

```php
// Connection to user database
$user_mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");

// User verification
$user_check = $user_mysqli->prepare("SELECT username, email FROM requests WHERE id = ?");
$user_check->bind_param("i", $_SESSION['user_id']);
```

### **Authentication Flow:**
1. **Login Process** (`login1.php`) - Validates credentials against `requests` table
2. **Session Management** - Stores `user_id` and `username` in PHP sessions
3. **Access Control** - All protected pages check `$_SESSION['user_id']`
4. **Card Login** (`check_card_login.php`) - Alternative authentication via student cards

### **User Types:**
- **Any Logged-in User** - Can access elevator controls AND lockout controls
- **No Admin Hierarchy** - Simplified security model for educational environment

---

## 🔒 Lockout/Tagout (LOTO) System

### **How Lockout/Tagout Works:**

#### **1. Safety Philosophy:**
- **Lock Out** = Disable all elevator movement for safety during maintenance
- **Tag Out** = Document who locked it out, when, and why
- **Audit Trail** = Complete history of all lockout/unlock events

#### **2. Technical Implementation:**

**Lockout Process:**
```php
// When user clicks "LOCKOUT ELEVATOR"
INSERT INTO elevator_lockout (
    elevator_id, is_locked_out, locked_by_user_id, 
    locked_by_username, lockout_reason
) VALUES (1, TRUE, ?, ?, ?)
```

**Movement Blocking:**
```php
// API checks lockout BEFORE any movement
$lockout_status = check_lockout_status();
if ($lockout_status['is_locked_out']) {
    // BLOCK all elevator commands
    exit('Elevator locked out');
}
```

#### **3. Real-Time Integration:**

**Frontend Polling:**
```javascript
// Every 2 seconds, check status
setInterval(function() {
    fetch('test_elevator_api.php')
    .then(response => response.json())
    .then(data => {
        if (data.is_locked_out) {
            // Disable all buttons, show warning
            disableElevatorControls();
        }
    });
}, 2000);
```

**Multi-Interface Updates:**
- `index.php` - Main inside elevator interface
- `outside.php` - External call buttons  
- `test_elevator.html` - Testing interface
- All show lockout status and disable controls when locked out

---

## 🛠️ Setup & Database Management

### **Setup Files Created:**

#### **1. `setup_lockout_db.php` - One-Click Setup**
```php
// Automated setup with GUI
- Creates elevator_lockout_db database
- Creates elevator_lockout table
- Inserts initial data (unlocked state)
- Provides verification and status
- Links to next steps
```

#### **2. `sql/simple_setup.sql` - Manual SQL Setup**
```sql
-- Clean, minimal setup script
USE elevator_lockout_db;
CREATE TABLE elevator_lockout (...);
INSERT INTO elevator_lockout VALUES (1, FALSE, 'System initialized');
```

#### **3. `check_database_structure.php` - Database Inspector**
```php
// Shows current database state
- Lists all tables in access_requests1
- Shows table structures
- Verifies user data
- Debugging tool
```

### **Complete System Setup (New Devices):**

#### **4. `setup_all_databases.php` - Complete One-Click Setup**
```php
// Sets up ALL three databases needed for the system
- Creates access_requests1 database (user management)
- Creates elevator_lockout_db database (safety system)  
- Creates elevator database (movement control)
- Creates all tables and initial data
- Sets up proper user permissions
- Provides verification and next steps
- Default admin user: Admin123/Admin123!
```

#### **5. Manual Setup Scripts:**
- `sql/setup_access_requests.sql` - User management database
- `sql/simple_setup.sql` - Lockout safety database
- (elevator database setup included in complete setup)

#### **6. For New Device Setup:**
1. **Copy project files** to new device
2. **Install XAMPP** and start Apache/MySQL
3. **Run** `setup_all_databases.php` 
4. **Login** with Admin123/Admin123!
5. **Test** all system components

### **Setup Process:**
1. **Complete Setup** - `setup_all_databases.php` (recommended for new devices)
2. **Individual Setup** - Use separate scripts if needed
3. **Verify Tables** - Use check scripts to confirm
4. **Test System** - Login and test all functionality

---

## 📁 File Structure & Components

### **Core System Files:**

#### **Authentication & User Management:**
- `login1.php` - Manual login form and processing
- `check_card_login.php` - Card-based authentication
- `register_user.php` - New user registration
- `request_access.php` - Access request form
- `approve_user.php` - Admin approval interface
- `logout.php` - Session cleanup

#### **Elevator Control Interfaces:**
- `index.php` - Main inside elevator interface
- `outside.php` - External call buttons
- `test_elevator.html` - Testing/debug interface
- `test_elevator_api.php` - Core API for movement commands

#### **Lockout/Tagout System:**
- `admin_lockout.php` - Lockout control panel
- Integrated into all elevator interfaces
- Real-time status checking and control disabling

#### **Setup & Maintenance:**
- `setup_lockout_db.php` - Automated database setup
- `check_database_structure.php` - Database inspector
- `sql/simple_setup.sql` - Manual setup script

---

## 🔄 Data Flow & System Integration

### **Complete User Journey:**

1. **Registration** → `register_user.php` → Store in `access_requests1.requests`
2. **Login** → `login1.php` → Validate against `requests` table → Create session
3. **Elevator Control** → `index.php` → Check lockout status → Allow/deny movement
4. **Lockout Control** → `admin_lockout.php` → Update `elevator_lockout` table
5. **Real-time Updates** → JavaScript polling → Update all interfaces

### **Safety Integration:**

```
User Action → Lockout Check → Movement Command → Database Update → UI Refresh
     ↓              ↓              ↓              ↓              ↓
  Button Click → API Call → MySQL Query → Log Entry → Visual Update
```

---

## 🚀 Key Features

### **✅ Implemented & Working:**

1. **🔐 User Authentication**
   - Manual login with password hashing
   - Card-based authentication
   - Session management
   - Access request/approval workflow

2. **🚁 Elevator Control**
   - Real-time floor movement
   - Multiple control interfaces
   - Visual elevator animation
   - Connection status monitoring

3. **🔒 Lockout/Tagout Safety**
   - One-click lockout/unlock
   - Required reason documentation
   - Complete audit trail with timestamps
   - Real-time status across all interfaces
   - Movement blocking when locked out

4. **📊 System Monitoring**
   - Database structure checking
   - Setup verification tools
   - Error handling and recovery
   - Comprehensive logging

### **🎯 System Benefits:**

- **Safety First** - Lockout prevents accidents during maintenance
- **Simple Security** - Any user can access lockout controls (appropriate for educational setting)
- **Complete Audit** - Every lockout/unlock is logged with user, time, and reason
- **Real-time Updates** - All interfaces reflect current status instantly
- **Easy Setup** - One-click database setup with verification
- **Robust Design** - Handles errors, connection issues, and recovery

---

## 🔧 Configuration & Credentials

### **Database Connections:**
```php
// User Management
$user_mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");

// Lockout System
$mysqli = new mysqli("localhost", "root", "", "elevator_lockout_db");

// Elevator Movement
$db = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
```

### **File Permissions:**
- PHP files: Web server readable
- SQL files: Database admin access required
- Setup files: Temporary use, can be removed after setup

---

## 📚 Usage Instructions

### **For System Administrators:**

1. **Initial Setup:**
   - Run `setup_lockout_db.php` for automated setup
   - OR run `sql/simple_setup.sql` manually in phpMyAdmin
   - Verify with `check_database_structure.php`

2. **User Management:**
   - Users register via `request_access.php`
   - Approve users via `approve_user.php`
   - Monitor access via database inspection tools

3. **System Monitoring:**
   - Check lockout status in `admin_lockout.php`
   - Review lockout history for maintenance records
   - Use database check tools for troubleshooting

### **For End Users:**

1. **Access System:**
   - Login via `login1.php` or card reader
   - Access elevator controls via `index.php` or `outside.php`

2. **Normal Operation:**
   - Use floor buttons when elevator is operational
   - Watch for real-time status updates

3. **Lockout Control:**
   - Any logged-in user can access `admin_lockout.php`
   - Enter detailed reason for lockout
   - System will block all movement and show status across interfaces
   - Unlock when maintenance is complete

---

## 🛡️ Security Features

- **Password Hashing** - All passwords securely hashed with PHP password functions
- **SQL Injection Prevention** - Prepared statements throughout
- **Session Security** - Proper session management and cleanup
- **Access Control** - Login required for all elevator functions
- **Audit Logging** - Complete history of all lockout actions
- **Error Handling** - Graceful degradation and error reporting

---

## 🔄 Maintenance & Troubleshooting

### **Common Tasks:**
- **Reset Lockout** - Use unlock button in admin panel
- **Check User Access** - Review `access_requests1.requests` table
- **Verify Connections** - Use database structure check tools
- **Review Logs** - Check lockout history in admin panel

### **Troubleshooting Tools:**
- `check_database_structure.php` - Database connectivity and structure
- `setup_lockout_db.php` - Re-run setup if needed
- Error messages in all interfaces for debugging

---

## 📈 Future Enhancements

- **Email Notifications** - Alert on lockout events
- **Scheduled Maintenance** - Automated lockout scheduling
- **Mobile Interface** - Responsive design for tablets/phones
- **Advanced Reporting** - Usage statistics and maintenance reports
- **Multiple Elevators** - Support for elevator arrays

---

*This system provides a complete, safe, and user-friendly elevator control solution with industry-standard lockout/tagout safety procedures.*
