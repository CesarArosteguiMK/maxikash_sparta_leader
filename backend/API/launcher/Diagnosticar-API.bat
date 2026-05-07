@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem =====================================================================
rem  Diagnosticar-API.bat
rem  Doble clic para ejecutar el doctor de la API.
rem  Si pasa /FIX, aplica auto-fix simples (.env, logs, libera puerto, etc.)
rem  Si pasa /INSTALL, ademas reinstala paquetes Python faltantes.
rem  La ventana NO se cierra al terminar (asi puede leer el resumen).
rem =====================================================================

for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"

set "DOC_ARGS="
if /i "%~1"=="/FIX"     set "DOC_ARGS=%DOC_ARGS% -Fix"
if /i "%~2"=="/FIX"     set "DOC_ARGS=%DOC_ARGS% -Fix"
if /i "%~1"=="/INSTALL" set "DOC_ARGS=%DOC_ARGS% -Fix -InstallMissing"
if /i "%~2"=="/INSTALL" set "DOC_ARGS=%DOC_ARGS% -Fix -InstallMissing"
if /i "%~1"=="/KILL"    set "DOC_ARGS=%DOC_ARGS% -KillPort"
if /i "%~2"=="/KILL"    set "DOC_ARGS=%DOC_ARGS% -KillPort"

echo.
echo ====================================================
echo   Diagnostico de la API verificacion documentos
echo   Carpeta: %API_DIR%
echo   Args   : %DOC_ARGS%
echo ====================================================
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1" %DOC_ARGS%
set "RC=%ERRORLEVEL%"

echo.
echo ====================================================
if "%RC%"=="0" echo  OK: el entorno esta sano (codigo %RC%).
if "%RC%"=="1" echo  ERROR: hay problemas bloqueantes (codigo %RC%). Vea el resumen arriba.
if "%RC%"=="2" echo  AVISO: hay advertencias (codigo %RC%). Vea el resumen arriba.
echo  Log mas reciente en: %API_DIR%\logs\doctor-*.log
echo ====================================================
echo.
echo Pulse cualquier tecla para cerrar...
pause >nul
exit /b %RC%
