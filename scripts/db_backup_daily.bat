@echo off
setlocal

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0db_backup_daily.ps1" %*
exit /b %ERRORLEVEL%
