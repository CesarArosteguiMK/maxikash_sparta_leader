@echo off
color 0A
title Configuracion Inicial - Sparta Ledger

echo.
echo ========================================
echo   SPARTA LEDGER - SETUP INICIAL
echo ========================================
echo.
echo Este script configura los permisos de la llave SSH
echo Solo necesitas ejecutarlo UNA VEZ por equipo
echo.
echo Presiona cualquier tecla para continuar...
pause >nul

cls
echo.
echo ========================================
echo   INICIANDO CONFIGURACION...
echo ========================================
echo.

REM Verificar que estamos en la carpeta correcta
if not exist "backend\config\ssh" (
    echo [ERROR] No se encuentra la carpeta backend\config\ssh
    echo.
    echo Asegurate de ejecutar este archivo desde la raiz del proyecto
    echo Ruta actual: %CD%
    echo.
    pause
    exit /b 1
)

REM Verificar que existe la llave SSH
if not exist "backend\config\ssh\jesusssh4.unknown" (
    echo [ERROR] No se encuentra el archivo jesusssh4.unknown
    echo.
    echo Por favor, copia el archivo jesusssh4.unknown en:
    echo %CD%\backend\config\ssh\
    echo.
    pause
    exit /b 1
)

echo [1/3] Verificando estructura del proyecto... OK
echo.

REM Ir a la carpeta de la llave
cd backend\config\ssh

echo [2/3] Configurando permisos de la llave SSH...
echo.

REM Quitar permisos heredados
icacls jesusssh4.unknown /inheritance:r >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] No se pudieron modificar los permisos
    echo.
    echo Posibles soluciones:
    echo 1. Ejecuta este archivo como Administrador
    echo    ^(Click derecho - Ejecutar como administrador^)
    echo 2. Verifica que el archivo exista
    echo.
    cd ..\..\..
    pause
    exit /b 1
)

REM Dar permisos solo al usuario actual
icacls jesusssh4.unknown /grant:r "%USERNAME%:(R)" >nul 2>&1

echo    - Permisos heredados removidos
echo    - Permisos otorgados a: %USERNAME%
echo.

echo [3/3] Verificando permisos finales...
echo.

REM Mostrar permisos actuales
icacls jesusssh4.unknown | findstr /C:"%USERNAME%"

REM Volver a la raiz
cd ..\..\..

echo.
echo ========================================
echo   CONFIGURACION COMPLETADA!
echo ========================================
echo.
echo La llave SSH esta correctamente configurada.
echo Ya puedes usar la aplicacion sin problemas.
echo.
echo Este script solo necesita ejecutarse una vez
echo por equipo o usuario.
echo.
echo Presiona cualquier tecla para cerrar...
pause >nul

exit /b 0