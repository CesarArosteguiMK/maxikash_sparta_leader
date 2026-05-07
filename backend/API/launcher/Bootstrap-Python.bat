@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem =====================================================================
rem  Bootstrap-Python.bat
rem  Repara/instala el Python portable (descarga embeddable de python.org
rem  + pip) cuando esta incompleto (falta _socket.pyd, pip no funciona).
rem
rem  Uso:
rem    Doble clic.
rem    Tambien:  Bootstrap-Python.bat /FORCE   (re-instala aunque parezca OK)
rem =====================================================================

cd /d "%~dp0"

set "PS_ARGS=-NoProfile -ExecutionPolicy Bypass -File ""%~dp0bootstrap-python.ps1"""
if /i "%~1"=="/FORCE" set "PS_ARGS=%PS_ARGS% -Force"

echo.
echo ============================================================
echo   Bootstrap Python portable - 1 clic
echo ============================================================
echo  Esto descargara el Python oficial embeddable de python.org
echo  y lo dejara en backend\API\tools\PythonPortable\
echo  con pip ya instalado.
echo  Tarda ~1-3 minutos.
echo ============================================================
echo.

powershell %PS_ARGS%
set "RC=%ERRORLEVEL%"

echo.
if "%RC%"=="0" (
    echo [OK] Python portable listo. Ahora puede ejecutar:
    echo      launcher\instalar-agente.bat /VENV
    echo o el boton "API" desde la web Sparta Ledger.
) else (
    echo [ERROR] El bootstrap fallo con codigo %RC%.
    echo         Revise: backend\API\logs\bootstrap-python-*.log
)
echo.
pause
exit /b %RC%
