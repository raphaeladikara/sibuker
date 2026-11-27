@echo off
setlocal enabledelayedexpansion
title SIBUKER
cd /d "%~dp0"

rem Pakai:  start.bat          jalankan aplikasi (setup otomatis pada run pertama)
rem         start.bat reset    kosongkan database lalu isi ulang data demo

set "LARAGON_MYSQL=C:\laragon\bin\mysql\mysql-8.4.3-winx64"
set "DB_NAME=sibuker_pt"
set "URL=http://127.0.0.1:8000"

where php >nul 2>nul || (echo [ERROR] PHP tidak ditemukan di PATH. & goto :gagal)
where composer >nul 2>nul || (echo [ERROR] Composer tidak ditemukan di PATH. & goto :gagal)

echo.
echo === [1/6] MySQL ===
"%LARAGON_MYSQL%\bin\mysqladmin.exe" -uroot ping >nul 2>nul
if not errorlevel 1 goto :mysql_siap
echo MySQL belum jalan, menyalakan...
start "MySQL SIBUKER" /min "%LARAGON_MYSQL%\bin\mysqld.exe" --defaults-file="%LARAGON_MYSQL%\my.ini" --console
set /a tunggu=0
:tunggu_mysql
ping -n 2 127.0.0.1 >nul
"%LARAGON_MYSQL%\bin\mysqladmin.exe" -uroot ping >nul 2>nul
if not errorlevel 1 goto :mysql_siap
set /a tunggu+=1
if !tunggu! lss 40 goto :tunggu_mysql
echo [ERROR] MySQL tidak mau menyala. Coba buka Laragon lalu klik Start All.
goto :gagal
:mysql_siap
echo MySQL siap.

echo.
echo === [2/6] Dependensi PHP ===
if not exist vendor\autoload.php (
    call composer install --no-interaction
    if errorlevel 1 goto :gagal
) else (
    echo vendor sudah ada.
)

echo.
echo === [3/6] File .env ===
if not exist .env (
    copy /y .env.example .env >nul
    call php artisan key:generate --ansi
    if errorlevel 1 goto :gagal
) else (
    echo .env sudah ada.
)

echo.
echo === [4/6] Database ===
"%LARAGON_MYSQL%\bin\mysql.exe" -uroot -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 goto :gagal

set "ADA_TABEL=1"
"%LARAGON_MYSQL%\bin\mysql.exe" -uroot -N -e "SELECT 1 FROM pengguna LIMIT 1" %DB_NAME% >nul 2>nul
if errorlevel 1 set "ADA_TABEL=0"

if /i "%~1"=="reset" (
    echo Reset database dan isi ulang data demo...
    call php artisan migrate:fresh --seed --force
    if errorlevel 1 goto :gagal
) else if "!ADA_TABEL!"=="0" (
    echo Run pertama: membuat tabel dan mengisi data demo...
    call php artisan migrate --seed --force
    if errorlevel 1 goto :gagal
) else (
    call php artisan migrate --force
    if errorlevel 1 goto :gagal
)

echo.
echo === [5/6] Storage link ===
if not exist public\storage (
    call php artisan storage:link
) else (
    echo sudah ada.
)

echo.
echo === [6/6] Menjalankan server ===
echo.
echo   Buka %URL%   (tutup jendela ini untuk menghentikan server)
echo   Kata sandi semua akun: password
echo.
echo   Pencari kerja ........ dimas@mail.test
echo   Pencari kerja ........ salsa@mail.test
echo   Pencari kerja ........ yohanes@mail.test
echo   Perusahaan ........... hr@nusantaradata.test
echo   Perusahaan ........... rekrut@arunika.test
echo   Verifikator .......... hendra@lsp-inf.test
echo   Pencari + verifikator  maya@praktisi.test
echo   Administrator ........ admin@sibuker.test
echo.

start "" "%URL%"
call php artisan serve --host=127.0.0.1 --port=8000
goto :eof

:gagal
echo.
echo Setup gagal. Lihat pesan di atas.
pause
exit /b 1
