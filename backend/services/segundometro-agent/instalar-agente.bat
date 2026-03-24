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

set "NODE_EXE="
if exist "C:\Program Files\nodejs\node.exe" set "NODE_EXE=C:\Program Files\nodejs\node.exe"
if not defined NODE_EXE if exist "C:\Program Files (x86)\nodejs\node.exe" set "NODE_EXE=C:\Program Files (x86)\nodejs\node.exe"
if not defined NODE_EXE if exist "%LocalAppData%\Programs\node\node.exe" set "NODE_EXE=%LocalAppData%\Programs\node\node.exe"

if not defined NODE_EXE (
    echo [ERROR] No se encontro node.exe
    echo Instale Node.js LTS desde https://nodejs.org
    echo.
    pause
    exit /b 1
)

set "NPM_CMD=%NODE_EXE:\node.exe=\npm.cmd%"
if not exist "%NPM_CMD%" set "NPM_CMD=%NODE_EXE:\node.exe=\npm.exe%"

echo [OK] Node: %NODE_EXE%
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
