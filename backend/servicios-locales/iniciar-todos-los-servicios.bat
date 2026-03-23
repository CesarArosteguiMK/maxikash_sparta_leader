@echo off
chcp 65001 >nul
setlocal
title Sparta Ledger - Iniciar todos los servicios locales

:: Raiz backend = carpeta padre de servicios-locales
for %%I in ("%~dp0..") do set "BACKEND=%%~fI"

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0iniciar-todos-los-servicios.ps1" -BackendRoot "%BACKEND%"
if errorlevel 1 (
    echo.
    echo [ERROR] Fallo al ejecutar iniciar-todos-los-servicios.ps1
    pause
    exit /b 1
)

echo.
pause
