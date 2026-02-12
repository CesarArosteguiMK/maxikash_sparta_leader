@echo off
setlocal
color 0A
title Configurando Permisos SSH

REM --- 1. UBICARSE EN LA CARPETA DEL SCRIPT ---
cd /d "%~dp0"
set "NOMBRE_LLAVE=jesusssh4.unknown"

echo.
echo ========================================
echo   CONFIGURANDO: %NOMBRE_LLAVE%
echo ========================================
echo.

REM --- 2. VERIFICAR EXISTENCIA ---
if not exist "%NOMBRE_LLAVE%" (
    color 0C
    echo [ERROR] No se encuentra el archivo:
    echo %~dp0%NOMBRE_LLAVE%
    echo.
    pause
    exit /b 1
)

REM --- 3. REINICIAR PERMISOS (Por si se rompieron antes) ---
echo 1/3 Limpiando permisos corruptos...
icacls "%NOMBRE_LLAVE%" /reset >nul 2>&1

REM --- 4. TOMAR PROPIEDAD (Asegura que tu usuario manda) ---
echo 2/3 Tomando propiedad del archivo...
takeown /f "%NOMBRE_LLAVE%" >nul 2>&1

REM --- 5. QUITAR HERENCIA Y DAR PERMISOS SOLO A SYSTEM (para Apache) ---
echo 3/3 Aplicando seguridad estricta...
REM /inheritance:r -> Quita herencia
REM Remover todos los usuarios comunes
icacls "%NOMBRE_LLAVE%" /remove "Users" >nul 2>&1
icacls "%NOMBRE_LLAVE%" /remove "Administrators" >nul 2>&1
icacls "%NOMBRE_LLAVE%" /remove "Everyone" >nul 2>&1
icacls "%NOMBRE_LLAVE%" /remove "Authenticated Users" >nul 2>&1
REM /grant:r -> Dar SOLO Lectura (R) a SYSTEM (usuario que ejecuta Apache)
icacls "%NOMBRE_LLAVE%" /inheritance:r /grant:r "NT AUTHORITY\SYSTEM:(R)"

if %errorlevel% neq 0 (
    color 0C
    echo.
    echo [ERROR] Hubo un problema aplicando los permisos.
    echo Asegurate de que el archivo no este en uso.
    pause
    exit /b 1
)

echo.
echo ========================================
echo      PERMISOS ACTUALES (Verificar)
echo ========================================
icacls "%NOMBRE_LLAVE%"
echo.
echo [EXITO] La llave esta lista.
echo.
pause