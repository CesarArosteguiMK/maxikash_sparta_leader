@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

set "AGENT_DIR=%~dp0"
if "%AGENT_DIR:~-1%"=="\" set "AGENT_DIR=%AGENT_DIR:~0,-1%"
cd /d "%AGENT_DIR%"

set "NODE_EXE="
if exist "C:\Program Files\nodejs\node.exe" set "NODE_EXE=C:\Program Files\nodejs\node.exe"
if not defined NODE_EXE if exist "C:\Program Files (x86)\nodejs\node.exe" set "NODE_EXE=C:\Program Files (x86)\nodejs\node.exe"
if not defined NODE_EXE if exist "%LocalAppData%\Programs\node\node.exe" set "NODE_EXE=%LocalAppData%\Programs\node\node.exe"

if not defined NODE_EXE (
    echo [ERROR] No se encontro node.exe. Instale Node.js LTS desde https://nodejs.org
    pause
    exit /b 1
)

if not exist "%AGENT_DIR%\package.json" (
    echo [ERROR] No esta package.json aqui.
    pause
    exit /b 1
)

if not exist "%AGENT_DIR%\node_modules" (
    echo [ERROR] No hay node_modules. Ejecute primero instalar-agente.bat
    pause
    exit /b 1
)

:: Puerto por defecto DOC_PORT=3001 en server.js (si cambia .env, ajuste tambien cerrar-agente.ps1)
netstat -ano 2>nul | findstr ":3001" 2>nul | findstr "LISTENING" >nul
if %errorlevel%==0 (
    echo La API de documentacion ya esta en marcha en el puerto 3001.
    ping 127.0.0.1 -n 3 >nul
    exit /b 0
)

powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "%AGENT_DIR%\iniciar-agente.ps1"

if errorlevel 1 (
    echo [ERROR] No se pudo arrancar la API. Revise Node y permisos.
    pause
    exit /b 1
)

echo API documentacion-candidato iniciada en segundo plano (puerto 3001 por defecto).
echo Para detener: cerrar-agente.bat
ping 127.0.0.1 -n 3 >nul
