# Configuración Inicial - Sparta Ledger

## 🚀 Setup rápido (solo una vez por equipo)

### Paso 1: Clonar el proyecto
```bash
git clone <URL_DEL_REPO>
cd sparta___SPARTA_SECRET_REDACTED__
```

### Paso 2: Obtener la llave SSH
Solicita al administrador del proyecto el archivo:
- **Archivo**: `jesusssh4.unknown`
- **Ubicación**: Cópialo en `backend/config/ssh/`

### Paso 3: Configurar permisos
1. Ve a la raíz del proyecto
2. Busca el archivo `setup.bat`
3. **Click derecho** → **"Ejecutar como administrador"**
4. Espera a que termine (5 segundos)
5. ¡Listo!

---

## ⚠️ Si tienes problemas después

### Error: "Bad permissions" o "UNPROTECTED PRIVATE KEY"

**Solución rápida:**
1. Ve a: `backend/config/ssh/`
2. Click derecho en `fix_permissions.bat`
3. **"Ejecutar como administrador"**
4. Recarga la aplicación

---

## 📋 Checklist de instalación

- [ ] Proyecto clonado
- [ ] Archivo `jesusssh4.unknown` en `backend/config/ssh/`
- [ ] Ejecutado `setup.bat` como administrador
- [ ] Aplicación funcionando correctamente

---

## 🆘 Soporte

Si sigues teniendo problemas:
1. Verifica que el archivo `jesusssh4.unknown` existe en `backend/config/ssh/`
2. Ejecuta `setup.bat` como administrador
3. Revisa los logs de Apache en: `C:\xampp\apache\logs\error.log`