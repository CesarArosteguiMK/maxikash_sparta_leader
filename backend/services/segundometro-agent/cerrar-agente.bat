@echo off
chcp 65001 >nul
setlocal

set "AGENT_DIR=%~dp0"
if "%AGENT_DIR:~-1%"=="\" set "AGENT_DIR=%AGENT_DIR:~0,-1%"

powershell -NoProfile -ExecutionPolicy Bypass -File "%AGENT_DIR%\cerrar-agente.ps1"
if errorlevel 1 (
    echo [ERROR] No se pudo ejecutar cerrar-agente.ps1.
    pause
    exit /b 1
)

echo.
pause
