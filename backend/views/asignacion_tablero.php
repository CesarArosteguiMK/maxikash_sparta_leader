<?php
/**
 * Tablero Asignación (Proyección / dos ventanas): HTML; rutas, CSS y JS desde Reporteria.
 * JSON: /reporteria/getAsignacionTableroJson?mostrar=todas (+ &dos_ventanas=1 en dos ventanas).
 */
$tituloTablero = (string) ($asg_titulo_tablero ?? 'Asignación — Tablero');
$excelPath = (string) ($asg_excel_path ?? '/reporteria/descargarAsignacionTableroExcel');
?>
<div class="comp-av container-fluid py-3 px-2 px-md-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 comp-av-page-header">
        <h4 class="mb-0 text-primary comp-av-heading d-flex align-items-center flex-wrap">
            <i class="fa-solid fa-user-check me-2" aria-hidden="true"></i>
            <span><?= htmlspecialchars($tituloTablero, ENT_QUOTES, 'UTF-8'); ?></span>
        </h4>
        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="/reporteria/asignacion" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="card shadow-sm border comp-av-card overflow-hidden">
        <div class="comp-av-export-root bg-body">
            <div class="card-body border-bottom py-3 comp-av-toolbar">
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
                    <div class="comp-av-logo-toolbar">
                        <img src="/assets/img/Logotipo-Maxikash-Outline.webp" alt="Maxikash" class="comp-av-logo" width="260" height="65" loading="lazy" decoding="async">
                    </div>
                </div>
            </div>

            <div class="comp-av-table-stack">
                <div id="asg-table-area">
                    <div id="asg-loading" class="text-center py-5 px-3">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando…</span></div>
                        <p class="small text-muted mt-2 mb-0">Cargando portafolio…</p>
                    </div>
                    <div id="asg-error" class="alert alert-danger mx-3 my-2 d-none" role="alert"></div>
                    <div class="table-responsive d-none" id="asg-table-scroll">
                        <table id="asg-table" class="table table-sm table-bordered mb-0 comp-av-table comp-av-table--asg" style="font-size:0.72rem;">
                            <colgroup id="asg-colgroup"></colgroup>
                            <thead class="comp-av-thead" id="asg-thead"></thead>
                            <tbody id="asg-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body border-top py-2 py-md-3 bg-body asg-footer-actions">
            <div class="d-flex flex-column align-items-start gap-2 w-100">
                <a id="asg-btn-descargar-excel" href="<?= htmlspecialchars($excelPath, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-success btn-sm" title="Descarga el portafolio completo en Excel (puede tardar unos segundos).">
                    <i class="fa-solid fa-file-excel me-1" aria-hidden="true"></i>Descargar Excel (.xlsx)
                </a>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-0 asg-form-mostrar">
                    <label for="asg-mostrar" class="form-label small text-secondary mb-0">Mostrar</label>
                    <select class="form-select form-select-sm asg-select-mostrar" id="asg-mostrar" aria-label="Cantidad de filas a mostrar por página">
                        <option value="10" selected>10</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="todas">Todas</option>
                    </select>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-end gap-3 w-100 pt-1 asg-footer-pag-wrap">
                    <nav class="asg-pag-nav" id="asg-pag-nav" aria-label="Paginación del tablero de asignación"></nav>
                </div>
            </div>
        </div>
    </div>
</div>
