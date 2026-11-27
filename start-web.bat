@echo off
setlocal
title SIBUKER - Web
cd /d "%~dp0"

rem Menjalankan aplikasi SIBUKER (pencari kerja, perusahaan, verifikator, admin).
rem Pakai:  start-web.bat          jalankan aplikasi (setup otomatis pada run pertama)
rem         start-web.bat reset    kosongkan database aplikasi lalu isi ulang data demo

set "PORT=8000"
set "URL=http://127.0.0.1:%PORT%"

call scripts\siapkan.bat %1
if errorlevel 1 goto :gagal

echo.
echo === Menjalankan web SIBUKER ===
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
call php artisan serve --host=127.0.0.1 --port=%PORT%
goto :eof

:gagal
echo.
echo Setup gagal. Lihat pesan di atas.
pause
exit /b 1
