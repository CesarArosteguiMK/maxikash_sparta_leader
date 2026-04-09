@echo off
chcp 65001 >nul
setlocal

title Sparta Ledger - deps Node + API Python

for %%I in ("%~dp0..") do set "BACKEND=%%~fI"

echo.
echo ============================================
echo   npm install en agentes Node ^(una sola vez
echo   o al cambiar package.json^)
echo   Backend: %BACKEND%
echo ============================================
echo.

set "NODE_EXE="
if exist "C:\Program Files\nodejs\node.exe" set "NODE_EXE=C:\Program Files\nodejs\node.exe"
if not defined NODE_EXE if exist "C:\Program Files (x86)\nodejs\node.exe" set "NODE_EXE=C:\Program Files (x86)\nodejs\node.exe"
if not defined NODE_EXE if exist "%LocalAppData%\Programs\node\node.exe" set "NODE_EXE=%LocalAppData%\Programs\node\node.exe"

if not defined NODE_EXE (
    echo [ERROR] No se encontro node.exe. Instale Node.js LTS.
    pause
    exit /b 1
)

set "NPM_CMD=%NODE_EXE:\node.exe=\npm.cmd%"
if not exist "%NPM_CMD%" set "NPM_CMD=%NODE_EXE:\node.exe=\npm.exe%"

call :NpmInstall "%BACKEND%\API\documentacion-candidato" documentacion-candidato
if errorlevel 1 goto :fin
call :NpmInstall "%BACKEND%\services\segundometro-agent" segundometro-agent
if errorlevel 1 goto :fin
call :NpmInstall "%BACKEND%\services\correos-primeros-pagos-agent" correos-primeros-pagos-agent
if errorlevel 1 goto :fin
call :NpmInstall "%BACKEND%\services\gastos-cobranza-agent" gastos-cobranza-agent
if errorlevel 1 goto :fin

echo.
echo --------------------------------------------
echo  API Python verificacion documentos
echo  ^(pip global por defecto; ver instalar-agente.bat /VENV^)
echo --------------------------------------------
call "%BACKEND%\API\instalar-agente.bat" /SILENT
if errorlevel 1 (
    echo [AVISO] Fallo instalacion API Python. Revise mensajes arriba.
    echo          Guia: API\REQUISITOS_API_LOCAL.md
    goto :fin
)

echo.
echo [OK] Dependencias listas en las cuatro carpetas Node ^(doc, segundometro, correos, gastos cobranza^).
echo      API Python: requirements.txt ^(global^) en API\
echo      Arranque con iniciar-todos-los-servicios.bat
echo.
echo AVISO: En correos, si aun no tiene .env, ejecute una vez:
echo        services\correos-primeros-pagos-agent\instalar-agente.bat
echo        ^(copia .env.example y npm install^)
echo.
goto :fin

:NpmInstall
set "DIR=%~1"
set "NOMBRE=%~2"
if not exist "%DIR%\package.json" (
    echo [SKIP] %NOMBRE% - no hay package.json en "%DIR%"
    exit /b 0
)
echo --------------------------------------------
echo  %NOMBRE%
echo --------------------------------------------
pushd "%DIR%"
call "%NPM_CMD%" install --no-audit --no-fund
set "ERR=%errorlevel%"
popd
if not "%ERR%"=="0" (
    echo [ERROR] npm install fallo en %NOMBRE%
    exit /b 1
)
exit /b 0

:fin
pause
