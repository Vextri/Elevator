@echo off
echo ============================================
echo XAMPP MySQL Password Reset Tool
echo ============================================
echo.
echo This script will reset your MySQL root password to blank (default XAMPP)
echo.
echo IMPORTANT: This will stop MySQL service temporarily
echo Make sure you don't have any critical MySQL operations running
echo.
pause

echo.
echo Step 1: Stopping XAMPP MySQL service...
taskkill /f /im mysqld.exe 2>nul
net stop mysql 2>nul

echo Step 2: Waiting for service to stop...
timeout /t 3

echo Step 3: Starting MySQL in safe mode...
start /b "" "C:\xampp\mysql\bin\mysqld.exe" --skip-grant-tables --skip-networking

echo Step 4: Waiting for MySQL to start...
timeout /t 5

echo Step 5: Resetting root password...
echo UPDATE mysql.user SET Password=PASSWORD('') WHERE User='root'; > reset.sql
echo UPDATE mysql.user SET authentication_string='' WHERE User='root'; >> reset.sql
echo FLUSH PRIVILEGES; >> reset.sql

"C:\xampp\mysql\bin\mysql.exe" -u root < reset.sql

echo Step 6: Stopping safe mode MySQL...
taskkill /f /im mysqld.exe 2>nul

echo Step 7: Cleaning up...
del reset.sql

echo.
echo ============================================
echo Password reset complete!
echo ============================================
echo.
echo Now start MySQL normally through XAMPP Control Panel
echo The root password should now be blank (no password)
echo.
pause
