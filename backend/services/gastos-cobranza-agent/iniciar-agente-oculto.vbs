' Ejecuta iniciar-agente.bat sin mostrar ventana de consola (acceso directo para el escritorio).
' Si algo falla, ejecute iniciar-agente.bat a mano para ver el mensaje de error.
Set sh = CreateObject("WScript.Shell")
bat = CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName) & "\iniciar-agente.bat"
sh.Run """" & bat & """", 0, False
