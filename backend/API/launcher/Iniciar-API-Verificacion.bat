@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem =====================================================================
rem  Iniciar-API-Verificacion.bat  (MODO INTELIGENTE 1-CLICK)
rem
rem  Uso normal (doble clic):
rem    - Diagnostica rápido el entorno
rem    - Si detecta errores bloqueantes, intenta auto-reparar
rem    - Intenta levantar la API en segundo plano
rem    - Te deja un mensaje claro de éxito/fallo
rem
rem  Opciones:
rem    /CONSOLA  -> abre depuración visible (foreground)
rem    /RAPIDO   -> arranque directo sin auto-reparación
rem =====================================================================

cd /d "%~dp0"
for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
if not exist "%API_DIR%\logs" mkdir "%API_DIR%\logs" >nul 2>&1

if /i "%~1"=="/CONSOLA" (
    start "API Verificacion Documentos (Consola)" cmd /k "cd /d ""%~dp0"" && call ""%~dp0iniciar-agente-foreground.bat"""
    exit /b 0
)

if /i "%~1"=="/RAPIDO" (
    call "%~dp0iniciar-agente.bat"
    exit /b %errorlevel%
)

echo.
echo ============================================================
echo   API Verificacion Documentos - 1 Click
echo ============================================================
echo   Carpeta: %API_DIR%
echo ============================================================
echo.

rem Si ya está levantada, no hacer nada más.
netstat -ano 2>nul | findstr ":8000" | findstr "LISTENING" >nul
if !errorlevel! EQU 0 (
    echo [OK] La API ya esta en marcha en el puerto 8000.
    echo      URL: http://127.0.0.1:8000/docs
    echo.
    ping 127.0.0.1 -n 3 >nul
    exit /b 0
)

echo [1/4] Diagnostico rapido...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1" -Quiet
set "DOC_RC=!ERRORLEVEL!"

if "!DOC_RC!"=="1" (
    echo [2/4] Se detectaron errores bloqueantes. Intentando auto-reparar...
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1" -Fix -InstallMissing -Quiet
    set "DOC_FIX_RC=!ERRORLEVEL!"

    if "!DOC_FIX_RC!"=="1" (
        echo [3/4] Auto-fix insuficiente. Ejecutando instalacion completa en venv...
        call "%~dp0instalar-agente.bat" /VENV /SILENT
    ) else (
        echo [3/4] Auto-fix aplicado.
    )
) else (
    echo [2/4] Entorno utilizable (sin errores bloqueantes).
    echo [3/4] Continuando...
)

echo [4/4] Intentando levantar la API...
call "%~dp0iniciar-agente.bat"
set "START_RC=!ERRORLEVEL!"

rem Confirmación final
netstat -ano 2>nul | findstr ":8000" | findstr "LISTENING" >nul
if !errorlevel! EQU 0 (
    echo.
    echo ============================================================
    echo   [OK] API levantada correctamente
    echo   URL:  http://127.0.0.1:8000/docs
    echo ============================================================
    echo.
    ping 127.0.0.1 -n 4 >nul
    exit /b 0
)

echo.
echo ============================================================
echo   [ERROR] No se pudo levantar la API automaticamente
echo ============================================================
echo   Revisa estos logs:
echo   - %API_DIR%\logs\api_oculto_startup.log
echo   - %API_DIR%\logs\uvicorn-stderr.log
echo   - %API_DIR%\logs\doctor-*.log  (el mas reciente)
echo.
echo   Siguiente paso recomendado (1 clic):
echo   - Ejecuta: %~dp0Iniciar-API-Verificacion.bat /CONSOLA
echo     para ver el error exacto en vivo.
echo ============================================================
echo.
ping 127.0.0.1 -n 9 >nul
exit /b %START_RC%
