@echo off
rem Setup bersama untuk start-web.bat dan start-dashboard.bat. Jangan dijalankan langsung.
rem Dipanggil dengan:  call scripts\siapkan.bat [reset]
rem Mengisi variabel LARAGON_MYSQL, MYSQL, MYSQL_PORT, dan DB_NAME untuk dipakai pemanggil.
rem Port MySQL bisa diganti, misalnya kalau 3306 sedang dipakai MySQL XAMPP:
rem     set MYSQL_PORT=3307
rem     start-web.bat

set "LARAGON_MYSQL=C:\laragon\bin\mysql\mysql-8.4.3-winx64"
set "DB_NAME=sibuker_pt"
if not defined MYSQL_PORT set "MYSQL_PORT=3306"
rem Laravel mengutamakan variabel lingkungan di atas .env, jadi aplikasi ikut port yang sama.
set "DB_PORT=%MYSQL_PORT%"
set MYSQL="%LARAGON_MYSQL%\bin\mysql.exe" -uroot -h127.0.0.1 -P%MYSQL_PORT%
set MYSQLADMIN="%LARAGON_MYSQL%\bin\mysqladmin.exe" -uroot -h127.0.0.1 -P%MYSQL_PORT%

where php >nul 2>nul || (echo [ERROR] PHP tidak ditemukan di PATH. & exit /b 1)
where composer >nul 2>nul || (echo [ERROR] Composer tidak ditemukan di PATH. & exit /b 1)

echo.
echo === [1/5] MySQL (port %MYSQL_PORT%) ===
%MYSQLADMIN% ping >nul 2>nul
if not errorlevel 1 goto :mysql_siap
echo MySQL belum jalan, menyalakan...
start "MySQL SIBUKER" /min "%LARAGON_MYSQL%\bin\mysqld.exe" --defaults-file="%LARAGON_MYSQL%\my.ini" --port=%MYSQL_PORT% --console
set /a tunggu=0
:tunggu_mysql
ping -n 2 127.0.0.1 >nul
%MYSQLADMIN% ping >nul 2>nul
if not errorlevel 1 goto :mysql_siap
set /a tunggu+=1
if %tunggu% lss 40 goto :tunggu_mysql
echo [ERROR] MySQL tidak mau menyala. Coba buka Laragon lalu klik Start All.
exit /b 1

:mysql_siap
rem MariaDB XAMPP juga menjawab ping di port 3306, padahal skema SIBUKER butuh MySQL 8.4.
%MYSQL% -N -e "SELECT VERSION()" | findstr /i mariadb >nul
if errorlevel 1 goto :bukan_mariadb
echo [ERROR] Port %MYSQL_PORT% dipakai MariaDB (MySQL XAMPP), bukan MySQL Laragon.
echo         Matikan MySQL di XAMPP Control Panel, atau pakai port lain:
echo             set MYSQL_PORT=3307
echo         lalu jalankan skrip ini lagi dari jendela yang sama.
exit /b 1
:bukan_mariadb
echo MySQL siap.

echo.
echo === [2/5] Dependensi PHP ===
if not exist vendor\autoload.php (
    call composer install --no-interaction
    if errorlevel 1 exit /b 1
) else (
    echo vendor sudah ada.
)

echo.
echo === [3/5] File .env ===
if not exist .env (
    copy /y .env.example .env >nul
    call php artisan key:generate --ansi
    if errorlevel 1 exit /b 1
) else (
    echo .env sudah ada.
)

echo.
echo === [4/5] Database aplikasi %DB_NAME% ===
%MYSQL% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 exit /b 1

set "ADA_TABEL=1"
%MYSQL% -N -e "SELECT 1 FROM pengguna LIMIT 1" %DB_NAME% >nul 2>nul
if errorlevel 1 set "ADA_TABEL=0"

if /i "%~1"=="reset" (
    echo Reset database dan isi ulang data demo...
    call php artisan migrate:fresh --seed --force
    if errorlevel 1 exit /b 1
) else if "%ADA_TABEL%"=="0" (
    echo Run pertama: membuat tabel dan mengisi data demo...
    call php artisan migrate --seed --force
    if errorlevel 1 exit /b 1
) else (
    call php artisan migrate --force
    if errorlevel 1 exit /b 1
)

echo.
echo === [5/5] Storage link ===
if not exist public\storage (
    call php artisan storage:link
) else (
    echo sudah ada.
)
exit /b 0
