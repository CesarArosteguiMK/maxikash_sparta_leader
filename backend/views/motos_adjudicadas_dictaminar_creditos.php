<?php
$madPuedeEnviarGestorLegacy = in_array(100, array_map('intval', $_SESSION['modulos'] ?? []), true);
?>
<div class="mad-dictamen container-fluid py-3 px-2 px-md-3">
    <div class="mad-page-head d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h4 class="mb-1 text-primary d-flex align-items-center">
                <i class="fa-solid fa-clipboard-check me-2" aria-hidden="true"></i>
                <span>Dictaminar creditos</span>
            </h4>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="/Adjudicacion/administracion" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <section class="mad-panel mad-search-panel mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <label for="mad-id-credito" class="form-label">ID credito</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                    <input type="number" id="mad-id-credito" class="form-control" placeholder="Ej. 1979397">
                    <button type="button" id="mad-btn-diagnosticar" class="btn btn-primary">
                        <i class="fa-solid fa-magnifying-glass me-1"></i>Diagnosticar
                    </button>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <label for="mad-id-usuario-legacy" class="form-label">id_usuario Legacy</label>
                <select id="mad-id-usuario-legacy" class="form-select">
                    <option value="">Cargando usuarios Legacy...</option>
                </select>
            </div>
            <div class="col-12 col-lg-auto">
                <button type="button" id="mad-btn-desbloquear" class="btn btn-outline-danger mad-hidden">
                    <i class="fa-solid fa-unlock-keyhole me-1"></i>Desbloquear componentes
                </button>
            </div>
        </div>
    </section>

    <div class="mad-grid mad-hidden" id="mad-results-grid">
        <aside class="mad-side">
            <section class="mad-panel mad-status-panel">
                <div class="mad-status-title">
                    <div>
                        <h5>Diagnostico del credito</h5>
                    </div>
                    <div class="mad-status-badges">
                        <span id="mad-badge-puede" class="badge bg-label-secondary">Pendiente</span>
                        <span id="mad-credit-status" class="badge bg-label-secondary">Sin cargar</span>
                    </div>
                </div>
                <div class="mad-status-content">
                    <div class="mad-summary">
                        <div><span>ID</span><strong id="mad-summary-id">-</strong></div>
                        <div><span>Cliente</span><strong id="mad-summary-cliente">-</strong></div>
                        <div><span>Estatus S2</span><strong id="mad-summary-estatus-s2">-</strong></div>
                        <div><span>Bucket</span><strong id="mad-summary-bucket">-</strong></div>
                        <div><span>Saldo capital</span><strong id="mad-summary-saldo">-</strong></div>
                    </div>
                    <div class="mad-check-list" id="mad-check-list">
                        <div class="mad-check muted"><i class="fa-regular fa-circle"></i><span>Segundometro</span><strong>-</strong></div>
                        <div class="mad-check muted"><i class="fa-regular fa-circle"></i><span>adj_operacion</span><strong>-</strong></div>
                        <div class="mad-check muted"><i class="fa-regular fa-circle"></i><span>tasks campana 432</span><strong>-</strong></div>
                        <div class="mad-check muted"><i class="fa-regular fa-circle"></i><span>dictums opcion 13</span><strong>-</strong></div>
                    </div>
                </div>
            </section>
        </aside>

        <main class="mad-main">
            <section class="mad-panel mad-dictamen-form mad-hidden">
                <div class="mad-panel-head">
                    <div>
                        <h5>Datos para el dictamen web</h5>
                    </div>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <?php if ($madPuedeEnviarGestorLegacy): ?>
                            <button type="button" id="mad-btn-enviar-gestor-legacy" class="btn btn-outline-primary" disabled>
                                <i class="fa-solid fa-share-from-square me-1"></i>Enviar campa&ntilde;a a gestor en Legacy
                            </button>
                        <?php endif; ?>
                        <button type="button" id="mad-btn-simular" class="btn btn-primary" disabled>
                            <i class="fa-solid fa-paper-plane me-1"></i>Guardar dictamen
                        </button>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="mad-fecha-gestion" class="form-label">Fecha y hora real de gestion</label>
                        <input type="datetime-local" id="mad-fecha-gestion" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Marca</label>
                        <input class="form-control mad-field" data-key="marca" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Modelo</label>
                        <input class="form-control mad-field" data-key="modelo" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Ano</label>
                        <input class="form-control mad-field" data-key="ano" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Color</label>
                        <input class="form-control mad-field" data-key="color" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">VIN / Serie</label>
                        <input class="form-control mad-field" data-key="no_de_serie_vin" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">No. motor</label>
                        <input class="form-control mad-field" data-key="no_de_motor" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Placas</label>
                        <input class="form-control mad-field" data-key="placas" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Kilometraje</label>
                        <input class="form-control mad-field" data-key="kilometraje" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Llave fisica</label>
                        <select class="form-select mad-field" data-key="tiene_llave_fisica" required>
                            <option value="si">Si</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Tarjeta circulacion</label>
                        <select class="form-select mad-field" data-key="tiene_tarjeta_de_circulacion_en_fisico" required>
                            <option value="si">Si</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Placa fisica</label>
                        <select class="form-select mad-field" data-key="la_moto_tiene_placa_fisica" required>
                            <option value="si">Si</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Lugar de resguardo</label>
                        <select class="form-select mad-field" data-key="donde_resguardaras_la_moto" required>
                            <option value="cedis-__SPARTA_SECRET_REDACTED__">CEDIS Maxikash</option>
                            <option value="centro-de-acopio">Centro de acopio</option>
                            <option value="agencia">Agencia</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Estado resguardo</label>
                        <input class="form-control mad-field" data-key="estado_resguardo" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Ciudad / Municipio</label>
                        <input class="form-control mad-field" data-key="ciudad_resguardo" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Direccion resguardo</label>
                        <input class="form-control mad-field" data-key="direccion_resguardo" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Responsable</label>
                        <input class="form-control mad-field" data-key="responsable_resguardo" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Telefono contacto</label>
                        <input class="form-control mad-field" data-key="telefono_contacto" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Latitud</label>
                        <input class="form-control mad-field" data-key="lat" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Longitud</label>
                        <input class="form-control mad-field" data-key="lng" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Direccion task</label>
                        <input class="form-control mad-field" data-key="direccion" required>
                    </div>
                </div>
            </section>

            <section class="mad-panel mad-dictamen-form mad-hidden">
                <div class="mad-panel-head mad-evidence-head">
                    <div>
                        <h5>Evidencias</h5>
                    </div>
                </div>
                <div class="mad-evidence-grid">
                    <div class="mad-upload" data-key="foto_dacion_hoja_1" data-accept="image/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-regular fa-image"></i></div>
                        <span>Foto dacion hoja 1</span><input type="file" class="mad-file" accept="image/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="foto_dacion_hoja_2" data-accept="image/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-regular fa-image"></i></div>
                        <span>Foto dacion hoja 2</span><input type="file" class="mad-file" accept="image/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="foto_tacometro" data-accept="image/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-regular fa-image"></i></div>
                        <span>Foto tacometro</span><input type="file" class="mad-file" accept="image/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="foto_serie" data-accept="image/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-regular fa-image"></i></div>
                        <span>Foto serie</span><input type="file" class="mad-file" accept="image/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="foto_frontal" data-accept="image/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-regular fa-image"></i></div>
                        <span>Foto frontal</span><input type="file" class="mad-file" accept="image/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="foto_trasera" data-accept="image/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-regular fa-image"></i></div>
                        <span>Foto trasera</span><input type="file" class="mad-file" accept="image/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="foto_lateral_izq" data-accept="image/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-regular fa-image"></i></div>
                        <span>Foto lateral izquierda</span><input type="file" class="mad-file" accept="image/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="foto_lateral_der" data-accept="image/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-regular fa-image"></i></div>
                        <span>Foto lateral derecha</span><input type="file" class="mad-file" accept="image/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="foto_checklist" data-accept="image/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-regular fa-image"></i></div>
                        <span>Foto checklist</span><input type="file" class="mad-file" accept="image/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="video_360" data-accept="video/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-solid fa-video"></i></div>
                        <span>Video 360</span><input type="file" class="mad-file" accept="video/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="video_cliente_acuerdo" data-accept="video/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-solid fa-video"></i></div>
                        <span>Video cliente acuerdo</span><input type="file" class="mad-file" accept="video/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                    <div class="mad-upload" data-key="video_vuelta_prueba" data-accept="video/*">
                        <button type="button" class="mad-remove-file" aria-label="Quitar archivo"><i class="fa-solid fa-xmark"></i></button>
                        <div class="mad-preview"><i class="fa-solid fa-video"></i></div>
                        <span>Video vuelta prueba</span><input type="file" class="mad-file" accept="video/*">
                        <b class="mad-upload-badge">Pendiente</b>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<style>
.mad-dictamen {
    color: #172b45;
    max-width: 1680px;
    margin: 0 auto;
    --mad-ink: #14233b;
    --mad-blue: #203a5f;
    --mad-cyan: #00b8d9;
    --mad-green: #31d158;
    --mad-line: #dbe4ef;
}
.mad-page-head h4 {
    font-size: 1.55rem;
    font-weight: 800;
    letter-spacing: 0;
}
.mad-panel {
    background: #fff;
    border: 1px solid #dbe4ef;
    border-radius: 8px;
    box-shadow: 0 .35rem 1rem rgba(22, 36, 56, .07);
    padding: 1rem;
}
.mad-hidden { display: none !important; }
.mad-search-panel {
    padding: .9rem 1rem;
    border-left: 5px solid var(--mad-cyan);
    background: linear-gradient(90deg, #ffffff 0%, #ffffff 62%, #f4fbff 100%);
}
.mad-search-panel .form-label,
.mad-dictamen .form-label {
    color: #334155;
    font-weight: 700;
    font-size: .78rem;
    margin-bottom: .35rem;
}
.mad-search-panel .form-control,
.mad-search-panel .input-group-text,
.mad-search-panel .btn {
    min-height: 2.7rem;
}
.mad-search-panel .input-group-text {
    background: #f8fbff;
    color: var(--mad-blue);
    font-weight: 900;
}
.mad-search-panel .btn-primary,
.mad-dictamen .btn-primary {
    background: linear-gradient(135deg, #1b2e4b, #203a5f);
    border: 0;
    box-shadow: 0 .4rem .8rem rgba(31, 55, 91, .22);
}
.mad-grid {
    display: grid;
    gap: 1rem;
}
.mad-side { display: block; }
.mad-status-panel {
    padding: 0;
    overflow: hidden;
    border: 0;
}
.mad-status-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .75rem 1rem;
    border-bottom: 0;
    background: linear-gradient(135deg, #15243b, #203a5f);
    color: #fff;
}
.mad-status-title h5 {
    margin: 0;
    color: #fff;
    font-size: .98rem;
    font-weight: 850;
}
.mad-status-title span {
    color: #dceeff;
    font-size: .78rem;
}
.mad-status-title .badge {
    border: 1px solid rgba(255, 255, 255, .36);
    box-shadow: none;
}
.mad-status-badges {
    display: inline-flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: .4rem;
}
.mad-status-content {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 0;
}
.mad-main {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(360px, .75fr);
    gap: 1rem;
    align-items: start;
}
.mad-main > .mad-panel {
    border-top: 4px solid #e6eef7;
}
.mad-main > .mad-panel:first-child {
    border-top-color: var(--mad-cyan);
}
.mad-main > .mad-panel:nth-child(2) {
    border-top-color: #ffb703;
}
.mad-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .9rem;
    padding-bottom: .65rem;
    border-bottom: 1px solid #edf2f7;
}
.mad-panel-head h5 {
    margin: 0;
    color: #0b2d4a;
    font-size: 1.02rem;
    font-weight: 800;
}
.mad-panel-head p,
.mad-summary span { color: #64748b; }
.mad-check-list {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
    padding: .7rem 1rem .85rem;
    background: #f8fbff;
    border-top: 1px solid #e6eef7;
}
.mad-check {
    display: inline-grid;
    grid-template-columns: .85rem auto auto;
    gap: .35rem;
    align-items: center;
    border: 1px solid #d9e6f3;
    border-radius: 999px;
    background: #fff;
    padding: .34rem .62rem;
    font-size: .74rem;
    min-height: 0;
    white-space: nowrap;
}
.mad-check strong {
    color: #18314d;
    font-size: .73rem;
    line-height: 1.15;
    font-weight: 850;
}
.mad-check.ok i { color: #22c55e; }
.mad-check.bad i { color: #ef4444; }
.mad-check.muted i { color: #94a3b8; }
.mad-summary {
    display: grid;
    grid-template-columns: minmax(300px, 1.45fr) minmax(95px, .42fr) repeat(3, minmax(135px, .58fr));
    grid-template-areas: "cliente id estatus bucket saldo";
    gap: 0;
    align-items: stretch;
    padding: 0;
    background: #fff;
}
.mad-summary div {
    border: 0;
    border-right: 1px solid #edf2f7;
    background: transparent;
    border-radius: 0;
    padding: .85rem 1rem;
    min-height: 0;
}
.mad-summary div:last-child {
    border-right: 0;
    padding-right: 0;
}
.mad-summary div:nth-child(1) { grid-area: id; }
.mad-summary div:nth-child(2) {
    grid-area: cliente;
    background:
        linear-gradient(90deg, rgba(0,184,217,.14), rgba(255,255,255,0) 56%),
        #ffffff;
}
.mad-summary div:nth-child(3) { grid-area: estatus; }
.mad-summary div:nth-child(4) { grid-area: bucket; }
.mad-summary div:nth-child(5) { grid-area: saldo; }
.mad-summary strong {
    display: block;
    color: var(--mad-ink);
    margin-top: .14rem;
    word-break: break-word;
    line-height: 1.15;
    font-size: .86rem;
    font-weight: 850;
}
.mad-summary div:nth-child(2) strong {
    font-size: 1rem;
}
.mad-summary div:nth-child(3) strong,
.mad-summary div:nth-child(4) strong {
    color: #183f70;
}
.mad-summary div:nth-child(5) strong {
    color: #087f5b;
}
.mad-summary span {
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.mad-main > .mad-panel:first-child .row {
    --bs-gutter-x: .85rem;
    --bs-gutter-y: .75rem;
}
.mad-dictamen .form-control,
.mad-dictamen .form-select {
    border-color: #cbd5e1;
    border-radius: 8px;
    min-height: 2.5rem;
}
.mad-dictamen .form-control:focus,
.mad-dictamen .form-select:focus {
    border-color: #24456d;
    box-shadow: 0 0 0 .18rem rgba(36, 69, 109, .13);
}
.mad-evidence-head {
    margin-bottom: .75rem;
}
.mad-evidence-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .72rem;
}
.mad-upload {
    position: relative;
    border: 1px solid #dbe4ef;
    border-radius: 9px;
    background: #fff;
    aspect-ratio: 1 / 1;
    padding: .55rem;
    overflow: hidden;
    text-align: center;
    transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
}
.mad-upload:hover {
    border-color: #9eb3ce;
    box-shadow: 0 .45rem .9rem rgba(22, 36, 56, .08);
}
.mad-upload span,
.mad-upload small { display: block; }
.mad-upload span {
    position: absolute;
    left: .5rem;
    right: 2.4rem;
    top: .48rem;
    z-index: 3;
    display: block !important;
    color: #0b2d4a;
    font-weight: 800;
    font-size: .68rem;
    line-height: 1.08;
    min-height: 1.35rem;
    max-height: 2.15rem;
    overflow: hidden;
    text-align: left;
}
.mad-upload small { display: none !important; }
.mad-upload.uploaded {
    border: 2px solid #159451;
    padding: calc(.55rem - 1px);
}
.mad-upload.uploading { opacity: .72; }
.mad-upload-badge {
    position: absolute;
    left: 50%;
    bottom: .42rem;
    transform: translateX(-50%);
    z-index: 3;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 4.4rem;
    border-radius: 999px;
    padding: .14rem .5rem;
    background: #fff7ed;
    color: #c47a0a;
    border: 1px solid rgba(196, 122, 10, .22);
    font-size: .6rem;
    font-weight: 800;
    box-shadow: 0 .12rem .35rem rgba(15, 23, 42, .08);
}
.mad-upload.uploading .mad-upload-badge {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: rgba(29, 78, 216, .22);
}
.mad-upload.uploaded .mad-upload-badge {
    background: #eafaf0;
    color: #159451;
    border-color: rgba(21, 148, 81, .24);
}
.mad-preview {
    position: absolute;
    left: .55rem;
    right: .55rem;
    top: 2.25rem;
    bottom: 1.85rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    display: grid;
    place-items: center;
    overflow: hidden;
}
.mad-preview i {
    color: #1a52a8;
    font-size: 1.45rem;
}
.mad-preview img,
.mad-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.mad-file {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}
.mad-remove-file {
    position: absolute;
    top: .5rem;
    right: .5rem;
    z-index: 4;
    width: 1.8rem;
    height: 1.8rem;
    border: 0;
    border-radius: 50%;
    background: #ef4444;
    color: #fff;
    display: none;
    align-items: center;
    justify-content: center;
    box-shadow: 0 .2rem .5rem rgba(15, 23, 42, .2);
}
.mad-upload.uploaded .mad-remove-file { display: flex; }
@media (min-width: 1500px) {
    .mad-evidence-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 1399.98px) {
    .mad-summary { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    .mad-main { grid-template-columns: minmax(0, 1.35fr) minmax(320px, .8fr); }
}
@media (max-width: 1199.98px) {
    .mad-main { grid-template-columns: 1fr; }
    .mad-evidence-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}
@media (max-width: 991.98px) {
    .mad-summary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        grid-template-areas:
            "cliente cliente"
            "id estatus"
            "bucket saldo";
    }
    .mad-summary div,
    .mad-summary div + div {
        border-right: 0;
        border-bottom: 1px solid #edf2f7;
        padding: .7rem 0;
    }
    .mad-evidence-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
    .mad-dictamen { padding-inline: .5rem !important; }
    .mad-page-head h4 { font-size: 1.22rem; }
    .mad-panel { padding: .8rem; border-radius: 9px; }
    .mad-status-panel { padding: 0; }
    .mad-status-title { align-items: flex-start; flex-direction: column; }
    .mad-summary,
    .mad-evidence-grid { grid-template-columns: 1fr; }
    .mad-summary {
        grid-template-areas:
            "cliente"
            "id"
            "estatus"
            "bucket"
            "saldo";
    }
    .mad-summary div { min-height: auto; }
    .mad-search-panel .input-group { display: grid; grid-template-columns: auto minmax(0, 1fr); }
    .mad-search-panel .input-group .btn { grid-column: 1 / -1; border-radius: 8px !important; }
}
</style>

<script>
(function () {
    const idInput = document.getElementById('mad-id-credito');
    const userInput = document.getElementById('mad-id-usuario-legacy');
    const fechaGestionInput = document.getElementById('mad-fecha-gestion');
    const btnDiag = document.getElementById('mad-btn-diagnosticar');
    const btnSim = document.getElementById('mad-btn-simular');
    const btnEnviarGestorLegacy = document.getElementById('mad-btn-enviar-gestor-legacy');
    const btnUnlock = document.getElementById('mad-btn-desbloquear');
    const resultsGrid = document.getElementById('mad-results-grid');
    let diagnosticoActual = null;
    let puedeDesbloquearComponentes = false;
    const uploadedFiles = {};

    function setFechaGestionDefault() {
        if (!fechaGestionInput || fechaGestionInput.value) return;
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        fechaGestionInput.value = now.toISOString().slice(0, 16);
    }

    function money(v) {
        const n = Number(v || 0);
        return n ? n.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }) : '-';
    }

    function checkRow(label, ok, value) {
        const cls = ok === true ? 'ok' : ok === false ? 'bad' : 'muted';
        const icon = ok === true ? 'fa-circle-check' : ok === false ? 'fa-circle-xmark' : 'fa-circle';
        return `<div class="mad-check ${cls}"><i class="fa-regular ${icon}"></i><span>${label}</span><strong>${value}</strong></div>`;
    }

    function renderDiag(d) {
        diagnosticoActual = d;
        resultsGrid?.classList.remove('mad-hidden');
        const seg = d.segundometro || {};
        const s2 = d.s2 || {};
        const s2Credito = s2.credito || {};
        const op = d.operacion || null;
        const legacy = d.legacy || {};
        const task = legacy.task || null;
        const dictum = legacy.dictamen || null;
        const tieneComponentesBloqueados = !!(op || task || dictum);

        document.getElementById('mad-summary-id').textContent = d.id_credito || '-';
        document.getElementById('mad-summary-cliente').textContent = s2Credito.nombre_cliente || seg.Nombre_cliente || task?.client_name || '-';
        document.getElementById('mad-summary-estatus-s2').textContent = s2.status_credito || s2Credito.status_credito || (s2.success === false ? 'No validado' : '-');
        document.getElementById('mad-summary-bucket').textContent = s2Credito.bucket || seg.Bucket_Morosidad_Real || '-';
        document.getElementById('mad-summary-saldo').textContent = money(s2Credito.saldo_actual || seg.Saldo_total_capital);
        document.getElementById('mad-credit-status').textContent = d.puede_simular ? 'Listo' : 'Bloqueado';
        document.getElementById('mad-credit-status').className = 'badge ' + (d.puede_simular ? 'bg-label-success' : 'bg-label-danger');
        document.getElementById('mad-badge-puede').textContent = d.puede_simular ? 'Puede guardar' : 'Bloqueado';
        document.getElementById('mad-badge-puede').className = 'badge ' + (d.puede_simular ? 'bg-label-success' : 'bg-label-danger');
        document.querySelectorAll('.mad-dictamen-form').forEach(el => {
            el.classList.toggle('mad-hidden', !d.puede_simular);
        });

        document.getElementById('mad-check-list').innerHTML = [
            checkRow('S2', !!s2.success, s2.success ? (s2.status_credito || s2Credito.status_credito || 'Validado') : 'No validado'),
            checkRow('Segundometro', !!seg.Id_credito, seg.Id_credito ? 'Existe' : 'Sin dato'),
            checkRow('Tracking', !op, op ? `Existe #${op.id}` : 'Libre'),
            checkRow('Task Legacy', task ? true : null, task ? `#${task.id}` : 'Se creara'),
            checkRow('Dictamen 13', !dictum, dictum ? `Existe #${dictum.id}` : 'Libre'),
        ].join('');

        btnSim.disabled = !d.puede_simular;
        if (btnEnviarGestorLegacy) {
            const puedeEnviarTask = !!d.puede_simular && !task;
            btnEnviarGestorLegacy.disabled = !puedeEnviarTask;
            btnEnviarGestorLegacy.title = puedeEnviarTask
                ? 'Crear task Legacy y asignarlo al gestor seleccionado.'
                : (task ? 'Ya existe Task Legacy para este credito.' : 'Primero diagnostica un credito libre y valido.');
        }
        if (btnUnlock) {
            btnUnlock.classList.toggle('mad-hidden', !(puedeDesbloquearComponentes && tieneComponentesBloqueados));
        }
        if (d.puede_simular) {
        }
    }

    function resetDiagView() {
        diagnosticoActual = null;
        resultsGrid?.classList.add('mad-hidden');
        document.querySelectorAll('.mad-dictamen-form').forEach(el => el.classList.add('mad-hidden'));
        if (btnSim) btnSim.disabled = true;
        if (btnEnviarGestorLegacy) btnEnviarGestorLegacy.disabled = true;
        btnUnlock?.classList.add('mad-hidden');
    }

    async function postJson(url, payload) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        return res.json();
    }

    async function getJson(url) {
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        return res.json();
    }

    function inicializarComboLegacy(intentos) {
        if (!userInput || typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.select2) {
            if ((intentos || 0) < 8) {
                setTimeout(() => inicializarComboLegacy((intentos || 0) + 1), 180);
            }
            return;
        }
        const $select = window.jQuery(userInput);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        $select.select2({
            width: '100%',
            placeholder: 'Selecciona usuario Legacy',
            allowClear: true,
            language: {
                noResults: function () { return 'Sin resultados'; },
                searching: function () { return 'Buscando...'; }
            }
        });
    }

    async function cargarUsuariosLegacy() {
        if (!userInput) return;
        userInput.disabled = true;
        userInput.innerHTML = '<option value="">Cargando usuarios Legacy...</option>';
        try {
            const data = await getJson('/Adjudicacion/usuariosActivosLegacy');
            if (!data || !data.success) {
                throw new Error((data && data.message) || 'No se pudieron cargar usuarios Legacy.');
            }
            const datos = Array.isArray(data.datos) ? data.datos : [];
            userInput.innerHTML = '';
            const opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = datos.length ? 'Selecciona usuario Legacy' : 'Sin usuarios Legacy activos';
            userInput.appendChild(opt0);
            datos.forEach(row => {
                const opt = document.createElement('option');
                opt.value = String(row.id || '');
                opt.textContent = row.label || ('#' + row.id);
                userInput.appendChild(opt);
            });
            userInput.disabled = datos.length === 0;
            inicializarComboLegacy(0);
        } catch (e) {
            userInput.innerHTML = '<option value="">No se pudieron cargar usuarios Legacy</option>';
            userInput.disabled = true;
            console.error(e);
        }
    }

    async function cargarPermisoDesbloqueo() {
        try {
            const data = await postJson('/Adjudicacion/estadoDesbloqueoDictamenMoto', {});
            puedeDesbloquearComponentes = !!(data && data.success && data.authorized);
            if (diagnosticoActual) renderDiag(diagnosticoActual);
        } catch (e) {
            puedeDesbloquearComponentes = false;
            btnUnlock?.classList.add('mad-hidden');
        }
    }

    async function uploadDictamenFile(box, file) {
        const id = (idInput.value || '').trim();
        const key = box.dataset.key || '';
        if (!id) {
            throw new Error('Primero indica el ID de credito.');
        }
        const fd = new FormData();
        fd.append('id_credito', id);
        fd.append('campo', key);
        fd.append('archivo', file);

        box.classList.add('uploading');
        const badge = box.querySelector('.mad-upload-badge');
        if (badge) badge.textContent = 'Subiendo';
        let small = box.querySelector('small');
        if (!small) {
            small = document.createElement('small');
            box.appendChild(small);
        }
        small.textContent = 'Subiendo archivo...';

        const res = await fetch('/Adjudicacion/subirArchivoDictamenMoto', { method: 'POST', body: fd });
        const data = await res.json();
        box.classList.remove('uploading');
        if (!data.success) {
            box.classList.remove('uploaded');
            if (badge) badge.textContent = 'Error';
            small.textContent = data.message || 'No se pudo subir.';
            throw new Error(data.message || 'No se pudo subir archivo.');
        }

        uploadedFiles[key] = {
            value: data.url || '',
            firebasePath: data.firebasePath || '',
            localFile: data.localFile || '',
            fileName: data.fileName || file.name || '',
            status: data.status || 'uploaded',
            typeApp: data.typeApp || ''
        };
        box.classList.add('uploaded');
        if (badge) badge.textContent = 'Cargado';
        renderUploadPreview(box, file, data.fileName || file.name);
        small.textContent = 'Listo: ' + (data.fileName || file.name);
    }

    function renderUploadPreview(box, file, fileName) {
        const preview = box.querySelector('.mad-preview');
        if (!preview) return;
        const url = URL.createObjectURL(file);
        if ((file.type || '').startsWith('video/')) {
            preview.innerHTML = `<video src="${url}" muted playsinline></video>`;
        } else if ((file.type || '').startsWith('image/')) {
            preview.innerHTML = `<img src="${url}" alt="">`;
        } else {
            preview.innerHTML = '<i class="fa-regular fa-file"></i>';
        }
        box.title = fileName || file.name || '';
    }

    function resetUploadBox(box) {
        const key = box.dataset.key || '';
        delete uploadedFiles[key];
        box.classList.remove('uploaded', 'uploading', 'primary');
        const input = box.querySelector('.mad-file');
        if (input) input.value = '';
        const preview = box.querySelector('.mad-preview');
        if (preview) {
            const isVideo = (box.dataset.accept || '').indexOf('video') >= 0;
            preview.innerHTML = isVideo ? '<i class="fa-solid fa-video"></i>' : '<i class="fa-regular fa-image"></i>';
        }
        const small = box.querySelector('small');
        if (small) small.remove();
        const badge = box.querySelector('.mad-upload-badge');
        if (badge) badge.textContent = 'Pendiente';
    }

    function validarFormularioDictamen() {
        const faltantes = [];
        const campos = [
            { el: fechaGestionInput, label: 'Fecha y hora real de gestion' },
            ...Array.from(document.querySelectorAll('.mad-field')).map(el => ({
                el,
                label: el.closest('[class*="col-"]')?.querySelector('.form-label')?.textContent?.trim() || el.dataset.key || 'Campo'
            }))
        ];

        campos.forEach(({ el, label }) => {
            if (!el) return;
            el.classList.remove('is-invalid');
            if ((el.value || '').trim() === '') {
                el.classList.add('is-invalid');
                faltantes.push(label);
            }
        });

        return faltantes;
    }

    btnDiag?.addEventListener('click', async function () {
        const id = (idInput.value || '').trim();
        resetDiagView();
        if (!id) {
            Swal?.fire?.('Falta credito', 'Indica el ID de credito.', 'warning');
            return;
        }
        Swal?.fire?.({ title: 'Validando credito', text: 'Consultando Segundometro, tracking y Legacy...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            const data = await postJson('/Adjudicacion/diagnosticarDictamenMoto', { id_credito: id });
            Swal?.close?.();
            if (!data.success) throw new Error(data.message || 'No se pudo diagnosticar.');
            renderDiag(data);
        } catch (e) {
            resetDiagView();
            Swal?.fire?.('Error', e.message || 'No se pudo diagnosticar.', 'error');
        }
    });

    idInput?.addEventListener('input', resetDiagView);

    btnSim?.addEventListener('click', async function () {
        if (!diagnosticoActual?.puede_simular) return;
        const payload = {
            id_credito: idInput.value,
            id_usuario_legacy: userInput.value,
            fecha_gestion: fechaGestionInput?.value || ''
        };
        document.querySelectorAll('.mad-field').forEach(el => { payload[el.dataset.key] = el.value || ''; });
        Object.keys(uploadedFiles).forEach(key => {
            const info = uploadedFiles[key] || {};
            payload[key] = info.value || '';
            payload[key + '_firebasePath'] = info.firebasePath || '';
            payload[key + '_localFile'] = info.localFile || '';
            payload[key + '_fileName'] = info.fileName || '';
        });
        if (!payload.id_usuario_legacy) {
            Swal?.fire?.('Falta usuario', 'Indica el id_usuario Legacy.', 'warning');
            return;
        }
        const faltantes = validarFormularioDictamen();
        if (faltantes.length) {
            Swal?.fire?.(
                'Campos obligatorios',
                'Completa estos campos antes de guardar: ' + faltantes.slice(0, 8).join(', ') + (faltantes.length > 8 ? '...' : ''),
                'warning'
            );
            return;
        }
        const confirm = await Swal.fire({
            icon: 'question',
            title: 'Guardar dictamen Legacy',
            text: `Se insertara en Legacy y adj_operacion con fecha operativa ${payload.fecha_gestion.replace('T', ' ')}.`,
            showCancelButton: true,
            confirmButtonText: 'Si, guardar',
            cancelButtonText: 'Cancelar'
        });
        if (!confirm.isConfirmed) return;

        Swal.fire({ title: 'Guardando dictamen', text: 'Insertando Legacy y sincronizando pipeline...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            const data = await postJson('/Adjudicacion/simularDictamenMoto', payload);
            Swal.close();
            if (!data.success) throw new Error(data.message || 'No se pudo guardar.');
            await Swal.fire('Listo', `${data.message} Task: ${data.task_id || '-'} / Dictum: ${data.dictum_id || '-'}`, 'success');
            btnDiag.click();
        } catch (e) {
            Swal.fire('Error', e.message || 'No se pudo guardar.', 'error');
        }
    });

    btnEnviarGestorLegacy?.addEventListener('click', async function () {
        if (!diagnosticoActual?.puede_simular) return;
        const legacy = diagnosticoActual.legacy || {};
        if (legacy.task) {
            Swal?.fire?.('Task existente', 'Ya existe Task Legacy para este credito. No se duplicara.', 'info');
            return;
        }
        const payload = {
            id_credito: idInput.value,
            id_usuario_legacy: userInput.value
        };
        document.querySelectorAll('.mad-field').forEach(el => {
            const key = el.dataset.key || '';
            if (['direccion', 'lat', 'lng'].indexOf(key) >= 0) {
                payload[key] = el.value || '';
            }
        });
        if (!payload.id_usuario_legacy) {
            Swal?.fire?.('Falta usuario', 'Indica el id_usuario Legacy que recibira la tarea.', 'warning');
            return;
        }
        const confirm = await Swal.fire({
            icon: 'question',
            title: 'Enviar campania a gestor',
            html: 'Se crear&aacute; una <strong>Task Legacy</strong> para que el gestor cargue la gesti&oacute;n. No se guardar&aacute; dictamen.',
            showCancelButton: true,
            confirmButtonText: 'Si, enviar',
            cancelButtonText: 'Cancelar'
        });
        if (!confirm.isConfirmed) return;

        Swal.fire({ title: 'Enviando a Legacy', text: 'Creando task y asignacion al gestor...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            const data = await postJson('/Adjudicacion/enviarCampaniaGestorLegacy', payload);
            Swal.close();
            if (!data.success) throw new Error(data.message || 'No se pudo enviar.');
            await Swal.fire('Listo', `${data.message} Task: ${data.task_id || '-'}`, 'success');
            btnDiag.click();
        } catch (e) {
            Swal.fire('Error', e.message || 'No se pudo enviar a gestor.', 'error');
        }
    });

    btnUnlock?.addEventListener('click', async function () {
        const id = (idInput.value || '').trim();
        if (!puedeDesbloquearComponentes || !diagnosticoActual || !id) return;

        const confirm = await Swal.fire({
            icon: 'warning',
            title: 'Desbloquear componentes',
            html: `
                <div class="text-start">
                    <p class="mb-2">Esto borrara el dictamen Legacy, la asignacion de la tarea, la tarea y el tracking local de esta adjudicacion.</p>
                    <label class="form-label">NIP de 6 digitos</label>
                    <input id="mad-unlock-nip" class="form-control text-center" type="password" inputmode="numeric" maxlength="6" autocomplete="off">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Desbloquear',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            focusConfirm: false,
            didOpen: () => {
                const input = document.getElementById('mad-unlock-nip');
                input?.focus();
                input?.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '').slice(0, 6);
                });
            },
            preConfirm: () => {
                const nip = (document.getElementById('mad-unlock-nip')?.value || '').trim();
                if (!/^\d{6}$/.test(nip)) {
                    Swal.showValidationMessage('El NIP debe tener 6 digitos.');
                    return false;
                }
                return nip;
            }
        });
        if (!confirm.isConfirmed) return;

        Swal.fire({
            title: 'Desbloqueando',
            text: 'Borrando componentes de Legacy y tracking local...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        try {
            const data = await postJson('/Adjudicacion/desbloquearDictamenMoto', {
                id_credito: id,
                nip: confirm.value
            });
            Swal.close();
            if (!data.success) throw new Error(data.message || 'No se pudo desbloquear.');
            const del = data.deleted || {};
            await Swal.fire(
                'Listo',
                `Se borraron dictums: ${del.legacy_dictums || 0}, asignaciones: ${del.legacy_task_user_assignments || 0}, tasks: ${del.legacy_tasks || 0}, adj_operacion: ${del.adj_operacion || 0}.`,
                'success'
            );
            btnDiag.click();
        } catch (e) {
            Swal.fire('Error', e.message || 'No se pudo desbloquear.', 'error');
        }
    });

    document.querySelectorAll('.mad-upload .mad-file').forEach(input => {
        input.addEventListener('change', async function () {
            const file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) return;
            const box = this.closest('.mad-upload');
            try {
                await uploadDictamenFile(box, file);
            } catch (e) {
                Swal?.fire?.('Archivo', e.message || 'No se pudo subir archivo.', 'error');
                this.value = '';
            }
        });
    });
    document.querySelectorAll('.mad-remove-file').forEach(btn => {
        btn.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            const box = this.closest('.mad-upload');
            if (box) resetUploadBox(box);
        });
    });
    cargarPermisoDesbloqueo();
    cargarUsuariosLegacy();
    setFechaGestionDefault();
})();
</script>
