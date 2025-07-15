# 🚁 Elevator Control System with Lockout/Tagout Safety

## 🚀 Quick Setup for New Devices

### **EASIEST METHOD: Smart Setup**
1. **Start XAMPP** and make sure MySQL is running
2. **Open browser** and go to: `http://localhost/projectsite/Elevator/smart_setup.php`
3. **Click "Auto-Detect & Setup"** - the system will automatically find your MySQL configuration
4. **Login** with: Username: `Admin123`, Password: `Admin123!`

### **If You Get "Access Denied" Error**
This is the most common issue on new XAMPP installations. Here are your solutions:

#### **Option 1: Use the Diagnostic Tool**
- Go to: `http://localhost/projectsite/Elevator/diagnose_mysql.php`
- Click "Run Full Diagnostics"
- Follow the suggested solutions

#### **Option 2: Reset MySQL Password (Windows)**
1. Run `reset_mysql_password.bat` as Administrator
2. Restart XAMPP MySQL service
3. Try setup again

#### **Option 3: Manual MySQL Reset**
```bash
# Stop MySQL in XAMPP Control Panel
# Open Command Prompt as Administrator
cd C:\xampp\mysql\bin
mysqld --skip-grant-tables --skip-networking

# In another Command Prompt:
mysql -u root
UPDATE mysql.user SET Password=PASSWORD('') WHERE User='root';
FLUSH PRIVILEGES;
EXIT;

# Restart MySQL service in XAMPP
```

#### **Option 4: Use phpMyAdmin**
If phpMyAdmin works (http://localhost/phpmyadmin), you can create databases manually using the provided SQL files.

---

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

## 🔧 Troubleshooting Guide

### **Common Setup Issues**

#### **Issue: "Access denied for user 'root'@'localhost'"**
**This is the most common issue on new XAMPP installations.**

**Cause:** MySQL root user has a password or insufficient permissions.

**Solutions (try in order):**

1. **Smart Setup** - Use `smart_setup.php` which auto-detects configuration
2. **Diagnostic Tool** - Run `diagnose_mysql.php` for detailed analysis
3. **Password Reset** - Run `reset_mysql_password.bat` (Windows)
4. **Manual Reset** - Follow MySQL password reset procedure above

#### **Issue: "Can't connect to MySQL server"**
**Cause:** MySQL service not running

**Solution:**
1. Open XAMPP Control Panel
2. Click "Start" next to MySQL
3. Wait for green "Running" status
4. Try setup again

#### **Issue: "Database already exists" warnings**
**Cause:** Previous installation exists

**Solution:** 
- This is normal and safe
- All setup scripts are designed to preserve existing data
- Use `check_database_structure.php` to verify integrity

#### **Issue: "Permission denied" on file operations**
**Cause:** File/folder permissions

**Solution:**
1. Run XAMPP as Administrator (Windows)
2. Check htdocs folder permissions
3. Ensure PHP has write access to project directory

### **Database Connection Issues**

#### **Test Connection Methods:**
```php
// Method 1: Default XAMPP
$mysqli = new mysqli("localhost", "root", "");

// Method 2: With password
$mysqli = new mysqli("localhost", "root", "your_password");

// Method 3: IP address
$mysqli = new mysqli("127.0.0.1", "root", "");
```

#### **Verify MySQL Users:**
```sql
SELECT User, Host FROM mysql.user WHERE User='root';
SHOW GRANTS FOR 'root'@'localhost';
```

### **Development & Testing Issues**

#### **Issue: Real-time updates not working**
**Solution:**
1. Check browser console for JavaScript errors
2. Verify AJAX endpoints are accessible
3. Test API directly: `test_elevator_api.php`

#### **Issue: Lockout status not updating**
**Solution:**
1. Verify `elevator_lockout` table structure
2. Check `admin_lockout.php` for errors
3. Test lockout API endpoints

#### **Issue: User login fails**
**Solution:**
1. Verify user exists: Check `requests` table in `access_requests1`
2. Test with default admin: Admin123/Admin123!
3. Check password hashing compatibility

### **Emergency Recovery**

#### **If All Else Fails:**

1. **Fresh Database Setup:**
   ```sql
   DROP DATABASE IF EXISTS access_requests1;
   DROP DATABASE IF EXISTS elevator_lockout_db;
   DROP DATABASE IF EXISTS elevator;
   ```
   Then run `setup_all_databases.php`

2. **Reset XAMPP MySQL:**
   - Stop all XAMPP services
   - Delete `C:\xampp\mysql\data` folder
   - Copy `C:\xampp\mysql\backup` to `C:\xampp\mysql\data`
   - Restart XAMPP

3. **Alternative Setup Methods:**
   - Use phpMyAdmin to create databases manually
   - Import SQL files directly via command line
   - Use MySQL Workbench or other database tools

### **Getting Help**

#### **Diagnostic Files:**
- `diagnose_mysql.php` - MySQL connection testing
- `check_database_structure.php` - Database integrity check
- `debug_status.html` - System status overview

#### **Log Files to Check:**
- XAMPP Control Panel logs
- Apache error logs (`C:\xampp\apache\logs\error.log`)
- MySQL error logs (`C:\xampp\mysql\data\*.err`)

#### **Common Error Codes:**
- **1045**: Access denied (password issue)
- **2002**: Can't connect (service not running)
- **1049**: Unknown database (not created yet)
- **1146**: Table doesn't exist (setup incomplete)

---

*This system provides a complete, safe, and user-friendly elevator control solution with industry-standard lockout/tagout safety procedures.*
