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
              }
              // success === false: comunes.js llama onSuccess y luego onError (una sola alerta ahí).
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

              if (!resp.datos || resp.datos.length === 0) {
                lista.innerHTML = `
                  <li class="text-center text-muted py-4">
                    No hay puestos registrados
                  </li>
                `;
                return;
              }

              resp.datos.forEach((p, i) => {
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

        const getDepartamentos = () => {
          window.departamentoOrganizacionActivo = null;
          actualizarBotonAccionOrganizacion();
          http.request({
            endpoint: "/departamentos/getDepartamentos",
            onSuccess: (resp) => {
              http.request({
                endpoint: "/departamentos/getPaisesActivos",
                onSuccess: (respPaises) => {
                  http.request({
                    endpoint: "/departamentos/getDepartamentosOrganizacionales",
                    onSuccess: (respOrg) => {
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
                  const departamentosOrganizacionales = (respOrg.success && Array.isArray(respOrg.datos)) ? respOrg.datos : [];
                  departamentosOrganizacionales.forEach(dep => {
                    const iso = dep.codigo_iso_pais || 'xx';
                    const orgId = dep.id || 'sin_departamento';
                    if (!grouped[iso]) grouped[iso] = {};
                    if (!grouped[iso][orgId]) {
                      grouped[iso][orgId] = {
                        id: orgId,
                        nombre: dep.nombre || 'Sin departamento',
                        activo: Number(dep.activo) === 1,
                        id_pais: dep.id_pais,
                        nombre_pais: dep.nombre_pais || '',
                        codigo_iso: iso,
                        areas: []
                      };
                    }
                  });

                  const areas = (resp.success && Array.isArray(resp.datos)) ? resp.datos : [];
                  areas.forEach(d => {
                    const iso = d.codigo_iso_pais || 'xx';
                    const orgId = d.id_departamento_organizacional || 'sin_departamento';
                    if (!grouped[iso]) grouped[iso] = {};
                    if (!grouped[iso][orgId]) {
                      grouped[iso][orgId] = {
                        id: orgId,
                        nombre: d.departamento_organizacional_nombre || 'Sin departamento',
                        activo: Number(d.departamento_organizacional_activo) === 1,
                        id_pais: d.id_pais,
                        nombre_pais: d.nombre_pais || '',
                        codigo_iso: iso,
                        areas: []
                      };
                    }
                    grouped[iso][orgId].areas.push(d);
                  });
                  window.departamentosOrganizacionData = grouped;

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
                    const departamentosOrg = Object.values(grouped[iso] || {});
                    const gradient = gradientes[iso] || 'linear-gradient(135deg, #6c757d, #495057)';

                    let bodyContent = '';
                    departamentosOrg.forEach(depOrg => {
                      bodyContent += `
                        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                          <div class="card h-100 rounded-3 dept-card">
                            <div class="row h-100 g-0">
                              <div class="col-sm-8 d-flex flex-column justify-content-center p-3">
                                <h5 class="mb-2">${depOrg.nombre}</h5>
                                <p class="mb-0 text-muted small">Áreas: <strong>${depOrg.areas.length}</strong></p>
                                <p class="mb-3 text-muted small">Estado: <strong>${depOrg.activo ? 'Activo' : 'Inactivo'}</strong></p>
                                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold text-uppercase"
                                   onclick="abrirDepartamentoOrganizacional('${iso}', '${depOrg.id}')">
                                  Entrar
                                </button>
                              </div>
                              <div class="col-sm-4 d-flex align-items-center justify-content-center p-1">
                                <img src="${imgUrl}" class="img-fluid" width="120" alt="${depOrg.nombre}">
                              </div>
                            </div>
                          </div>
                        </div>`;
                    });

                    if (!bodyContent) {
                      bodyContent = `<div class="text-center text-muted py-4"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No hay departamentos registrados en ${pais.nombre}.</div>`;
                    }

                    bodyContent = renderDepartamentosPais(iso, pais.nombre, departamentosOrg, imgUrl);

                    const accordionItem = `
                    <div class="accordion-item mb-3">
                      <h2 class="accordion-header" id="heading-${iso}">
                        <button class="accordion-button collapsed fw-bold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse-${iso}"
                                aria-expanded="false" aria-controls="collapse-${iso}"
                                style="background: ${gradient}; color: #fff; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
                          <span class="fi fi-${iso} fis me-3" style="font-size: 1.5rem; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.25);"></span>
                          <span class="me-auto">${pais.nombre}</span>
                          <span class="badge org-count-badge" id="org-count-badge-${iso}" data-areas-count="${departamentosOrg.length}">
                            ${departamentosOrg.length} ${departamentosOrg.length === 1 ? 'área' : 'áreas'}
                          </span>
                        </button>
                      </h2>
                      <div id="collapse-${iso}" class="accordion-collapse collapse"
                           aria-labelledby="heading-${iso}" data-bs-parent="#departamentosAccordion">
                        <div class="accordion-body">
                          <div id="org-body-${iso}"><div class="row g-4">${bodyContent}</div></div>
                        </div>
                      </div>
                    </div>`;

                    container.insertAdjacentHTML('beforeend', accordionItem);
                  });

                  if (container.innerHTML === '') {
                    container.innerHTML = '<div class="text-center text-muted py-5">No hay países activos ni departamentos registrados.</div>';
                  }
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

        function actualizarBotonAccionOrganizacion() {
          const btn = document.getElementById('btnAccionOrganizacion') || document.querySelector('button[onclick="abrirModalNuevoDepartamento()"]');
          if (!btn) return;

          if (window.departamentoOrganizacionActivo) {
            btn.innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nuevo Departamento';
            return;
          }

          btn.innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nueva Área';
        }

        function renderDepartamentosPais(iso, nombrePais, departamentosOrg, imgUrl) {
          if (!departamentosOrg.length) {
            return `<div class="col-12 text-center text-muted py-4"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No hay departamentos registrados en ${escapeHtml(nombrePais)}.</div>`;
          }

          let cardsHTML = '';
          departamentosOrg.forEach(depOrg => {
            const totalPuestos = depOrg.areas.reduce((sum, area) => sum + Number(area.total_puestos || 0), 0);
            const totalPersonas = depOrg.areas.reduce((sum, area) => sum + Number(area.total_personas || 0), 0);
            cardsHTML += `
              <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="card h-100 rounded-3 dept-card">
                  <div class="row h-100 g-0">
                    <div class="col-sm-8 d-flex flex-column justify-content-center p-3">
                      <h5 class="mb-2">${escapeHtml(depOrg.nombre)}</h5>
                      <p class="mb-0 text-muted small">Áreas: <strong>${depOrg.areas.length}</strong></p>
                      <p class="mb-0 text-muted small">Puestos: <strong>${totalPuestos}</strong></p>
                      <p class="mb-3 text-muted small">Personal: <strong>${totalPersonas}</strong></p>
                      <button type="button" class="btn btn-sm btn-outline-primary fw-semibold text-uppercase"
                         onclick="abrirDepartamentoOrganizacional('${escapeJs(iso)}', '${escapeJs(depOrg.id)}')">
                        Entrar
                      </button>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center justify-content-center p-1">
                      <img src="${imgUrl}" class="img-fluid" width="120" alt="${escapeHtml(depOrg.nombre)}">
                    </div>
                  </div>
                </div>
              </div>`;
          });
          return cardsHTML;
        }

        function abrirDepartamentoOrganizacional(iso, orgId) {
          const depOrg = window.departamentosOrganizacionData?.[iso]?.[orgId];
          const body = document.getElementById(`org-body-${iso}`);
          if (!depOrg || !body) return;
          window.departamentoOrganizacionActivo = depOrg;
          actualizarBotonAccionOrganizacion();
          const badge = document.getElementById(`org-count-badge-${iso}`);
          if (badge) {
            const totalDepartamentos = depOrg.areas.length;
            badge.textContent = `${totalDepartamentos} ${totalDepartamentos === 1 ? 'depto' : 'depts'}`;
          }

          let cardsHTML = '';
          depOrg.areas.forEach(d => {
            const nombreSafe = escapeJs(d.departamento_nombre || '');
            const imagen = d.img_url || 'https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/lady-with-laptop-light.png';
            cardsHTML += `
              <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="card h-100 rounded-3 dept-card">
                  <div class="row h-100 g-0">
                    <div class="col-sm-8 d-flex flex-column justify-content-center p-3">
                      <h5 class="mb-2">${escapeHtml(d.departamento_nombre)}</h5>
                      <p class="mb-0 text-muted small">Puestos: <strong>${d.total_puestos ?? 0}</strong></p>
                      <p class="mb-0 text-muted small">Personal: <strong>${d.total_personas ?? 0}</strong></p>
                      <p class="mb-3 text-muted small">Estado: <strong>${Number(d.activo) === 1 ? 'Activo' : 'Inactivo'}</strong></p>
                      <button type="button" class="btn btn-sm btn-outline-primary fw-semibold text-uppercase"
                         onclick="abrirModalDepartamento(${d.departamento_id}, '${nombreSafe}')">
                        Editar
                      </button>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center justify-content-center p-1">
                      <img src="${imagen}" class="img-fluid" width="120" alt="${escapeHtml(d.departamento_nombre)}">
                    </div>
                  </div>
                </div>
              </div>`;
          });

          if (!cardsHTML) {
            cardsHTML = '<div class="col-12 text-center text-muted py-4"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No hay áreas registradas en este departamento.</div>';
          }

          body.innerHTML = `
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
              <div>
                <h5 class="mb-0">${escapeHtml(depOrg.nombre)}</h5>
                <p class="text-muted mb-0">Departamentos registrados en ${escapeHtml(depOrg.nombre_pais || '')}</p>
              </div>
              <button type="button" class="btn btn-outline-secondary" onclick="getDepartamentos()">
                <i class="fa fa-arrow-left me-2"></i>Volver
              </button>
            </div>
            <div class="row g-4">${cardsHTML}</div>`;
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
              const filtrados = datos.filter(d => !idPais || String(d.id_pais || '') === String(idPais));
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
          const contextOrg = document.getElementById('addDepartamentoContextOrgId');
          const selectPais = document.getElementById('addDepartamentoPaisId');
          const selectOrg = document.getElementById('addDepartamentoOrganizacionalId');
          const paisGroup = selectPais?.closest('.col-12');
          const orgGroup = selectOrg?.closest('.col-12');
          const title = modalEl?.querySelector('.modal-body .text-center h4');
          const help = modalEl?.querySelector('.modal-body .text-center p');
          const nombreLabel = modalEl?.querySelector('label[for="modalNombreDepartamento"]');

          if (form) form.reset();
          if (input) input.classList.remove('is-invalid');
          if (selectPais) selectPais.classList.remove('is-invalid');
          if (selectOrg) selectOrg.classList.remove('is-invalid');
          ['errorNombre', 'errorPais', 'errorDepartamentoOrganizacional'].forEach(id => {
            const err = document.getElementById(id);
            if (err) err.style.display = 'none';
          });

          if (modoInput) modoInput.value = modo;
          if (contextPais) contextPais.value = opciones.idPais || '';
          if (contextOrg) contextOrg.value = opciones.idOrg || '';

          if (modo === 'area') {
            if (title) title.textContent = 'Agregar nuevo departamento';
            if (help) help.textContent = `Se agregará al área ${opciones.nombreOrg || ''}.`;
            if (nombreLabel) nombreLabel.textContent = 'Nombre del Departamento *';
            if (input) input.placeholder = 'Ej. Call Center, Campo 1-7, Despachos...';
            if (paisGroup) paisGroup.style.display = 'none';
            if (orgGroup) orgGroup.style.display = 'none';
          } else {
            if (title) title.textContent = 'Agregar nueva área';
            if (help) help.textContent = 'Selecciona el país y escribe el nombre del área.';
            if (nombreLabel) nombreLabel.textContent = 'Nombre del Área *';
            if (input) input.placeholder = 'Ej. Cobranza, Comercial, Administración de Finanzas...';
            if (paisGroup) paisGroup.style.display = '';
            if (orgGroup) orgGroup.style.display = 'none';
          }
        }

        function abrirModalNuevoDepartamento() {
          if (window.departamentoOrganizacionActivo) {
            const depOrg = window.departamentoOrganizacionActivo;
            abrirModalNuevaArea(depOrg.id_pais, depOrg.id, depOrg.nombre);
            return;
          }

          const select = document.getElementById('addDepartamentoPaisId');
          const selectOrg = document.getElementById('addDepartamentoOrganizacionalId');
          configurarModalDepartamento('departamento');
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
                  const iso = p.codigo_iso || 'xx';
                  select.insertAdjacentHTML('beforeend',
                    `<option value="${p.id}">${p.nombre}</option>`
                  );
                });
              }
              cargarDepartamentosOrganizacionalesModal(select.value || '');
              const modal = new bootstrap.Modal(document.getElementById('addDepartamentoModal'));
              modal.show();
            },
            onError: () => {
              const modal = new bootstrap.Modal(document.getElementById('addDepartamentoModal'));
              modal.show();
            }
          });
        }

        function abrirModalNuevaArea(idPais, idOrg, nombreOrg) {
          configurarModalDepartamento('area', { idPais, idOrg, nombreOrg });
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
            onSuccess: (resp) => {},
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
              const selectOrg = document.getElementById('addDepartamentoOrganizacionalId');
              const input = document.getElementById('modalNombreDepartamento');
              const nombre = input.value.trim();
              const modo = document.getElementById('addDepartamentoModo')?.value || 'departamento';
              const idPais = modo === 'area'
                ? (document.getElementById('addDepartamentoContextPaisId')?.value || '')
                : selectPais.value;
              const idDepartamentoOrganizacional = modo === 'departamento'
                ? 'departamento'
                : modo === 'area'
                ? (document.getElementById('addDepartamentoContextOrgId')?.value || '')
                : (selectOrg ? selectOrg.value : '');
              const errorNombre = document.getElementById('errorNombre');
              const errorPais = document.getElementById('errorPais');
              const errorOrg = document.getElementById('errorDepartamentoOrganizacional');

              let valid = true;

              if (!idPais) {
                errorPais.textContent = 'Debes seleccionar un país';
                errorPais.style.display = 'block';
                selectPais.classList.add('is-invalid');
                valid = false;
              } else {
                errorPais.style.display = 'none';
                selectPais.classList.remove('is-invalid');
              }

              if (!idDepartamentoOrganizacional) {
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

              if (!nombre) {
                errorNombre.textContent = modo === 'departamento' ? 'El nombre del área es requerido' : 'El nombre del departamento es requerido';
                errorNombre.style.display = 'block';
                input.classList.add('is-invalid');
                valid = false;
              } else {
                errorNombre.style.display = 'none';
                input.classList.remove('is-invalid');
              }

              if (!valid) return;

              const submitBtn = form.querySelector('button[type="submit"]');
              const originalText = submitBtn.innerHTML;
              submitBtn.disabled = true;
              submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Guardando...';

              const endpoint = modo === 'departamento'
                ? "/departamentos/InsertDepartamentoOrganizacional"
                : "/departamentos/InsertDepartamento";
              const requestData = modo === 'departamento'
                ? { nombre, id_pais: idPais }
                : { nombre, id_pais: idPais, id_departamento_organizacional: idDepartamentoOrganizacional };

              http.request({
                endpoint,
                method: "POST",
                data: requestData,
                onSuccess: (resp) => {
                  if (resp.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addDepartamentoModal'));
                    modal.hide();
                    form.reset();
                    getDepartamentos();
                    Swal.fire({
                      icon: 'success',
                      title: modo === 'departamento' ? 'Área creada' : 'Departamento creado',
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
        self::respuestaJSON(DepartamentosDAO::InsertDepartamento($nombre, $id_pais, $id_departamento_organizacional));
    }

    public function InsertDepartamentoOrganizacional()
    {
        $nombre = $_POST['nombre'] ?? null;
        $id_pais = $_POST['id_pais'] ?? 1;
        self::respuestaJSON(DepartamentosDAO::InsertDepartamentoOrganizacional($nombre, $id_pais));
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
