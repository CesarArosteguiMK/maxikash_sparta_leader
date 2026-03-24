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

if not exist "%AGENT_DIR%\node_modules\" (
    echo [ERROR] No hay node_modules. Ejecute primero instalar-agente.bat en esta carpeta ^(solo una vez o al cambiar package.json^).
    pause
    exit /b 1
)

:: Si ya hay algo escuchando en 3100, no lanzar otro Node
netstat -ano 2>nul | findstr ":3100" 2>nul | findstr "LISTENING" >nul
if %errorlevel%==0 (
    echo El agente ya esta en marcha en el puerto 3100.
    ping 127.0.0.1 -n 3 >nul
    exit /b 0
)

:: Node en proceso aparte, sin ventana (logica en iniciar-agente.ps1 para evitar errores de CMD)
powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "%AGENT_DIR%\iniciar-agente.ps1"

if errorlevel 1 (
    echo [ERROR] No se pudo arrancar el agente. Revise Node y permisos.
    pause
    exit /b 1
)

echo Agente iniciado en segundo plano en el puerto 3100.
echo Para detenerlo: Administrador de tareas, proceso node.exe, o reinicie el equipo.
ping 127.0.0.1 -n 3 >nul
