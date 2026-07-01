@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem =====================================================================
rem  iniciar-agente.bat
rem  Arranca uvicorn (puerto 8001) en SEGUNDO PLANO sin ventana.
rem  - Si la API ya esta levantada, sale OK.
rem  - Si hay un error, ofrece lanzar Diagnosticar-API.bat para que el
rem    usuario vea exactamente que fallo (en vez de quedarse a oscuras).
rem =====================================================================

for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
set "API_PORT=%SPARTA_API_PORT%"
if "%API_PORT%"=="" set "API_PORT=8001"
cd /d "%API_DIR%"

if not exist "%API_DIR%\app\main.py" (
    echo [ERROR] No esta app\main.py. Revise la carpeta API.
    pause
    exit /b 1
)

echo [START] Comprobando health actual de la API...
call :ApiReady 3
if !errorlevel! EQU 0 (
    echo La API ya responde en el puerto !API_PORT!.
    ping 127.0.0.1 -n 2 >nul
    exit /b 0
)

powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "%~dp0iniciar-agente-oculto.ps1"
set "RC=!ERRORLEVEL!"

if not "%RC%"=="0" goto :failed

rem Confirmar que efectivamente responde HTTP
ping 127.0.0.1 -n 2 >nul
call :ApiReady 5
if !errorlevel! NEQ 0 goto :failed

echo API verificacion documentos iniciada: http://127.0.0.1:!API_PORT!  ^(docs: /docs^)
echo Log de arranque: %TEMP%\sparta___SPARTA_SECRET_REDACTED___api_logs\api_oculto_startup.log
echo Log de uvicorn : %TEMP%\sparta___SPARTA_SECRET_REDACTED___api_logs\uvicorn-stderr.log
echo Para detener   : launcher\cerrar-agente.bat
ping 127.0.0.1 -n 2 >nul
exit /b 0

:failed
echo.
echo ============================================================
echo  [ERROR] No se pudo arrancar la API (codigo %RC%).
echo ============================================================
echo  Log de arranque:
echo    %TEMP%\sparta___SPARTA_SECRET_REDACTED___api_logs\api_oculto_startup.log
echo  Log de uvicorn (stderr):
echo    %TEMP%\sparta___SPARTA_SECRET_REDACTED___api_logs\uvicorn-stderr.log
echo.
echo  Voy a abrir el DOCTOR para diagnostico detallado...
echo  (Si no quiere, cierre esta ventana ahora.)
echo ============================================================
ping 127.0.0.1 -n 4 >nul
call "%~dp0Diagnosticar-API.bat"
exit /b %RC%

:ApiReady
set "API_READY_TIMEOUT=%~1"
if "%API_READY_TIMEOUT%"=="" set "API_READY_TIMEOUT=4"
set "API_READY_PORT=%API_PORT%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "try { $t=[int]$env:API_READY_TIMEOUT; $p=[int]$env:API_READY_PORT; $r = Invoke-WebRequest -Uri ('http://127.0.0.1:' + $p + '/api/v1/health') -UseBasicParsing -TimeoutSec $t; if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500) { exit 0 } else { exit 1 } } catch { try { $r = Invoke-WebRequest -Uri ('http://127.0.0.1:' + $p + '/docs') -UseBasicParsing -TimeoutSec $t; if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500) { exit 0 } else { exit 1 } } catch { exit 1 } }" >nul 2>nul
exit /b !ERRORLEVEL!
