' Detiene todos los servicios sin mostrar consola.
Set sh = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
dirScripts = fso.GetParentFolderName(WScript.ScriptFullName)
backend = fso.GetParentFolderName(fso.GetParentFolderName(dirScripts))
ps1 = dirScripts & "\cerrar-todos-los-servicios.ps1"
sh.Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File """ & ps1 & """ -BackendRoot """ & backend & """", 0, False
