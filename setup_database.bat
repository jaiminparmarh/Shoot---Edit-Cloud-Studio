@echo off
echo Starting EditX Studio Database Setup...
echo.

REM Check if XAMPP is installed
if exist "C:\xampp\mysql\bin\mysql.exe" (
    echo Found XAMPP MySQL
    set MYSQL_PATH="C:\xampp\mysql\bin\mysql.exe"
) else if exist "C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe" (
    echo Found WAMP MySQL
    set MYSQL_PATH="C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe"
) else (
    echo MySQL not found in common locations
    echo Please install XAMPP or WAMP first
    pause
    exit /b 1
)

REM Check if MySQL service is running
echo Checking MySQL service...
sc query mysql 2>nul | find "RUNNING" >nul
if %errorlevel% neq 0 (
    echo MySQL service is not running
    echo Please start MySQL from XAMPP/WAMP control panel
    echo Then run this script again
    pause
    exit /b 1
)

echo MySQL service is running
echo Creating database...

REM Create database and tables
%MYSQL_PATH% -u root < database_setup.sql

if %errorlevel% equ 0 (
    echo.
    echo ✅ Database setup completed successfully!
    echo.
    echo Admin Login Details:
    echo Username: admin
    echo Password: admin123
    echo.
    echo You can now access:
    echo - Homepage: http://localhost/editing project/
    echo - Admin Panel: http://localhost/editing project/login.php
) else (
    echo.
    echo ❌ Database setup failed
    echo Please check MySQL connection and try again
)

echo.
pause
