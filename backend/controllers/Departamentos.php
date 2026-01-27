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

        /* ---------- TÍTULO DEPARTAMENTO ---------- */
        function inicioEdicionTitulo(element) {
          valorOriginal = element.textContent.trim();
        }
        
        function guardarTituloDepartamento(element) {
          const nuevoValor = element.textContent.trim();
          const idDepartamento = element.dataset.departamentoId;
        
          // Si quedó vacío, revertimos al valor original
          if (!nuevoValor) {
            element.textContent = valorOriginal;
            element.contentEditable = false;
            return;
          }
        
          // Desactivamos edición
          element.contentEditable = false;
        
          // POST al backend
          http.request({
            endpoint: "/departamentos/UpdateNombreDepartamento",
            method: "POST",
            data: {
              id_departamento: idDepartamento,
              nombre: nuevoValor
            },
            onSuccess: (resp) => {
              console.log('Departamento actualizado en BD:', resp);
              if (resp.success) {
                // Actualizar también el título en la lista de tarjetas si es necesario
                window.departamentoActivo && getDepartamentos();
              }
            },
            onError: (err) => {
              console.error('Error al actualizar departamento:', err);
              // Revertir en caso de error
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
          const nuevoValor = element.textContent.trim();
        
          if (!nuevoValor) {
            element.textContent = valorOriginal;
          }
        
          element.contentEditable = false;
          console.log('Puesto actualizado:', nuevoValor);
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
          if (!nombre) return;
        
          const lista = document.getElementById('listaPuestos');
        
          // POST al backend para insertar el nuevo puesto
          http.request({
            endpoint: "/departamentos/InsertPuesto",
            method: "POST",
            data: { nombre: nombre, id_departamento: id_departamento },
            onSuccess: (resp) => {
              if (resp.success) {
                // Recargar lista para que el nuevo puesto tenga número y sea arrastrable
                cargarPuestosDepartamento(id_departamento);
                input.value = '';
                document.getElementById('nuevoPuestoContainer').classList.add('d-none');
              } else {
                console.error('Error al insertar puesto:', resp.mensaje);
              }
            },
            onError: (err) => {
              console.error('Error al llamar InsertPuesto:', err);
            }
          });
        }
        
        function abrirModalDepartamento(idDepartamento, nombreDepartamento) {
          // Guardamos el ID activo (muy importante para después)
          window.departamentoActivo = idDepartamento;
        
          // Título del modal
          const titulo = document.getElementById('tituloDepartamento');
          titulo.textContent = nombreDepartamento;
          titulo.dataset.departamentoId = idDepartamento;
          titulo.contentEditable = false; // Asegurar que no esté editable al inicio
          
          document.getElementById('id_departamento').value = idDepartamento;
        
          // Limpiamos lista
          const lista = document.getElementById('listaPuestos');
          lista.innerHTML = `
            <li class="text-center text-muted py-4">
              <i class="fa fa-spinner fa-spin me-2"></i> Cargando puestos...
            </li>
          `;
        
          // Abrimos modal de inmediato (UX PRO)
          const modal = new bootstrap.Modal(
            document.getElementById('modalDetalleDepartamento')
          );
          modal.show();
        
          // Cargamos puestos reales
          cargarPuestosDepartamento(idDepartamento);
        }
        
        function cargarPuestosDepartamento(idDepartamento) {
          http.request({
            endpoint: "/departamentos/getPuestosPorDepartamento",
            method: "POST",
            data: {
              id_departamento: idDepartamento
            },
            onSuccess: (resp) => {
              console.log(resp);
              
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
            onSuccess: (resp) => {
              if (resp && resp.success) console.log('Orden guardado.');
            },
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



        
         const getDepartamentos = () => {
            http.request({
                endpoint: "/departamentos/getDepartamentos",
                onSuccess: (resp) => {
                    
                    const container = $("#departamentosCards");
                    container.empty();
                    
                    const imgUrl = "https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/lady-with-laptop-light.png";
                    
                    // Tarjetas de departamentos
                    resp.datos.forEach(d => {
                        const tarjeta = `
                        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm rounded-3">
                                <div class="row h-100 g-0">
                                    <div class="col-sm-8 d-flex flex-column justify-content-center p-3">
                                        <h5 class="mb-2">${d.departamento_nombre}</h5>
                                        <p class="mb-0 text-muted small">Puestos: <strong>${d.total_puestos ?? 0}</strong></p>
                                        <p class="mb-0 text-muted small">Personal: <strong>${d.total_personas ?? 0}</strong></p>
                                        <p class="mb-3 text-muted small">Estado: <strong>${d.activo === 1 ? 'Activo' : 'Inactivo'}</strong></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button
                                              type="button"
                                              class="btn btn-sm btn-outline-primary fw-semibold text-uppercase"
                                               onclick="abrirModalDepartamento(${d.departamento_id}, '${d.departamento_nombre.replace(/'/g, "\\'")}')">
                                              Editar
                                            </button>
                                            <a href="javascript:void(0);" class="text-secondary fs-5">
                                                <i class="bx bx-copy"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 d-flex align-items-center justify-content-center p-1">
                                        <img src="${d.img_url ?? imgUrl}" class="img-fluid" width="120" alt="${d.departamento_nombre}">
                                    </div>
                                </div>
                            </div>
                        </div>`;
                        container.append(tarjeta);
                    });
                    
                    // Tarjeta “Add New Departamento” igual que tu referencia
                    const addNew = `
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card h-100 shadow-sm rounded-3">
                            <div class="row h-100">
                                <div class="col-sm-5">
                                    <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-4 ps-6">
                                        <img src="${imgUrl}" class="img-fluid" alt="Image" width="120">
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="card-body text-sm-end text-center ps-sm-0">
                                        <button data-bs-target="#addDepartamentoModal" data-bs-toggle="modal" class="btn btn-sm btn-primary mb-4 text-nowrap add-new-role">+ Nuevo Departamento</button>
                                       <p class="mb-0 text-muted small">
                                            Agrega un nuevo departamento si no existe.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    container.append(addNew);

                    
                }
            });
        };
        
        $(document).ready(() => {
            getDepartamentos();
        });
        
        /* =====================================================
           INTEGRACIÓN SEGURA – EDICIÓN DE PUESTOS (NO ROMPE NADA)
           ===================================================== */
        
        /* Cuando entra en foco (guardamos valor original) */
        function inicioEdicion(element) {
          valorOriginal = element.textContent.trim();
        }
        
        /* Guardar puesto desde blur */
        function guardarPuesto(element) {
          const nuevoValor = element.textContent.trim();
          const idPuesto = element.dataset.puestoId;
        
          // Si quedó vacío, revertimos al valor original
          if (!nuevoValor) {
            element.textContent = valorOriginal;
            element.contentEditable = false;
            return;
          }
        
          // Desactivamos edición
          element.contentEditable = false;
        
          // POST al backend
          http.request({
            endpoint: "/departamentos/getActualizaNombrePues",
            method: "POST",
            data: {
              id_puesto: idPuesto,
              nombre: nuevoValor
            },
            onSuccess: (resp) => {
              console.log('Puesto actualizado en BD:', resp);
            },
            onError: (err) => {
              console.error('Error al actualizar puesto:', err);
              // Revertir en caso de error
              element.textContent = valorOriginal;
            }
          });
        
          console.log('Puesto actualizado localmente:', {
            id_puesto: idPuesto,
            nombre: nuevoValor,
            departamento: window.departamentoActivo
          });
                  
        }
        
        /* Click en ícono lápiz */
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
        
        // Manejar submit del formulario de nuevo departamento
        $(document).ready(function() {
          const form = document.getElementById('addDepartamentoForm');
          
          if (form) {
            form.addEventListener('submit', function(e) {
              e.preventDefault();
              
              const input = document.getElementById('modalNombreDepartamento');
              const nombre = input.value.trim();
              const errorDiv = document.getElementById('errorNombre');
              
              // Validación
              if (!nombre) {
                errorDiv.textContent = 'El nombre del departamento es requerido';
                errorDiv.style.display = 'block';
                input.classList.add('is-invalid');
                return;
              }
              
              // Limpiar errores
              errorDiv.style.display = 'none';
              input.classList.remove('is-invalid');
              
              // Deshabilitar botón mientras se procesa
              const submitBtn = form.querySelector('button[type="submit"]');
              const originalText = submitBtn.innerHTML;
              submitBtn.disabled = true;
              submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Guardando...';
              
              // POST al backend
              http.request({
                endpoint: "/departamentos/InsertDepartamento",
                method: "POST",
                data: { nombre },
                onSuccess: (resp) => {
                  if (resp.success) {
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addDepartamentoModal'));
                    modal.hide();
                    
                    // Limpiar formulario
                    form.reset();
                    
                    // Recargar lista de departamentos
                    getDepartamentos();
                    
                    // Mostrar notificación de éxito (opcional)
                    console.log('Departamento creado exitosamente:', resp.mensaje);
                  } else {
                    errorDiv.textContent = resp.mensaje || 'Error al crear el departamento';
                    errorDiv.style.display = 'block';
                    input.classList.add('is-invalid');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                  }
                },
                onError: (err) => {
                  console.error('Error al crear departamento:', err);
                  errorDiv.textContent = 'Error de conexión. Intenta nuevamente.';
                  errorDiv.style.display = 'block';
                  input.classList.add('is-invalid');
                  submitBtn.disabled = false;
                  submitBtn.innerHTML = originalText;
                }
              });
            });
            
            // Limpiar errores al escribir
            const input = document.getElementById('modalNombreDepartamento');
            if (input) {
              input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                document.getElementById('errorNombre').style.display = 'none';
              });
            }
            
            // Limpiar formulario al cerrar modal
            $('#addDepartamentoModal').on('hidden.bs.modal', function() {
              form.reset();
              const errorDiv = document.getElementById('errorNombre');
              errorDiv.style.display = 'none';
              const input = document.getElementById('modalNombreDepartamento');
              input.classList.remove('is-invalid');
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

    public function InsertPuesto()
    {
        $nombre = $_POST['nombre'] ?? null;
        $id_departamento = $_POST['id_departamento'] ?? null;
        self::respuestaJSON(DepartamentosDAO::InsertPuestos($nombre, $id_departamento));
    }
    public function getPuestosPorDepartamento()
    {
        $id_dep = $_POST['id_departamento'] ?? null;
        self::respuestaJSON(DepartamentosDAO::getConsultaPuestos($id_dep));
    }

    public function getActualizaNombrePues()
    {
        $id_pues = $_POST['id_puesto'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        self::respuestaJSON(DepartamentosDAO::UpdateNombrePuesto($id_pues, $nombre));
    }

    /**
     * Actualizar orden de puestos (drag and drop)
     */
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
        self::respuestaJSON(DepartamentosDAO::InsertDepartamento($nombre));
    }

    public function UpdateNombreDepartamento()
    {
        $id_departamento = $_POST['id_departamento'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        self::respuestaJSON(DepartamentosDAO::UpdateNombreDepartamento($id_departamento, $nombre));
    }

}
