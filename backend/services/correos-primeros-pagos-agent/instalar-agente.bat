@echo off
chcp 65001 >nul
setlocal

set "AGENT_DIR=%~dp0"
if "%AGENT_DIR:~-1%"=="\" set "AGENT_DIR=%AGENT_DIR:~0,-1%"
cd /d "%AGENT_DIR%"

echo.
echo ============================================
echo   Correos primeros pagos - instalacion
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
    echo [ERROR] No esta package.json. Ejecute este archivo DENTRO de correos-primeros-pagos-agent.
    pause
    exit /b 1
)

if not exist "%AGENT_DIR%\.env.example" (
    echo [AVISO] No hay .env.example. Se omitira la copia a .env.
    goto npm_install
)

if not exist "%AGENT_DIR%\.env" (
    echo [OK] Creando .env desde .env.example ...
    copy /Y "%AGENT_DIR%\.env.example" "%AGENT_DIR%\.env" >nul
    if errorlevel 1 (
        echo [ERROR] No se pudo copiar .env.example a .env
        pause
        exit /b 1
    )
    echo      Edite .env si su PHP no esta en la ruta por defecto.
    goto npm_install
)

echo [OK] Ya existe .env — no se sobrescribe. ^(Borre .env si quiere volver a generarlo desde .env.example^)

:npm_install
echo.
echo [npm install] Instalando dependencias...
echo.
call "%NPM_CMD%" install --no-audit --no-fund
if errorlevel 1 (
    echo.
    echo [ERROR] npm install fallo.
    pause
    exit /b 1
)

echo.
echo ============================================
echo   Listo.
echo   Para arrancar el agente: iniciar-agente-oculto.vbs
echo   ^(o iniciar-agente.bat^)
echo ============================================
echo.
pause
