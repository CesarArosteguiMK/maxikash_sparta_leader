@echo off
chcp 65001 >nul
for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
cd /d "%API_DIR%"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0cerrar-agente.ps1"
pause
