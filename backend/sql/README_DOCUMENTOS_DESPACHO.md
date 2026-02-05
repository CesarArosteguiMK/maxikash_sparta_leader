# Sistema de Gestión de Documentos para Despachos

## Descripción
Sistema completo para la carga, gestión y descarga de documentos requeridos para cada despacho de cobranza.

## Componentes

### Base de Datos
- **catalogo_documentos_despacho**: Catálogo de tipos de documentos requeridos
- **documentos_despacho**: Registro de documentos cargados por cada despacho

### Backend

#### Modelo (Models\Despachos)
- `obtenerCatalogoDocumentos()`: Obtiene el listado de documentos requeridos
- `obtenerDocumentosDespacho($idPersona)`: Obtiene documentos cargados de un despacho
- `subirDocumento($idPersona, $idCatalogoDocumento, $nombreArchivo, $rutaArchivo)`: Registra nuevo documento

#### Controlador (Controllers\Despachos)
- `ObtenerCatalogoDocumentos()`: Endpoint GET para catálogo
- `ObtenerDocumentosDespacho()`: Endpoint POST para documentos del despacho
- `SubirDocumento()`: Endpoint POST para carga de archivos
- `DescargarDocumento($idDocumento)`: Endpoint GET para descarga

### Frontend

#### Vista (views/asignacion_creditosDespacho.php)
- Acordeón de documentos con Bootstrap 5 Collapse
- Formularios de carga por documento
- Indicadores de estatus (Vigente/Vencido/Rechazado)
- Botones de descarga y reemplazo

#### JavaScript
- `cargarCatalogoDocumentos()`: Carga catálogo al iniciar
- `cargarDocumentosDespacho(idPersona)`: Carga documentos al seleccionar despacho
- `renderizarAcordeonDocumentos()`: Genera HTML del acordeón
- `subirDocumento(event, idCatalogoDocumento)`: Maneja carga
- `reemplazarDocumento(idCatalogoDocumento)`: Permite reemplazar documento existente

## Flujo de Trabajo

1. Usuario selecciona un despacho
2. Se carga automáticamente el catálogo de documentos
3. Se consultan documentos ya cargados
4. Se renderiza acordeón mostrando:
   - **Sin subir**: Formulario de carga
   - **Vigente**: Badge verde + botón descarga + botón reemplazar
   - **Vencido**: Badge amarillo + botón descarga + botón reemplazar
   - **Rechazado**: Badge rojo + botón descarga + botón reemplazar

## Validaciones

### Frontend
- Extensiones permitidas: PDF, JPG, JPEG, PNG, DOC, DOCX
- Tamaño máximo: 5MB

### Backend
- Validación de extensión
- Validación de tamaño
- Generación de nombre único
- Manejo de errores con rollback (si falla BD, elimina archivo)

## Almacenamiento
- Ruta física: `/uploads/documentos_despacho/`
- Nombre archivo: `{nombre_original}_{timestamp}.{extension}`
- Registro BD: `documentos_despacho` con referencias a despacho, catálogo y usuario

## Estatus de Documentos

- **Vigente**: Documento aprobado y activo (badge verde)
- **Vencido**: Documento que requiere actualización (badge amarillo)
- **Rechazado**: Documento no válido (badge rojo)

## Seguridad
- Validación de sesión (usuario_id)
- Validación de extensiones en frontend y backend
- Prevención de sobrescritura con timestamp
- Constraints de FK en base de datos

## Instalación

1. Ejecutar script SQL:
   ```bash
   mysql -u root -p __SPARTA_SECRET_REDACTED__ < backend/sql/documentos_despacho_setup.sql
   ```

2. Verificar permisos del directorio:
   ```bash
   chmod 777 uploads/documentos_despacho/
   ```

3. El sistema está listo para usar

## Mejoras Futuras
- [ ] Notificaciones por correo al cargar/actualizar documento
- [ ] Vencimientos automáticos por tipo de documento
- [ ] Historial de versiones de documentos
- [ ] Vista previa de documentos en modal
- [ ] Firma digital de documentos
- [ ] Exportar reporte de documentos faltantes
