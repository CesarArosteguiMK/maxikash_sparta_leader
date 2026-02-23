<?php

namespace controllers;

use Core\Controller;
use Models\Paises as PaisesDAO;

class Paises extends Controller
{
    public function consulta()
    {
        $script = <<<'HTML'
        <script>
        function getPaises() {
            http.request({
                endpoint: "/paises/getPaises",
                onSuccess: (resp) => {
                    if (!resp.success) return;
                    const container = document.getElementById('paisesCards');
                    container.innerHTML = '';

                    resp.datos.forEach(pais => {
                        const activo = Number(pais.activo) === 1;
                        const badgeClass = activo ? 'badge-glass-success' : 'badge-glass-secondary';
                        const badgeText = activo ? 'Activo' : 'Inactivo';
                        const btnClass = activo ? 'btn-outline-danger' : 'btn-outline-success';
                        const btnText = activo ? 'Desactivar' : 'Activar';
                        const btnIcon = activo ? 'fa-toggle-off' : 'fa-toggle-on';

                        const card = `
                        <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 rounded-3 overflow-hidden pais-card">
                                <div class="pais-card-header" style="height: 100px; position: relative; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <span class="fi fi-${pais.codigo_iso} fis" style="font-size: 3.5rem; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.3);"></span>
                                    </div>
                                    <span class="badge ${badgeClass} position-absolute" style="top: 10px; right: 10px; font-size: 0.75rem; font-weight: 600; padding: 0.4em 0.8em; backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); border-radius: 12px; border: 1px solid rgba(255,255,255,0.4); box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
                                        ${badgeText}
                                    </span>
                                </div>
                                <div class="card-body text-center pt-3">
                                    <h5 class="fw-bold mb-1">${pais.nombre}</h5>
                                    <p class="text-muted small mb-3">Código: ${pais.codigo_iso.toUpperCase()}</p>
                                    <div class="d-flex justify-content-center gap-4 mb-3">
                                        <div class="text-center">
                                            <div class="fw-bold fs-5 text-primary">${pais.total_personas ?? 0}</div>
                                            <small class="text-muted">Personal</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="fw-bold fs-5 text-info">${pais.total_departamentos ?? 0}</div>
                                            <small class="text-muted">Departamentos</small>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm ${btnClass}" onclick="togglePais(${pais.id})">
                                        <i class="fa-solid ${btnIcon} me-1"></i>${btnText}
                                    </button>
                                </div>
                            </div>
                        </div>`;
                        container.insertAdjacentHTML('beforeend', card);
                    });

                    const addCard = `
                    <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 rounded-3 overflow-hidden pais-card-new d-flex align-items-center justify-content-center" style="min-height: 280px; cursor: pointer;" onclick="abrirModalNuevoPais()">
                            <div class="text-center p-4">
                                <div class="mb-3" style="width: 64px; height: 64px; margin: 0 auto; border-radius: 50%; background: rgba(105,108,255,0.1); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-circle-plus fa-2x text-primary"></i>
                                </div>
                                <h5 class="fw-bold text-primary mb-1">Nuevo País</h5>
                                <p class="text-muted small mb-0">Agregar un nuevo país donde opera la empresa</p>
                            </div>
                        </div>
                    </div>`;
                    container.insertAdjacentHTML('beforeend', addCard);
                }
            });
        }

        function togglePais(id) {
            http.request({
                endpoint: "/paises/toggleActivo",
                method: "POST",
                data: { id: id },
                onSuccess: (resp) => {
                    if (resp.success) {
                        getPaises();
                    }
                }
            });
        }

        function abrirModalNuevoPais() {
            document.getElementById('inputNuevoPais').value = '';
            document.getElementById('previewBandera').innerHTML = '<i class="fa-solid fa-globe fa-3x text-muted"></i>';
            document.getElementById('previewNombrePais').textContent = 'Escribe el nombre del país';
            document.getElementById('errorNuevoPais').style.display = 'none';
            const modal = new bootstrap.Modal(document.getElementById('modalNuevoPais'));
            modal.show();
        }

        function previsualizarBandera() {
            const nombre = document.getElementById('inputNuevoPais').value.trim();
            const preview = document.getElementById('previewBandera');
            const label = document.getElementById('previewNombrePais');

            if (!nombre) {
                preview.innerHTML = '<i class="fa-solid fa-globe fa-3x text-muted"></i>';
                label.textContent = 'Escribe el nombre del país';
                return;
            }

            const iso = detectarISO(nombre);
            if (iso && iso !== 'xx') {
                preview.innerHTML = `<span class="fi fi-${iso} fis" style="font-size: 3.5rem; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.3);"></span>`;
            } else {
                preview.innerHTML = '<i class="fa-solid fa-flag fa-3x text-warning"></i>';
            }
            label.textContent = nombre;
        }

        const paisesIsoMap = {
            'mexico':'mx','méxico':'mx','guatemala':'gt','colombia':'co',
            'argentina':'ar','brasil':'br','chile':'cl','peru':'pe','perú':'pe',
            'ecuador':'ec','venezuela':'ve','uruguay':'uy','paraguay':'py',
            'bolivia':'bo','panama':'pa','panamá':'pa','costa rica':'cr',
            'cuba':'cu','honduras':'hn','el salvador':'sv','nicaragua':'ni',
            'republica dominicana':'do','república dominicana':'do',
            'puerto rico':'pr','jamaica':'jm','haiti':'ht','belice':'bz',
            'trinidad y tobago':'tt','surinam':'sr','guyana':'gy',
            'estados unidos':'us','canada':'ca','españa':'es','espana':'es',
            'francia':'fr','alemania':'de','italia':'it','portugal':'pt',
            'reino unido':'gb','japon':'jp','china':'cn','india':'in',
            'corea del sur':'kr','australia':'au','nueva zelanda':'nz',
            'rusia':'ru','sudafrica':'za','marruecos':'ma','egipto':'eg',
            'israel':'il','turquia':'tr','filipinas':'ph','tailandia':'th',
            'vietnam':'vn','indonesia':'id','malasia':'my','singapur':'sg',
            'pakistan':'pk','nigeria':'ng','kenia':'ke','ghana':'gh',
            'senegal':'sn','etiopia':'et','angola':'ao','mozambique':'mz',
            'suecia':'se','noruega':'no','finlandia':'fi','dinamarca':'dk',
            'irlanda':'ie','suiza':'ch','austria':'at','belgica':'be',
            'paises bajos':'nl','polonia':'pl','rumania':'ro','hungria':'hu',
            'grecia':'gr','croacia':'hr','serbia':'rs','ucrania':'ua',
            'georgia':'ge','armenia':'am','moldavia':'md','lituania':'lt',
            'letonia':'lv','estonia':'ee','islandia':'is','luxemburgo':'lu',
            'eslovaquia':'sk','eslovenia':'si','republica checa':'cz',
            'bulgaria':'bg','albania':'al','andorra':'ad','myanmar':'mm',
            'mongolia':'mn','taiwan':'tw','iran':'ir','irak':'iq',
            'jordania':'jo','libano':'lb','emiratos arabes unidos':'ae',
            'kazajistan':'kz','tunez':'tn','barbados':'bb'
        };

        function detectarISO(nombre) {
            const n = nombre.toLowerCase().trim()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            const nOriginal = nombre.toLowerCase().trim();

            if (paisesIsoMap[nOriginal]) return paisesIsoMap[nOriginal];
            if (paisesIsoMap[n]) return paisesIsoMap[n];

            for (const [key, iso] of Object.entries(paisesIsoMap)) {
                const keyNorm = key.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                if (keyNorm === n || key === nOriginal) return iso;
            }
            return 'xx';
        }

        function guardarNuevoPais() {
            const input = document.getElementById('inputNuevoPais');
            const nombre = input.value.trim();
            const errorDiv = document.getElementById('errorNuevoPais');

            if (!nombre) {
                errorDiv.textContent = 'El nombre del país es requerido';
                errorDiv.style.display = 'block';
                input.classList.add('is-invalid');
                return;
            }

            errorDiv.style.display = 'none';
            input.classList.remove('is-invalid');

            const btn = document.getElementById('btnGuardarPais');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Guardando...';

            http.request({
                endpoint: "/paises/insertPais",
                method: "POST",
                data: { nombre: nombre },
                onSuccess: (resp) => {
                    if (resp.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoPais'));
                        modal.hide();
                        getPaises();
                        Swal.fire({
                            icon: 'success',
                            title: 'País agregado',
                            text: resp.mensaje,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        errorDiv.textContent = resp.mensaje || 'Error al agregar el país';
                        errorDiv.style.display = 'block';
                        input.classList.add('is-invalid');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                },
                onError: () => {
                    errorDiv.textContent = 'Error de conexión.';
                    errorDiv.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        }

        $(document).ready(() => {
            getPaises();

            const input = document.getElementById('inputNuevoPais');
            if (input) {
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    document.getElementById('errorNuevoPais').style.display = 'none';
                    previsualizarBandera();
                });
            }

            $('#modalNuevoPais').on('hidden.bs.modal', function() {
                document.getElementById('inputNuevoPais').value = '';
                document.getElementById('inputNuevoPais').classList.remove('is-invalid');
                document.getElementById('errorNuevoPais').style.display = 'none';
                const btn = document.getElementById('btnGuardarPais');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save me-2"></i>Guardar';
            });
        });
        </script>
        HTML;

        self::set("titulo", "Gestión de Países");
        self::set("script", $script);
        self::render("paises_all");
    }

    public function getPaises()
    {
        self::respuestaJSON(PaisesDAO::getAll());
    }

    public function toggleActivo()
    {
        $id = $_POST['id'] ?? null;
        self::respuestaJSON(PaisesDAO::toggleActivo($id));
    }

    public function insertPais()
    {
        $nombre = $_POST['nombre'] ?? null;
        self::respuestaJSON(PaisesDAO::insertPais($nombre));
    }

    public function getPaisesActivos()
    {
        header('Content-Type: application/json; charset=utf-8');
        $datos = PaisesDAO::getPaisesActivos();
        echo json_encode(['success' => true, 'datos' => $datos]);
        exit;
    }
}
