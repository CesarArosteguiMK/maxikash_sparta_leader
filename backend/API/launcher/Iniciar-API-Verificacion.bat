@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem =====================================================================
rem  Iniciar-API-Verificacion.bat  (MODO INTELIGENTE 1-CLICK)
rem
rem  Uso normal (doble clic):
rem    - Diagnostica rapido el entorno
rem    - Si detecta errores bloqueantes, intenta auto-reparar
rem    - Intenta levantar la API en segundo plano
rem    - Te deja un mensaje claro de exito/fallo
rem
rem  Opciones:
rem    /CONSOLA  -> abre depuracion visible (foreground)
rem    /RAPIDO   -> arranque directo sin auto-reparacion
rem =====================================================================

cd /d "%~dp0"
for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
set "API_PORT=%SPARTA_API_PORT%"
if "%API_PORT%"=="" set "API_PORT=8001"
set "LOG_DIR=%TEMP%\sparta___SPARTA_SECRET_REDACTED___api_logs"
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%" >nul 2>&1

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

rem Si ya esta levantada, no hacer nada mas.
echo [PRECHECK] Probando si la API ya responde en 127.0.0.1:!API_PORT!...
call :ApiReady 4
if !errorlevel! EQU 0 (
    set "API_READY=1"
) else (
    set "API_READY=0"
)
echo [PRECHECK] API_READY=!API_READY!

if "!API_READY!"=="1" (
    echo [OK] La API ya esta en marcha en el puerto !API_PORT!.
    echo      URL: http://127.0.0.1:!API_PORT!/docs
    echo.
    echo [CHECK] Verificando dependencias criticas por si el servidor quedo a medias...
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1"
    set "DOC_READY_RC=!ERRORLEVEL!"
    if "!DOC_READY_RC!"=="1" (
        echo [FIX] La API esta viva, pero faltan dependencias criticas. Reparando...
        powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1" -Fix -InstallMissing
        set "DOC_READY_FIX_RC=!ERRORLEVEL!"
        if "!DOC_READY_FIX_RC!"=="1" (
            echo [ERROR] No se pudieron instalar todas las dependencias criticas.
            echo         Revise: %LOG_DIR%\doctor-pip-*.log
            ping 127.0.0.1 -n 8 >nul
            exit /b 1
        )
        echo [RESTART] Dependencias actualizadas. Reiniciando API para cargar PaddleOCR/Paddle...
        powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0cerrar-agente.ps1" -Silent
        if !errorlevel! NEQ 0 (
            echo [ERROR] No se pudo detener la API anterior. No se arranca encima para evitar codigo viejo en memoria.
            echo         Use "Parar ejecucion" o cierre el proceso que ocupa el puerto 8001 y vuelva a intentar.
            exit /b 1
        )
        call "%~dp0iniciar-agente.bat"
        exit /b !ERRORLEVEL!
    )
    echo [RESTART] La API ya estaba viva. Reiniciando para cargar el codigo actual...
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0cerrar-agente.ps1" -Silent
    if !errorlevel! NEQ 0 (
        echo [ERROR] No se pudo detener la API anterior. No se arranca encima para evitar codigo viejo en memoria.
        echo         Use "Parar ejecucion" o cierre el proceso que ocupa el puerto 8001 y vuelva a intentar.
        exit /b 1
    )
    call "%~dp0iniciar-agente.bat"
    exit /b !ERRORLEVEL!
)

rem ----- Bootstrap defensivo: si Python portable existe pero esta roto -----
rem (le faltan .pyd como _socket), descargar el embeddable oficial. Todo lo
rem demas (pip, requirements) depende de que socket/ssl funcionen.
set "PORTABLE_PY=%API_DIR%\tools\PythonPortable\python.exe"
if exist "%PORTABLE_PY%" (
    "%PORTABLE_PY%" -c "import _socket, ssl" >nul 2>&1
    if errorlevel 1 (
        echo [0/4] Python portable incompleto ^(falta _socket/ssl^). Reparando...
        powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0bootstrap-python.ps1"
        set "BS_RC=!ERRORLEVEL!"
        if not "!BS_RC!"=="0" (
            echo [ERROR] Bootstrap del Python portable fallo ^(codigo !BS_RC!^).
            echo         Vea: %LOG_DIR%\bootstrap-python-*.log
        )
    )
) else (
    echo [0/4] No hay Python portable. Descargando uno limpio...
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0bootstrap-python.ps1"
)

rem ----- Bootstrap zbar: descarga DLL MSYS2 solo dentro de API\tools\zbar\bin -----
if exist "%~dp0bootstrap-zbar-local.ps1" (
    set "ZBAR_OK=0"
    set "ZBAR_PY=%PORTABLE_PY%"
    if not exist "!ZBAR_PY!" if exist "%API_DIR%\tools\python312\python.exe" set "ZBAR_PY=%API_DIR%\tools\python312\python.exe"
    if not exist "!ZBAR_PY!" if exist "%API_DIR%\venv\Scripts\python.exe" set "ZBAR_PY=%API_DIR%\venv\Scripts\python.exe"
    if exist "!ZBAR_PY!" if exist "%~dp0_zbar_smoke.py" (
        pushd "%API_DIR%" >nul
        "!ZBAR_PY!" "%~dp0_zbar_smoke.py" >nul 2>&1
        if not errorlevel 1 set "ZBAR_OK=1"
        popd >nul
    )
    if "!ZBAR_OK!"=="1" (
        echo [0/4] zbar local ^(QR/PDF417^): OK, no requiere bootstrap.
    ) else (
        echo [0/4] zbar local ^(QR/PDF417^): preparando DLLs en API\tools\zbar\bin...
        echo       Timeout defensivo: 180 segundos. Si la red del servidor no responde, el panel no quedara colgado.
        set "ZBAR_BOOTSTRAP=%~dp0bootstrap-zbar-local.ps1"
        powershell -NoProfile -ExecutionPolicy Bypass -Command "$p=Start-Process -FilePath 'powershell.exe' -ArgumentList @('-NoProfile','-ExecutionPolicy','Bypass','-File',$env:ZBAR_BOOTSTRAP) -NoNewWindow -PassThru; if (-not $p.WaitForExit(180000)) { try { $p.Kill() } catch {}; Write-Host '[zbar] TIMEOUT: bootstrap supero 180 segundos.'; exit 124 }; exit $p.ExitCode" >>"%LOG_DIR%\bootstrap-zbar.log" 2>&1
        if errorlevel 1 (
            set "ZBAR_OK=0"
            if exist "!ZBAR_PY!" if exist "%~dp0_zbar_smoke.py" (
                pushd "%API_DIR%" >nul
                "!ZBAR_PY!" "%~dp0_zbar_smoke.py" >nul 2>&1
                if not errorlevel 1 set "ZBAR_OK=1"
                popd >nul
            )
            if "!ZBAR_OK!"=="1" (
                echo       [OK] zbar local ya funciona ^(DLLs dentro de API^).
            ) else (
                echo       [AVISO] zbar no quedo listo o se agoto el tiempo. Ver: %LOG_DIR%\bootstrap-zbar.log
                echo       Continuando: la API puede levantar; solo QR/PDF417 podria fallar hasta reparar zbar.
            )
        )
    )
)

echo [1/4] Diagnostico rapido...
rem Sin -Quiet: asi la salida llega al log web y el panel no parece congelado.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1"
set "DOC_RC=!ERRORLEVEL!"

if "!DOC_RC!"=="1" (
    echo [2/4] Se detectaron errores bloqueantes. Intentando auto-reparar...
    echo NOTA: pip puede tardar 15-45 min ^(torch/opencv son grandes^). Ver lineas abajo...
    rem Sin -Quiet: ver progreso en panel web mientras instala paquetes.
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1" -Fix -InstallMissing
    set "DOC_FIX_RC=!ERRORLEVEL!"
    echo [2/4] Auto-reparacion termino con codigo !DOC_FIX_RC!

    if "!DOC_FIX_RC!"=="1" (
        rem Antes de hacer una "instalacion completa", validamos con smoke import:
        rem si la app ya se importa, los [ERR] del doctor son ruido cosmetico
        rem (estados intermedios) y no hay que reinstalar nada.
        set "PORTABLE_PY=%API_DIR%\tools\PythonPortable\python.exe"
        set "SMOKE_OK=0"
        if exist "%API_DIR%\launcher\_smoke_import.py" (
            if exist "!PORTABLE_PY!" (
                pushd "%API_DIR%" >nul
                "!PORTABLE_PY!" "%API_DIR%\launcher\_smoke_import.py" >nul 2>&1
                if not errorlevel 1 set "SMOKE_OK=1"
                popd >nul
            )
        )
        if "!SMOKE_OK!"=="1" (
            echo [3/4] Auto-fix dejo la app importable; saltando instalacion adicional.
        ) else (
            echo [3/4] Auto-fix insuficiente. Ejecutando instalacion completa...
            rem Modo GLOBAL: el portable embeddable no soporta venv.
            call "%~dp0instalar-agente.bat" /GLOBAL /SILENT
        )
    ) else (
        echo [3/4] Auto-fix aplicado.
    )
) else (
    rem OJO: no usar parentesis literales sin escapar dentro de bloques if (...)^); CMD los interpreta como cierre de bloque.
    echo [2/4] Entorno utilizable - sin errores bloqueantes.
    echo [3/4] Continuando...
)

echo [4/4] Intentando levantar la API...
call "%~dp0iniciar-agente.bat"
set "START_RC=!ERRORLEVEL!"

rem Confirmacion final
echo [4/4] Confirmando health de la API...
call :ApiReady 6
if !errorlevel! EQU 0 (
    echo.
    echo ============================================================
    echo   [OK] API levantada correctamente
    echo   URL:  http://127.0.0.1:!API_PORT!/api/v1/health
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
echo   - %LOG_DIR%\api_oculto_startup.log
echo   - %LOG_DIR%\uvicorn-stderr.log
echo   - %LOG_DIR%\doctor-*.log   ^(el mas reciente^)
echo.
echo   Siguiente paso recomendado ^(1 clic^):
echo   - Ejecuta: %~dp0Iniciar-API-Verificacion.bat /CONSOLA
echo     para ver el error exacto en vivo.
echo ============================================================
echo.
ping 127.0.0.1 -n 9 >nul
exit /b %START_RC%

:ApiReady
set "API_READY_TIMEOUT=%~1"
if "%API_READY_TIMEOUT%"=="" set "API_READY_TIMEOUT=4"
set "API_READY_PORT=%API_PORT%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "try { $t=[int]$env:API_READY_TIMEOUT; $p=[int]$env:API_READY_PORT; $r = Invoke-WebRequest -Uri ('http://127.0.0.1:' + $p + '/api/v1/health') -UseBasicParsing -TimeoutSec $t; if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500) { exit 0 } else { exit 1 } } catch { try { $r = Invoke-WebRequest -Uri ('http://127.0.0.1:' + $p + '/docs') -UseBasicParsing -TimeoutSec $t; if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500) { exit 0 } else { exit 1 } } catch { exit 1 } }" >nul 2>nul
exit /b !ERRORLEVEL!
