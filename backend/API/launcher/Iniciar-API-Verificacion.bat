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
            echo         Vea: %API_DIR%\logs\bootstrap-python-*.log
        )
    )
) else (
    echo [0/4] No hay Python portable. Descargando uno limpio...
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0bootstrap-python.ps1"
)

rem ----- Bootstrap zbar: descarga DLL MSYS2 solo dentro de API\tools\zbar\bin -----
if exist "%~dp0bootstrap-zbar-local.ps1" (
    echo [0/4] zbar local ^(QR/PDF417^): preparando DLLs en API\tools\zbar\bin...
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0bootstrap-zbar-local.ps1" >>"%API_DIR%\logs\bootstrap-zbar.log" 2>&1
    if errorlevel 1 (
        rem Si el bootstrap no pudo descargar (por red), pero las DLLs versionadas
        rem ya funcionan, no mostrar falso aviso: validar pyzbar igual que el doctor.
        set "ZBAR_OK=0"
        if exist "%PORTABLE_PY%" if exist "%~dp0_zbar_smoke.py" (
            pushd "%API_DIR%" >nul
            "%PORTABLE_PY%" "%~dp0_zbar_smoke.py" >nul 2>&1
            if not errorlevel 1 set "ZBAR_OK=1"
            popd >nul
        )
        if "!ZBAR_OK!"=="1" (
            echo       [OK] zbar local ya funciona ^(DLLs dentro de API^).
        ) else (
            echo       [AVISO] bootstrap-zbar-local.ps1 fallo. Ver: %API_DIR%\logs\bootstrap-zbar.log
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
echo   - %API_DIR%\logs\doctor-*.log   ^(el mas reciente^)
echo.
echo   Siguiente paso recomendado ^(1 clic^):
echo   - Ejecuta: %~dp0Iniciar-API-Verificacion.bat /CONSOLA
echo     para ver el error exacto en vivo.
echo ============================================================
echo.
ping 127.0.0.1 -n 9 >nul
exit /b %START_RC%
