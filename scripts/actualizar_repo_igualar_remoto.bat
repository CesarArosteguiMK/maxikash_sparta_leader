@echo off
chcp 65001 >nul
pushd "%~dp0.."

echo.
echo *** IGUALAR ESTA CARPETA CON GITHUB ***
echo.
echo Solo uses esto si esta copia es del servidor y NO necesitas conservar
echo cambios locales que no esten subidos con git push (se pierden).
echo.
pause

if exist "C:\xampp\apache_stop.bat" call "C:\xampp\apache_stop.bat"
if exist "D:\xampp\apache_stop.bat" call "D:\xampp\apache_stop.bat"
if defined XAMPP_HOME if exist "%XAMPP_HOME%\apache_stop.bat" call "%XAMPP_HOME%\apache_stop.bat"
timeout /t 3 /nobreak >nul

git fetch origin
if errorlevel 1 (
  echo Fallo git fetch.
  popd
  pause
  exit /b 1
)

git rev-parse --verify origin/main 1>nul 2>nul
if errorlevel 1 (
  echo Usando rama remota origin/master...
  git reset --hard origin/master
) else (
  echo Igualando con origin/main...
  git reset --hard origin/main
)

if errorlevel 1 (
  echo reset --hard fallo.
  popd
  pause
  exit /b 1
)

echo.
echo Hecho. Inicia Apache de nuevo en XAMPP.
popd
pause
