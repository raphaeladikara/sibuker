@echo off
setlocal
title SIBUKER - Dashboard Analitik
cd /d "%~dp0"

rem Menjalankan dashboard Analitik (/admin/analitik) yang membaca data warehouse sibuker_dw.
rem Pakai:  start-dashboard.bat            jalankan dashboard (sibuker_dw diimpor kalau belum ada)
rem         start-dashboard.bat muat-ulang  timpa sibuker_dw dengan isi database\dw\sibuker_dw.sql
rem Port 8001 dipakai supaya bisa jalan bersamaan dengan start-web.bat di port 8000.

set "PORT=8001"
set "URL=http://127.0.0.1:%PORT%/admin/analitik"
set "DW_NAME=sibuker_dw"
set "DW_DUMP=database\dw\sibuker_dw.sql"

call scripts\siapkan.bat
if errorlevel 1 goto :gagal

echo.
echo === Data warehouse %DW_NAME% ===
set "ADA_DW=1"
%MYSQL% -N -e "SELECT 1 FROM fakta_lamaran LIMIT 1" %DW_NAME% >nul 2>nul
if errorlevel 1 set "ADA_DW=0"

if /i "%~1"=="muat-ulang" set "ADA_DW=0"
if "%ADA_DW%"=="1" (
    echo %DW_NAME% sudah ada, dipakai apa adanya.
    goto :dw_siap
)
if not exist "%DW_DUMP%" (
    echo [ERROR] %DW_DUMP% tidak ditemukan dan %DW_NAME% belum ada.
    goto :gagal
)
echo Mengimpor %DW_DUMP% ke %DW_NAME%...
%MYSQL% -e "DROP DATABASE IF EXISTS %DW_NAME%; CREATE DATABASE %DW_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 goto :gagal
%MYSQL% %DW_NAME% < "%DW_DUMP%"
if errorlevel 1 goto :gagal
echo Impor selesai.
:dw_siap

echo.
echo === Menjalankan dashboard ===
echo.
echo   Buka %URL%   (tutup jendela ini untuk menghentikan server)
echo   Masuk sebagai admin@sibuker.test, kata sandi: password
echo.

start "" "%URL%"
call php artisan serve --host=127.0.0.1 --port=%PORT%
goto :eof

:gagal
echo.
echo Setup gagal. Lihat pesan di atas.
pause
exit /b 1
