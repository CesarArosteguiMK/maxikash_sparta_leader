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
            // 🎨 DATOS SIMULADOS PARA DISEÑO
            const generarDatosSimulados = () => {
                const archivos = [];
                const fechaHoy = new Date();
                const fechaAyer = new Date(fechaHoy);
                fechaAyer.setDate(fechaAyer.getDate() - 1);
                
                const fechas = [
                    {
                        fecha: fechaHoy.toISOString().split('T')[0],
                        fechaArchivo: fechaHoy.toISOString().split('T')[0].replace(/-/g, ''),
                        display: 'Hoy - ' + fechaHoy.toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
                    },
                    {
                        fecha: fechaAyer.toISOString().split('T')[0],
                        fechaArchivo: fechaAyer.toISOString().split('T')[0].replace(/-/g, ''),
                        display: 'Ayer - ' + fechaAyer.toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
                    }
                ];
                
                const horas = ['07', '09', '11', '13', '15', '17', '19', '21', '23'];
                
                fechas.forEach(fechaObj => {
                    horas.forEach(hora => {
                        const minuto = '31';
                        const segundo = String(Math.floor(Math.random() * 10) + 50).padStart(2, '0');
                        const nombre = `mega_rpt_${fechaObj.fechaArchivo}_${hora}_${minuto}_${segundo}.csv.zip`;
                        const tamanoMB = Math.floor(Math.random() * 50) + 5;
                        
                        archivos.push({
                            nombre: nombre,
                            fecha: fechaObj.fecha,
                            fecha_display: fechaObj.display,
                            hora: `${hora}:${minuto}:${segundo}`,
                            tamano: `${tamanoMB} MB`
                        });
                    });
                });
                
                return archivos;
            };
            
            // 🎨 RENDERIZAR ARCHIVOS EN LA INTERFAZ
            const renderArchivos = (archivos) => {
                const container = document.getElementById('archivos-container');
                
                if (!archivos || archivos.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">No se encontraron archivos de reportes</p>
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
                            display: archivo.fecha_display
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
                                                <th width="180" class="text-center"><i class="fa fa-cog me-1"></i>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${archivosDelDia.map((archivo, index) => `
                                                <tr>
                                                    <td class="text-muted">${index + 1}</td>
                                                    <td class="font-monospace text-primary fw-semibold">${archivo.nombre}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info">${archivo.hora}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="text-muted">${archivo.tamano}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button 
                                                            class="btn btn-sm btn-success" 
                                                            onclick="copiarArchivo('${archivo.nombre}')"
                                                            title="Copiar archivo con +1 segundo">
                                                            <i class="fa fa-copy me-1"></i>Copiar +1s
                                                        </button>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            };
            
            // 📋 COPIAR ARCHIVO CON +1 SEGUNDO (SIMULADO)
            const copiarArchivo = async (nombreArchivo) => {
                // Extraer componentes del nombre
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
                            // Incrementar fecha (simplificado para demo)
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
                
                // Simular proceso
                Swal.fire({
                    title: 'Procesando...',
                    html: 'Ejecutando comando de copia',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Simular delay de ejecución
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Archivo copiado exitosamente!',
                        html: `
                            <div class="text-start">
                                <div class="alert alert-success">
                                    <div class="mb-2">
                                        <strong>✅ Origen:</strong><br>
                                        <code>${nombreArchivo}</code>
                                    </div>
                                    <div>
                                        <strong>✅ Destino:</strong><br>
                                        <code>${nombreDestino}</code>
                                    </div>
                                </div>
                                <p class="text-muted mb-0 small">
                                    <i class="fa fa-info-circle me-1"></i>
                                    El archivo se ha copiado correctamente en el servidor
                                </p>
                            </div>
                        `,
                        confirmButtonText: 'Aceptar'
                    });
                    
                    // Recargar lista (en producción esto actualizaría desde el servidor)
                    // listarArchivos();
                }, 1500);
            };
            
            // 🔄 REFRESCAR LISTA
            const refrescarLista = () => {
                Swal.fire({
                    title: 'Actualizando...',
                    text: 'Cargando archivos del servidor',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                setTimeout(() => {
                    const archivos = generarDatosSimulados();
                    renderArchivos(archivos);
                    Swal.close();
                }, 1000);
            };
            
            // 🚀 INICIALIZAR AL CARGAR
            document.addEventListener('DOMContentLoaded', () => {
                const archivos = generarDatosSimulados();
                renderArchivos(archivos);
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
            
            self::json([
                'success' => true,
                'datos' => $archivos
            ]);
        } catch (\Exception $e) {
            self::json([
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
            
            self::json([
                'success' => true,
                'mensaje' => 'Archivo copiado exitosamente',
                'datos' => $resultado
            ]);
        } catch (\Exception $e) {
            self::json([
                'success' => false,
                'mensaje' => 'Error al copiar archivo: ' . $e->getMessage()
            ]);
        }
    }
}
