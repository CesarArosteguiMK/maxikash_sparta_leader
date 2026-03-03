# Pruebas: Subir documentos y API de verificación

## 1. Comprobar que la API está en marcha

En una terminal:

```powershell
# Health
curl.exe -s "http://127.0.0.1:8000/api/v1/health"
# Debe devolver: {"status":"ok","version":"1.0.0"}

# Verificar una imagen (frente)
curl.exe -s -X POST "http://127.0.0.1:8000/api/v1/verificar?tipo_documento=RESIDENCIA_TEMPORAL" -H "X-API-Key: sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key" -F "imagen=@ruta\frente.jpeg"
# Debe devolver resultado ORIGINAL y score_autenticidad (ej. 93)
```

Si la API no está corriendo:

```powershell
cd c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\API
python -m uvicorn app.main:app --host 0.0.0.0 --port 8000
```

Configuración en `backend/config/config.ini`:

- `[doc_verificacion]` → `api_url = "http://127.0.0.1:8000/api/v1/verificar"` y `api_key` correctos.

---

## 2. Probar subida de documentos (enlace por token)

1. **Entra al menú Candidatos** (donde está LAZARO GONZALEZ MENDEZ o el candidato de prueba).
2. **Obtén el enlace para subir documentos**  
   (botón/link “Enlace para que el candidato suba sus documentos” o “Reenviar correo” para ver el enlace).  
   La URL será algo como:  
   `http://localhost/sparta___SPARTA_SECRET_REDACTED__/public/CapHum/subirDocumentosCandidato/TOKEN`
3. **Abre ese enlace en el navegador** (sin estar logueado, como el candidato).
4. **Sube archivos**  
   - Puedes subir **todos** los documentos de una vez (solicitud, CV, acta, CURP, ID frente y reverso, comprobante, constancia fiscal, NSS, etc.).  
   - O subir **solo algunos** (envío parcial).
5. **Envía el formulario.**  
   Debe aparecer un mensaje de éxito y, si la API está activa y hay frente + reverso (+ opcionalmente PDFs), en la respuesta puede venir `validacion_expediente` con `todo_coincide`, `checks_ok`, `alertas`, etc.
6. **Vuelve a abrir el mismo enlace** (F5 o abrir de nuevo la URL).  
   - Los documentos ya subidos deben mostrarse como **“Ya subido: [nombre archivo]”** en verde.  
   - Solo deben verse campos para los que **falta** subir algo.  
   - Si solo falta el reverso del ID, solo debe aparecer ese campo.
7. **(Opcional)** Sube solo los que faltan y envía de nuevo; debe aceptar el envío parcial.

---

## 3. Probar el botón “Documentación” y modal

1. **Entra al menú Candidatos** (con usuario que tenga permiso).
2. En la tabla, localiza al candidato que ya subió documentos.
3. **Haz clic en el botón “Documentación”** (icono carpeta, color gris).
4. Se abre un **modal** con el nombre del candidato y la **lista de documentos** subidos (tipo, nombre de archivo, fecha).
5. **Abrir**  
   - Clic en el botón/link “Abrir” (o icono de enlace) de un documento.  
   - Debe abrirse en otra pestaña el archivo (PDF o imagen) del expediente.
6. **Eliminar**  
   - Clic en el botón “Eliminar” (icono papelera) de un documento.  
   - Debe salir una confirmación.  
   - Al confirmar, el documento desaparece de la lista y el archivo se elimina del expediente.  
   - La lista del modal se actualiza sola.

---

## 4. Dónde se guardan los archivos

- **Ruta en disco:**  
  `backend/storage/candidatos/{id_candidato}/expediente/`  
  Con nombres fijos: `identificacion_frente.jpg`, `identificacion_reverso.jpg`, `curp.pdf`, `acta_nacimiento.pdf`, etc.
- **En base de datos:** tabla `candidato_documento`, campo `ruta_archivo` con valor como:  
  `candidatos/{id}/expediente/nombre_archivo.ext`

---

## 5. Si algo falla

- **“Faltan documentos”**  
  Solo aplica a tipos que **aún no** tienen archivo subido. Si vuelves a abrir el enlace y ya todo está subido, no deberías poder enviar vacío; debe decir “Selecciona al menos un documento para subir.” si intentas enviar sin adjuntar nada nuevo.
- **API no responde**  
  Comprueba que uvicorn esté corriendo en el puerto 8000 y que `config.ini` tenga la URL correcta (por ejemplo `http://127.0.0.1:8000` para local).
- **Modal Documentación vacío**  
  Comprueba que ese candidato tenga registros en `candidato_documento` y que las rutas en `ruta_archivo` existan bajo `backend/storage/`.
