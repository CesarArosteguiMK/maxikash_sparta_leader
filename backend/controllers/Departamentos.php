<?php

namespace controllers;

use Core\Controller;
use Models\Departamentos as DepartamentosDAO;

class Departamentos extends Controller
{
    public function consulta()
    {
        $script = <<<'HTML'
        <script>
        let valorOriginal = '';

        /** Misma regla que antes en oninput (sin reasignar value/textContent a ciegas: eso mandaba el cursor al inicio). */
        function sanitizarCadenaNombreDepartamento(s) {
          return String(s)
            .replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s]/g, '')
            .replace(/^\s+/, '')
            .replace(/\s{2,}/g, ' ');
        }

        function sanitizarInputNombre(el) {
          if (!el || el.tagName !== 'INPUT') return;
          const raw = el.value;
          const start = typeof el.selectionStart === 'number' ? el.selectionStart : raw.length;
          const end = typeof el.selectionEnd === 'number' ? el.selectionEnd : start;
          const nue = sanitizarCadenaNombreDepartamento(raw);
          if (nue === raw) return;
          el.value = nue;
          const mapPos = (pos) => {
            const p = Math.max(0, Math.min(pos, raw.length));
            return Math.min(sanitizarCadenaNombreDepartamento(raw.substring(0, p)).length, nue.length);
          };
          try {
            el.setSelectionRange(mapPos(start), mapPos(end));
          } catch (e) { /* tipos que no admiten caret */ }
        }

        /* ---------- TÍTULO DEPARTAMENTO ---------- */
        function inicioEdicionTitulo(element) {
          valorOriginal = element.textContent.trim();
        }

        function guardarTituloDepartamento(element) {
          const nuevoValor = sanitizarCadenaNombreDepartamento(element.textContent.trim());
          element.textContent = nuevoValor;
          const idDepartamento = element.dataset.departamentoId;

          if (!nuevoValor) {
            element.textContent = valorOriginal;
            element.contentEditable = false;
            return;
          }

          element.contentEditable = false;

          http.request({
            endpoint: "/departamentos/UpdateNombreDepartamento",
            method: "POST",
            data: {
              id_departamento: idDepartamento,
              nombre: nuevoValor
            },
            onSuccess: (resp) => {
              if (resp.success) {
                window.departamentoActivo && getDepartamentos();
              }
            },
            onError: (err) => {
              console.error('Error al actualizar departamento:', err);
              element.textContent = valorOriginal;
            }
          });
        }

        function forzarEdicionTitulo(icono) {
          const titulo = icono.previousElementSibling;
          valorOriginal = titulo.textContent.trim();
          titulo.contentEditable = true;
          titulo.focus();

          document.execCommand('selectAll', false, null);
          document.getSelection().collapseToEnd();

          titulo.onkeydown = (e) => {
            if (e.key === 'Enter') {
              e.preventDefault();
              titulo.blur();
            }
            if (e.key === 'Escape') {
              titulo.textContent = valorOriginal;
              titulo.contentEditable = false;
            }
          };

          titulo.onblur = () => guardarTituloDepartamento(titulo);
        }

        /* ---------- PUESTOS ---------- */
        function editarPuesto(icon) {
          const nombre = icon.previousElementSibling;

          valorOriginal = nombre.textContent.trim();
          nombre.contentEditable = true;
          nombre.focus();

          document.execCommand('selectAll', false, null);
          document.getSelection().collapseToEnd();

          nombre.onkeydown = (e) => {
            if (e.key === 'Enter') {
              e.preventDefault();
              guardarEdicion(nombre);
            }
            if (e.key === 'Escape') {
              cancelarEdicion(nombre);
            }
          };

          nombre.onblur = () => guardarEdicion(nombre);
        }

        function guardarEdicion(element) {
          const nuevoValor = sanitizarCadenaNombreDepartamento(element.textContent.trim());
          element.textContent = nuevoValor;

          if (!nuevoValor) {
            element.textContent = valorOriginal;
          }

          element.contentEditable = false;
        }

        function cancelarEdicion(element) {
          element.textContent = valorOriginal;
          element.contentEditable = false;
        }

        /* ---------- NUEVO PUESTO ---------- */
        function mostrarInputNuevoPuesto() {
          document.getElementById('nuevoPuestoContainer').classList.remove('d-none');
          document.getElementById('inputNuevoPuesto').focus();
        }

        function guardarNuevoPuesto() {
          const input = document.getElementById('inputNuevoPuesto');
          const id_departamento = document.getElementById('id_departamento').value.trim();
          const nombre = input.value.trim();
          if (!nombre) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Nombre requerido', text: 'Escriba el nombre del puesto.' });
            return;
          }
          if (!id_departamento) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Departamento', text: 'No se identificó el departamento. Cierre el modal y vuelva a abrirlo.' });
            return;
          }

          http.request({
            endpoint: "/departamentos/InsertPuesto",
            metodo: "POST",
            data: { nombre: nombre, id_departamento: id_departamento },
            onSuccess: (resp) => {
              if (resp.success) {
                cargarPuestosDepartamento(id_departamento);
                input.value = '';
                document.getElementById('nuevoPuestoContainer').classList.add('d-none');
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Puesto guardado', timer: 2000, showConfirmButton: false });
                return;
              }
              const msg = (resp && (resp.mensaje || resp.error)) ? (resp.mensaje || resp.error) : 'No se pudo guardar el puesto.';
              if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'No se guardó', text: msg });
              else console.warn(msg);
            },
            onError: (err) => {
              const msg = typeof err === 'string' ? err : (err && err.message) ? err.message : 'Error de red o del servidor.';
              if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se guardó', text: msg });
              else console.error('Error al llamar InsertPuesto:', err);
            }
          });
        }

        function abrirModalDepartamento(idDepartamento, nombreDepartamento) {
          window.departamentoActivo = idDepartamento;

          const titulo = document.getElementById('tituloDepartamento');
          titulo.textContent = nombreDepartamento;
          titulo.dataset.departamentoId = idDepartamento;
          titulo.contentEditable = false;

          document.getElementById('id_departamento').value = idDepartamento;

          const lista = document.getElementById('listaPuestos');
          lista.innerHTML = `
            <li class="text-center text-muted py-4">
              <i class="fa fa-spinner fa-spin me-2"></i> Cargando puestos...
            </li>
          `;

          const modal = new bootstrap.Modal(
            document.getElementById('modalDetalleDepartamento')
          );
          modal.show();

          cargarPuestosDepartamento(idDepartamento);
        }

        function eliminarDepartamento(idDepartamento, nombreDisplay) {
          const nombre = (nombreDisplay || 'este departamento').trim() || 'este departamento';
          if (!confirm('¿Eliminar el departamento "' + nombre + '"? Se borrarán también sus puestos. Esta acción no se puede deshacer.')) {
            return;
          }
          http.request({
            endpoint: "/departamentos/eliminarDepartamento",
            method: "POST",
            data: { id_departamento: idDepartamento },
            onSuccess: (resp) => {
              if (resp && resp.success === true) {
                cerrarModalDepartamentoSiAbierto();
                if (typeof getDepartamentos === 'function') getDepartamentos();
                return;
              }
              const msg = (resp && resp.mensaje) ? resp.mensaje : 'No se pudo eliminar el departamento.';
              alert(msg);
            },
            onError: () => {
              alert('Error al eliminar el departamento.');
            }
          });
        }

        function cerrarModalDepartamentoSiAbierto() {
          try {
            const modalEl = document.getElementById('modalDetalleDepartamento');
            if (modalEl && modalEl.classList.contains('show')) {
              const modal = bootstrap.Modal.getInstance(modalEl);
              if (modal) modal.hide();
            }
          } catch (e) {}
        }

        function eliminarDepartamentoDesdeModal() {
          const id = window.departamentoActivo;
          const tituloEl = document.getElementById('tituloDepartamento');
          const nombre = (tituloEl && tituloEl.textContent) ? tituloEl.textContent.trim() : 'este departamento';
          if (!id) {
            alert('No hay departamento seleccionado.');
            return;
          }
          if (!confirm('¿Eliminar el departamento "' + nombre + '"? Se borrarán también sus puestos. Esta acción no se puede deshacer.')) {
            return;
          }
          http.request({
            endpoint: "/departamentos/eliminarDepartamento",
            method: "POST",
            data: { id_departamento: id },
            onSuccess: (resp) => {
              if (resp && resp.success === true) {
                cerrarModalDepartamentoSiAbierto();
                if (typeof getDepartamentos === 'function') getDepartamentos();
                return;
              }
              const msg = (resp && resp.mensaje) ? resp.mensaje : 'No se pudo eliminar el departamento.';
              alert(msg);
            },
            onError: () => {
              alert('Error al eliminar el departamento.');
            }
          });
        }

        function actualizarContadorPuestosDepartamento(idDepartamento, totalPuestos) {
          const id = String(idDepartamento || '');
          const total = Number(totalPuestos || 0);
          const idSelector = window.CSS && CSS.escape ? CSS.escape(id) : id.replace(/"/g, '\\"');
          document.querySelectorAll(`[data-puestos-count-departamento="${idSelector}"]`).forEach(el => {
            el.textContent = total;
          });

          const grupos = window.departamentosOrganizacionAreasById || {};
          Object.values(grupos).forEach(areasPorPais => {
            Object.values(areasPorPais || {}).forEach(area => {
              (area.areas || []).forEach(dep => {
                if (String(dep.departamento_id) === id) {
                  dep.total_puestos = total;
                }
              });
            });
          });
        }

        function cargarPuestosDepartamento(idDepartamento) {
          var id = idDepartamento != null ? Number(idDepartamento) : 0;
          if (!id) {
            var lista = document.getElementById('listaPuestos');
            if (lista) lista.innerHTML = '<li class="text-center text-muted py-4">No hay puestos registrados</li>';
            return;
          }
          http.request({
            endpoint: "/departamentos/getPuestosPorDepartamento",
            method: "POST",
            contentType: "application/json; charset=UTF-8",
            processData: false,
            data: JSON.stringify({ id_departamento: id }),
            onSuccess: (resp) => {
              const lista = document.getElementById('listaPuestos');
              lista.innerHTML = '';
              const puestos = Array.isArray(resp.datos) ? resp.datos : [];
              actualizarContadorPuestosDepartamento(id, puestos.length);

              if (puestos.length === 0) {
                lista.innerHTML = `
                  <li class="text-center text-muted py-4">
                    No hay puestos registrados
                  </li>
                `;
                return;
              }

              puestos.forEach((p, i) => {
                lista.insertAdjacentHTML('beforeend', crearItemPuesto(p, i + 1));
              });
              initDragDropPuestos();
            }
          });
        }

        function crearItemPuesto(p, numero) {
          const num = numero || 1;
          const nombreEsc = (p.puesto_nombre || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
          const descEsc = (p.descripcion || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
          return `
            <li class="list-group-item drag-item d-flex align-items-center puesto-item mb-2 rounded"
                draggable="true"
                data-puesto-id="${p.id_puesto}">
              <span class="puesto-numero">${num}</span>
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2">
                  <h6 class="mb-0 fw-normal puesto-nombre"
                      contenteditable="false"
                      data-puesto-id="${p.id_puesto}">
                    ${nombreEsc}
                  </h6>
                  <i class="fa fa-pencil editar-puesto"
                     onclick="forzarEdicion(this)"></i>
                </div>
                <p class="text-muted mb-0 small">${descEsc}</p>
              </div>
            </li>
          `;
        }

        function actualizarNumerosPuestos() {
          const lista = document.getElementById('listaPuestos');
          if (!lista) return;
          const items = lista.querySelectorAll('.drag-item');
          items.forEach((el, i) => {
            const numSpan = el.querySelector('.puesto-numero');
            if (numSpan) numSpan.textContent = i + 1;
          });
        }

        function guardarOrdenPuestos() {
          const idDep = document.getElementById('id_departamento').value;
          if (!idDep) return;
          const lista = document.getElementById('listaPuestos');
          const items = lista.querySelectorAll('.drag-item[data-puesto-id]');
          const ordenes = Array.from(items).map(el => el.getAttribute('data-puesto-id'));
          if (ordenes.length === 0) return;
          http.request({
            endpoint: '/departamentos/UpdateOrdenPuestos',
            method: 'POST',
            data: { id_departamento: idDep, ordenes: ordenes },
            onSuccess: (resp) => {},
            onError: (err) => console.error('Error al guardar orden:', err)
          });
        }

        function initDragDropPuestos() {
          const lista = document.getElementById('listaPuestos');
          if (!lista) return;
          const items = lista.querySelectorAll('.drag-item');
          let draggedEl = null;
          items.forEach(item => {
            item.setAttribute('draggable', 'true');
            item.addEventListener('dragstart', function (e) {
              draggedEl = this;
              this.classList.add('dragging');
              e.dataTransfer.setData('text/plain', this.getAttribute('data-puesto-id'));
              e.dataTransfer.effectAllowed = 'move';
              e.dataTransfer.setDragImage(this, 0, 0);
            });
            item.addEventListener('dragend', function () {
              this.classList.remove('dragging');
              lista.querySelectorAll('.drag-item').forEach(i => i.classList.remove('drag-over'));
              draggedEl = null;
              actualizarNumerosPuestos();
              guardarOrdenPuestos();
            });
            item.addEventListener('dragover', function (e) {
              e.preventDefault();
              e.dataTransfer.dropEffect = 'move';
              if (draggedEl && draggedEl !== this) this.classList.add('drag-over');
            });
            item.addEventListener('dragleave', function () {
              this.classList.remove('drag-over');
            });
            item.addEventListener('drop', function (e) {
              e.preventDefault();
              this.classList.remove('drag-over');
              if (!draggedEl || draggedEl === this) return;
              const all = Array.from(lista.querySelectorAll('.drag-item'));
              const idxDrag = all.indexOf(draggedEl);
              const idxTarget = all.indexOf(this);
              if (idxDrag === -1 || idxTarget === -1) return;
              if (idxDrag < idxTarget) {
                this.parentNode.insertBefore(draggedEl, this.nextSibling);
              } else {
                this.parentNode.insertBefore(draggedEl, this);
              }
            });
          });
        }

        /* =====================================================
           DEPARTAMENTOS CON ACORDEONES POR PAÍS
           ===================================================== */

        let departamentosRequestSeq = 0;
        window.organizacionEmpresasPorPais = {};
        window.organizacionEmpresaSeleccionadaPorPais = {};
        window.organizacionEmpresaActiva = null;

        function getEmpresaOrganizacionSeleccionada(iso = '') {
          if (iso) return String(window.organizacionEmpresaSeleccionadaPorPais?.[iso] || '');
          return String(window.organizacionEmpresaActiva?.idEmpresa || '');
        }

        function coincideEmpresaOrganizacion(row, iso = '') {
          const empresaId = getEmpresaOrganizacionSeleccionada(iso);
          if (!empresaId) return true;
          return String(row?.id_empresa || 1) === empresaId;
        }

        function nombreEmpresaOrganizacion(row) {
          const id = String(row?.id_empresa || 1);
          return String(row?.nombre_empresa || (id === '2' ? 'Furia Motos' : 'MaxiKash'));
        }

        function registrarEmpresaOrganizacion(row) {
          const iso = String(row?.codigo_iso_pais || row?.codigo_iso || 'xx');
          const id = String(row?.id_empresa || 1);
          const nombre = nombreEmpresaOrganizacion(row);
          if (!window.organizacionEmpresasPorPais[iso]) window.organizacionEmpresasPorPais[iso] = [];
          if (!window.organizacionEmpresasPorPais[iso].some(item => String(item.id) === id)) {
            window.organizacionEmpresasPorPais[iso].push({ id, nombre });
          }
        }

        function getEmpresasPaisOrganizacion(iso) {
          return (window.organizacionEmpresasPorPais?.[iso] || [])
            .slice()
            .sort((a, b) => String(a.nombre || '').localeCompare(String(b.nombre || '')));
        }

        function getTodasEmpresasOrganizacion() {
          const mapa = new Map();
          Object.values(window.organizacionEmpresasPorPais || {}).forEach(lista => {
            (lista || []).forEach(row => {
              if (!mapa.has(String(row.id))) mapa.set(String(row.id), String(row.nombre || 'Empresa'));
            });
          });
          return Array.from(mapa.entries())
            .map(([id, nombre]) => ({ id, nombre }))
            .sort((a, b) => a.nombre.localeCompare(b.nombre));
        }

        function llenarEmpresasModalOrganizacion(valorActual = '') {
          const select = document.getElementById('addDepartamentoEmpresaSelect');
          if (!select) return;
          const empresas = getTodasEmpresasOrganizacion();
          select.innerHTML = '<option value="">-- Selecciona una empresa --</option>';
          empresas.forEach(row => {
            const opt = document.createElement('option');
            opt.value = row.id;
            opt.textContent = row.nombre;
            select.appendChild(opt);
          });
          if (valorActual && empresas.some(row => String(row.id) === String(valorActual))) select.value = String(valorActual);
          if (!select.value && empresas.length === 1) select.value = String(empresas[0].id);
        }

        function getDireccionesPaisEmpresa(iso) {
          if (!getEmpresaOrganizacionSeleccionada(iso)) return [];
          return Object.values(window.departamentosOrganizacionData?.[iso] || {})
            .filter(dir => dir && dir.id !== 'sin_direccion' && Number(dir.activo) === 1 && coincideEmpresaOrganizacion(dir, iso));
        }

        function renderPanelEmpresaPais(iso, nombrePais, imgUrl) {
          const empresas = getEmpresasPaisOrganizacion(iso);
          const totalDireccionesEmpresa = (idEmpresa) => Object.values(window.departamentosOrganizacionData?.[iso] || {})
            .filter(dir => dir && dir.id !== 'sin_direccion' && Number(dir.activo) === 1 && String(dir.id_empresa || 1) === String(idEmpresa))
            .length;
          const empresasHtml = empresas.length
            ? empresas.map(row => {
                const total = totalDireccionesEmpresa(row.id);
                return `
                  <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="card h-100 rounded-3 dept-card organizacion-empresa-card">
                      <div class="row h-100 g-0">
                        <div class="col-sm-8 d-flex flex-column justify-content-center p-3">
                          <h5 class="empresa-name mb-2">${escapeHtml(row.nombre)}</h5>
                          <p class="empresa-meta">Direcciones: <strong>${total}</strong></p>
                          <p class="empresa-meta mb-3">País: <strong>${escapeHtml(nombrePais || '')}</strong></p>
                          <button type="button" class="btn btn-sm btn-outline-primary fw-semibold text-uppercase"
                             onclick="seleccionarEmpresaPaisOrganizacion('${escapeJs(iso)}', '${escapeJs(row.id)}')">
                            Entrar
                          </button>
                        </div>
                        <div class="col-sm-4 d-flex align-items-center justify-content-center p-1">
                          <span class="organizacion-empresa-visual" aria-hidden="true">
                            <i class="fa-solid fa-building"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>`;
              }).join('')
            : '<div class="col-12 text-center text-muted py-4"><i class="fa fa-building fa-2x mb-2 d-block"></i>No hay empresas registradas en este pais.</div>';
          return `
            <div class="organizacion-empresa-title"><i class="fa fa-building"></i><span>Selecciona empresa</span></div>
            <div class="row g-4">${empresasHtml}</div>`;
        }

        function seleccionarEmpresaPaisOrganizacion(iso, idEmpresa) {
          window.organizacionEmpresaSeleccionadaPorPais[iso] = String(idEmpresa || '');
          window.organizacionEmpresaActiva = { iso, idEmpresa: String(idEmpresa || '') };
          window.direccionOrganizacionActiva = null;
          window.departamentoOrganizacionActivo = null;
          actualizarBotonAccionOrganizacion();
          const body = document.getElementById(`org-body-${iso}`);
          if (!body) return;
          const groupedPais = window.departamentosOrganizacionData?.[iso] || {};
          const nombrePais = Object.values(groupedPais)[0]?.nombre_pais || '';
          const imgUrl = "https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/lady-with-laptop-light.png";
          const empresa = getEmpresasPaisOrganizacion(iso).find(row => String(row.id) === String(idEmpresa || ''));
          const direcciones = getDireccionesPaisEmpresa(iso);
          body.innerHTML = `
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
              <div>
                <h5 class="mb-0">${escapeHtml(empresa?.nombre || 'Empresa')}</h5>
                <p class="text-muted mb-0">Direcciones registradas en ${escapeHtml(nombrePais || '')}</p>
              </div>
              <button type="button" class="btn btn-sm organizacion-back-btn" onclick="volverDireccionesEmpresa('${escapeJs(iso)}')">
                <i class="fa fa-arrow-left me-2"></i>Volver a direcciones
              </button>
            </div>
            <div class="row g-4">${renderDepartamentosPais(iso, nombrePais, direcciones, imgUrl)}</div>`;
          const backEmpresaBtn = body.querySelector('.organizacion-back-btn');
          if (backEmpresaBtn) {
            backEmpresaBtn.setAttribute('onclick', `volverDepartamentosPais('${escapeJs(iso)}')`);
            backEmpresaBtn.innerHTML = '<i class="fa fa-arrow-left me-2"></i>Volver a empresas';
          }
          const total = direcciones.length;
          const badge = document.getElementById(`org-count-badge-${iso}`);
          if (badge) badge.textContent = `${total} ${total === 1 ? 'direcciÃ³n' : 'direcciones'}`;
        }

        const getDepartamentos = () => {
          const requestSeq = ++departamentosRequestSeq;
          window.organizacionEmpresaActiva = null;
          window.departamentoOrganizacionActivo = null;
          window.direccionOrganizacionActiva = null;
          actualizarBotonAccionOrganizacion();
          http.request({
            endpoint: "/departamentos/getDepartamentos",
            onSuccess: (resp) => {
              http.request({
                endpoint: "/departamentos/getPaisesActivos",
                onSuccess: (respPaises) => {
                  http.request({
                    endpoint: "/departamentos/getDirecciones",
                    onSuccess: (respDir) => {
                      http.request({
                        endpoint: "/departamentos/getDepartamentosOrganizacionales",
                        onSuccess: (respOrg) => {
                  if (requestSeq !== departamentosRequestSeq) return;
                  const container = document.getElementById('departamentosAccordion');
                  if (!container) return;
                  container.innerHTML = '';

                  const imgUrl = "https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/lady-with-laptop-light.png";

                  const gradientes = {
                    'mx': 'linear-gradient(135deg, #006847, #009B3A)',
                    'gt': 'linear-gradient(135deg, #4997D0, #2E6BA4)',
                    'co': 'linear-gradient(135deg, #003893, #1A52A8)'
                  };

                  const grouped = {};
                  const areasById = {};
                  window.organizacionEmpresasPorPais = {};
                  const direcciones = (respDir.success && Array.isArray(respDir.datos)) ? respDir.datos : [];
                  direcciones.forEach(dir => {
                    registrarEmpresaOrganizacion(dir);
                    const iso = dir.codigo_iso_pais || 'xx';
                    const dirId = dir.id || 'sin_direccion';
                    const nombreDireccion = String(dir.nombre || '').trim();
                    if (!nombreDireccion || Number(dir.activo) !== 1) return;
                    if (!grouped[iso]) grouped[iso] = {};
                    if (!areasById[iso]) areasById[iso] = {};
                    if (!grouped[iso][dirId]) {
                      grouped[iso][dirId] = {
                        id: dirId,
                        nombre: nombreDireccion,
                        activo: true,
                        id_pais: dir.id_pais,
                        nombre_pais: dir.nombre_pais || '',
                        codigo_iso: iso,
                        id_empresa: dir.id_empresa || 1,
                        nombre_empresa: nombreEmpresaOrganizacion(dir),
                        areas: []
                      };
                    }
                  });
                  const departamentosOrganizacionales = (respOrg.success && Array.isArray(respOrg.datos)) ? respOrg.datos : [];
                  departamentosOrganizacionales.forEach(dep => {
                    registrarEmpresaOrganizacion(dep);
                    if (Number(dep.activo) !== 1) return;
                    if (Number(dep.direccion_activo) !== 1) return;
                    if (!dep.id_direccion || String(dep.id_direccion) === '0') return;
                    const iso = dep.codigo_iso_pais || 'xx';
                    const dirId = dep.id_direccion;
                    const orgId = dep.id || 'sin_area';
                    if (!grouped[iso]) grouped[iso] = {};
                    if (!areasById[iso]) areasById[iso] = {};
                    if (!grouped[iso][dirId]) {
                      grouped[iso][dirId] = {
                        id: dirId,
                        nombre: dep.direccion_nombre || 'Sin dirección',
                        activo: Number(dep.direccion_activo) === 1,
                        id_pais: dep.id_pais,
                        nombre_pais: dep.nombre_pais || '',
                        codigo_iso: iso,
                        id_empresa: dep.id_empresa || 1,
                        nombre_empresa: nombreEmpresaOrganizacion(dep),
                        areas: []
                      };
                    }
                    const depOrg = {
                      id: orgId,
                      nombre: dep.nombre || 'Sin área',
                      activo: Number(dep.activo) === 1,
                      id_pais: dep.id_pais,
                      id_direccion: dirId,
                      nombre_direccion: dep.direccion_nombre || '',
                      nombre_pais: dep.nombre_pais || '',
                      codigo_iso: iso,
                      id_empresa: dep.id_empresa || 1,
                      nombre_empresa: nombreEmpresaOrganizacion(dep),
                      areas: []
                    };
                    grouped[iso][dirId].areas.push(depOrg);
                    areasById[iso][orgId] = depOrg;
                  });

                  const areas = (resp.success && Array.isArray(resp.datos)) ? resp.datos : [];
                  areas.forEach(d => {
                    registrarEmpresaOrganizacion(d);
                    if (Number(d.activo) !== 1) return;
                    if (Number(d.departamento_organizacional_activo) !== 1) return;
                    if (Number(d.direccion_activo) !== 1) return;
                    const iso = d.codigo_iso_pais || 'xx';
                    const dirId = d.id_direccion || 'sin_direccion';
                    const orgId = d.id_departamento_organizacional || 'sin_area';
                    if (!grouped[iso]) grouped[iso] = {};
                    if (!areasById[iso]) areasById[iso] = {};
                    if (!grouped[iso][dirId]) {
                      grouped[iso][dirId] = {
                        id: dirId,
                        nombre: d.direccion_nombre || 'Sin dirección',
                        activo: Number(d.direccion_activo) === 1,
                        id_pais: d.id_pais,
                        nombre_pais: d.nombre_pais || '',
                        codigo_iso: iso,
                        id_empresa: d.id_empresa || 1,
                        nombre_empresa: nombreEmpresaOrganizacion(d),
                        areas: []
                      };
                    }
                    if (!areasById[iso][orgId]) {
                      areasById[iso][orgId] = {
                        id: orgId,
                        nombre: d.departamento_organizacional_nombre || 'Sin área',
                        activo: Number(d.departamento_organizacional_activo) === 1,
                        id_pais: d.id_pais,
                        id_direccion: dirId,
                        nombre_direccion: d.direccion_nombre || '',
                        nombre_pais: d.nombre_pais || '',
                        codigo_iso: iso,
                        id_empresa: d.id_empresa || 1,
                        nombre_empresa: nombreEmpresaOrganizacion(d),
                        areas: []
                      };
                      grouped[iso][dirId].areas.push(areasById[iso][orgId]);
                    }
                    areasById[iso][orgId].areas.push(d);
                  });
                  window.departamentosOrganizacionData = grouped;
                  window.departamentosOrganizacionAreasById = areasById;

                  const paisesActivos = (respPaises.success && respPaises.datos) ? respPaises.datos : [];

                  const paisesOrdenados = [];
                  const isoAgregados = new Set();
                  const ordenPrioridad = ['mx', 'gt', 'co'];

                  ordenPrioridad.forEach(iso => {
                    const pais = paisesActivos.find(p => p.codigo_iso === iso);
                    if (pais) { paisesOrdenados.push(pais); isoAgregados.add(iso); }
                  });
                  paisesActivos.forEach(p => {
                    if (!isoAgregados.has(p.codigo_iso)) { paisesOrdenados.push(p); isoAgregados.add(p.codigo_iso); }
                  });

                  paisesOrdenados.forEach((pais) => {
                    const iso = pais.codigo_iso || 'xx';
                    const gradient = gradientes[iso] || 'linear-gradient(135deg, #6c757d, #495057)';
                    const bodyContent = renderPanelEmpresaPais(iso, pais.nombre, imgUrl);
                    const empresasPais = getEmpresasPaisOrganizacion(iso);
                    const departamentosOrg = empresasPais;

                    const accordionItem = `
                    <div class="accordion-item mb-3">
                      <h2 class="accordion-header" id="heading-${iso}">
                        <button class="accordion-button collapsed fw-bold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse-${iso}"
                                aria-expanded="false" aria-controls="collapse-${iso}"
                                style="background: ${gradient}; color: #fff; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
                          <span class="fi fi-${iso} fis me-3" style="font-size: 1.5rem; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.25);"></span>
                          <span class="me-auto">${pais.nombre}</span>
                          <span class="badge org-count-badge" id="org-count-badge-${iso}" data-areas-count="${empresasPais.length}">
                            ${departamentosOrg.length} ${departamentosOrg.length === 1 ? 'dirección' : 'direcciones'}
                          </span>
                        </button>
                      </h2>
                      <div id="collapse-${iso}" class="accordion-collapse collapse"
                           aria-labelledby="heading-${iso}" data-bs-parent="#departamentosAccordion">
                        <div class="accordion-body">
                          <div id="org-body-${iso}">${bodyContent}</div>
                        </div>
                      </div>
                    </div>`;

                    container.insertAdjacentHTML('beforeend', accordionItem);
                    const badgePais = document.getElementById(`org-count-badge-${iso}`);
                    if (badgePais) {
                      badgePais.textContent = `${empresasPais.length} ${empresasPais.length === 1 ? 'empresa' : 'empresas'}`;
                    }
                  });

                  if (container.innerHTML === '') {
                    container.innerHTML = '<div class="text-center text-muted py-5">No hay países activos ni departamentos registrados.</div>';
                  }

                  const destino = window.organizacionDestinoDespuesRecarga || null;
                  window.organizacionDestinoDespuesRecarga = null;
                  if (destino && destino.iso) {
                    if (destino.idEmpresa) {
                      window.organizacionEmpresaSeleccionadaPorPais[destino.iso] = String(destino.idEmpresa);
                    }
                    setTimeout(() => {
                      const collapseEl = document.getElementById(`collapse-${destino.iso}`);
                      if (collapseEl && window.bootstrap?.Collapse) {
                        bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
                      }
                      if (destino.tipo === 'direccion' && destino.idDireccion) {
                        abrirDireccionOrganizacional(destino.iso, destino.idDireccion);
                      } else if (destino.tipo === 'area' && destino.idArea) {
                        abrirDepartamentoOrganizacional(destino.iso, destino.idArea);
                      }
                    }, 0);
                  }
                    }
                      });
                    }
                  });
                }
              });
            }
          });
        };

        function escapeHtml(value) {
          return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
        }

        function escapeJs(value) {
          return String(value ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }

        const DEPARTAMENTO_DEFAULT_IMAGE = 'https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/lady-with-laptop-light.png';

        function normalizarTextoVisualDepartamento(value) {
          return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
        }

        function getDepartamentoVisualConfig(nombre) {
          const texto = normalizarTextoVisualDepartamento(nombre);
          const reglas = [
            { keys: ['sistema', 'tecnologia', 'desarrollo', 'soporte', 'admin sistemas'], icon: 'fa fa-server', theme: 'tech' },
            { keys: ['auditoria'], icon: 'fa fa-shield-alt', theme: 'audit' },
            { keys: ['call center', 'contact center', 'atencion'], icon: 'fa fa-headset', theme: 'support' },
            { keys: ['data', 'inteligencia', 'analitica', 'negocio'], icon: 'fa fa-chart-line', theme: 'data' },
            { keys: ['despacho', 'juridico', 'legal'], icon: 'fa fa-balance-scale', theme: 'legal' },
            { keys: ['direccion'], icon: 'fa fa-compass', theme: 'ops' },
            { keys: ['cobranza', 'cartera', 'credito', 'creditos'], icon: 'fa fa-hand-holding-usd', theme: 'money' },
            { keys: ['estrategia', 'marketing', 'digital'], icon: 'fa fa-bullhorn', theme: 'digital' },
            { keys: ['mercantil'], icon: 'fa fa-store', theme: 'ops' },
            { keys: ['motos', 'adjudicadas'], icon: 'fa fa-motorcycle', theme: 'ops' },
            { keys: ['producto'], icon: 'fa fa-box-open', theme: 'data' },
            { keys: ['riesgo'], icon: 'fa fa-exclamation-triangle', theme: 'risk' },
            { keys: ['mesa de control', 'control'], icon: 'fa fa-tasks', theme: 'audit' },
            { keys: ['campo', 'operacion', 'operaciones'], icon: 'fa fa-users-cog', theme: 'support' },
          ];

          const match = reglas.find(regla => regla.keys.some(key => texto.includes(key)));
          return match || { icon: 'fa fa-building', theme: 'tech' };
        }

        function renderDepartamentoVisual(nombre, imgUrl) {
          const image = String(imgUrl || '').trim();
          if (image && image !== DEPARTAMENTO_DEFAULT_IMAGE) {
            return `<img src="${escapeHtml(image)}" class="img-fluid dept-visual-img" width="120" alt="${escapeHtml(nombre)}">`;
          }

          const config = getDepartamentoVisualConfig(nombre);
          return `<div class="dept-visual dept-visual-${config.theme}" title="${escapeHtml(nombre)}"><i class="${config.icon}" aria-hidden="true"></i></div>`;
        }

        function actualizarBotonAccionOrganizacion() {
          const btn = document.getElementById('btnAccionOrganizacion') || document.querySelector('button[onclick="abrirModalNuevoDepartamento()"]');
          if (!btn) return;
          btn.style.display = '';

          if (window.departamentoOrganizacionActivo) {
            btn.innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nuevo Departamento';
            return;
          }

          if (window.direccionOrganizacionActiva) {
            btn.innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nueva Área';
            return;
          }

          btn.innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nueva Dirección';
        }

        function actualizarBotonAccionOrganizacion() {
          const btn = document.getElementById('btnAccionOrganizacion') || document.querySelector('button[onclick="abrirModalNuevoDepartamento()"]');
          if (!btn) return;
          btn.style.display = '';

          if (window.departamentoOrganizacionActivo) {
            btn.innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nuevo Departamento';
            return;
          }

          if (window.direccionOrganizacionActiva) {
            btn.innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nueva Area';
            return;
          }

          if (window.organizacionEmpresaActiva?.idEmpresa) {
            btn.innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nueva Direccion';
            return;
          }

          btn.style.display = 'none';
          btn.innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nueva Direccion';
        }

        function renderDepartamentosPais(iso, nombrePais, direcciones, imgUrl) {
          if (!direcciones.length) {
            return `<div class="col-12 text-center text-muted py-4"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No hay direcciones registradas en ${escapeHtml(nombrePais)}.</div>`;
          }

          let cardsHTML = '';
          direcciones.forEach(dir => {
            const totalPuestos = dir.areas.reduce((sum, area) => sum + area.areas.reduce((s, dep) => s + Number(dep.total_puestos || 0), 0), 0);
            const totalPersonas = dir.areas.reduce((sum, area) => sum + area.areas.reduce((s, dep) => s + Number(dep.total_personas || 0), 0), 0);
            cardsHTML += `
              <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="card h-100 rounded-3 dept-card">
                  <div class="row h-100 g-0">
                    <div class="col-sm-8 d-flex flex-column justify-content-center p-3">
                      <h5 class="mb-2">${escapeHtml(dir.nombre)}</h5>
                      <p class="mb-0 text-muted small">Áreas: <strong>${dir.areas.length}</strong></p>
                      <p class="mb-0 text-muted small">Puestos: <strong>${totalPuestos}</strong></p>
                      <p class="mb-3 text-muted small">Personal: <strong>${totalPersonas}</strong></p>
                      <button type="button" class="btn btn-sm btn-outline-primary fw-semibold text-uppercase"
                         onclick="abrirDireccionOrganizacional('${escapeJs(iso)}', '${escapeJs(dir.id)}')">
                        Entrar
                      </button>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center justify-content-center p-1">
                      ${renderDepartamentoVisual(dir.nombre, imgUrl)}
                    </div>
                  </div>
                </div>
              </div>`;
          });
          return cardsHTML;
        }

        function abrirDireccionOrganizacional(iso, direccionId) {
          const direccion = window.departamentosOrganizacionData?.[iso]?.[direccionId];
          const body = document.getElementById(`org-body-${iso}`);
          if (!direccion || !body) return;

          window.direccionOrganizacionActiva = direccion;
          window.departamentoOrganizacionActivo = null;
          actualizarBotonAccionOrganizacion();

          const badge = document.getElementById(`org-count-badge-${iso}`);
          if (badge) {
            const totalAreas = direccion.areas.length;
            badge.textContent = `${totalAreas} ${totalAreas === 1 ? 'área' : 'áreas'}`;
          }

          let cardsHTML = '';
          direccion.areas.forEach(depOrg => {
            const totalPuestos = depOrg.areas.reduce((sum, area) => sum + Number(area.total_puestos || 0), 0);
            const totalPersonas = depOrg.areas.reduce((sum, area) => sum + Number(area.total_personas || 0), 0);
            cardsHTML += `
              <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="card h-100 rounded-3 dept-card">
                  <div class="row h-100 g-0">
                    <div class="col-sm-8 d-flex flex-column justify-content-center p-3">
                      <h5 class="mb-2">${escapeHtml(depOrg.nombre)}</h5>
                      <p class="mb-0 text-muted small">Departamentos: <strong>${depOrg.areas.length}</strong></p>
                      <p class="mb-0 text-muted small">Puestos: <strong>${totalPuestos}</strong></p>
                      <p class="mb-3 text-muted small">Personal: <strong>${totalPersonas}</strong></p>
                      <button type="button" class="btn btn-sm btn-outline-primary fw-semibold text-uppercase"
                         onclick="abrirDepartamentoOrganizacional('${escapeJs(iso)}', '${escapeJs(depOrg.id)}')">
                        Entrar
                      </button>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center justify-content-center p-1">
                      ${renderDepartamentoVisual(depOrg.nombre, '')}
                    </div>
                  </div>
                </div>
              </div>`;
          });

          if (!cardsHTML) {
            cardsHTML = '<div class="col-12 text-center text-muted py-4"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No hay áreas registradas en esta dirección.</div>';
          }

          body.innerHTML = `
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
              <div>
                <h5 class="mb-0">${escapeHtml(direccion.nombre)}</h5>
                <p class="text-muted mb-0">Áreas registradas en ${escapeHtml(direccion.nombre_pais || '')}</p>
              </div>
              <button type="button" class="btn btn-sm organizacion-back-btn" onclick="volverDepartamentosPais('${escapeJs(iso)}')">
                <i class="fa fa-arrow-left me-2"></i>Volver a empresas
              </button>
            </div>
            <div class="row g-4">${cardsHTML}</div>`;
          const backDireccionesBtn = body.querySelector('.organizacion-back-btn');
          if (backDireccionesBtn) {
            backDireccionesBtn.setAttribute('onclick', `volverDireccionesEmpresa('${escapeJs(iso)}')`);
            backDireccionesBtn.innerHTML = '<i class="fa fa-arrow-left me-2"></i>Volver a direcciones';
          }
        }
        function volverDireccionesEmpresa(iso) {
          const idEmpresa = getEmpresaOrganizacionSeleccionada(iso);
          if (idEmpresa) {
            seleccionarEmpresaPaisOrganizacion(iso, idEmpresa);
            return;
          }
          volverDepartamentosPais(iso);
        }

        function abrirDepartamentoOrganizacional(iso, orgId) {
          const depOrg = window.departamentosOrganizacionAreasById?.[iso]?.[orgId];
          const body = document.getElementById(`org-body-${iso}`);
          if (!depOrg || !body) return;
          window.departamentoOrganizacionActivo = depOrg;
          actualizarBotonAccionOrganizacion();
          const badge = document.getElementById(`org-count-badge-${iso}`);
          if (badge) {
            const totalDepartamentos = depOrg.areas.filter(d => Number(d.activo) === 1).length;
            badge.textContent = `${totalDepartamentos} ${totalDepartamentos === 1 ? 'depto' : 'depts'}`;
          }

          let cardsHTML = '';
          const departamentosActivos = depOrg.areas.filter(d => Number(d.activo) === 1);
          departamentosActivos.forEach(d => {
            const nombreSafe = escapeJs(d.departamento_nombre || '');
            const visualDepartamento = renderDepartamentoVisual(d.departamento_nombre || '', d.img_url || '');
            cardsHTML += `
              <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="card h-100 rounded-3 dept-card">
                  <div class="row h-100 g-0">
                    <div class="col-sm-8 d-flex flex-column justify-content-center p-3">
                      <h5 class="mb-2">${escapeHtml(d.departamento_nombre)}</h5>
                      <p class="mb-0 text-muted small">Puestos: <strong data-puestos-count-departamento="${escapeHtml(String(d.departamento_id))}">${d.total_puestos ?? 0}</strong></p>
                      <p class="mb-0 text-muted small">Personal: <strong>${d.total_personas ?? 0}</strong></p>
                      <p class="mb-3 text-muted small">Estado: <strong>${Number(d.activo) === 1 ? 'Activo' : 'Inactivo'}</strong></p>
                      <button type="button" class="btn btn-sm btn-outline-primary fw-semibold text-uppercase"
                         onclick="abrirModalDepartamento(${d.departamento_id}, '${nombreSafe}')">
                        Editar
                      </button>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center justify-content-center p-1">
                      ${visualDepartamento}
                    </div>
                  </div>
                </div>
              </div>`;
          });

          if (!cardsHTML) {
            cardsHTML = '<div class="col-12 text-center text-muted py-4"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No hay departamentos registrados en esta área.</div>';
          }

          body.innerHTML = `
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
              <div>
                <h5 class="mb-0">${escapeHtml(depOrg.nombre)}</h5>
                <p class="text-muted mb-0">Departamentos registrados en ${escapeHtml(depOrg.nombre_pais || '')}</p>
              </div>
              <button type="button" class="btn btn-sm organizacion-back-btn" onclick="abrirDireccionOrganizacional('${escapeJs(iso)}', '${escapeJs(depOrg.id_direccion)}')">
                <i class="fa fa-arrow-left me-2"></i>Volver a áreas
              </button>
            </div>
            <div class="row g-4">${cardsHTML}</div>`;
        }

        function volverDepartamentosPais(iso) {
          const body = document.getElementById(`org-body-${iso}`);
          const groupedPais = window.departamentosOrganizacionData?.[iso] || {};
          if (!body) {
            getDepartamentos();
            return;
          }

          window.organizacionEmpresaSeleccionadaPorPais[iso] = '';
          const empresasPais = getEmpresasPaisOrganizacion(iso);
          const nombrePais = Object.values(groupedPais)[0]?.nombre_pais || '';
          const imgUrl = "https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/lady-with-laptop-light.png";
          body.innerHTML = renderPanelEmpresaPais(iso, nombrePais, imgUrl);

          const badge = document.getElementById(`org-count-badge-${iso}`);
          if (badge) {
            const total = empresasPais.length;
            badge.textContent = `${total} ${total === 1 ? 'dirección' : 'direcciones'}`;
          }

          window.departamentoOrganizacionActivo = null;
          window.direccionOrganizacionActiva = null;
          actualizarBotonAccionOrganizacion();
          const badgeEmpresaPais = document.getElementById(`org-count-badge-${iso}`);
          if (badgeEmpresaPais) {
            const totalEmpresasPais = empresasPais.length;
            badgeEmpresaPais.textContent = `${totalEmpresasPais} ${totalEmpresasPais === 1 ? 'empresa' : 'empresas'}`;
          }
        }

        function cargarDepartamentosOrganizacionalesModal(idPais) {
          const select = document.getElementById('addDepartamentoOrganizacionalId');
          if (!select) return;
          select.innerHTML = '<option value="">-- Selecciona un área --</option>';
          select.classList.remove('is-invalid');
          const error = document.getElementById('errorDepartamentoOrganizacional');
          if (error) error.style.display = 'none';

          http.request({
            endpoint: "/departamentos/getDepartamentosOrganizacionales",
            onSuccess: (resp) => {
              const datos = resp.success && Array.isArray(resp.datos) ? resp.datos : [];
              const idEmpresaModal = document.getElementById('addDepartamentoEmpresaId')?.value || document.getElementById('addDepartamentoEmpresaSelect')?.value || '';
              const filtrados = datos.filter(d =>
                (!idPais || String(d.id_pais || '') === String(idPais))
                && (!idEmpresaModal || String(d.id_empresa || 1) === String(idEmpresaModal))
              );
              filtrados.forEach(d => {
                select.insertAdjacentHTML('beforeend', `<option value="${d.id}">${d.nombre}</option>`);
              });
              if (filtrados.length === 1) {
                select.value = String(filtrados[0].id);
              }
            }
          });
        }

        function configurarModalDepartamento(modo, opciones = {}) {
          const modalEl = document.getElementById('addDepartamentoModal');
          const form = document.getElementById('addDepartamentoForm');
          const input = document.getElementById('modalNombreDepartamento');
          const modoInput = document.getElementById('addDepartamentoModo');
          const contextPais = document.getElementById('addDepartamentoContextPaisId');
          const contextEmpresa = document.getElementById('addDepartamentoEmpresaId');
          const contextDireccion = document.getElementById('addDepartamentoContextDireccionId');
          const contextOrg = document.getElementById('addDepartamentoContextOrgId');
          const selectEmpresa = document.getElementById('addDepartamentoEmpresaSelect');
          const selectPais = document.getElementById('addDepartamentoPaisId');
          const selectOrg = document.getElementById('addDepartamentoOrganizacionalId');
          const empresaGroup = selectEmpresa?.closest('.col-12');
          const paisGroup = selectPais?.closest('.col-12');
          const orgGroup = selectOrg?.closest('.col-12');
          const title = modalEl?.querySelector('.modal-body .text-center h4');
          const help = modalEl?.querySelector('.modal-body .text-center p');
          const nombreLabel = modalEl?.querySelector('label[for="modalNombreDepartamento"]');

          if (form) form.reset();
          if (input) input.classList.remove('is-invalid');
          if (selectEmpresa) selectEmpresa.classList.remove('is-invalid');
          if (selectPais) selectPais.classList.remove('is-invalid');
          if (selectOrg) selectOrg.classList.remove('is-invalid');
          ['errorNombre', 'errorPais', 'errorEmpresa', 'errorDepartamentoOrganizacional'].forEach(id => {
            const err = document.getElementById(id);
            if (err) err.style.display = 'none';
          });

          if (modoInput) modoInput.value = modo;
          if (contextEmpresa) contextEmpresa.value = opciones.idEmpresa || '';
          llenarEmpresasModalOrganizacion(opciones.idEmpresa || '');
          if (contextPais) contextPais.value = opciones.idPais || '';
          if (contextDireccion) contextDireccion.value = opciones.idDireccion || '';
          if (contextOrg) contextOrg.value = opciones.idOrg || '';

          if (modo === 'area') {
            if (title) title.textContent = 'Agregar nuevo departamento';
            if (help) help.textContent = `Se agregará al área ${opciones.nombreOrg || ''}.`;
            if (nombreLabel) nombreLabel.textContent = 'Nombre del Departamento *';
            if (input) input.placeholder = 'Ej. Call Center, Campo 1-7, Despachos...';
            if (empresaGroup) empresaGroup.style.display = 'none';
            if (paisGroup) paisGroup.style.display = 'none';
            if (orgGroup) orgGroup.style.display = 'none';
            return;
          }

          if (modo === 'departamento') {
            if (title) title.textContent = 'Agregar nueva área';
            if (help) help.textContent = `Se agregará a la dirección ${opciones.nombreDireccion || ''}.`;
            if (nombreLabel) nombreLabel.textContent = 'Nombre del Área *';
            if (input) input.placeholder = 'Ej. Cobranza, Comercial, Administración de Finanzas...';
            if (empresaGroup) empresaGroup.style.display = 'none';
            if (paisGroup) paisGroup.style.display = 'none';
            if (orgGroup) orgGroup.style.display = 'none';
            return;
          }

          if (title) title.textContent = 'Agregar nueva dirección';
          if (help) help.textContent = 'Selecciona el país y escribe el nombre de la dirección.';
          if (nombreLabel) nombreLabel.textContent = 'Nombre de la Dirección *';
          if (input) input.placeholder = 'Ej. Dirección 1, Dirección 2, Dirección Comercial...';
          if (empresaGroup) empresaGroup.style.display = '';
          if (paisGroup) paisGroup.style.display = '';
          if (orgGroup) orgGroup.style.display = 'none';
        }

        function abrirModalNuevoDepartamento() {
          if (window.departamentoOrganizacionActivo) {
            const depOrg = window.departamentoOrganizacionActivo;
            abrirModalNuevaArea(depOrg.id_pais, depOrg.id, depOrg.nombre, depOrg.id_empresa);
            return;
          }

          if (window.direccionOrganizacionActiva) {
            const dir = window.direccionOrganizacionActiva;
            abrirModalNuevaAreaOrganizacional(dir.id_pais, dir.id, dir.nombre, dir.id_empresa);
            return;
          }

          const select = document.getElementById('addDepartamentoPaisId');
          const selectOrg = document.getElementById('addDepartamentoOrganizacionalId');
          configurarModalDepartamento('direccion', { idEmpresa: getEmpresaOrganizacionSeleccionada() || '' });
          select.innerHTML = '<option value="">-- Selecciona un país --</option>';
          if (selectOrg) selectOrg.innerHTML = '<option value="">-- Selecciona un área --</option>';
          select.classList.remove('is-invalid');
          document.getElementById('errorPais').style.display = 'none';
          const errorOrg = document.getElementById('errorDepartamentoOrganizacional');
          if (errorOrg) errorOrg.style.display = 'none';

          http.request({
            endpoint: "/departamentos/getPaisesActivos",
            onSuccess: (resp) => {
              if (resp.success && resp.datos) {
                resp.datos.forEach(p => {
                  select.insertAdjacentHTML('beforeend', `<option value="${p.id}">${p.nombre}</option>`);
                });
              }
              const modal = new bootstrap.Modal(document.getElementById('addDepartamentoModal'));
              modal.show();
            },
            onError: () => {
              const modal = new bootstrap.Modal(document.getElementById('addDepartamentoModal'));
              modal.show();
            }
          });
        }

        function abrirModalNuevaAreaOrganizacional(idPais, idDireccion, nombreDireccion, idEmpresa = '') {
          configurarModalDepartamento('departamento', { idPais, idDireccion, nombreDireccion, idEmpresa });
          const modal = new bootstrap.Modal(document.getElementById('addDepartamentoModal'));
          modal.show();
        }

        function abrirModalNuevaArea(idPais, idOrg, nombreOrg, idEmpresa = '') {
          configurarModalDepartamento('area', { idPais, idOrg, nombreOrg, idEmpresa });
          const modal = new bootstrap.Modal(document.getElementById('addDepartamentoModal'));
          modal.show();
        }
        $(document).ready(() => {
          window.getDepartamentos = getDepartamentos;
          window.departamentoOrganizacionActivo = null;
          actualizarBotonAccionOrganizacion();
          getDepartamentos();
        });

        /* =====================================================
           EDICIÓN DE PUESTOS
           ===================================================== */

        function inicioEdicion(element) {
          valorOriginal = element.textContent.trim();
        }

        function guardarPuesto(element) {
          const nuevoValor = sanitizarCadenaNombreDepartamento(element.textContent.trim());
          element.textContent = nuevoValor;
          const idPuesto = element.dataset.puestoId;

          if (!nuevoValor) {
            element.textContent = valorOriginal;
            element.contentEditable = false;
            return;
          }

          element.contentEditable = false;

          http.request({
            endpoint: "/departamentos/getActualizaNombrePues",
            method: "POST",
            data: {
              id_puesto: idPuesto,
              nombre: nuevoValor
            },
            onSuccess: (resp) => {
              if (resp && resp.success === false) {
                const msg = resp.mensaje || resp.error || 'No se pudo actualizar el puesto.';
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'No se actualizó', text: msg });
                else console.warn(msg);
                element.textContent = valorOriginal;
              }
            },
            onError: (err) => {
              console.error('Error al actualizar puesto:', err);
              element.textContent = valorOriginal;
            }
          });
        }

        function forzarEdicion(icono) {
          const nombre = icono.previousElementSibling;
          valorOriginal = nombre.textContent.trim();
          nombre.contentEditable = true;
          nombre.focus();

          document.execCommand('selectAll', false, null);
          document.getSelection().collapseToEnd();

          nombre.onkeydown = (e) => {
            if (e.key === 'Enter') {
              e.preventDefault();
              nombre.blur();
            }
            if (e.key === 'Escape') {
              cancelarEdicion(nombre);
            }
          };

          nombre.onblur = () => guardarPuesto(nombre);
        }

        /* =====================================================
           NUEVO DEPARTAMENTO - FORMULARIO MODAL
           ===================================================== */

        $(document).ready(function() {
          const form = document.getElementById('addDepartamentoForm');

          if (form) {
            form.addEventListener('submit', function(e) {
              e.preventDefault();

              const selectPais = document.getElementById('addDepartamentoPaisId');
              const selectEmpresa = document.getElementById('addDepartamentoEmpresaSelect');
              const selectOrg = document.getElementById('addDepartamentoOrganizacionalId');
              const input = document.getElementById('modalNombreDepartamento');
              const nombre = input.value.trim();
              const modo = document.getElementById('addDepartamentoModo')?.value || 'direccion';
              const idEmpresa = document.getElementById('addDepartamentoEmpresaId')?.value || selectEmpresa?.value || '';
              const idDireccion = document.getElementById('addDepartamentoContextDireccionId')?.value || '';
              const idPais = (modo === 'area' || modo === 'departamento')
                ? (document.getElementById('addDepartamentoContextPaisId')?.value || '')
                : selectPais.value;
              const idDepartamentoOrganizacional = modo === 'area'
                ? (document.getElementById('addDepartamentoContextOrgId')?.value || '')
                : '';
              const errorNombre = document.getElementById('errorNombre');
              const errorPais = document.getElementById('errorPais');
              const errorEmpresa = document.getElementById('errorEmpresa');
              const errorOrg = document.getElementById('errorDepartamentoOrganizacional');

              let valid = true;

              if (modo === 'direccion' && !idEmpresa) {
                if (errorEmpresa) {
                  errorEmpresa.textContent = 'Debes seleccionar una empresa';
                  errorEmpresa.style.display = 'block';
                }
                if (selectEmpresa) selectEmpresa.classList.add('is-invalid');
                valid = false;
              } else {
                if (errorEmpresa) errorEmpresa.style.display = 'none';
                if (selectEmpresa) selectEmpresa.classList.remove('is-invalid');
              }

              if (!idPais) {
                errorPais.textContent = 'Debes seleccionar un país';
                errorPais.style.display = 'block';
                selectPais.classList.add('is-invalid');
                valid = false;
              } else {
                errorPais.style.display = 'none';
                selectPais.classList.remove('is-invalid');
              }

              if (modo === 'area' && !idDepartamentoOrganizacional) {
                if (errorOrg) {
                  errorOrg.textContent = 'Debes seleccionar un área';
                  errorOrg.style.display = 'block';
                }
                if (selectOrg) selectOrg.classList.add('is-invalid');
                valid = false;
              } else {
                if (errorOrg) errorOrg.style.display = 'none';
                if (selectOrg) selectOrg.classList.remove('is-invalid');
              }

              if (modo === 'departamento' && !idDireccion) {
                errorNombre.textContent = 'No se encontró la dirección activa. Vuelve a entrar a la dirección e intenta nuevamente.';
                errorNombre.style.display = 'block';
                input.classList.add('is-invalid');
                valid = false;
              }

              if (!nombre) {
                errorNombre.textContent = modo === 'direccion'
                  ? 'El nombre de la dirección es requerido'
                  : modo === 'departamento'
                  ? 'El nombre del área es requerido'
                  : 'El nombre del departamento es requerido';
                errorNombre.style.display = 'block';
                input.classList.add('is-invalid');
                valid = false;
              } else if (valid) {
                errorNombre.style.display = 'none';
                input.classList.remove('is-invalid');
              }

              if (!valid) return;

              const submitBtn = form.querySelector('button[type="submit"]');
              const originalText = submitBtn.innerHTML;
              submitBtn.disabled = true;
              submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Guardando...';

              const endpoint = modo === 'direccion'
                ? "/departamentos/InsertDireccion"
                : modo === 'departamento'
                ? "/departamentos/InsertDepartamentoOrganizacional"
                : "/departamentos/InsertDepartamento";
              const requestData = modo === 'direccion'
                ? { nombre, id_pais: idPais, id_empresa: idEmpresa }
                : modo === 'departamento'
                ? { nombre, id_pais: idPais, id_direccion: idDireccion, id_empresa: idEmpresa }
                : { nombre, id_pais: idPais, id_departamento_organizacional: idDepartamentoOrganizacional, id_empresa: idEmpresa };
              http.request({
                endpoint,
                method: "POST",
                data: requestData,
                onSuccess: (resp) => {
                  if (resp.success) {
                    if (modo === 'departamento' && idDireccion) {
                      window.organizacionDestinoDespuesRecarga = {
                        tipo: 'direccion',
                        iso: window.direccionOrganizacionActiva?.codigo_iso || '',
                        idDireccion: String(idDireccion),
                        idEmpresa: String(idEmpresa || '')
                      };
                    } else if (modo === 'area' && idDepartamentoOrganizacional) {
                      window.organizacionDestinoDespuesRecarga = {
                        tipo: 'area',
                        iso: window.departamentoOrganizacionActivo?.codigo_iso || '',
                        idArea: String(idDepartamentoOrganizacional),
                        idEmpresa: String(idEmpresa || '')
                      };
                    }
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addDepartamentoModal'));
                    modal.hide();
                    form.reset();
                    getDepartamentos();
                    Swal.fire({
                      icon: 'success',
                      title: modo === 'direccion' ? 'Dirección creada' : modo === 'departamento' ? 'Área creada' : 'Departamento creado',
                      text: resp.mensaje,
                      timer: 2000,
                      showConfirmButton: false
                    });
                  } else {
                    errorNombre.textContent = resp.mensaje || 'Error al crear el departamento';
                    errorNombre.style.display = 'block';
                    input.classList.add('is-invalid');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                  }
                },
                onError: (err) => {
                  console.error('Error al crear departamento:', err);
                  errorNombre.textContent = 'Error de conexión. Intenta nuevamente.';
                  errorNombre.style.display = 'block';
                  input.classList.add('is-invalid');
                  submitBtn.disabled = false;
                  submitBtn.innerHTML = originalText;
                }
              });
            });

            const inputNombre = document.getElementById('modalNombreDepartamento');
            if (inputNombre) {
              inputNombre.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                document.getElementById('errorNombre').style.display = 'none';
              });
            }

            const selectPais = document.getElementById('addDepartamentoPaisId');
            if (selectPais) {
              selectPais.addEventListener('change', function() {
                this.classList.remove('is-invalid');
                document.getElementById('errorPais').style.display = 'none';
                cargarDepartamentosOrganizacionalesModal(this.value || '');
              });
            }

            const selectOrg = document.getElementById('addDepartamentoOrganizacionalId');
            if (selectOrg) {
              selectOrg.addEventListener('change', function() {
                this.classList.remove('is-invalid');
                const err = document.getElementById('errorDepartamentoOrganizacional');
                if (err) err.style.display = 'none';
              });
            }

            $('#addDepartamentoModal').on('hidden.bs.modal', function() {
              form.reset();
              document.getElementById('errorNombre').style.display = 'none';
              document.getElementById('errorPais').style.display = 'none';
              const errorOrg = document.getElementById('errorDepartamentoOrganizacional');
              if (errorOrg) errorOrg.style.display = 'none';
              document.getElementById('modalNombreDepartamento').classList.remove('is-invalid');
              document.getElementById('addDepartamentoPaisId').classList.remove('is-invalid');
              const selectOrgReset = document.getElementById('addDepartamentoOrganizacionalId');
              if (selectOrgReset) selectOrgReset.classList.remove('is-invalid');
              const submitBtn = form.querySelector('button[type="submit"]');
              submitBtn.disabled = false;
              submitBtn.innerHTML = '<i class="fa fa-save me-2"></i>Guardar';
            });
          }
        });

        </script>
        HTML;

        self::set("titulo", "Gestión de Departamentos");
        self::set("script", $script);
        self::render("departamentos_all");
    }

    public function getDepartamentos()
    {
        self::respuestaJSON(DepartamentosDAO::getConsultaDepartamentos());
    }

    public function getDepartamentosOrganizacionales()
    {
        self::respuestaJSON(DepartamentosDAO::getConsultaDepartamentosOrganizacionales());
    }

    public function getDirecciones()
    {
        self::respuestaJSON(DepartamentosDAO::getConsultaDirecciones());
    }

    public function InsertPuesto()
    {
        $input = [];
        $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (is_string($ct) && stripos($ct, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            if ($raw !== false && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $input = $decoded;
                }
            }
        }
        $nombre = $input['nombre'] ?? $_POST['nombre'] ?? null;
        $id_departamento = $input['id_departamento'] ?? $_POST['id_departamento'] ?? null;
        DepartamentosDAO::InsertPuestos($nombre, $id_departamento);
    }

    public function getPuestosPorDepartamento()
    {
        $id_dep = $_POST['id_departamento'] ?? null;
        if ($id_dep === null) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id_dep = $input['id_departamento'] ?? null;
        }
        $id_dep = $id_dep !== null && $id_dep !== '' ? (int) $id_dep : null;
        self::respuestaJSON(DepartamentosDAO::getConsultaPuestos($id_dep));
    }

    public function getActualizaNombrePues()
    {
        $id_pues = $_POST['id_puesto'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        self::respuestaJSON(DepartamentosDAO::UpdateNombrePuesto($id_pues, $nombre));
    }

    public function UpdateOrdenPuestos()
    {
        $id_departamento = $_POST['id_departamento'] ?? null;
        $ordenes = $_POST['ordenes'] ?? [];
        if (!is_array($ordenes)) {
            $ordenes = [];
        }
        DepartamentosDAO::UpdateOrdenPuestos($id_departamento, $ordenes);
    }

    public function InsertDepartamento()
    {
        $nombre = $_POST['nombre'] ?? null;
        $id_pais = $_POST['id_pais'] ?? 1;
        $id_departamento_organizacional = $_POST['id_departamento_organizacional'] ?? null;
        $id_empresa = $_POST['id_empresa'] ?? 1;
        self::respuestaJSON(DepartamentosDAO::InsertDepartamento($nombre, $id_pais, $id_departamento_organizacional, $id_empresa));
    }

    public function InsertDepartamentoOrganizacional()
    {
        $nombre = $_POST['nombre'] ?? null;
        $id_pais = $_POST['id_pais'] ?? 1;
        $id_direccion = $_POST['id_direccion'] ?? null;
        $id_empresa = $_POST['id_empresa'] ?? 1;
        self::respuestaJSON(DepartamentosDAO::InsertDepartamentoOrganizacional($nombre, $id_pais, $id_direccion, $id_empresa));
    }

    public function InsertDireccion()
    {
        $nombre = $_POST['nombre'] ?? null;
        $id_pais = $_POST['id_pais'] ?? 1;
        $id_empresa = $_POST['id_empresa'] ?? 1;
        self::respuestaJSON(DepartamentosDAO::InsertDireccion($nombre, $id_pais, $id_empresa));
    }

    public function UpdateNombreDepartamento()
    {
        $id_departamento = $_POST['id_departamento'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        self::respuestaJSON(DepartamentosDAO::UpdateNombreDepartamento($id_departamento, $nombre));
    }

    public function eliminarDepartamento()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id_departamento']) ? (int) $input['id_departamento'] : (int) ($_POST['id_departamento'] ?? 0);
        DepartamentosDAO::eliminarDepartamento($id);
    }

    public function getPaisesActivos()
    {
        header('Content-Type: application/json; charset=utf-8');
        $datos = \Models\Paises::getPaisesActivos();
        echo json_encode(['success' => true, 'datos' => $datos]);
        exit;
    }

}
