@echo off
chcp 65001 >nul
setlocal
title Sparta Ledger - Iniciar todos los servicios locales

:: Raiz backend = dos niveles arriba (services\servicios-locales -> backend)
for %%I in ("%~dp0..\..") do set "BACKEND=%%~fI"

echo.
echo  Incluye API verificacion documentos (puerto 8000) + agentes Node (3001, 3100, 3110, 3120).
echo  Carpeta backend: %BACKEND%
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0iniciar-todos-los-servicios.ps1" -BackendRoot "%BACKEND%"
if errorlevel 1 (
    echo.
    echo [ERROR] Fallo al ejecutar iniciar-todos-los-servicios.ps1
    pause
    exit /b 1
)

echo.
pause
