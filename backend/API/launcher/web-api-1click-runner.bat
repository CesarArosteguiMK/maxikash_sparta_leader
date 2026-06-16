@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem ---------------------------------------------------------------------
rem Entrada del boton "API" de Inicio.
rem La tarea programada corre un supervisor como SYSTEM. Este runner no
rem necesita llamar schtasks /Run; solo deja una bandera de reinicio que el
rem supervisor consume desde backend/API/runtime.
rem ---------------------------------------------------------------------

set "TASK_NAME=Sparta API Verificacion Documentos"
set "TASK_INSTALLER=%~dp0instalar-tarea-api-documentos.ps1"
set "TASK_FILE=%SystemRoot%\System32\Tasks\%TASK_NAME%"
set "TASK_USABLE=0"

call :RefreshTaskUsable
if not "!TASK_USABLE!"=="1" (
    if exist "%TASK_INSTALLER%" (
        echo [TASK] No existe tarea supervisor utilizable o apunta al arranque anterior.
        echo [TASK] Intentando instalar/actualizar la tarea persistente desde el boton 1-click...
        powershell -NoProfile -ExecutionPolicy Bypass -File "%TASK_INSTALLER%"
        if "!ERRORLEVEL!"=="0" (
            echo [TASK] Tarea programada instalada/actualizada correctamente.
        ) else (
            echo [TASK][ERROR] No se pudo instalar/actualizar la tarea desde este proceso web.
            echo [TASK][ERROR] Normalmente falta ejecutar como Administrador para crear la tarea SYSTEM.
        )
    ) else (
        echo [TASK][ERROR] No existe instalador de tarea programada: %TASK_INSTALLER%
    )
)

call :RefreshTaskUsable
if not "!TASK_USABLE!"=="1" (
    echo [TASK][ERROR] El boton no hara arranque directo porque ese modo se cae al cerrar sesion.
    echo [TASK][ERROR] Instale una vez la tarea programada como Administrador:
    echo [TASK][ERROR]   powershell -NoProfile -ExecutionPolicy Bypass -File "%TASK_INSTALLER%"
    if /I "%SPARTA_API_ALLOW_DIRECT_FALLBACK%"=="1" (
        echo [TASK][WARN] SPARTA_API_ALLOW_DIRECT_FALLBACK=1 detectado; usando arranque directo temporal.
        call :DirectFallback
        exit /b !ERRORLEVEL!
    )
    echo __FIN__:2
    exit /b 2
)

call :RunDependencyPreflight
if errorlevel 1 (
    set "RC=!ERRORLEVEL!"
    echo __FIN__:!RC!
    exit /b !RC!
)

call :RequestSupervisorRestart
set "RC=!ERRORLEVEL!"
echo __FIN__:!RC!
exit /b !RC!

:DirectFallback
call "%~dp0Iniciar-API-Verificacion.bat"
set "RC=!ERRORLEVEL!"
echo __FIN__:!RC!
exit /b !RC!

:RunDependencyPreflight
echo [CHECK] Verificando dependencias criticas antes del arranque por tarea...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1"
set "DOC_RC=!ERRORLEVEL!"
if "!DOC_RC!"=="1" (
    echo [CHECK] Hay errores bloqueantes. Intentando reparacion automatica de dependencias...
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1" -Fix -InstallMissing
    set "DOC_FIX_RC=!ERRORLEVEL!"
    if "!DOC_FIX_RC!"=="1" (
        echo [CHECK][ERROR] No se pudieron reparar todas las dependencias criticas.
        echo [CHECK][ERROR] Revise el ultimo doctor-*.log en backend\API\logs.
        exit /b 1
    )
    echo [CHECK] Reparacion terminada con codigo !DOC_FIX_RC!. Continuando.
    exit /b 0
)
if "!DOC_RC!"=="2" (
    echo [CHECK] Doctor termino con avisos, sin errores bloqueantes. Continuando.
) else (
    echo [CHECK] Dependencias criticas OK.
)
exit /b 0

:RequestSupervisorRestart
for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "!API_DIR:~-1!"=="\" set "API_DIR=!API_DIR:~0,-1!"
set "RUNTIME_DIR=!API_DIR!\runtime"
set "RESTART_FLAG=!RUNTIME_DIR!\api-restart-request.flag"
if not exist "!RUNTIME_DIR!" mkdir "!RUNTIME_DIR!" >nul 2>&1

echo [TASK] Supervisor detectado en Task Scheduler.
echo [TASK] Solicitando reinicio persistente por bandera: !RESTART_FLAG!
> "!RESTART_FLAG!" echo requested_at=%DATE% %TIME%
>> "!RESTART_FLAG!" echo requested_by=web-api-1click-runner
set "SPARTA_API_RESTART_FLAG=!RESTART_FLAG!"

powershell -NoProfile -ExecutionPolicy Bypass -Command "$flag=$env:SPARTA_API_RESTART_FLAG; $ok=$false; for($i=0;$i -lt 120;$i++){ $gone=-not (Test-Path -LiteralPath $flag); if($gone){ try { $r=Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/v1/health' -UseBasicParsing -TimeoutSec 2; if($r.StatusCode -ge 200 -and $r.StatusCode -lt 500){ $ok=$true; break } } catch {} }; Start-Sleep -Milliseconds 500 }; if($ok){ exit 0 } exit 1"
if "!ERRORLEVEL!"=="0" (
    echo [TASK] Supervisor consumio la solicitud y la API responde: http://127.0.0.1:8000
    exit /b 0
)

echo [TASK][ERROR] El supervisor no consumio la solicitud o la API no confirmo health.
echo [TASK][ERROR] Revise logs\api-supervisor.log, logs\api_oculto_startup.log y logs\uvicorn-stderr.log.
exit /b 1

:RefreshTaskUsable
set "TASK_USABLE=0"
set "SPARTA_TASK_FILE=%TASK_FILE%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$p=$env:SPARTA_TASK_FILE; try { $c=Get-Content -LiteralPath $p -Raw -ErrorAction Stop; if($c -like '*supervisar-api-documentos.ps1*'){ exit 0 } } catch {}; exit 1" >nul 2>nul
if "!ERRORLEVEL!"=="0" set "TASK_USABLE=1"
exit /b 0
