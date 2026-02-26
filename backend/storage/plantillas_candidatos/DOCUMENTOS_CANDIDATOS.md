# Plantillas para candidatos

Copia aquí los PDFs que el candidato puede descargar:

1. **carta_no_adeudo_infonavit_fonacot.pdf**  
   Carta de no créditos INFONAVIT y FONACOT. El candidato la descarga, firma y sube si no tiene hoja de retención.

2. **solicitud_interna___SPARTA_SECRET_REDACTED__.pdf**  
   Plantilla de Solicitud Interna Maxikash. Se prellenará con datos del candidato (nombre, email, teléfono, puesto, departamento, fecha); el candidato la descarga, firma y sube.

Nombres exactos de archivo (en minúsculas, sin espacios):
- `carta_no_adeudo_infonavit_fonacot.pdf`
- `solicitud_interna___SPARTA_SECRET_REDACTED__.pdf`

---

## Ajustar posiciones del texto en la Solicitud Interna

Si al descargar la solicitud los datos no coinciden con los recuadros de tu PDF, edita en el controlador las posiciones (en milímetros):

**Archivo:** `backend/controllers/CapHum.php`  
**Método:** `descargarDocumentoCandidato`  
**Busca:** el array `$posiciones` dentro del bloque `if ($tipo === 'solicitud_interna')`.

Cada elemento tiene:
- **x**: margen izquierdo en mm (ej. 30).
- **y**: distancia desde el borde superior de la página en mm.
- **texto**: el valor que se imprime (no cambiar, viene de la BD).

Orden actual: nombre completo, email, teléfono, puesto, departamento, fecha.  
Ajusta solo `x` e `y` hasta que coincidan con tu plantilla. Ejemplo: si “Nombre” en tu PDF está más abajo, aumenta el primer `y` (p. ej. de 58 a 65).
