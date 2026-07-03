@echo off
chcp 65001 >nul
setlocal

set "AGENT_DIR=%~dp0"
if "%AGENT_DIR:~-1%"=="\" set "AGENT_DIR=%AGENT_DIR:~0,-1%"
cd /d "%AGENT_DIR%"

set "NODE_EXE=%NODE_EXE%"
if defined NODE_EXE if not exist "%NODE_EXE%" set "NODE_EXE="
if not defined NODE_EXE if exist "C:\Program Files\nodejs\node.exe" set "NODE_EXE=C:\Program Files\nodejs\node.exe"
if not defined NODE_EXE if exist "C:\Program Files (x86)\nodejs\node.exe" set "NODE_EXE=C:\Program Files (x86)\nodejs\node.exe"
if not defined NODE_EXE if exist "%LocalAppData%\Programs\node\node.exe" set "NODE_EXE=%LocalAppData%\Programs\node\node.exe"
if not defined NODE_EXE (
    for /f "delims=" %%I in ('where node.exe 2^>nul') do (
        set "NODE_EXE=%%I"
        goto :node_found
    )
)
:node_found

if not defined NODE_EXE (
    echo [ERROR] No se encontro node.exe. Instale Node.js LTS.
    exit /b 1
)

if not exist "%AGENT_DIR%\package.json" (
    echo [ERROR] Falta package.json en esta carpeta.
    exit /b 1
)

if not exist "%AGENT_DIR%\node_modules\" (
    echo [ERROR] No hay node_modules. Ejecute primero instalar-agente.bat en esta carpeta ^(solo una vez o al cambiar package.json^).
    exit /b 1
)

netstat -ano 2>nul | findstr ":3110" 2>nul | findstr "LISTENING" >nul
if %errorlevel%==0 (
    echo El agente ya esta en marcha en el puerto 3110.
    ping 127.0.0.1 -n 3 >nul
    exit /b 0
)

powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "%AGENT_DIR%\iniciar-agente.ps1"

if errorlevel 1 (
    echo [ERROR] No se pudo arrancar el agente. Revise Node y permisos.
    exit /b 1
)

echo Agente correos primeros pagos iniciado en segundo plano ^(puerto 3110^).
echo Para detenerlo: cerrar-agente.bat o Administrador de tareas ^(node.exe^).
ping 127.0.0.1 -n 3 >nul
