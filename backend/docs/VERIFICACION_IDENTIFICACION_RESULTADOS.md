# Verificación identificación oficial – Residencia temporal

**Fecha:** 2 de marzo de 2026  
**Imágenes:** `pruebas_OCR/frente.jpeg`, `pruebas_OCR/inverso.jpeg`  
**Tipo:** RESIDENCIA_TEMPORAL (Residente temporal INM)

---

## Cotejo automático frente ↔ reverso

El sistema ahora compara automáticamente los datos del frente y el reverso.

### Endpoint de cotejo: `POST /api/v1/cotejar-documento`

Envía ambas imágenes y recibe la comparación:

```powershell
curl.exe -s -X POST "http://localhost:8000/api/v1/cotejar-documento?tipo_documento=RESIDENCIA_TEMPORAL" `
  -H "X-API-Key: sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key" `
  -F "frente=@c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\pruebas_OCR\frente.jpeg" `
  -F "reverso=@c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\pruebas_OCR\inverso.jpeg"
```

### Resultado del cotejo

| Dato | Frente | Reverso (MRZ) | ¿Coincide? |
|------|--------|---------------|------------|
| **Nombre** | GONZALEZ LEYVA LAZARO RAUDEL | GONZALEZ LEYVA LAZARO RAUDEL | **Sí** |
| **Fecha nacimiento** | 12/01/1996 (del CURP) | 12/01/1996 (del MRZ) | **Sí** |
| **CURP** | GOLL960112HNENYZ09 | — (no impreso en reverso) | — |

### Campos del reverso (MRZ)

- **`mrz_nombre_completo`**: `GONZALEZ LEYVA LAZARO RAUDEL`
- **`mrz_fecha_nacimiento`**: `12/01/1996`

---

## Calidad de foto

En **`checks.forense`** la API incluye:

- **`calidad_foto`**: `ok` | `revisar_brillo` | `revisar_borroso` | `revisar_brillo_y_borroso`.
- **`borroso`**: `true` si se detecta posible desenfoque (Laplacian bajo).
- **`brillo_excesivo`** y **`porcentaje_sobreexpuesto`**.

---

## 1. FRENTE (`frente.jpeg`)

### Resumen
| Campo | Valor API | Valor esperado | ¿Correcto? |
|-------|-----------|----------------|------------|
| **CURP** | `GOLL960112HNENYZ09` | GOLL960112HNENYZ09 | **Sí** |
| **NUE** | `0000002848625` | 0000002848625 | **Sí** |
| **Tipo doc** | RESIDENCIA_TEMPORAL | RESIDENTE TEMPORAL | **Sí** |
| **Expedición** | 19/12/2025 | 19/12/2025 | **Sí** |
| **Vencimiento** | 18/12/2028 | 18/12/2028 | **Sí** |
| **Nombre OCR** | GONZALEZ LEYVA LAZARO RAUDEL | GONZALEZ LEYVA LAZARO RAUDEL | **Sí** |
| **Fecha nac. (CURP)** | 12/01/1996 | 12/01/1996 | **Sí** |
| **Textos INM** | detectado | — | **Sí** |
| **Calidad foto** | ok | — | **Sí** |

### Resultado global
- **Score:** 93/100
- **Resultado:** ORIGINAL
- **Confianza:** ALTA
- **Tiempo:** ~30 s

### Detección de CURP — Cómo funciona

El OCR de Tesseract lee `GOLL960112HNENYZOS` (confunde 0→O y 9→S). El sistema:

1. Busca la etiqueta "CURP:" en el texto OCR
2. Toma una ventana de texto tras la etiqueta
3. Busca patrones de 18 caracteres alfanuméricos (estricto → flexible → ultra-flexible)
4. Aplica `_corregir_curp()` con mapa de confusiones OCR:
   - `O → 0`, `S → [9, 5]`, `I → 1`, `Z → 2`, `B → 8`, `G → [9, 6]`
5. Valida con el patrón oficial de la SEGOB (18 caracteres)
6. Solo devuelve CURP si pasa la validación oficial

---

## 2. REVERSO (`inverso.jpeg`)

### Resumen
| Campo | Valor API | Comentario |
|-------|-----------|------------|
| **MRZ nombre** | `GONZALEZ LEYVA LAZARO RAUDEL` | Nombre completo extraído del MRZ |
| **MRZ fecha nac.** | `12/01/1996` | YYMMDD → 960112 → 12/01/1996 |
| **Número MRZ** | `14095434` | Del MRZ (I&lt;MEX14095434) — diferente al NUE del frente |
| **Expedición** | 19/12/2025 | Detectada |
| **CURP** | `null` | Correcto: no se inventa desde MRZ |
| **Calidad foto** | ok | Sin problemas |

### Resultado global
- **Score:** 81/100
- **Resultado:** ORIGINAL
- **Confianza:** MEDIA
- **Tiempo:** ~27 s

---

## 3. Nuevos campos en la API

### En `ocr_campos`:
| Campo | Descripción |
|-------|-------------|
| `nombre_ocr` | Nombre extraído del texto OCR (frente) |
| `fecha_nacimiento_curp` | Fecha nacimiento decodificada del CURP |
| `mrz_nombre_completo` | Nombre completo del MRZ (reverso) |
| `mrz_fecha_nacimiento` | Fecha nacimiento del MRZ |
| `cotejo_frente_reverso` | Comparación automática (si ambos datos disponibles) |

### Endpoint de cotejo:
`POST /api/v1/cotejar-documento` — Recibe `frente` y `reverso`, compara automáticamente nombre y fecha de nacimiento.

---

## 4. Comandos para repetir las pruebas

```powershell
# Frente
curl.exe -s -X POST "http://localhost:8000/api/v1/verificar?tipo_documento=RESIDENCIA_TEMPORAL" `
  -H "X-API-Key: sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key" `
  -F "imagen=@c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\pruebas_OCR\frente.jpeg" | python -m json.tool

# Reverso
curl.exe -s -X POST "http://localhost:8000/api/v1/verificar?tipo_documento=RESIDENCIA_TEMPORAL" `
  -H "X-API-Key: sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key" `
  -F "imagen=@c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\pruebas_OCR\inverso.jpeg" | python -m json.tool

# Cotejo completo (frente + reverso)
curl.exe -s -X POST "http://localhost:8000/api/v1/cotejar-documento?tipo_documento=RESIDENCIA_TEMPORAL" `
  -H "X-API-Key: sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key" `
  -F "frente=@c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\pruebas_OCR\frente.jpeg" `
  -F "reverso=@c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\pruebas_OCR\inverso.jpeg" | python -m json.tool
```
