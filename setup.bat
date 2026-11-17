@echo off
title Setup Otomatis Proyek Geprek GLO
echo ========================================================
echo   SETUP OTOMATIS PROYEK WEBSITE UMKM GEPREK GLO
echo ========================================================
echo.

if not exist .env (
    echo [1/4] Membuat file .env dari .env.example...
    copy .env.example .env >nul
) else (
    echo [1/4] File .env sudah ada.
)

echo [2/4] Menghasilkan Application Key...
call php artisan key:generate

echo [3/4] Menghubungkan penyimpanan foto (storage link)...
call php artisan storage:link

echo [4/4] Membuat database geprek_glo & mengisi data menu otomatis...
call php artisan migrate --seed --force

echo.
echo ========================================================
echo   SETUP SELESAI! APLIKASI SIAP DIJALANKAN.
echo ========================================================
echo   Website Publik : http://127.0.0.1:8000
echo   Panel Admin    : http://127.0.0.1:8000/admin
echo   Username Admin : admin
echo   Password Admin : admin123
echo ========================================================
echo.
echo Menjalankan web server lokal...
call php artisan serve
