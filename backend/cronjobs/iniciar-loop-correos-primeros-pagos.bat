@echo off
chcp 65001 >nul
setlocal

set "CRON_DIR=%~dp0"
if "%CRON_DIR:~-1%"=="\" set "CRON_DIR=%CRON_DIR:~0,-1%"
cd /d "%CRON_DIR%"

set "PHP_EXE="
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not defined PHP_EXE if exist "C:\Program Files\PHP\php.exe" set "PHP_EXE=C:\Program Files\PHP\php.exe"

if not defined PHP_EXE (
    for /f "delims=" %%i in ('where php 2^>nul') do (
        set "PHP_EXE=%%i"
        goto :havephp
    )
)
:havephp

if not defined PHP_EXE (
    echo [ERROR] No se encontro php.exe. Use XAMPP o anada PHP al PATH.
    pause
    exit /b 1
)

echo [loop] Usando PHP: %PHP_EXE%
echo [loop] Mientras esta ventana este abierta, cada ~10 min se ejecuta el cron (si "Auto horario" esta activo). Horarios CDMX en PHP.
echo [loop] Para parar: cierre esta ventana, o ejecute cerrar-loop-correos-primeros-pagos.bat
echo.

"%PHP_EXE%" "%CRON_DIR%\loop_enviar_primeros_pagos_lunes.php"
if errorlevel 1 (
    echo [ERROR] El bucle termino con error.
    pause
    exit /b 1
)
pause
