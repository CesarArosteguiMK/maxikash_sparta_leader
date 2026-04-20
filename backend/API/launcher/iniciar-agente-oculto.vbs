' Arranca la API (puerto 8000) sin ventana CMD: solo PowerShell oculto + proceso Python.
' Si falla (sin venv, etc.), revise logs\api_oculto_startup.log o ejecute iniciar-agente.bat a mano.

Dim sh, fso, ps1
Set fso = CreateObject("Scripting.FileSystemObject")
ps1 = fso.GetParentFolderName(WScript.ScriptFullName) & "\iniciar-agente-oculto.ps1"
Set sh = CreateObject("WScript.Shell")
sh.Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File """ & ps1 & """", 0, False
