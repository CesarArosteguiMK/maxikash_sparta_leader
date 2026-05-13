@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem =====================================================================
rem  iniciar-agente.bat
rem  Arranca uvicorn (puerto 8000) en SEGUNDO PLANO sin ventana.
rem  - Si la API ya esta levantada, sale OK.
rem  - Si hay un error, ofrece lanzar Diagnosticar-API.bat para que el
rem    usuario vea exactamente que fallo (en vez de quedarse a oscuras).
rem =====================================================================

for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
cd /d "%API_DIR%"

if not exist "%API_DIR%\app\main.py" (
    echo [ERROR] No esta app\main.py. Revise la carpeta API.
    pause
    exit /b 1
)

netstat -ano 2>nul | findstr ":8000" | findstr "LISTENING" >nul
if !errorlevel! EQU 0 (
    echo La API ya esta en marcha en el puerto 8000.
    ping 127.0.0.1 -n 2 >nul
    exit /b 0
)

powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "%~dp0iniciar-agente-oculto.ps1"
set "RC=!ERRORLEVEL!"

if not "%RC%"=="0" goto :failed

rem Confirmar que efectivamente quedo escuchando
ping 127.0.0.1 -n 2 >nul
netstat -ano 2>nul | findstr ":8000" | findstr "LISTENING" >nul
if !errorlevel! NEQ 0 (
    powershell -NoProfile -ExecutionPolicy Bypass -Command "try { $r = Invoke-WebRequest -Uri 'http://127.0.0.1:8000/docs' -UseBasicParsing -TimeoutSec 4; if ($r.StatusCode -eq 200) { exit 0 } else { exit 1 } } catch { exit 1 }" >nul 2>nul
    if !errorlevel! NEQ 0 goto :failed
)

echo API verificacion documentos iniciada: http://127.0.0.1:8000  ^(docs: /docs^)
echo Log de arranque: %API_DIR%\logs\api_oculto_startup.log
echo Log de uvicorn : %API_DIR%\logs\uvicorn-stderr.log
echo Para detener   : launcher\cerrar-agente.bat
ping 127.0.0.1 -n 2 >nul
exit /b 0

:failed
echo.
echo ============================================================
echo  [ERROR] No se pudo arrancar la API (codigo %RC%).
echo ============================================================
echo  Log de arranque:
echo    %API_DIR%\logs\api_oculto_startup.log
echo  Log de uvicorn (stderr):
echo    %API_DIR%\logs\uvicorn-stderr.log
echo.
echo  Voy a abrir el DOCTOR para diagnostico detallado...
echo  (Si no quiere, cierre esta ventana ahora.)
echo ============================================================
ping 127.0.0.1 -n 4 >nul
call "%~dp0Diagnosticar-API.bat"
exit /b %RC%
