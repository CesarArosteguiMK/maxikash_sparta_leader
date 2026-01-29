<?php

namespace Controllers;

use Core\Controller;
use Models\SegundometroDAO;

class Segundometro extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Vista principal del Shell de Segundómetro
     */
    public function shell()
    {
        $script = <<<'HTML'
        <script>
            // 🎨 RENDERIZAR ARCHIVOS EN LA INTERFAZ (SOLO ARCHIVOS REALES DEL SERVIDOR)
            const renderArchivos = (archivos) => {
                const container = document.getElementById('archivos-container');
                
                if (!archivos || archivos.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">No se encontraron archivos de reportes en el servidor</p>
                            <small class="text-muted">Los archivos aparecerán aquí cuando existan en el servidor</small>
                        </div>
                    `;
                    return;
                }
                
                // Agrupar por fecha
                const archivosPorFecha = {};
                archivos.forEach(archivo => {
                    if (!archivosPorFecha[archivo.fecha]) {
                        archivosPorFecha[archivo.fecha] = {
                            archivos: [],
                            display: archivo.fecha_display || archivo.fecha
                        };
                    }
                    archivosPorFecha[archivo.fecha].archivos.push(archivo);
                });
                
                let html = '';
                
                // Renderizar por fecha (más reciente primero)
                Object.keys(archivosPorFecha).sort().reverse().forEach(fecha => {
                    const data = archivosPorFecha[fecha];
                    const archivosDelDia = data.archivos;
                    
                    html += `
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fa fa-calendar-day me-2"></i>
                                    ${data.display}
                                    <span class="badge bg-light text-dark ms-2">${archivosDelDia.length} archivos</span>
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50"><i class="fa fa-hashtag"></i></th>
                                                <th><i class="fa fa-file-archive me-1"></i>Nombre del Archivo</th>
                                                <th width="120"><i class="fa fa-clock me-1"></i>Hora</th>
                                                <th width="120"><i class="fa fa-hdd me-1"></i>Tamaño</th>
                                                <th width="260" class="text-center"><i class="fa fa-cog me-1"></i>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${archivosDelDia.map((archivo, index) => {
                                                const owner = (archivo.owner || '').toString();
                                                const esProveedor = owner === 's2';
                                                const esNosotros = owner === 'root';
                                                let etiqueta = '';
                                                if (esProveedor) etiqueta = '<span class="text-danger ms-1">(proveedor)</span>';
                                                else if (esNosotros) etiqueta = '<span class="text-success ms-1">(nosotros)</span>';
                                                const nombreEscapado = (archivo.nombre || '').replace(/'/g, "\\\\'");
                                                return `
                                                <tr>
                                                    <td class="text-muted">${index + 1}</td>
                                                    <td class="font-monospace text-primary fw-semibold">${archivo.nombre} ${etiqueta}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info">${archivo.hora}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="text-muted">${archivo.tamano}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
                                                            <a href="/segundometro/descargarArchivo?nombre_archivo=${encodeURIComponent(archivo.nombre)}" class="btn btn-sm btn-primary" title="Descargar reporte">
                                                                <i class="fa fa-download me-1"></i>Descargar
                                                            </a>
                                                            <button 
                                                                class="btn btn-sm btn-success" 
                                                                onclick="copiarArchivo('${nombreEscapado}')"
                                                                title="Copiar archivo con +1 segundo">
                                                                <i class="fa fa-copy me-1"></i>Copiar +1s
                                                            </button>
                                                            ${esNosotros ? `
                                                            <button 
                                                                class="btn btn-sm btn-danger" 
                                                                onclick="eliminarArchivo('${nombreEscapado}')"
                                                                title="Eliminar archivo (solo nosotros)">
                                                                <i class="fa fa-trash me-1"></i>Eliminar
                                                            </button>
                                                            ` : ''}
                                                        </div>
                                                    </td>
                                                </tr>
                                            `;
                                            }).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            };
            
            // 📋 COPIAR ARCHIVO CON +1 SEGUNDO (LLAMADA REAL AL SERVIDOR)
            const copiarArchivo = async (nombreArchivo) => {
                // Extraer componentes del nombre para mostrar preview
                const match = nombreArchivo.match(/mega_rpt_(\d{8})_(\d{2})_(\d{2})_(\d{2})\.csv\.zip/);
                
                if (!match) {
                    Swal.fire('Error', 'Formato de archivo inválido', 'error');
                    return;
                }
                
                const [, fecha, hora, minuto, segundo] = match;
                let nuevoSegundo = parseInt(segundo) + 1;
                let nuevoMinuto = parseInt(minuto);
                let nuevaHora = parseInt(hora);
                let nuevaFecha = fecha;
                
                // Manejar overflow de segundos
                if (nuevoSegundo >= 60) {
                    nuevoSegundo = 0;
                    nuevoMinuto++;
                    
                    if (nuevoMinuto >= 60) {
                        nuevoMinuto = 0;
                        nuevaHora++;
                        
                        if (nuevaHora >= 24) {
                            nuevaHora = 0;
                            const dateObj = new Date(
                                parseInt(fecha.substring(0, 4)),
                                parseInt(fecha.substring(4, 6)) - 1,
                                parseInt(fecha.substring(6, 8))
                            );
                            dateObj.setDate(dateObj.getDate() + 1);
                            nuevaFecha = dateObj.toISOString().split('T')[0].replace(/-/g, '');
                        }
                    }
                }
                
                const nombreDestino = `mega_rpt_${nuevaFecha}_${String(nuevaHora).padStart(2, '0')}_${String(nuevoMinuto).padStart(2, '0')}_${String(nuevoSegundo).padStart(2, '0')}.csv.zip`;
                
                // Confirmar acción
                const result = await Swal.fire({
                    title: 'Confirmar copia de archivo',
                    html: `
                        <div class="text-start">
                            <p class="mb-3">¿Desea copiar este archivo con +1 segundo?</p>
                            <div class="alert alert-light border">
                                <div class="mb-2">
                                    <strong>📄 Origen:</strong><br>
                                    <code class="text-primary">${nombreArchivo}</code>
                                </div>
                                <div>
                                    <strong>📋 Destino:</strong><br>
                                    <code class="text-success">${nombreDestino}</code>
                                </div>
                            </div>
                            <div class="alert alert-warning mb-0">
                                <small><strong>Comando:</strong> <code>sudo cp ${nombreArchivo} ${nombreDestino}</code></small>
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, copiar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                });
                
                if (!result.isConfirmed) return;
                
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    html: 'Ejecutando comando de copia en el servidor remoto',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Llamada AJAX real al servidor
                try {
                    const formData = new FormData();
                    formData.append('nombre_archivo', nombreArchivo);
                    
                    const response = await fetch('/segundometro/copiarArchivo', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Front-Request': 'true'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        throw new Error(data.mensaje || 'Error desconocido');
                    }
                    
                    // Éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Archivo copiado exitosamente!',
                        html: `
                            <div class="text-start">
                                <div class="alert alert-success">
                                    <div class="mb-2">
                                        <strong>✅ Origen:</strong><br>
                                        <code>${data.datos.origen}</code>
                                    </div>
                                    <div>
                                        <strong>✅ Destino:</strong><br>
                                        <code>${data.datos.destino}</code>
                                    </div>
                                </div>
                                <p class="text-muted mb-0 small">
                                    <i class="fa fa-info-circle me-1"></i>
                                    El archivo se ha copiado correctamente en el servidor remoto
                                </p>
                            </div>
                        `,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Recargar lista después de copiar
                        listarArchivos();
                    });
                    
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al copiar archivo',
                        text: error.message || 'Ocurrió un error al ejecutar el comando en el servidor',
                        confirmButtonText: 'Aceptar'
                    });
                }
            };
            
            // 🗑️ ELIMINAR ARCHIVO (SOLO NOSOTROS / ROOT)
            const eliminarArchivo = async (nombreArchivo) => {
                const result = await Swal.fire({
                    title: '¿Eliminar archivo?',
                    html: '<p class="mb-2">Se eliminará permanentemente del servidor:</p><code class="d-block text-start">' + nombreArchivo + '</code><p class="text-danger mt-2 mb-0 small">Esta acción no se puede deshacer.</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                });
                if (!result.isConfirmed) return;
                Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                try {
                    const formData = new FormData();
                    formData.append('nombre_archivo', nombreArchivo);
                    const response = await fetch('/segundometro/eliminarArchivo', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Front-Request': 'true' }
                    });
                    const data = await response.json();
                    if (!data.success) throw new Error(data.mensaje || 'Error al eliminar');
                    Swal.fire({ icon: 'success', title: 'Archivo eliminado', text: 'El archivo se eliminó correctamente del servidor.', confirmButtonText: 'Aceptar' }).then(() => listarArchivos());
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Error al eliminar', text: error.message || 'No se pudo eliminar el archivo.', confirmButtonText: 'Aceptar' });
                }
            };
            
            // 🔄 LISTAR ARCHIVOS DESDE EL SERVIDOR (LLAMADA REAL)
            const listarArchivos = async (silent = false) => {
                const container = document.getElementById('archivos-container');
                try {
                    const response = await fetch('/segundometro/listarArchivos', {
                        method: 'GET',
                        headers: {
                            'Front-Request': 'true'
                        }
                    });
                    
                    const contentType = response.headers.get('Content-Type') || '';
                    if (!contentType.includes('application/json')) {
                        throw new Error('El servidor respondió con un formato inesperado. Compruebe que la sesión esté activa.');
                    }
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        throw new Error(data.mensaje || 'Error al obtener archivos');
                    }
                    
                    renderArchivos(data.datos || []);
                    
                } catch (error) {
                    if (!silent) {
                        console.error('Error al listar archivos:', error);
                        const msg = error.message || 'Error al conectar con el servidor';
                        container.innerHTML = `
                            <div class="alert alert-danger text-center">
                                <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                                <p class="mb-0">Error al conectar con el servidor</p>
                                <small class="text-muted">${msg}</small>
                            </div>
                        `;
                    }
                }
            };
            
            // 🚀 INICIALIZAR AL CARGAR Y ACTUALIZACIÓN AUTOMÁTICA CADA 30 s
            document.addEventListener('DOMContentLoaded', () => {
                listarArchivos(false);
                setInterval(() => listarArchivos(true), 30000);
            });
        </script>
        HTML;

        self::set("titulo", "Shell Segundómetro");
        self::set("script", $script);
        self::render("shell_segundometro");
    }

    /**
     * Listar archivos de reportes de los últimos N días
     */
    public function listarArchivos()
    {
        try {
            $archivos = SegundometroDAO::obtenerArchivos();
            
            self::respuestaJSON([
                'success' => true,
                'datos' => $archivos
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al listar archivos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Copiar archivo con +1 segundo
     */
    public function copiarArchivo()
    {
        try {
            $nombreArchivo = $_POST['nombre_archivo'] ?? null;
            
            if (!$nombreArchivo) {
                throw new \Exception('Nombre de archivo no proporcionado');
            }
            
            $resultado = SegundometroDAO::copiarConSegundoAdelantado($nombreArchivo);
            
            self::respuestaJSON([
                'success' => true,
                'mensaje' => 'Archivo copiado exitosamente',
                'datos' => $resultado
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al copiar archivo: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Descargar reporte: copia del remoto a temporal y envía al navegador
     */
    public function descargarArchivo()
    {
        $nombreArchivo = $_GET['nombre_archivo'] ?? null;
        if (!$nombreArchivo || !preg_match('/^mega_rpt_\d{8}_\d{2}_\d{2}_\d{2}\.csv\.zip$/', $nombreArchivo)) {
            header('HTTP/1.0 400 Bad Request');
            echo 'Nombre de archivo inválido';
            exit;
        }
        $rutaLocal = null;
        try {
            $rutaLocal = SegundometroDAO::copiarRemotoATemporal($nombreArchivo);
            if (!is_file($rutaLocal)) {
                throw new \Exception('No se pudo obtener el archivo');
            }
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Content-Length: ' . filesize($rutaLocal));
            readfile($rutaLocal);
            @unlink($rutaLocal);
            exit;
        } catch (\Exception $e) {
            if ($rutaLocal && is_file($rutaLocal)) {
                @unlink($rutaLocal);
            }
            header('HTTP/1.0 500');
            echo 'Error al descargar: ' . $e->getMessage();
            exit;
        }
    }
    
    /**
     * Eliminar archivo en el servidor remoto (solo si owner es root)
     */
    public function eliminarArchivo()
    {
        try {
            $nombreArchivo = $_POST['nombre_archivo'] ?? null;
            if (!$nombreArchivo) {
                throw new \Exception('Nombre de archivo no proporcionado');
            }
            SegundometroDAO::eliminarArchivo($nombreArchivo);
            self::respuestaJSON(['success' => true, 'mensaje' => 'Archivo eliminado correctamente']);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }
}
