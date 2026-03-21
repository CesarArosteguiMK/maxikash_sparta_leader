' Ejecuta el bucle de correos sin mostrar consola (mismo patron que segundometro-agent).
Set sh = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
dir = fso.GetParentFolderName(WScript.ScriptFullName)
bat = dir & "\iniciar-loop-correos-primeros-pagos.bat"
sh.Run """" & bat & """", 0, False
