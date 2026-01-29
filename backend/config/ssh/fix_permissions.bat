@echo off
color 0E
title Reparar Permisos SSH - Sparta Ledger

echo.
echo ========================================
echo   REPARAR PERMISOS DE LLAVE SSH
echo ========================================
echo.

REM Ir al directorio del script
cd /d "%~dp0"

REM Verificar que existe el archivo
if not exist "jesusssh4.unknown" (
    echo [ERROR] No se encuentra el archivo jesusssh4.unknown
    echo.
    echo Ubicacion esperada: %CD%\jesusssh4.unknown
    echo.
    pause
    exit /b 1
)

echo Reparando permisos de: jesusssh4.unknown
echo.

REM Quitar permisos heredados
echo [1/2] Removiendo permisos heredados...
icacls jesusssh4.unknown /inheritance:r >nul 2>&1

REM Dar permisos solo al usuario
echo [2/2] Otorgando permisos a: %USERNAME%
icacls jesusssh4.unknown /grant:r "%USERNAME%:(R)" >nul 2>&1

echo.
echo Permisos actuales:
icacls jesusssh4.unknown

echo.
echo ========================================
echo   PERMISOS REPARADOS CORRECTAMENTE
echo ========================================
echo.
echo Ya puedes cerrar esta ventana y recargar
echo la aplicacion en tu navegador.
echo.
pause