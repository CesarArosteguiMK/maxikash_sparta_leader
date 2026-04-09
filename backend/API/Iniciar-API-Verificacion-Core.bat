@echo off
:: Flujo Docker (compose). Uso diario sin Docker: iniciar-agente.bat o Iniciar-API-Verificacion.bat
chcp 65001 >nul
title API Verificación Documentos - Sparta Ledger (Docker)
cd /d "%~dp0"

echo.
echo  ============================================
echo   API Verificación Documentos (Docker)
echo  ============================================
echo.

:: Comprobar si Docker responde
docker info >nul 2>&1
if errorlevel 1 (
    echo  [AVISO] Docker no está en ejecución.
    echo.
    echo  Intentando abrir Docker Desktop...
    for %%P in (
        "%ProgramFiles%\Docker\Docker\Docker Desktop.exe"
        "%ProgramFiles(x86)%\Docker\Docker\Docker Desktop.exe"
    ) do if exist %%P (
        start "" %%P
        echo  Esperando a que Docker Desktop inicie, 60 segundos.
        timeout /t 60 /nobreak >nul
        goto :check_again
    )
    echo.
    echo  No se encontró Docker Desktop. Por favor:
    echo  1. Instala Docker Desktop para Windows
    echo  2. Inícialo manualmente
    echo  3. Vuelve a hacer doble clic en este archivo
    echo.
    goto :fin
)

:check_again
docker info >nul 2>&1
if errorlevel 1 (
    echo  [AVISO] Docker sigue sin responder. Espera unos segundos más e inténtalo de nuevo.
    goto :fin
)

echo  Levantando contenedores api, postgres, redis.
echo.
docker compose up -d
if errorlevel 1 (
    echo.
    echo  [ERROR] No se pudo levantar la API. Revisa que Docker Desktop esté en ejecución.
    goto :fin
)

echo.
echo  ============================================
echo   API en ejecución en http://127.0.0.1:8000
echo   Informacion de ingresos FAD, verificacion documentos
echo  ============================================
echo.
echo  Para detener: doble clic en "Detener-API-Verificacion.bat"
echo.

:fin
echo.
echo  Pulsa una tecla para cerrar.
pause >nul
