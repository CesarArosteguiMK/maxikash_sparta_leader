@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

:: Carpeta del agente = carpeta de este .bat
set "AGENT_DIR=%~dp0"
if "%AGENT_DIR:~-1%"=="\" set "AGENT_DIR=%AGENT_DIR:~0,-1%"
cd /d "%AGENT_DIR%"

echo.
echo ============================================
echo   Segundometro Agent - instalacion (npm)
echo   Carpeta: %AGENT_DIR%
echo ============================================
echo   Ejecutar cuando:
echo   - Primera vez en esta carpeta
echo   - Cambie package.json o agregue dependencias
echo ============================================
echo.

set "NODE_EXE=%NODE_EXE%"
if defined NODE_EXE if not exist "%NODE_EXE%" (
    echo [WARN] NODE_EXE apunta a una ruta invalida: %NODE_EXE%
    set "NODE_EXE="
)
if not defined NODE_EXE if exist "C:\Program Files\nodejs\node.exe" set "NODE_EXE=C:\Program Files\nodejs\node.exe"
if not defined NODE_EXE if exist "C:\Program Files (x86)\nodejs\node.exe" set "NODE_EXE=C:\Program Files (x86)\nodejs\node.exe"
if not defined NODE_EXE if exist "%LocalAppData%\Programs\node\node.exe" set "NODE_EXE=%LocalAppData%\Programs\node\node.exe"
if not defined NODE_EXE if exist "C:\nodejs\node.exe" set "NODE_EXE=C:\nodejs\node.exe"
if not defined NODE_EXE (
    for /f "delims=" %%I in ('where node.exe 2^>nul') do (
        set "NODE_EXE=%%I"
        goto :node_found
    )
)
:node_found

if not defined NODE_EXE (
    echo [ERROR] No se encontro node.exe
    echo Instale Node.js LTS desde https://nodejs.org
    echo.
    pause
    exit /b 1
)

set "NPM_CMD=%NODE_EXE:\node.exe=\npm.cmd%"
if not exist "%NPM_CMD%" set "NPM_CMD=%NODE_EXE:\node.exe=\npm.exe%"
if not exist "%NPM_CMD%" (
    for /f "delims=" %%I in ('where npm.cmd 2^>nul') do (
        set "NPM_CMD=%%I"
        goto :npm_found
    )
)
if not exist "%NPM_CMD%" (
    for /f "delims=" %%I in ('where npm.exe 2^>nul') do (
        set "NPM_CMD=%%I"
        goto :npm_found
    )
)
:npm_found
if not exist "%NPM_CMD%" (
    echo [ERROR] Se encontro Node, pero no npm. Reinstale Node.js LTS desde https://nodejs.org
    echo.
    pause
    exit /b 1
)

echo [OK] Node: %NODE_EXE%
for %%D in ("%NODE_EXE%") do set "NODE_DIR=%%~dpD"
if defined NODE_DIR set "PATH=%NODE_DIR%;%PATH%"
echo.

if not exist "%AGENT_DIR%\package.json" (
    echo [ERROR] No esta package.json aqui. Coloque instalar-agente.bat DENTRO de segundometro-agent.
    pause
    exit /b 1
)

echo [npm install] Puede tardar la primera vez...
echo.
call "%NPM_CMD%" install --no-audit --no-fund
if errorlevel 1 (
    echo.
    echo [ERROR] npm install fallo. Revise mensajes arriba.
    pause
    exit /b 1
)

echo.
echo [OK] Dependencias listas.
echo      Para arrancar el agente sin ventana negra: iniciar-agente.bat
echo      (o doble clic en iniciar-agente-oculto.vbs para no ver ni esta consola)
echo.
pause
