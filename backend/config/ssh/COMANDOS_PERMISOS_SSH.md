# Comandos para configurar permisos SSH en Windows Server

## 🎯 Objetivo
Dar permisos de lectura SOLO al usuario `SYSTEM` (quien ejecuta Apache) para que SSH acepte la clave privada.

---

## ✅ MÉTODO 1: Usar setup.bat (RECOMENDADO)

Ejecutar como **Administrador** en el servidor:

```batch
cd C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\config\ssh
setup.bat
```

El script automáticamente:
1. Resetea permisos corruptos
2. Toma propiedad del archivo
3. Quita herencia de permisos
4. Remueve usuarios innecesarios
5. Da permisos SOLO a `NT AUTHORITY\SYSTEM` con lectura

---

## 🔧 MÉTODO 2: Comandos manuales en PowerShell (Si setup.bat falla)

Ejecutar **PowerShell como Administrador** en el servidor:

```powershell
# Navegar a la carpeta
cd C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\config\ssh

# 1. Resetear permisos existentes
icacls jesusssh4.unknown /reset

# 2. Tomar propiedad del archivo (asegura que puedas modificarlo)
takeown /f jesusssh4.unknown

# 3. Quitar herencia de permisos padre
icacls jesusssh4.unknown /inheritance:r

# 4. Remover explícitamente todos los usuarios/grupos comunes
icacls jesusssh4.unknown /remove "Users"
icacls jesusssh4.unknown /remove "Administrators"
icacls jesusssh4.unknown /remove "Everyone"
icacls jesusssh4.unknown /remove "Authenticated Users"
icacls jesusssh4.unknown /remove "BUILTIN\Users"
icacls jesusssh4.unknown /remove "BUILTIN\Administrators"

# 5. Dar permisos SOLO a SYSTEM (Lectura únicamente)
icacls jesusssh4.unknown /grant:r "NT AUTHORITY\SYSTEM:(R)"

# 6. VERIFICAR permisos finales (debe mostrar SOLO SYSTEM)
icacls jesusssh4.unknown
```

### Resultado esperado del paso 6:
```
jesusssh4.unknown NT AUTHORITY\SYSTEM:(R)

Successfully processed 1 files; Failed processing 0 files
```

---

## 📋 MÉTODO 3: Interfaz gráfica de Windows (Si prefieres visual)

1. **Click derecho** en `jesusssh4.unknown` → **Propiedades**

2. Pestaña **Seguridad** → **Avanzadas**

3. **Cambiar propietario:**
   - Click en "Cambiar" junto al propietario
   - Escribir: `SYSTEM`
   - Click en "Comprobar nombres"
   - Aceptar

4. **Deshabilitar herencia:**
   - Click en "Deshabilitar la herencia"
   - Elegir "Convertir los permisos heredados en permisos explícitos en este objeto"

5. **Eliminar TODOS los usuarios/grupos** excepto SYSTEM:
   - Seleccionar cada entrada (Users, Administrators, etc.)
   - Click en "Quitar"
   - Dejar SOLO `NT AUTHORITY\SYSTEM`

6. **Verificar permisos de SYSTEM:**
   - Debe tener SOLO **Lectura** marcado (Read)
   - Desmarcar cualquier otro permiso (Write, Execute, etc.)

7. **Aceptar** todo

---

## 🚀 MÉTODO 4: Copiar la clave a ubicación segura (Plan B)

Si ninguno de los métodos anteriores funciona, podemos mover la clave a una ubicación donde SYSTEM ya sea propietario:

```powershell
# 1. Copiar clave a carpeta temporal de SYSTEM
copy C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\config\ssh\jesusssh4.unknown C:\Windows\Temp\jesusssh4_system.key

# 2. Dar permisos restrictivos
icacls C:\Windows\Temp\jesusssh4_system.key /inheritance:r
icacls C:\Windows\Temp\jesusssh4_system.key /grant:r "NT AUTHORITY\SYSTEM:(R)"

# 3. Verificar
icacls C:\Windows\Temp\jesusssh4_system.key
```

Luego modificar en `SegundometroDAO.php`:
```php
private static $SSH_KEY = 'C:\Windows\Temp\jesusssh4_system.key';
```

---

## ✅ Verificación final

Después de cualquier método, verificar con el diagnóstico:

1. Entrar a http://34.51.32.249/
2. Click en botón flotante 🔍 (esquina inferior derecha)
3. Buscar en sección 3 (CLAVE SSH):
   - Debe decir: `✅ Usuario actual puede leer la clave`
4. Buscar en sección 4 (CONECTIVIDAD SSH):
   - Test 2 debe decir: `✅ Conexión SSH exitosa` (sin warnings de UNPROTECTED)

---

## 📝 Notas importantes:

- **Usuario que ejecuta Apache:** `SYSTEM` (verificado en el diagnóstico)
- **Por qué falla:** SSH rechaza claves con permisos `0666` (múltiples usuarios pueden leer/escribir)
- **Solución:** Permisos restrictivos - SOLO `SYSTEM` puede leer
- **Seguridad:** En producción esto es correcto; la clave debe ser legible solo por quien la usa

---

## 🆘 Si todo falla:

Contacta y comparte el resultado del diagnóstico después de intentar estos métodos.
