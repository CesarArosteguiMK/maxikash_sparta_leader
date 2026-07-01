@echo off
set LOG_DIR=%TEMP%\sparta___SPARTA_SECRET_REDACTED___servicios_locales_logs
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%" >nul 2>&1
set LOG=%LOG_DIR%\arranque-auto.log
set BASEDIR=C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\services\servicios-locales

:: Archivo temporal único para esta ejecución
set ENTERFILE=%TEMP%\sparta_enter_%RANDOM%.txt
echo. > "%ENTERFILE%"

echo. >> "%LOG%"
echo ======================================== >> "%LOG%"
echo [%DATE% %TIME%] Reinicio detectado - iniciando secuencia >> "%LOG%"

echo [%DATE% %TIME%] Esperando 45s para que el sistema estabilice... >> "%LOG%"
timeout /t 45 /nobreak > nul

:: Cierre preventivo
echo [%DATE% %TIME%] Ejecutando cierre preventivo... >> "%LOG%"
call "%BASEDIR%\cerrar-todos-los-servicios.bat" < "%ENTERFILE%" >> "%LOG%" 2>&1

timeout /t 5 /nobreak > nul

:: Arranque de servicios
echo [%DATE% %TIME%] Ejecutando arranque de servicios... >> "%LOG%"
call "%BASEDIR%\iniciar-todos-los-servicios.bat" < "%ENTERFILE%" >> "%LOG%" 2>&1

:: Limpiar temporal
del "%ENTERFILE%" >nul 2>&1

echo [%DATE% %TIME%] Secuencia completada. >> "%LOG%"
exit /b 0