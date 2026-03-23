@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

set "AGENT_DIR=%~dp0"
if "%AGENT_DIR:~-1%"=="\" set "AGENT_DIR=%AGENT_DIR:~0,-1%"
cd /d "%AGENT_DIR%"

echo.
echo ============================================
echo   Documentacion Candidato API - npm install
echo   Carpeta: %AGENT_DIR%
echo ============================================
echo.

set "NODE_EXE="
if exist "C:\Program Files\nodejs\node.exe" set "NODE_EXE=C:\Program Files\nodejs\node.exe"
if not defined NODE_EXE if exist "C:\Program Files (x86)\nodejs\node.exe" set "NODE_EXE=C:\Program Files (x86)\nodejs\node.exe"
if not defined NODE_EXE if exist "%LocalAppData%\Programs\node\node.exe" set "NODE_EXE=%LocalAppData%\Programs\node\node.exe"

if not defined NODE_EXE (
    echo [ERROR] No se encontro node.exe. Instale Node.js LTS desde https://nodejs.org
    pause
    exit /b 1
)

set "NPM_CMD=%NODE_EXE:\node.exe=\npm.cmd%"
if not exist "%NPM_CMD%" set "NPM_CMD=%NODE_EXE:\node.exe=\npm.exe%"

if not exist "%AGENT_DIR%\package.json" (
    echo [ERROR] Falta package.json en esta carpeta.
    pause
    exit /b 1
)

echo [npm install] Puede tardar la primera vez...
call "%NPM_CMD%" install --no-audit --no-fund
if errorlevel 1 (
    echo [ERROR] npm install fallo.
    pause
    exit /b 1
)

echo.
echo [OK] Dependencias listas. Arranque con iniciar-agente.bat o iniciar-agente-oculto.vbs
echo.
pause
