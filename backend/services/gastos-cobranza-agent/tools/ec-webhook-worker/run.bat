@echo off
setlocal
cd /d "%~dp0"
if exist "%~dp0config.local.env" (
  c:\xampp\php\php.exe "%~dp0worker.php" %*
) else (
  echo Falta config.local.env - copie config.example.env y configure TOKEN y GOOGLE_CHAT_WEBHOOK_URL
  exit /b 1
)
