' Arranca todos los servicios sin ventana de consola.
' Si algo falla, ejecute iniciar-todos-los-servicios.bat en esta misma carpeta.
Set sh = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
dirScripts = fso.GetParentFolderName(WScript.ScriptFullName)
backend = fso.GetParentFolderName(dirScripts)
ps1 = dirScripts & "\iniciar-todos-los-servicios.ps1"
sh.Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File """ & ps1 & """ -BackendRoot """ & backend & """", 0, False
