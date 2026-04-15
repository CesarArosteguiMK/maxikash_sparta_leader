<?php

namespace controllers;

use Core\Controller;
use Models\Condonaciones as CondonacionesDAO;

class Condonaciones extends Controller
{
    public function consulta()
    {
        $script = <<<'HTML'
        <script>
        let valorOriginal = '';
        let condonacionActiva = null;

        /* ---------- CARGAR CONDONACIONES ---------- */
        function getCondonaciones() {
          http.request({
            endpoint: "/condonaciones/getConsultaCondonaciones",
            method: "GET",
            onSuccess: (resp) => {
              if (resp.success && resp.datos) {
                renderCondonaciones(resp.datos);
              } else {
                console.warn('No se encontraron condonaciones');
                document.getElementById('listaCondonaciones').innerHTML =
                  '<div class="text-center py-5"><p class="text-muted">No hay condonaciones registradas</p></div>';
              }
            },
            onError: (err) => {
              console.error('Error al cargar condonaciones:', err);
              document.getElementById('listaCondonaciones').innerHTML =
                '<div class="text-center py-5"><p class="text-danger">Error al cargar datos</p></div>';
            }
          });
        }

        /* ---------- RENDERIZAR CONDONACIONES ---------- */
        function renderCondonaciones(datos) {
          const container = document.getElementById('listaCondonaciones');
          if (!datos || datos.length === 0) {
            container.innerHTML = '<div class="text-center py-5"><p class="text-muted">No hay condonaciones</p></div>';
            return;
          }

          let html = '<div class="row g-3">';
          datos.forEach(item => {
            const badge = item.estado === 'aprobada'
              ? '<span class="badge bg-success">Aprobada</span>'
              : item.estado === 'rechazada'
              ? '<span class="badge bg-danger">Rechazada</span>'
              : '<span class="badge bg-warning">Pendiente</span>';

            html += `
              <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm hover-shadow cursor-pointer"
                     onclick="seleccionarCondonacion(${item.id})">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <h5 class="card-title mb-0">Condonación #${item.id}</h5>
                      ${badge}
                    </div>
                    <p class="text-muted mb-2"><strong>Colaborador:</strong> ${item.nombre_colaborador || 'N/A'}</p>
                    <p class="text-muted mb-2"><strong>Monto:</strong> $${parseFloat(item.monto_condonado).toFixed(2)}</p>
                    <p class="text-muted mb-2"><strong>Fecha:</strong> ${item.fecha_solicitud}</p>
                    <p class="text-muted mb-0 text-truncate"><strong>Motivo:</strong> ${item.motivo || 'Sin especificar'}</p>
                  </div>
                </div>
              </div>
            `;
          });
          html += '</div>';
          container.innerHTML = html;
        }

        /* ---------- SELECCIONAR CONDONACIÓN ---------- */
        function seleccionarCondonacion(id) {
          condonacionActiva = id;
          getDetalleCondonacion(id);
        }

        /* ---------- DETALLE CONDONACIÓN ---------- */
        function getDetalleCondonacion(id) {
          http.request({
            endpoint: "/condonaciones/getDetalleCondonacion",
            method: "POST",
            data: { id_condonacion: id },
            onSuccess: (resp) => {
              if (resp.success && resp.datos) {
                renderDetalleCondonacion(resp.datos);
              }
            },
            onError: (err) => {
              console.error('Error al cargar detalle:', err);
            }
          });
        }

        /* ---------- RENDERIZAR DETALLE ---------- */
        function renderDetalleCondonacion(datos) {
          const container = document.getElementById('detalleCondonacion');

          const badgeEstado = datos.estado === 'aprobada'
            ? '<span class="badge bg-label-success">Aprobada</span>'
            : datos.estado === 'rechazada'
            ? '<span class="badge bg-label-danger">Rechazada</span>'
            : '<span class="badge bg-label-warning">Pendiente</span>';

          const botonesAccion = datos.estado === 'pendiente' ? `
            <button class="btn btn-success btn-sm" onclick="cambiarEstadoCondonacion(${datos.id}, 'aprobada')">
              <i class="bx bx-check"></i> Aprobar
            </button>
            <button class="btn btn-danger btn-sm" onclick="cambiarEstadoCondonacion(${datos.id}, 'rechazada')">
              <i class="bx bx-x"></i> Rechazar
            </button>
          ` : '';

          container.innerHTML = `
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Condonación #${datos.id}</h5>
                ${badgeEstado}
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <p class="mb-2"><strong>Colaborador:</strong> ${datos.nombre_colaborador}</p>
                    <p class="mb-2"><strong>Departamento:</strong> ${datos.departamento || 'N/A'}</p>
                    <p class="mb-2"><strong>Puesto:</strong> ${datos.puesto || 'N/A'}</p>
                  </div>
                  <div class="col-md-6">
                    <p class="mb-2"><strong>Monto Condonado:</strong> $${parseFloat(datos.monto_condonado).toFixed(2)}</p>
                    <p class="mb-2"><strong>Fecha Solicitud:</strong> ${datos.fecha_solicitud}</p>
                    <p class="mb-2"><strong>Solicitado por:</strong> ${datos.usuario_solicita || 'N/A'}</p>
                  </div>
                </div>
                <div class="mb-3">
                  <strong>Motivo:</strong>
                  <p class="mt-2">${datos.motivo || 'Sin especificar'}</p>
                </div>
                ${datos.observaciones ? `
                <div class="mb-3">
                  <strong>Observaciones:</strong>
                  <p class="mt-2">${datos.observaciones}</p>
                </div>
                ` : ''}
                ${botonesAccion}
              </div>
            </div>
          `;
        }

        /* ---------- CAMBIAR ESTADO ---------- */
        function cambiarEstadoCondonacion(id, nuevoEstado) {
          const mensaje = nuevoEstado === 'aprobada'
            ? '¿Está seguro de aprobar esta condonación?'
            : '¿Está seguro de rechazar esta condonación?';

          if (!confirm(mensaje)) return;

          http.request({
            endpoint: "/condonaciones/cambiarEstado",
            method: "POST",
            data: {
              id_condonacion: id,
              estado: nuevoEstado
            },
            onSuccess: (resp) => {
              if (resp.success) {
                showToast('Estado actualizado correctamente', 'success');
                getCondonaciones();
                getDetalleCondonacion(id);
              } else {
                showToast(resp.mensaje || 'Error al actualizar', 'error');
              }
            },
            onError: (err) => {
              showToast('Error al procesar solicitud', 'error');
              console.error(err);
            }
          });
        }

        /* ---------- NUEVA CONDONACIÓN ---------- */
        function mostrarFormularioNuevaCondonacion() {
          // Modal o formulario para crear nueva condonación
          document.getElementById('detalleCondonacion').innerHTML = `
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">Nueva Condonación</h5>
              </div>
              <div class="card-body">
                <form id="formNuevaCondonacion" onsubmit="guardarNuevaCondonacion(event)">
                  <div class="mb-3">
                    <label class="form-label">ID Colaborador</label>
                    <input type="number" class="form-control" name="id_colaborador" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Monto a Condonar</label>
                    <input type="number" step="0.01" class="form-control" name="monto_condonado" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Motivo</label>
                    <textarea class="form-control" name="motivo" rows="3" required></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" name="observaciones" rows="2"></textarea>
                  </div>
                  <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> Guardar Condonación
                  </button>
                  <button type="button" class="btn btn-secondary" onclick="getCondonaciones()">
                    Cancelar
                  </button>
                </form>
              </div>
            </div>
          `;
        }

        /* ---------- GUARDAR NUEVA CONDONACIÓN ---------- */
        function guardarNuevaCondonacion(event) {
          event.preventDefault();
          const formData = new FormData(event.target);
          const datos = Object.fromEntries(formData);

          http.request({
            endpoint: "/condonaciones/crear",
            method: "POST",
            data: datos,
            onSuccess: (resp) => {
              if (resp.success) {
                showToast('Condonación creada exitosamente', 'success');
                getCondonaciones();
              } else {
                showToast(resp.mensaje || 'Error al crear', 'error');
              }
            },
            onError: (err) => {
              showToast('Error al procesar solicitud', 'error');
              console.error(err);
            }
          });
        }

        /* ---------- INICIALIZACIÓN ---------- */
        document.addEventListener('DOMContentLoaded', function() {
          getCondonaciones();
        });
        </script>
        HTML;

        $vista = <<<HTML
        <div class="container-fluid py-4">
          <div class="row">
            <!-- Panel Principal: Lista de Condonaciones -->
            <div class="col-lg-8">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Condonaciones</h4>
                <button class="btn btn-primary btn-sm" onclick="mostrarFormularioNuevaCondonacion()">
                  <i class="bx bx-plus"></i> Nueva Condonación
                </button>
              </div>
              <div id="listaCondonaciones">
                <!-- Se llena dinámicamente -->
              </div>
            </div>

            <!-- Panel Derecho: Detalle de Condonación -->
            <div class="col-lg-4">
              <div id="detalleCondonacion">
                <div class="card">
                  <div class="card-body text-center py-5">
                    <i class="bx bx-file-blank mb-3" style="font-size: 3rem; color: #d0d0d0;"></i>
                    <p class="text-muted">Selecciona una condonación para ver los detalles</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        HTML;

        echo $script;
        echo $vista;
    }

    /**
     * Historial de condonaciones de cobranza (menú Gastos Cobranza).
     */
    public function historial()
    {
        $this->set('titulo', 'Historial condonaciones');
        $this->set('script', '');
        $this->render('historial_condonaciones');
    }

    /* ========== MÉTODOS API ========== */

    public function getConsultaCondonaciones()
    {
        CondonacionesDAO::getConsultaCondonaciones();
    }

    public function getDetalleCondonacion()
    {
        $id = $_POST['id_condonacion'] ?? 0;
        CondonacionesDAO::getDetalleCondonacion($id);
    }

    public function cambiarEstado()
    {
        $id = $_POST['id_condonacion'] ?? 0;
        $estado = $_POST['estado'] ?? '';
        CondonacionesDAO::cambiarEstado($id, $estado);
    }

    public function crear()
    {
        $datos = $_POST;
        CondonacionesDAO::crear($datos);
    }

    public function getGastosCobranza()
    {
        $id_credito = $_POST['id_credito'] ?? 0;
        CondonacionesDAO::getGastosCobranza($id_credito);
    }

    public function getEstadisticas()
    {
        CondonacionesDAO::getEstadisticas();
    }

    public function getHistorialCredito()
    {
        $id_credito = $_POST['id_credito'] ?? 0;
        CondonacionesDAO::getHistorialCredito($id_credito);
    }
}
