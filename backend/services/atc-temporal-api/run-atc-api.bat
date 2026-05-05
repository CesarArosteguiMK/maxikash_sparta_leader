@echo off
setlocal
set PORT=8097
echo Iniciando API temporal ATC en http://127.0.0.1:%PORT%
c:\xampp\php\php.exe -S 127.0.0.1:%PORT% "c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\services\atc-temporal-api\server.php"

