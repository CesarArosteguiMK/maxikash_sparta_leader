@echo off
chcp 65001 >nul
setlocal
title Sparta Ledger - Detener todos los servicios locales

for %%I in ("%~dp0..\..") do set "BACKEND=%%~fI"

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0cerrar-todos-los-servicios.ps1" -BackendRoot "%BACKEND%"
if errorlevel 1 (
    echo [ERROR] Fallo al ejecutar cerrar-todos-los-servicios.ps1
    pause
    exit /b 1
)

pause
