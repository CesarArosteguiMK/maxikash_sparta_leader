<?php
/**
 * Tablero Asignación: columna ID Crédito + semana pasada, actual y próxima (mar–lun); cada semana = External ID, Nombre del gestor, Puesto.
 * Datos de semanas + portafolio automático: Models\AsignacionTablero.
 */
$asgMostrarRaw = isset($_GET['mostrar']) ? (string) $_GET['mostrar'] : '';
$asgLimite = \Models\AsignacionTablero::parseLimiteMostrar($asgMostrarRaw !== '' ? $asgMostrarRaw : null, '10');
$asgMostrarQuery = \Models\AsignacionTablero::limiteMostrarAQuery($asgLimite);

$tabAsg = \Models\AsignacionTablero::obtenerPortafolioAutomatico();
$asgSemanas = is_array($tabAsg['semanas'] ?? null) ? $tabAsg['semanas'] : [];
$asgSubcols = is_array($tabAsg['subcols'] ?? null) ? $tabAsg['subcols'] : [];
$asgFilasCompletas = is_array($tabAsg['filas'] ?? null) ? $tabAsg['filas'] : [];
$asgTotalFilas = count($asgFilasCompletas);
$asgPaginaRaw = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$asgPaginaTam = $asgLimite === null ? ($asgTotalFilas > 0 ? $asgTotalFilas : 1) : (int) $asgLimite;
$asgTotalPaginas = $asgPaginaTam > 0 ? max(1, (int) ceil($asgTotalFilas / $asgPaginaTam)) : 1;
$asgPaginaActual = min(max(1, $asgPaginaRaw), $asgTotalPaginas);
$asgOffset = ($asgPaginaActual - 1) * $asgPaginaTam;
$asgFilas = $asgLimite === null ? $asgFilasCompletas : array_slice($asgFilasCompletas, $asgOffset, $asgPaginaTam);
$asgDesde = $asgTotalFilas > 0 ? ($asgOffset + 1) : 0;
$asgHasta = $asgTotalFilas > 0 ? min($asgOffset + count($asgFilas), $asgTotalFilas) : 0;
$asgUrlPagina = static function (int $pagina) use ($asgMostrarQuery): string {
    $pagina = max(1, $pagina);
    return '/reporteria/asignacionTablero?mostrar=' . rawurlencode($asgMostrarQuery) . '&pagina=' . $pagina;
};
?>
<div class="comp-av container-fluid py-3 px-2 px-md-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 comp-av-page-header">
        <h4 class="mb-0 text-primary comp-av-heading d-flex align-items-center flex-wrap">
            <i class="fa-solid fa-user-check me-2" aria-hidden="true"></i>
            <span>Asignación — Tablero</span>
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
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 comp-av-table comp-av-table--asg" style="font-size:0.72rem;">
                            <colgroup>
                                <col class="asg-col-id">
                                <?php for ($asgCi = 0, $asgNcols = count($asgSemanas) * count($asgSubcols); $asgCi < $asgNcols; $asgCi++): ?>
                                <col class="asg-col-equal">
                                <?php endfor; ?>
                                <col class="asg-col-cambio">
                            </colgroup>
                            <thead class="comp-av-thead">
                                <tr class="asg-thead-chips">
                                    <th rowspan="3" scope="col" class="text-center align-middle small asg-th-id-col comp-sep-r">ID Crédito</th>
                                    <?php foreach ($asgSemanas as $si => $sem): ?>
                                        <?php
                                        $hl = (int) ($sem['hist_level'] ?? 0);
                                        if ($hl >= 1 && $hl <= 3) {
                                            $chipPill = 'comp-chip-pill-hist asg-chip-hist-' . $hl;
                                        } elseif ($sem['th_class'] === 'comp-th-act') {
                                            $chipPill = 'comp-chip-pill-act';
                                        } else {
                                            $chipPill = 'comp-chip-pill-fut';
                                        }
                                        ?>
                                    <th colspan="3" scope="colgroup" class="text-center asg-chip-th comp-sep-week asg-sep-week-end">
                                        <span class="badge rounded-pill comp-chip-pill comp-chip-pill--asg-multiline <?= htmlspecialchars($chipPill, ENT_QUOTES, 'UTF-8'); ?>" title="Ventana martes a lunes: <?= htmlspecialchars($sem['range'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($sem['chip_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <?php if ($si < count($asgSemanas) - 1): ?>
                                        <i class="fa-solid fa-chevron-right comp-chip-arrow" aria-hidden="true"></i>
                                        <?php endif; ?>
                                    </th>
                                    <?php endforeach; ?>
                                    <th rowspan="3" scope="col" class="text-center align-middle small asg-th-cambio-col">Cambio proyectado</th>
                                </tr>
                                <tr class="asg-thead-semana">
                                    <?php foreach ($asgSemanas as $sem): ?>
                                        <?php
                                        $hl = (int) ($sem['hist_level'] ?? 0);
                                        $histBg = ($hl >= 1 && $hl <= 3) ? ' asg-hist-bg-' . $hl : '';
                                        ?>
                                    <th colspan="3" class="text-center small comp-sep-week asg-sep-week-end<?= htmlspecialchars($histBg, ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($sem['th_class'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($sem['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </th>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <?php foreach ($asgSemanas as $sem): ?>
                                        <?php foreach ($asgSubcols as $ci => $sub): ?>
                                            <?php
                                            $thAct = $sem['th_class'] === 'comp-th-act';
                                            $hl = (int) ($sem['hist_level'] ?? 0);
                                            $histBg = ($hl >= 1 && $hl <= 3) ? ' asg-hist-bg-' . $hl : '';
                                            $colKind = $sub['key'] === 'ext' ? 'asg-col-ext' : ($sub['key'] === 'nom' ? 'asg-col-nom' : 'asg-sep-week-end');
                                            $thCls = 'small comp-subcell ' . $sub['align'] . $histBg . ' ' . $colKind . ($thAct ? ' bg-label-success' : '');
                                            $thSt = $thAct ? ' style="--bs-bg-opacity:.2"' : '';
                                            ?>
                                    <th class="<?= htmlspecialchars($thCls, ENT_QUOTES, 'UTF-8'); ?>"<?= $thSt; ?>><?= htmlspecialchars($sub['text'], ENT_QUOTES, 'UTF-8'); ?></th>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($asgFilas === []): ?>
                                <tr>
                                    <td class="small text-start comp-num-empty asg-td-id-col">—</td>
                                    <?php foreach ($asgSemanas as $sem): ?>
                                        <?php foreach ($asgSubcols as $ci => $sub): ?>
                                            <?php
                                            $hl = (int) ($sem['hist_level'] ?? 0);
                                            $histBg = ($hl >= 1 && $hl <= 3) ? ' asg-hist-bg-' . $hl : '';
                                            $colKind = $sub['key'] === 'ext' ? 'asg-col-ext' : ($sub['key'] === 'nom' ? 'asg-col-nom' : 'asg-sep-week-end');
                                            $cellBase = 'asg-cell-' . str_replace('comp-th-', '', $sem['th_class']);
                                            ?>
                                    <td class="small comp-num-empty <?= htmlspecialchars($sub['align'], ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($cellBase . $histBg . ' ' . $colKind, ENT_QUOTES, 'UTF-8'); ?>">—</td>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                    <td class="small text-start comp-num-empty asg-cambio-cell">—</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($asgFilas as $fila): ?>
                                        <?php
                                        $metaFila = is_array($fila['meta'] ?? null) ? $fila['meta'] : [];
                                        $hayCambioProxima = !empty($metaFila['hay_cambio_proxima']);
                                        $motivoCambio = trim((string) ($metaFila['motivo_cambio'] ?? ''));
                                        if ($motivoCambio === '') {
                                            $motivoCambio = $hayCambioProxima ? 'Cambio proyectado en próxima semana' : 'Sin cambios';
                                        }
                                        $esCambioInformativo = $hayCambioProxima || strcasecmp($motivoCambio, 'Sin cambios') !== 0;
                                        ?>
                                <tr>
                                    <td class="small text-start asg-td-id-col"><?= htmlspecialchars((string) ($fila['id_credito'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php foreach ($asgSemanas as $si => $sem): ?>
                                            <?php
                                            $cellSem = is_array($fila['cells'][$si] ?? null) ? $fila['cells'][$si] : [];
                                            ?>
                                            <?php foreach ($asgSubcols as $ci => $sub): ?>
                                                <?php
                                                $hl = (int) ($sem['hist_level'] ?? 0);
                                                $histBg = ($hl >= 1 && $hl <= 3) ? ' asg-hist-bg-' . $hl : '';
                                                $colKind = $sub['key'] === 'ext' ? 'asg-col-ext' : ($sub['key'] === 'nom' ? 'asg-col-nom' : 'asg-sep-week-end');
                                                $cellBase = 'asg-cell-' . str_replace('comp-th-', '', $sem['th_class']);
                                                $value = '—';
                                                if ($sub['key'] === 'ext') {
                                                    $value = trim((string) ($cellSem['ext'] ?? '')) ?: '—';
                                                } elseif ($sub['key'] === 'nom') {
                                                    $value = trim((string) ($cellSem['nom'] ?? '')) ?: '—';
                                                } elseif ($sub['key'] === 'pue') {
                                                    $value = trim((string) ($cellSem['pue'] ?? '')) ?: '—';
                                                }
                                                ?>
                                    <td class="small <?= htmlspecialchars($sub['align'], ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($cellBase . $histBg . ' ' . $colKind, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    <td class="small text-start asg-cambio-cell">
                                        <?php if ($esCambioInformativo): ?>
                                            <span class="badge text-bg-warning-subtle border border-warning-subtle text-warning-emphasis asg-badge-cambio">
                                                <i class="fa-solid fa-rotate me-1" aria-hidden="true"></i><?= htmlspecialchars($motivoCambio, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-secondary-emphasis">
                                                <i class="fa-regular fa-circle-check me-1" aria-hidden="true"></i><?= htmlspecialchars($motivoCambio, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body border-top py-2 py-md-3 bg-body asg-footer-actions">
            <div class="d-flex flex-column align-items-start gap-2 w-100">
                <a id="asg-btn-descargar-excel" href="/reporteria/descargarAsignacionTableroExcel" class="btn btn-outline-success btn-sm" title="Descarga el portafolio completo en Excel (puede tardar unos segundos).">
                    <i class="fa-solid fa-file-excel me-1" aria-hidden="true"></i>Descargar Excel (.xlsx)
                </a>
                <form method="get" action="/reporteria/asignacionTablero" class="d-flex flex-wrap align-items-center gap-2 mb-0 asg-form-mostrar">
                    <label for="asg-mostrar" class="form-label small text-secondary mb-0">Mostrar</label>
                    <select class="form-select form-select-sm asg-select-mostrar" id="asg-mostrar" name="mostrar" aria-label="Cantidad de filas a mostrar" onchange="this.form.submit()">
                        <option value="10"<?= $asgMostrarQuery === '10' ? ' selected' : ''; ?>>10</option>
                        <option value="50"<?= $asgMostrarQuery === '50' ? ' selected' : ''; ?>>50</option>
                        <option value="100"<?= $asgMostrarQuery === '100' ? ' selected' : ''; ?>>100</option>
                        <option value="todas"<?= $asgMostrarQuery === 'todas' ? ' selected' : ''; ?>>Todas</option>
                    </select>
                </form>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100 pt-1">
                    <small class="text-secondary">
                        Mostrando <?= (int) $asgDesde; ?>-<?= (int) $asgHasta; ?> de <?= (int) $asgTotalFilas; ?> · Página <?= (int) $asgPaginaActual; ?> / <?= (int) $asgTotalPaginas; ?>
                    </small>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Paginación del tablero de asignación">
                        <a class="btn btn-outline-secondary<?= $asgPaginaActual <= 1 ? ' disabled' : ''; ?>" href="<?= $asgPaginaActual <= 1 ? '#' : htmlspecialchars($asgUrlPagina(1), ENT_QUOTES, 'UTF-8'); ?>" aria-disabled="<?= $asgPaginaActual <= 1 ? 'true' : 'false'; ?>">«</a>
                        <a class="btn btn-outline-secondary<?= $asgPaginaActual <= 1 ? ' disabled' : ''; ?>" href="<?= $asgPaginaActual <= 1 ? '#' : htmlspecialchars($asgUrlPagina($asgPaginaActual - 1), ENT_QUOTES, 'UTF-8'); ?>" aria-disabled="<?= $asgPaginaActual <= 1 ? 'true' : 'false'; ?>">‹</a>
                        <span class="btn btn-outline-primary disabled"><?= (int) $asgPaginaActual; ?> / <?= (int) $asgTotalPaginas; ?></span>
                        <a class="btn btn-outline-secondary<?= $asgPaginaActual >= $asgTotalPaginas ? ' disabled' : ''; ?>" href="<?= $asgPaginaActual >= $asgTotalPaginas ? '#' : htmlspecialchars($asgUrlPagina($asgPaginaActual + 1), ENT_QUOTES, 'UTF-8'); ?>" aria-disabled="<?= $asgPaginaActual >= $asgTotalPaginas ? 'true' : 'false'; ?>">›</a>
                        <a class="btn btn-outline-secondary<?= $asgPaginaActual >= $asgTotalPaginas ? ' disabled' : ''; ?>" href="<?= $asgPaginaActual >= $asgTotalPaginas ? '#' : htmlspecialchars($asgUrlPagina($asgTotalPaginas), ENT_QUOTES, 'UTF-8'); ?>" aria-disabled="<?= $asgPaginaActual >= $asgTotalPaginas ? 'true' : 'false'; ?>">»</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Reutiliza tokens visuales del tablero Comparativas (comp-av-*) + Asignación 3×3 (sin columna Corte) */
.comp-av-page-header { min-width: 0; }
.comp-av-toolbar-dia { min-width: 0; }
.asg-footer-actions .asg-select-mostrar {
    min-width: 7.25rem;
    max-width: 11rem;
}
.comp-av-logo-toolbar {
    display: flex;
    align-items: flex-end;
    padding-bottom: 0.1rem;
}
.comp-av-toolbar .comp-av-logo {
    height: 3.35rem;
    width: auto;
    max-width: min(320px, 62vw);
    object-fit: contain;
    display: block;
}
.comp-av-heading { line-height: 1.25; letter-spacing: -0.02em; }
.comp-av-export-root { overflow: hidden; }
.comp-av .comp-chip-pill-fut {
    border-color: var(--bs-info) !important;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bs-info) 35%, var(--bs-white));
    color: var(--bs-info) !important;
}
.comp-av .comp-chip-pill-fut strong { color: var(--bs-info-text-emphasis); }

.comp-av-table {
    --bs-table-border-color: var(--bs-gray-200);
    --comp-sep-color: var(--bs-gray-300);
    border-collapse: collapse;
    border: 1px solid var(--bs-gray-400) !important;
}
.comp-av-table.comp-av-table--asg {
    table-layout: fixed;
    width: 100%;
}
.comp-av-table--asg > colgroup > col.asg-col-id {
    width: 7%;
}
.comp-av-table--asg > colgroup > col.asg-col-equal {
    width: 8.555%;
}
.comp-av-table--asg > colgroup > col.asg-col-cambio {
    width: 16%;
}
.comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-id-col,
.comp-av-table--asg > tbody > tr > td.asg-td-id-col {
    position: sticky;
    left: 0;
    z-index: 5;
    background-color: var(--bs-body-bg);
    box-shadow: 1px 0 0 var(--bs-table-border-color);
    background-clip: padding-box;
}
.comp-av-table--asg > tbody > tr > td.asg-td-id-col {
    z-index: 2;
}
.comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-id-col {
    font-weight: 700;
    font-size: 0.62rem;
    letter-spacing: 0.02em;
    color: var(--bs-primary);
    border-right: 1px solid var(--bs-gray-300) !important;
}
.comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-cambio-col {
    font-weight: 700;
    font-size: 0.62rem;
    letter-spacing: 0.02em;
    color: var(--bs-warning-text-emphasis);
    min-width: 12rem;
}
.comp-av-table--asg .asg-cambio-cell {
    min-width: 12rem;
}
.comp-av-table--asg .asg-badge-cambio {
    white-space: normal;
    text-align: left;
}
.comp-av-table.table-bordered > :not(caption) > * > * {
    border-style: solid !important;
    border-width: 1px !important;
    border-color: var(--bs-table-border-color) !important;
}
.comp-av-table > thead.comp-av-thead > tr > th,
.comp-av-table > tbody > tr > td {
    border-style: solid !important;
    border-width: 1px !important;
    border-color: var(--bs-table-border-color) !important;
}
.comp-av-table > :not(caption) > * > * {
    padding: 0.35rem 0.45rem !important;
    vertical-align: middle;
    line-height: 1.25;
}
.comp-av-table > tbody > tr > td {
    min-height: 2.1rem;
}
.comp-av-table > thead.comp-av-thead > tr:first-child > th {
    border-bottom-width: 1px !important;
}
.comp-av-table-stack {
    border: 1px solid var(--bs-gray-400);
    border-radius: 0.65rem;
    overflow: hidden;
    margin: 0.5rem 1rem 1rem;
}
.comp-av-table-stack #asg-table-area .table-responsive {
    border: 0 !important;
    border-radius: 0 !important;
    overflow-x: auto;
}
.comp-av-table--asg thead tr.asg-thead-chips > th.asg-chip-th {
    background-color: color-mix(in srgb, var(--bs-secondary-bg) 70%, var(--bs-white));
    font-weight: 400;
    vertical-align: middle;
    padding: 0.42rem 0.35rem !important;
    position: relative;
}
.comp-av-table--asg thead tr.asg-thead-chips > th.asg-chip-th .comp-chip-arrow {
    position: absolute;
    right: -0.08rem;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    font-size: 0.65rem;
    color: var(--bs-primary);
    opacity: 0.55;
    line-height: 1;
    pointer-events: none;
}
.comp-av-table--asg thead tr.asg-thead-chips .comp-chip-pill.comp-chip-pill--asg-multiline {
    white-space: normal;
    max-width: 100%;
    overflow: visible;
    text-overflow: clip;
    line-height: 1.28;
    padding: 0.38rem 0.42rem;
    border-radius: 0.65rem;
    text-align: center;
    display: inline-block;
}
.comp-av-table-stack .comp-av-table > thead.comp-av-thead > tr:first-child > th {
    border-top-width: 0 !important;
}
.comp-av .comp-chip-pill {
    padding: 0.35rem 0.55rem;
    font-size: 0.72rem;
    font-weight: 500;
    border: 1px solid var(--bs-primary-border-subtle);
    color: var(--bs-primary);
    background-color: var(--bs-body-bg);
}
.comp-av .comp-chip-pill strong { font-weight: 700; }
.comp-av .comp-chip-pill-hist strong { color: var(--bs-primary); }
.comp-av .comp-chip-pill-act {
    border-color: var(--bs-success) !important;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bs-success) 35%, var(--bs-white));
}
.comp-av .comp-chip-pill-act strong { color: var(--bs-success); }
.comp-av-table > thead.comp-av-thead > tr > th {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: none;
    white-space: normal;
    border-style: solid !important;
    border-width: 1px !important;
    border-color: var(--bs-gray-200) !important;
}
.comp-av-table--asg > thead.comp-av-thead > tr:nth-child(3) > th {
    text-transform: none;
    font-size: 0.6rem;
    border-top-color: var(--bs-gray-200) !important;
}
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th {
    font-size: 0.62rem;
    font-weight: 700;
}
.comp-av-table tbody td {
    background-clip: padding-box;
}
.comp-av-table > thead.comp-av-thead > tr:first-child > th {
    border-bottom-color: var(--bs-gray-200) !important;
}
/* Bordes: dentro de la semana líneas suaves; cierre de semana mismo grosor/color que el resto de la tabla */
.comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-col-ext {
    border-left: 1px solid color-mix(in srgb, var(--bs-gray-400) 28%, var(--bs-white)) !important;
    border-right: 1px solid color-mix(in srgb, var(--bs-gray-400) 22%, var(--bs-white)) !important;
}
.comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-col-nom {
    border-left: 1px solid color-mix(in srgb, var(--bs-gray-400) 22%, var(--bs-white)) !important;
    border-right: 1px solid color-mix(in srgb, var(--bs-gray-400) 18%, var(--bs-white)) !important;
}
.comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-sep-week-end {
    border-left: 1px solid color-mix(in srgb, var(--bs-gray-400) 18%, var(--bs-white)) !important;
    border-right: 1px solid var(--bs-table-border-color) !important;
}
.comp-av-table--asg > tbody > tr > td.asg-col-ext {
    border-right: 1px solid color-mix(in srgb, var(--bs-gray-400) 25%, var(--bs-white)) !important;
}
.comp-av-table--asg > tbody > tr > td.asg-col-nom {
    border-right: 1px solid color-mix(in srgb, var(--bs-gray-400) 18%, var(--bs-white)) !important;
}
.comp-av-table--asg > tbody > tr > td.asg-sep-week-end {
    border-right: 1px solid var(--bs-table-border-color) !important;
}
/* Chips y fila «Semana»: sin borde vertical entre bloques (no el borde exterior de la última semana) */
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-chips > th.asg-sep-week-end:not(:last-of-type),
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th.asg-sep-week-end:not(:last-of-type) {
    border-right-width: 0 !important;
    border-right-style: none !important;
}
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-chips > th.comp-sep-week + th.comp-sep-week,
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th.comp-sep-week + th.comp-sep-week {
    border-left-width: 0 !important;
    border-left-style: none !important;
}
/* Semanas pasadas: solo el fondo sube contraste con la antigüedad; el texto sigue color normal */
.comp-av-table--asg thead .asg-hist-bg-1,
.comp-av-table--asg tbody td.asg-hist-bg-1 {
    background-color: color-mix(in srgb, var(--bs-gray-300) 38%, var(--bs-white)) !important;
    color: var(--bs-emphasis-color) !important;
}
.comp-av-table--asg thead .asg-hist-bg-2,
.comp-av-table--asg tbody td.asg-hist-bg-2 {
    background-color: color-mix(in srgb, var(--bs-gray-400) 34%, var(--bs-white)) !important;
    color: var(--bs-emphasis-color) !important;
}
.comp-av-table--asg thead .asg-hist-bg-3,
.comp-av-table--asg tbody td.asg-hist-bg-3 {
    background-color: color-mix(in srgb, var(--bs-gray-500) 28%, var(--bs-gray-100)) !important;
    color: var(--bs-emphasis-color) !important;
}
.comp-av .asg-chip-hist-1 { background-color: color-mix(in srgb, var(--bs-gray-300) 40%, var(--bs-body-bg)) !important; color: var(--bs-emphasis-color) !important; }
.comp-av .asg-chip-hist-2 { background-color: color-mix(in srgb, var(--bs-gray-400) 32%, var(--bs-body-bg)) !important; color: var(--bs-emphasis-color) !important; }
.comp-av .asg-chip-hist-3 { background-color: color-mix(in srgb, var(--bs-gray-500) 26%, var(--bs-body-bg)) !important; color: var(--bs-emphasis-color) !important; }

.comp-av .comp-th-act {
    background-color: color-mix(in srgb, var(--bs-success) 16%, var(--bs-white)) !important;
    color: var(--bs-success-text-emphasis) !important;
}
.comp-av .comp-th-fut {
    background-color: color-mix(in srgb, var(--bs-info) 18%, var(--bs-white)) !important;
    color: var(--bs-info-text-emphasis) !important;
}
.comp-av-table tbody td.asg-cell-hist:not([class*="asg-hist-bg"]) {
    background-color: color-mix(in srgb, var(--bs-secondary-bg) 28%, var(--bs-white));
}
.comp-av-table tbody td.asg-cell-act {
    background-color: color-mix(in srgb, var(--bs-success) 8%, var(--bs-white));
}
.comp-av-table tbody td.asg-cell-fut {
    background-color: color-mix(in srgb, var(--bs-info) 10%, var(--bs-white));
}
.comp-num-empty { color: var(--bs-secondary-color) !important; }
.comp-av-table--asg tbody td[class*="asg-hist-bg-"].comp-num-empty {
    color: var(--bs-emphasis-color) !important;
}

body.dark-mode .comp-av-card {
    background-color: var(--bs-secondary-bg);
    border-color: var(--bs-border-color) !important;
    color: var(--bs-body-color);
}
body.dark-mode .comp-av-toolbar {
    background-color: var(--bs-body-bg);
    border-color: var(--bs-border-color) !important;
}
body.dark-mode .comp-av-page-header .comp-av-heading { color: var(--bs-heading-color) !important; }
body.dark-mode .comp-av-page-header .text-primary { color: var(--bs-link-color) !important; }
body.dark-mode .comp-av-toolbar .comp-av-logo { filter: brightness(1.08); }
body.dark-mode .comp-av-toolbar .form-label { color: var(--bs-secondary-color) !important; }
body.dark-mode .comp-av-toolbar .form-select {
    background-color: var(--bs-tertiary-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
}
body.dark-mode .comp-av-table--asg thead tr.asg-thead-chips > th.asg-chip-th {
    background-color: var(--bs-body-bg) !important;
}
body.dark-mode .comp-av-table-stack {
    border-color: var(--bs-border-color);
    margin: 0.5rem 1rem 1rem;
}
body.dark-mode .comp-av-table-stack #asg-table-area .table-responsive { border: 0 !important; }
body.dark-mode .comp-av .comp-chip-pill {
    background-color: var(--bs-secondary-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
}
body.dark-mode .comp-av .comp-chip-pill-hist strong { color: var(--bs-body-color); }
body.dark-mode .comp-av .comp-chip-pill-act {
    border-color: var(--bs-success) !important;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bs-success) 35%, var(--bs-secondary-bg));
}
body.dark-mode .comp-av .comp-chip-pill-act strong { color: var(--bs-success); }
body.dark-mode .comp-av .comp-chip-pill-fut {
    border-color: var(--bs-info) !important;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bs-info) 35%, var(--bs-secondary-bg));
}
body.dark-mode .comp-av .comp-chip-pill-fut strong { color: var(--bs-info); }
body.dark-mode .comp-av-table {
    --bs-table-border-color: var(--bs-gray-500);
    --comp-sep-color: color-mix(in srgb, var(--bs-gray-300) 68%, var(--bs-white));
    color: var(--bs-body-color);
    --bs-table-bg: transparent;
    border-collapse: collapse;
    border-color: var(--bs-border-color) !important;
}
body.dark-mode .comp-av-table .comp-sep-r {
    border-right-color: var(--comp-sep-color) !important;
}
body.dark-mode .comp-av-table > thead.comp-av-thead > tr > th {
    border-color: var(--bs-gray-500) !important;
}
body.dark-mode .comp-av-table > thead.comp-av-thead .comp-subcell {
    border-left-color: var(--comp-sep-color) !important;
    border-right-color: var(--comp-sep-color) !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-col-ext {
    border-left-color: color-mix(in srgb, var(--bs-gray-500) 55%, var(--bs-border-color)) !important;
    border-right-color: color-mix(in srgb, var(--bs-gray-500) 42%, var(--bs-border-color)) !important;
    border-left-width: 1px !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-col-nom {
    border-left-color: color-mix(in srgb, var(--bs-gray-500) 42%, var(--bs-border-color)) !important;
    border-right-color: color-mix(in srgb, var(--bs-gray-500) 35%, var(--bs-border-color)) !important;
    border-left-width: 1px !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-sep-week-end {
    border-left-color: color-mix(in srgb, var(--bs-gray-500) 35%, var(--bs-border-color)) !important;
    border-right-color: var(--bs-border-color) !important;
    border-left-width: 1px !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-chips > th.asg-sep-week-end:not(:last-of-type),
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th.asg-sep-week-end:not(:last-of-type) {
    border-right-width: 0 !important;
    border-right-style: none !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-chips > th.comp-sep-week + th.comp-sep-week,
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th.comp-sep-week + th.comp-sep-week {
    border-left-width: 0 !important;
    border-left-style: none !important;
}
body.dark-mode .comp-av-table--asg > tbody > tr > td.asg-col-ext {
    border-right-color: color-mix(in srgb, var(--bs-gray-500) 40%, var(--bs-border-color)) !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > tbody > tr > td.asg-col-nom {
    border-right-color: color-mix(in srgb, var(--bs-gray-500) 32%, var(--bs-border-color)) !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > tbody > tr > td.asg-sep-week-end {
    border-right-color: var(--bs-border-color) !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-id-col,
body.dark-mode .comp-av-table--asg > tbody > tr > td.asg-td-id-col {
    background-color: var(--bs-body-bg);
    box-shadow: 1px 0 0 var(--bs-border-color);
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-id-col {
    border-right-color: var(--bs-gray-500) !important;
}
body.dark-mode .comp-av .comp-th-act {
    background-color: var(--bs-secondary-bg) !important;
    color: var(--bs-success) !important;
}
body.dark-mode .comp-av-table--asg thead .asg-hist-bg-1,
body.dark-mode .comp-av-table--asg tbody td.asg-hist-bg-1 {
    background-color: color-mix(in srgb, var(--bs-gray-600) 22%, var(--bs-secondary-bg)) !important;
    color: var(--bs-emphasis-color) !important;
}
body.dark-mode .comp-av-table--asg thead .asg-hist-bg-2,
body.dark-mode .comp-av-table--asg tbody td.asg-hist-bg-2 {
    background-color: color-mix(in srgb, var(--bs-gray-600) 32%, var(--bs-secondary-bg)) !important;
    color: var(--bs-emphasis-color) !important;
}
body.dark-mode .comp-av-table--asg thead .asg-hist-bg-3,
body.dark-mode .comp-av-table--asg tbody td.asg-hist-bg-3 {
    background-color: color-mix(in srgb, var(--bs-gray-700) 28%, var(--bs-body-bg)) !important;
    color: var(--bs-emphasis-color) !important;
}
body.dark-mode .comp-av .comp-th-fut {
    background-color: color-mix(in srgb, var(--bs-info) 22%, var(--bs-secondary-bg)) !important;
    color: var(--bs-info) !important;
}
body.dark-mode .comp-av-table:not(.comp-av-table--asg) tbody tr:nth-child(odd) td { background-color: var(--bs-secondary-bg); }
body.dark-mode .comp-av-table:not(.comp-av-table--asg) tbody tr:nth-child(even) td { background-color: var(--bs-body-bg); }
body.dark-mode .comp-av-table tbody td.asg-cell-hist:not([class*="asg-hist-bg"]) {
    background-color: color-mix(in srgb, var(--bs-secondary-bg) 88%, var(--bs-body-bg));
}
body.dark-mode .comp-av-table tbody td.asg-cell-act {
    background-color: color-mix(in srgb, var(--bs-success) 12%, var(--bs-secondary-bg));
}
body.dark-mode .comp-av-table tbody td.asg-cell-fut {
    background-color: color-mix(in srgb, var(--bs-info) 14%, var(--bs-secondary-bg));
}
body.dark-mode .comp-av .asg-chip-hist-1 { background-color: color-mix(in srgb, var(--bs-gray-600) 24%, var(--bs-secondary-bg)) !important; color: var(--bs-emphasis-color) !important; }
body.dark-mode .comp-av .asg-chip-hist-2 { background-color: color-mix(in srgb, var(--bs-gray-600) 34%, var(--bs-secondary-bg)) !important; color: var(--bs-emphasis-color) !important; }
body.dark-mode .comp-av .asg-chip-hist-3 { background-color: color-mix(in srgb, var(--bs-gray-700) 30%, var(--bs-body-bg)) !important; color: var(--bs-emphasis-color) !important; }
body.dark-mode .comp-av .comp-num-empty { color: var(--bs-secondary-color) !important; }
body.dark-mode .comp-av-table--asg tbody td[class*="asg-hist-bg-"].comp-num-empty {
    color: var(--bs-emphasis-color) !important;
}
</style>
<script>
(function () {
    var btn = document.getElementById('asg-btn-descargar-excel');
    if (!btn) return;
    var url = btn.getAttribute('href');
    if (!url) return;
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (typeof Swal === 'undefined') {
            window.location.href = url;
            return;
        }
        Swal.fire({
            title: 'Descargando Excel',
            html: '<p class="text-body-secondary mb-3 mb-md-4">Por favor espere mientras se genera el archivo con <strong>todo</strong> el portafolio…</p>' +
                '<div class="spinner-border text-success" style="width:3rem;height:3rem;" role="status" aria-hidden="true"></div>' +
                '<span class="visually-hidden">Cargando</span>',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: { popup: 'shadow' }
        });
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('HTTP ' + r.status);
                }
                var cd = r.headers.get('Content-Disposition') || '';
                var name = 'Asignacion_Tablero.xlsx';
                var m = cd.match(/filename="([^"]+)"/i) || cd.match(/filename=([^;\s]+)/i);
                if (m && m[1]) {
                    name = m[1].replace(/^["']|["']$/g, '');
                }
                return r.blob().then(function (blob) {
                    return { blob: blob, name: name };
                });
            })
            .then(function (x) {
                Swal.close();
                var u = URL.createObjectURL(x.blob);
                var a = document.createElement('a');
                a.href = u;
                a.download = x.name;
                a.rel = 'noopener';
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(function () { URL.revokeObjectURL(u); }, 120000);
            })
            .catch(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo descargar',
                    text: 'Intente de nuevo o contacte a sistemas si el problema continúa.'
                });
            });
    });
})();
</script>
