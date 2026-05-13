@echo off
chcp 65001 >nul
pushd "%~dp0.."

echo.
echo === Sparta Ledger: actualizar codigo (git pull) ===
echo Carpeta: %CD%
echo.

echo [1/3] Intentando detener Apache (XAMPP) para que no bloquee archivos del proyecto...
if exist "C:\xampp\apache_stop.bat" call "C:\xampp\apache_stop.bat"
if exist "D:\xampp\apache_stop.bat" call "D:\xampp\apache_stop.bat"
if defined XAMPP_HOME if exist "%XAMPP_HOME%\apache_stop.bat" call "%XAMPP_HOME%\apache_stop.bat"
timeout /t 3 /nobreak >nul

echo [2/3] git fetch...
git fetch origin
if errorlevel 1 (
  echo Fallo git fetch (red o credenciales). Revisa internet y acceso al repositorio.
  popd
  pause
  exit /b 1
)

echo [3/3] git pull...
git pull --no-edit
if errorlevel 1 (
  echo.
  echo --- git pull fallo ---
  echo No se borra PythonPortable: Git ya no versiona esa carpeta (.gitignore).
  echo Cierra procesos que usen archivos del repo, vuelve a ejecutar este archivo.
  echo Si esta copia es solo servidor sin cambios locales a conservar:
  echo   scripts\actualizar_repo_igualar_remoto.bat
  echo.
  popd
  pause
  exit /b 1
)

echo.
echo Listo. Puedes volver a iniciar Apache desde el panel de XAMPP.
echo.

if not exist "backend\API\tools\PythonPortable\python.exe" (
  echo ----------------------------------------------------------
  echo Aviso: la API de documentacion necesita Python en disco.
  echo Git NO trae esa carpeta a proposito (.gitignore).
  echo Para crearla en esta PC: doble clic en
  echo    backend\API\launcher\Bootstrap-Python.bat
  echo Despues: backend\API\launcher\instalar-agente.bat  (venv/deps)
  echo ----------------------------------------------------------
  echo.
)

popd
pause
