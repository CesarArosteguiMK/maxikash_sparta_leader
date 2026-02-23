🛠️ Guía de Configuración Inicial - Sparta Ledger

Este documento detalla los pasos para configurar el entorno local y solucionar problemas de permisos SSH.
🚀 Setup Rápido (Configuración única)

Realiza estos pasos solo la primera vez que configures el proyecto en tu ordenador.
1. Clonar el repositorio
Bash

git clone <URL_DEL_REPO>
cd sparta___SPARTA_SECRET_REDACTED__

2. Instalar credenciales SSH

Solicita al administrador los archivos de llave y colócalos en:

    📂 Ruta: backend/config/ssh/

    - **jesusssh4.unknown** – clave en formato OpenSSH (para diagnóstico o si usas OpenSSH).
    - **jesusssh4.ppk** – clave en formato PuTTY. Si en config.ini tienes `ssh_use_plink = 1`, hace falta este archivo. Puedes generarlo con PuTTYgen: Conversions → Import key (elegir jesusssh4.unknown) → Save private key.

    ⚠️ No cambies los nombres de los archivos.

3. Configurar permisos automáticos

Para que la conexión SSH funcione sin contraseña, debes ejecutar el script de configuración:

    Navega a la carpeta: backend/config/ssh/

    Localiza el archivo configurar_llave.bat (o fix_permissions.bat).

    Haz Click derecho → "Ejecutar como administrador".

    Espera el mensaje de confirmación (aprox. 5 segundos) y presiona una tecla para salir.

⚠️ Solución de Problemas (Troubleshooting)

Si al intentar conectar recibes errores como:

    "Load key...: bad permissions" "WARNING: UNPROTECTED PRIVATE KEY FILE!"

Solución: El sistema operativo ha restablecido los permisos de seguridad (suele ocurrir al mover carpetas o reinstalar Windows).

    Ve a backend/config/ssh/.

    Ejecuta nuevamente fix_permissions.bat como administrador.

    Reinicia tu terminal o la aplicación.

✅ Checklist de Verificación

Asegúrate de tener todo listo antes de reportar un error:

    [ ] Proyecto clonado correctamente.

    [ ] El archivo jesusssh4.unknown está dentro de backend/config/ssh/.

    [ ] Has ejecutado el script .bat y te ha dado el mensaje de "ÉXITO".

    [ ] Has verificado que no haya errores en C:\xampp\apache\logs\error.log.

🆘 Soporte Técnico

Si el problema persiste tras seguir estos pasos:

    Toma una captura de pantalla del error.

    Verifica que tu usuario de Windows tenga permisos de escritura en la carpeta del proyecto.

    Contacta al equipo de desarrollo.