@echo off
chcp 65001 >nul
title Crear acceso directo
cd /d "%~dp0"

set "BAT_PATH=%~dp0Iniciar-API-Verificacion.bat"
for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0crear_shortcut.ps1" -BatPath "%BAT_PATH%" -ApiDir "%API_DIR%"

if %errorlevel% equ 0 (
    echo.
    echo  Acceso directo creado en el Escritorio.
    echo.
) else (
    echo.
    echo  No se pudo crear el acceso directo.
    echo.
)
pause
