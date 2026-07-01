@echo off
chcp 65001 >nul
setlocal

set "CRON_DIR=%~dp0"
if "%CRON_DIR:~-1%"=="\" set "CRON_DIR=%CRON_DIR:~0,-1%"

for %%I in ("%CRON_DIR%\..") do set "BACKEND_DIR=%%~fI"
set "STATE_DIR=%BACKEND_DIR%\storage\runtime\primeros_pagos"
set "FLAG=%STATE_DIR%\loop_primeros_pagos_stop.flag"
if not exist "%STATE_DIR%" mkdir "%STATE_DIR%" 2>nul
echo. > "%FLAG%"

echo [OK] Se solicito la parada del bucle (si estaba en marcha, se detendra en el siguiente ciclo ~90s).
echo      Si sigue corriendo, cierre la ventana del loop o finalice php.exe en Administrador de tareas.
ping 127.0.0.1 -n 3 >nul
