@echo off
chcp 65001 >nul
set "REPO=%~dp0.."
cd /d "%REPO%"
echo.
echo --- Actualizando Sparta Ledger (git pull) ---
echo Carpeta: %CD%
echo.
git pull
if errorlevel 1 (
  echo.
  echo Error en git pull. Suele deberse a archivos en uso: cierra Apache/XAMPP y cualquier terminal
  echo abierta dentro de esta carpeta, y vuelve a ejecutar este archivo.
  pause
  exit /b 1
)
echo.
echo OK.
pause
