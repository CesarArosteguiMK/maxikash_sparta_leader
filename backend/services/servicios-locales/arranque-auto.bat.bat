@echo off
:: ============================================================
::  Sparta Ledger - Arranque automático tras reinicio
::  Ejecutado por Task Scheduler al inicio del sistema
:: ============================================================

set LOG=C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\services\servicios-locales\arranque-auto.log
set BASEDIR=C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\services\servicios-locales

echo. >> "%LOG%"
echo ======================================== >> "%LOG%"
echo [%DATE% %TIME%] Reinicio detectado - iniciando secuencia >> "%LOG%"

:: --- 1) Esperar a que el sistema estabilice ---
echo [%DATE% %TIME%] Esperando 30s... >> "%LOG%"
timeout /t 30 /nobreak > nul

:: --- 2) Cierre preventivo de servicios anteriores ---
echo [%DATE% %TIME%] Ejecutando cierre preventivo... >> "%LOG%"
call "%BASEDIR%\cerrar-todos-los-servicios.bat" >> "%LOG%" 2>&1

timeout /t 5 /nobreak > nul

:: --- 3) Arranque de servicios ---
echo [%DATE% %TIME%] Ejecutando arranque de servicios... >> "%LOG%"
call "%BASEDIR%\iniciar-todos-los-servicios.bat" >> "%LOG%" 2>&1

echo [%DATE% %TIME%] Secuencia completada. >> "%LOG%"
exit /b 0