@echo off
chcp 65001 >nul
setlocal

set "AGENT_DIR=%~dp0"
if "%AGENT_DIR:~-1%"=="\" set "AGENT_DIR=%AGENT_DIR:~0,-1%"
cd /d "%AGENT_DIR%"

set "NODE_EXE="
if exist "C:\Program Files\nodejs\node.exe" set "NODE_EXE=C:\Program Files\nodejs\node.exe"
if not defined NODE_EXE if exist "C:\Program Files (x86)\nodejs\node.exe" set "NODE_EXE=C:\Program Files (x86)\nodejs\node.exe"
if not defined NODE_EXE if exist "%LocalAppData%\Programs\node\node.exe" set "NODE_EXE=%LocalAppData%\Programs\node\node.exe"

if not defined NODE_EXE (
    echo [ERROR] No se encontro node.exe. Instale Node.js LTS.
    pause
    exit /b 1
)

if not exist "%AGENT_DIR%\package.json" (
    echo [ERROR] Falta package.json en esta carpeta.
    pause
    exit /b 1
)

if not exist "%AGENT_DIR%\node_modules\" (
    echo [1/2] npm install ...
    call "%NODE_EXE:\node.exe=\npm.cmd%" install --no-audit --no-fund
    if errorlevel 1 (
        echo [ERROR] npm install fallo.
        pause
        exit /b 1
    )
)

echo [2/2] Arrancando agente de correos (HTTP estado en http://127.0.0.1:3110 por defecto^)
echo       Cierre esta ventana o ejecute cerrar-agente.bat para detener.
echo.

"%NODE_EXE%" "%AGENT_DIR%\index.js"
if errorlevel 1 (
    echo [ERROR] El agente termino con error.
    pause
    exit /b 1
)
pause
