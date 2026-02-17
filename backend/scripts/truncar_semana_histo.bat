@echo off
chcp 65001 >nul
title Truncar Semana -> Historial
echo Ejecutando: Copia Semana a Historial, webhooks y truncar...
echo.

set PHP_EXE=php
where php >nul 2>&1
if errorlevel 1 set PHP_EXE=C:\xampp\php\php.exe

"%PHP_EXE%" "%~dp0truncar_semana_histo.php"

echo.
pause
