@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem ---------------------------------------------------------------------
rem Punto de entrada cuando el usuario 878 pulsa «API» en Inicio:
rem   Controller\Inicio::apiDocOneClickIniciar → este .bat →
rem   Iniciar-API-Verificacion.bat (doctor + install si falta + arranque oculto).
rem No bifurcar otro camino desde la UI; así el polling y logs web-api-1click-*.log cuadran.
rem ---------------------------------------------------------------------

set "TASK_NAME=Sparta API Verificacion Documentos"
set "TASK_INSTALLER=%~dp0instalar-tarea-api-documentos.ps1"
set "TASK_USABLE=0"

call :RefreshTaskUsable
if not "!TASK_USABLE!"=="1" (
    if exist "%TASK_INSTALLER%" (
        echo [TASK] No existe tarea programada utilizable o apunta al arranque anterior. Intentando instalar/actualizar desde el boton 1-click...
        powershell -NoProfile -ExecutionPolicy Bypass -File "%TASK_INSTALLER%"
        if "!ERRORLEVEL!"=="0" (
            echo [TASK] Tarea programada instalada/actualizada correctamente desde el boton 1-click.
        ) else (
            echo [TASK] No se pudo instalar/actualizar la tarea programada desde este proceso web. Se usara flujo directo como respaldo.
        )
    ) else (
        echo [TASK] No existe instalador de tarea programada. Se usara flujo directo como respaldo.
    )
)

call :RefreshTaskUsable
if "!TASK_USABLE!"=="1" (
    echo [TASK] Tarea programada detectada: %TASK_NAME%
    echo [TASK] Se intenta reinicio limpio por tarea para que no dependa de la sesion del usuario.
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0cerrar-agente.ps1" -Silent
    schtasks /Run /TN "%TASK_NAME%"
    if "!ERRORLEVEL!"=="0" (
        powershell -NoProfile -ExecutionPolicy Bypass -Command "$ok=$false; for($i=0;$i -lt 90;$i++){ try { $r=Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/v1/health' -UseBasicParsing -TimeoutSec 2; if($r.StatusCode -ge 200 -and $r.StatusCode -lt 500){ $ok=$true; break } } catch {}; Start-Sleep -Milliseconds 500 }; if($ok){ exit 0 } exit 1"
        if "!ERRORLEVEL!"=="0" (
            echo [TASK] API levantada por tarea programada: http://127.0.0.1:8000
            echo __FIN__:0
            exit /b 0
        )
        echo [TASK] La tarea se ejecuto, pero no confirmo health. Se usara flujo directo como respaldo.
    ) else (
        echo [TASK] No se pudo ejecutar la tarea. Se usara flujo directo como respaldo.
    )
)

call "%~dp0Iniciar-API-Verificacion.bat"
set "RC=%ERRORLEVEL%"
echo __FIN__:%RC%
exit /b %RC%

:RefreshTaskUsable
set "TASK_USABLE=0"
schtasks /Query /TN "%TASK_NAME%" >nul 2>nul
if not "!ERRORLEVEL!"=="0" exit /b 0
for /f "delims=" %%L in ('schtasks /Query /TN "%TASK_NAME%" /XML 2^>nul ^| findstr /I "iniciar-agente-tarea.bat"') do set "TASK_USABLE=1"
exit /b 0
