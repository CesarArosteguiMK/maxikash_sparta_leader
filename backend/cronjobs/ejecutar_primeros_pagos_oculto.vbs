' Llama ejecutar_primeros_pagos.bat sin ventana (evita parpadeos de CMD en Tareas programadas).
' En el Programador de tareas: acción Programa = wscript.exe
' Argumentos: //B //Nologo "ruta\completa\a\ejecutar_primeros_pagos_oculto.vbs"
Set sh = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
dir = fso.GetParentFolderName(WScript.ScriptFullName)
bat = dir & "\ejecutar_primeros_pagos.bat"
sh.Run """" & bat & """", 0, False
