@echo off
chcp 65001 >nul
setlocal

set "AGENT_DIR=%~dp0"
if "%AGENT_DIR:~-1%"=="\" set "AGENT_DIR=%AGENT_DIR:~0,-1%"

set "PIDFILE=%AGENT_DIR%\correos_agent.pid"
if not exist "%PIDFILE%" (
    echo No hay archivo correos_agent.pid (el agente no esta en marcha o ya se detuvo).
    ping 127.0.0.1 -n 3 >nul
    exit /b 0
)

for /f "usebackq delims=" %%a in ("%PIDFILE%") do set "PID=%%a"
if not defined PID (
    echo PID invalido.
    ping 127.0.0.1 -n 3 >nul
    exit /b 1
)

echo Deteniendo proceso PID %PID% ...
taskkill /PID %PID% /F >nul 2>&1
if errorlevel 1 (
    echo No se pudo finalizar el proceso (ya estaba cerrado^).
) else (
    echo [OK] Agente detenido.
)
del "%PIDFILE%" 2>nul
ping 127.0.0.1 -n 3 >nul
