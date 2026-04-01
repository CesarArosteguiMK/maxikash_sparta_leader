<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <i class="fa fa-file-invoice-dollar text-primary me-2"></i>
                                <?= htmlspecialchars(isset($tituloShell) ? $tituloShell : 'Shell Gastos Cobranza', ENT_QUOTES, 'UTF-8') ?>
                            </h4>
                            <p class="text-muted mb-0">
                                Agente para <code>reporte_cobranza.py</code> (y modo prueba sin .py). Log en panel inferior.
                                <span class="badge bg-label-success ms-1">INI habilitado · puerto 3120</span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted d-block">
                                <i class="fa fa-shield-alt me-1"></i>Módulo 31
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-plug text-primary me-2"></i>Estado del agente</h5>
                    <p class="small text-muted mb-3">
                        URL: <code id="gastosCobranzaUrlDisplay"><?= htmlspecialchars(isset($gastosCobranzaAgenteUrl) ? $gastosCobranzaAgenteUrl : '', ENT_QUOTES, 'UTF-8') ?></code>
                        · <span id="gastosCobranzaHabilitadoIni"><?= !empty($gastosCobranzaAgenteHabilitado) ? '<span class="text-success">enabled=1</span>' : '<span class="text-warning">enabled=0</span>' ?></span>
                    </p>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span id="gastosCobranzaEstadoBadge" class="badge bg-label-secondary">Comprobando…</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnGastosCobranzaRefrescar">
                            <i class="fa fa-sync-alt me-1"></i>Actualizar
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="btnGastosCobranzaEjecutar" disabled>
                            <i class="fa fa-play me-1"></i>Ejecutar (agente)
                        </button>
                    </div>
                    <p class="small mb-2" id="gastosCobranzaDetalle">—</p>
                    <p class="small text-muted mb-0" id="gastosCobranzaAyudaEjecutar">
                        <strong>Ejecutar (agente)</strong> solo genera el Excel en la carpeta <code>reporte/</code> (Python real si hay script; si no, modo prueba). El <strong>Worker</strong> del lote S2 + lista negra se lanza desde la tabla de reportes (botón junto a descargar) o subiendo otro Excel en la sección EC Worker.
                    </p>
                    <div id="gastosCobranzaSalidaWrap" class="d-none mt-3">
                        <label class="form-label small fw-semibold">Salida de la última ejecución</label>
                        <pre id="gastosCobranzaSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:220px;overflow:auto;white-space:pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-map-signs text-primary me-2"></i>Guía rápida</h5>
                    <p class="small text-muted mb-3 mb-lg-4">
                        Primero confirma que el agente esté <strong>en línea</strong> (badge verde). Si aparece desconectado, avisa a quien administre el equipo donde corre el servicio.
                    </p>
                    <ul class="list-unstyled small mb-0">
                        <li class="d-flex gap-2 mb-3">
                            <span class="text-success flex-shrink-0"><i class="fa fa-play-circle fa-lg"></i></span>
                            <span><strong class="text-body">Ejecutar (agente)</strong> solo genera el Excel de cobranza (ayer CDMX) y lo deja en la tabla. En esa fila, el botón <strong>Worker</strong> lanza el EC Worker con ese archivo (sin subirlo otra vez) y, al terminar, la lista negra con el mismo Excel.</span>
                        </li>
                        <li class="d-flex gap-2 mb-3">
                            <span class="text-primary flex-shrink-0"><i class="fa fa-rocket fa-lg"></i></span>
                            <span><strong class="text-body">EC Worker / Excel enriquecido</strong> sube un Excel con IDs y corre el flujo S2 o el enriquecido. Con <strong>Worker</strong>, al <strong>terminar la corrida</strong> se actualiza la lista negra sola (también si hubo errores en algunos créditos; usa el panel opcional si quieres cambiar parámetros de la carga).</span>
                        </li>
                        <li class="d-flex gap-2 mb-3">
                            <span class="text-secondary flex-shrink-0"><i class="fa fa-database fa-lg"></i></span>
                            <span><strong class="text-body">Lista negra</strong>: el Worker la aplica en automático al cerrar el lote. El panel <em>Carga manual</em> está oculto por defecto; ábrelo solo para cargar sin Worker o para ver parámetros.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <span class="text-info flex-shrink-0"><i class="fa fa-scroll fa-lg"></i></span>
                            <span><strong class="text-body">Log</strong> muestra lo que hace el agente en segundo plano; puedes dejarlo en automático o refrescar cuando lo necesites.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 border-primary h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-rocket text-primary me-2"></i>EC Worker / Excel enriquecido</h5>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Excel (.xlsx)</label>
                            <input type="file" class="form-control form-control-sm" id="ecLauncherFile" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Fecha corte S2</label>
                            <input type="date" class="form-control form-control-sm" id="ecLauncherFecha">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Columna ID en Excel</label>
                            <input type="text" class="form-control form-control-sm" id="ecLauncherCol" value="ID CREDITO" placeholder="ID CREDITO">
                        </div>
                        <div class="col-12 col-md-auto flex-grow-0">
                            <label class="form-label small mb-1 text-nowrap" title="Saltar primeros créditos (N)">Omitir N</label>
                            <input type="number" class="form-control form-control-sm" id="ecLauncherOmitir" value="0" min="0" title="Saltar primeros créditos: ignorar los primeros N IDs del Excel" style="max-width: 4.25rem">
                        </div>
                        <div class="col-12">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="ecLauncherEnrich" autocomplete="off">
                                <label class="form-check-label small" for="ecLauncherEnrich">Excel enriquecido (+ Chat)</label>
                            </div>
                            <p class="small text-muted mb-0 mt-1">Sin marcar: <strong>Worker</strong> (S2 + BD + Chat; al terminar la corrida, lista negra automática). Marcada: <strong>enriquecido completo</strong> (mismo criterio de BD y auditoría que el flujo enrich del servidor).</p>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary btn-sm" id="btnEcLauncherEjecutar" disabled>
                                <i class="fa fa-cloud-upload-alt me-1"></i>Subir Excel y ejecutar vía agente
                            </button>
                            <span class="small text-muted ms-2">Puede tardar mucho. Con el Worker, al terminar la corrida la lista negra se actualiza sola con el mismo Excel.</span>
                            <p class="small text-muted mb-0 mt-2">
                                Mientras corre Worker o Enrich, el avance (líneas <code>[n/total]</code> y hitos 25/50/75/100%) aparece en <strong>Log del agente</strong> abajo; con auto activo se refresca más seguido durante ese proceso.
                            </p>
                        </div>
                    </div>
                    <div id="ecErroresReintentoBanner" class="alert alert-warning d-none mt-3 mb-0 py-2 small" role="alert">
                        Tras la segunda pasada automática aún hubo créditos con error.
                        <a id="ecErroresReintentoLink" class="alert-link fw-semibold" href="#">Descargar CSV (id, tipo de error y detalle)</a>
                        para revisión manual.
                    </div>
                    <div id="ecLauncherSalidaWrap" class="d-none mt-3">
                        <label class="form-label small fw-semibold">Salida EC worker / enrich</label>
                        <pre id="ecLauncherSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:260px;overflow:auto;white-space:pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid #3abaf4 !important;">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-file-export text-info me-2"></i>Descargo cobranza GC (estatus 3)</h5>
                    <div class="small text-muted mb-3">
                        <div class="mb-2">
                            Lee <code>cobranza_gc_verificacion_semana</code> con <code>estatus = 3</code>.
                            <strong>Descargar</strong> ejecuta el script y, si hay filas nuevas, baja el Excel directo.
                        </div>
                        <div class="p-2 rounded bg-label-secondary">
                            <strong>guia_descargo.json</strong> es el checkpoint del último avance real
                            (<code>registrado_en_cdmx</code> + <code>id</code>); sirve para que el siguiente descargo incremental solo traiga filas posteriores.
                        </div>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="chkDescargoDesdeCero" autocomplete="off">
                        <label class="form-check-label small" for="chkDescargoDesdeCero">Desde cero (ignora la guía, vuelca todo el filtro y al final actualiza la guía)</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="chkDescargoSinActualizarGuia" autocomplete="off">
                        <label class="form-check-label small" for="chkDescargoSinActualizarGuia">Solo prueba: generar Excel pero <strong>no</strong> escribir <code>guia_descargo.json</code> (déjalo desmarcado para corrida real)</label>
                    </div>
                    <div class="d-grid d-sm-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-primary" id="btnDescargoEstatus3" disabled>
                            <i class="fa fa-download me-1"></i>Descargar
                        </button>
                        <span id="descargoEstatus3Spinner" class="text-primary d-none" role="status" aria-live="polite" aria-label="Descargando">
                            <i class="fa fa-spinner fa-spin fa-lg" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 border-secondary">
                <div class="card-body py-3">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="chkMostrarCargaVerifManual" autocomplete="off">
                        <label class="form-check-label small" for="chkMostrarCargaVerifManual">
                            <strong>Carga manual a lista negra</strong> (opcional). El <strong>Worker</strong> ya aplica la lista negra al terminar el lote; abre esto solo para ajustar parámetros o para subir un Excel <em>sin</em> pasar por el Worker.
                        </label>
                    </div>
                </div>
                <div id="wrapCargaVerifManual" class="d-none border-top">
                    <div class="card-body pt-3">
                        <h6 class="mb-2"><i class="fa fa-database text-secondary me-2"></i>Carga verificación semana (Excel → BD)</h6>
                        <p class="small text-muted mb-3">
                            Tabla <code>cobranza_gc_verificacion_semana</code>. <strong>SALDO APLICABLE A GC</strong> → <code>monto_aplicar</code>.
                            <strong>Inicio de semana</strong> se calcula solo en el agente: martes que abre la semana operativa según <em>hoy</em> (hora Ciudad de México), igual que en la pantalla de estado de cuenta.
                            Si el mismo <code>id_credito</code> ya tiene fila en esa semana con <strong>estatus 3</strong>, no se duplica: pasa a <strong>estatus 2</strong> y se actualiza <code>celula</code> si viene COMENTARIOS en el Excel. Los <strong>nuevos</strong> se insertan con el estatus que elijas abajo (por defecto 2).                             En esta carga, <code>tipo_reporte</code> debe quedar <strong>NULL</strong> en MySQL.
                            Si en la tabla la columna sigue <code>NOT NULL</code>, ejecute en <strong>__SPARTA_SECRET_REDACTED__</strong> el script
                            <code>backend/services/gastos-cobranza-agent/scripts/sql_alter_tipo_reporte_nullable.sql</code>
                            (si no, el motor puede terminar guardando <code>falta_aplicar</code> por defecto).
                            Misma lógica cuando el Worker dispara la carga automática.
                        </p>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small mb-1">Excel (.xlsx)</label>
                                <input type="file" class="form-control form-control-sm" id="cargaVerifFile" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Estatus (filas nuevas)</label>
                                <select class="form-select form-select-sm" id="cargaVerifEstatus" title="Solo aplica a INSERT; las que ya estaban en 3 pasan a 2">
                                    <option value="2" selected>2</option>
                                    <option value="1">1</option>
                                    <option value="0">0</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small mb-1">Mensaje lote (opcional)</label>
                                <input type="text" class="form-control form-control-sm" id="cargaVerifMensaje" placeholder="Vacío = mensaje automático en el script" maxlength="500">
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="cargaVerifDryRun">
                                    <label class="form-check-label small" for="cargaVerifDryRun">Solo simular (dry-run, no inserta en BD)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-secondary btn-sm" id="btnCargaVerifEjecutar" disabled>
                                    <i class="fa fa-upload me-1"></i>Subir Excel y cargar vía agente
                                </button>
                                <span class="small text-muted ms-2">Python: <code>pandas</code>, <code>mysql-connector-python</code> en el agente.</span>
                            </div>
                        </div>
                        <div id="cargaVerifSalidaWrap" class="d-none mt-3">
                            <label class="form-label small fw-semibold">Salida carga verificación</label>
                            <pre id="cargaVerifSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:260px;overflow:auto;white-space:pre-wrap;"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="card-title mb-0"><i class="fa fa-table text-success me-2"></i>Reportes en carpeta <code>reporte/</code></h5>
                        <button type="button" class="btn btn-sm btn-outline-success" id="btnGastosCobranzaListarReportes">
                            <i class="fa fa-sync-alt me-1"></i>Actualizar lista
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Archivo</th>
                                    <th class="text-end">Tamaño</th>
                                    <th>Modificado (UTC)</th>
                                    <th title="Pasa el cursor sobre la celda para ver el texto completo">Estado</th>
                                    <th class="text-center" width="200">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="gastosCobranzaTablaReportes">
                                <tr><td colspan="6" class="text-muted small">Cargando…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h5 class="card-title mb-0"><i class="fa fa-scroll text-secondary me-2"></i>Log del agente (casi en tiempo real)</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="chkGastosCobranzaLogAuto" checked>
                            <label class="form-check-label small" for="chkGastosCobranzaLogAuto">Auto cada 4 s</label>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnGastosCobranzaLogAhora">
                            <i class="fa fa-download me-1"></i>Traer log ahora
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" id="btnGastosCobranzaLogVaciar" title="Borra el historial del archivo de log en el agente (solo la bitácora en disco)">
                            <i class="fa fa-eraser me-1"></i>Vaciar log
                        </button>
                    </div>
                    <pre id="gastosCobranzaLogPanel" class="bg-dark text-light border-0 rounded p-3 small mb-0 font-monospace" style="max-height:320px;overflow:auto;white-space:pre-wrap;">—</pre>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-light border py-3 small mb-0" role="region" aria-label="Explicación de la pantalla Gastos cobranza">
                <p class="mb-2"><strong>¿Qué significa todo esto si no eres de sistemas?</strong>
                    No es un aviso de error. Es una explicación breve de cómo esta pantalla trabaja con el <em>agente</em>
                    (el programa que corre en el equipo o servidor donde está configurado y que hace los procesos pesados).</p>
                <p class="mb-2">Tú solo usas los botones de arriba; no hace falta escribir direcciones raras ni códigos. Detrás de cada acción, la aplicación le pide al agente que haga el trabajo y luego te muestra el resultado o el archivo.</p>
                <ul class="mb-0 ps-3">
                    <li><strong>Estado del agente</strong> — Comprueba si ese programa está encendido y disponible, y qué herramientas tiene listas (reportes, worker, carga a lista negra, descargo, etc.).</li>
                    <li><strong>Generar reporte</strong> — Lanza el proceso que arma el reporte de cobranza y lo deja en la lista de archivos para descargar.</li>
                    <li><strong>Log del agente</strong> — Muestra las últimas líneas de la bitácora en disco; si el archivo crece demasiado, el agente recorta y deja solo el final. Puedes usar <em>Vaciar log</em> para borrar el historial y empezar limpio.</li>
                    <li><strong>Lista de reportes y descargar</strong> — Te deja ver qué Excels ya están listos y bajar el que elijas.</li>
                    <li><strong>Subir Excel y worker / enriquecido</strong> — Sube tu archivo de créditos y ejecuta la consulta a S2 y el cruce en base de datos (o el flujo enriquecido, según elijas).</li>
                    <li><strong>CSV de errores en reintento</strong> — Solo si el worker hizo una segunda pasada y aún fallaron algunos créditos; aparece un enlace para bajar la lista de esos casos.</li>
                    <li><strong>Carga a lista negra (verificación semana)</strong> — Registra en bloque los datos del Excel en la tabla de verificación; tras el worker se hace sola al terminar la corrida (aunque fallen créditos sueltos).</li>
                    <li><strong>Descargo cobranza GC (estatus 3)</strong> — Genera y descarga un Excel con los registros pendientes de ese proceso; a veces responde con un mensaje en pantalla si no hay filas nuevas.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

<style>
    .container-p-y .row.mb-4:last-of-type {
        margin-bottom: 1rem !important;
    }
    /* Estados en tabla reporte/: tonos suaves, legibles en Sneat */
    .gc-rep-estado {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 500;
        padding: 0.2em 0.55em;
        border-radius: 0.35rem;
        line-height: 1.35;
        border: 1px solid transparent;
    }
    .gc-rep-est-generado { background: #e8f4ec; color: #2d4a38; border-color: rgba(45, 74, 56, 0.14); }
    .gc-rep-est-proc { background: #e9eef9; color: #314e7a; border-color: rgba(49, 78, 122, 0.12); }
    .gc-rep-est-aplic { background: #f0ebf7; color: #4a3a68; border-color: rgba(74, 58, 104, 0.12); }
    .gc-rep-est-worker { background: #faf3e8; color: #6b4e2e; border-color: rgba(107, 78, 46, 0.14); }
    .gc-rep-est-negra { background: #eef1f4; color: #3d4551; border-color: rgba(61, 69, 81, 0.12); }
    .gc-rep-est-vacio { background: transparent; color: #a0a8b0; border: none; font-weight: 400; }
    .gc-rep-est-otro { background: #f3f4f6; color: #4b5563; border-color: rgba(75, 85, 99, 0.12); }
</style>

<script>
(function () {
    var badge = document.getElementById('gastosCobranzaEstadoBadge');
    var detalle = document.getElementById('gastosCobranzaDetalle');
    var btnRef = document.getElementById('btnGastosCobranzaRefrescar');
    var btnRun = document.getElementById('btnGastosCobranzaEjecutar');
    var btnEcLauncher = document.getElementById('btnEcLauncherEjecutar');
    var btnCargaVerif = document.getElementById('btnCargaVerifEjecutar');
    var btnDescargoEstatus3 = document.getElementById('btnDescargoEstatus3');
    var chkDescargoDesdeCero = document.getElementById('chkDescargoDesdeCero');
    var chkDescargoSinActualizarGuia = document.getElementById('chkDescargoSinActualizarGuia');
    var descargoSpinner = document.getElementById('descargoEstatus3Spinner');
    var cargaVerifFile = document.getElementById('cargaVerifFile');
    var cargaVerifEstatus = document.getElementById('cargaVerifEstatus');
    var cargaVerifMensaje = document.getElementById('cargaVerifMensaje');
    var cargaVerifDry = document.getElementById('cargaVerifDryRun');
    var cargaVerifOutWrap = document.getElementById('cargaVerifSalidaWrap');
    var cargaVerifOutPre = document.getElementById('cargaVerifSalida');
    var chkMostrarCargaVerifManual = document.getElementById('chkMostrarCargaVerifManual');
    var wrapCargaVerifManual = document.getElementById('wrapCargaVerifManual');
    var ecFile = document.getElementById('ecLauncherFile');
    var ecFecha = document.getElementById('ecLauncherFecha');
    var ecEnrich = document.getElementById('ecLauncherEnrich');
    var ecCol = document.getElementById('ecLauncherCol');
    var ecOmitir = document.getElementById('ecLauncherOmitir');
    var ecOutWrap = document.getElementById('ecLauncherSalidaWrap');
    var ecOutPre = document.getElementById('ecLauncherSalida');
    var ecErroresReintentoBanner = document.getElementById('ecErroresReintentoBanner');
    var ecErroresReintentoLink = document.getElementById('ecErroresReintentoLink');
    var outWrap = document.getElementById('gastosCobranzaSalidaWrap');
    var outPre = document.getElementById('gastosCobranzaSalida');
    var logPanel = document.getElementById('gastosCobranzaLogPanel');
    var chkLog = document.getElementById('chkGastosCobranzaLogAuto');
    var btnLog = document.getElementById('btnGastosCobranzaLogAhora');
    var btnLogVaciar = document.getElementById('btnGastosCobranzaLogVaciar');
    var btnListarRep = document.getElementById('btnGastosCobranzaListarReportes');
    var tbodyRep = document.getElementById('gastosCobranzaTablaReportes');
    var ivEstado = null;
    var ivLog = null;
    var ivRep = null;
    /** Log más frecuente mientras corre EC launcher (worker/enrich). */
    var ivLogEcWorker = null;
    /** Evita reejecutar el mismo día (CDMX) tras un reporte real exitoso. */
    var LS_REPORTE_OK_YMD = 'gastosCobranza_reporteRealOkYmd';

    function fechaCalendarioCdmxYmd() {
        return new Intl.DateTimeFormat('en-CA', {
            timeZone: 'America/Mexico_City',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(new Date());
    }

    /** Nombre de archivo que genera reporte_cobranza.py hoy (CDMX). */
    function nombreArchivoReporteCobranzaHoyCdmx() {
        var ymd = fechaCalendarioCdmxYmd().split('-');
        return 'reporte_cobranza_' + ymd[2] + '-' + ymd[1] + '-' + ymd[0] + '.xlsx';
    }

    async function hayReporteCobranzaHoyEnServidor() {
        try {
            var r = await fetch('/gastoscobranza/listarReportes', {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (!data.success || !Array.isArray(data.archivos)) {
                return false;
            }
            var esperado = nombreArchivoReporteCobranzaHoyCdmx().toLowerCase();
            return data.archivos.some(function (a) {
                return String(a.nombre || '').toLowerCase() === esperado;
            });
        } catch (e) {
            return false;
        }
    }

    /**
     * Aviso si el reporte del día ya existe (misma máquina u otra sesión).
     * @returns {Promise<string>} 'forzar' si el usuario elige generar igual; 'cancelar' si no debe ejecutar.
     */
    function dialogoReporteYaGeneradoHoy() {
        var html = '<p class="mb-2">Este reporte <strong>ya se generó hoy</strong> (calendario Ciudad de México). '
            + 'No hace falta volver a ejecutarlo, salvo que tu área pida otra corrida.</p>'
            + '<p class="mb-0">Puedes <strong>descargar el Excel</strong> en la tabla '
            + '<em>«Reportes en carpeta reporte/»</em> más abajo.</p>';
        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                icon: 'info',
                title: 'Reporte ya generado hoy',
                html: '<div class="text-start small">' + html + '</div>',
                confirmButtonText: 'Entendido',
                showDenyButton: true,
                denyButtonText: 'Generar de todas formas',
            }).then(function (r) {
                return r.isDenied ? 'forzar' : 'cancelar';
            });
        }
        window.alert('Este reporte ya se generó hoy (Ciudad de México). Descarga el Excel en la tabla de reportes más abajo.');
        return Promise.resolve('cancelar');
    }

    function claseEstadoReporteCarpeta(estRaw) {
        var m = {
            'Generado': 'gc-rep-est-generado',
            'Proc. cartera': 'gc-rep-est-proc',
            'Aplic. cartera': 'gc-rep-est-aplic',
            'En Worker': 'gc-rep-est-worker',
            'Lista negra': 'gc-rep-est-negra',
        };
        return m[estRaw] || '';
    }

    function formatoBytes(n) {
        n = Number(n) || 0;
        if (n < 1024) return n + ' B';
        var u = ['KB', 'MB', 'GB'];
        var i = -1;
        do { n /= 1024; i++; } while (n >= 1024 && i < u.length - 1);
        return n.toFixed(i === 0 ? 0 : 2) + ' ' + u[i];
    }

    async function traerListaReportes() {
        try {
            var r = await fetch('/gastoscobranza/listarReportes', {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (!data.success || !data.archivos) {
                tbodyRep.innerHTML = '<tr><td colspan="6" class="text-warning small">' +
                    (data.mensaje || 'No se pudo listar reportes.') + '</td></tr>';
                return;
            }
            var list = data.archivos;
            if (!list.length) {
                tbodyRep.innerHTML = '<tr><td colspan="6" class="text-muted small">Aún no hay archivos .xlsx en <code>reporte/</code>. Ejecuta el reporte para generar uno.</td></tr>';
                return;
            }
            function escCelda(s) {
                return String(s)
                    .split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;').split('"').join('&quot;');
            }
            function esExcelReporteCobranza(nom) {
                return /^reporte_cobranza_/i.test(String(nom || ''));
            }
            tbodyRep.innerHTML = list.map(function (a) {
                var nom = String(a.nombre || '');
                var safe = escCelda(nom);
                var href = '/gastoscobranza/descargarReporte?nombre=' + encodeURIComponent(nom);
                var estRaw = (a.estado != null && String(a.estado).trim() !== '') ? String(a.estado).trim() : '';
                var estHtml = estRaw ? escCelda(estRaw) : '';
                var estTip = (a.estadoDetalle != null && String(a.estadoDetalle).trim() !== '')
                    ? escCelda(String(a.estadoDetalle).trim()) : '';
                var estTdAttr = estTip ? ' title="' + estTip + '"' : '';
                var estSubCls = claseEstadoReporteCarpeta(estRaw);
                if (estRaw && !estSubCls) estSubCls = 'gc-rep-est-otro';
                var estInner = estRaw
                    ? '<span class="gc-rep-estado ' + estSubCls + '">' + estHtml + '</span>'
                    : '<span class="gc-rep-estado gc-rep-est-vacio">—</span>';
                var esRep = esExcelReporteCobranza(nom);
                var btnWorker = esRep
                    ? '<button type="button" class="btn btn-sm btn-outline-primary btn-gc-worker-reporte ms-1" data-nombre-enc="' +
                        encodeURIComponent(nom) + '" title="Worker S2 con este Excel (sin volver a subirlo). Al terminar, lista negra automática. Usa fecha, columna ID y Omitir N de la sección EC Worker.">' +
                        '<i class="fa fa-cogs me-1"></i>Worker</button>'
                    : '';
                return '<tr>' +
                    '<td class="font-monospace small">' + safe + '</td>' +
                    '<td class="text-end small">' + formatoBytes(a.bytes) + '</td>' +
                    '<td class="small">' + (a.modificado || '') + '</td>' +
                    '<td class="small text-nowrap"' + estTdAttr + '>' + estInner + '</td>' +
                    '<td class="text-center text-nowrap">' +
                    '<a class="btn btn-sm btn-primary" href="' + href + '" title="Descargar"><i class="fa fa-download"></i></a>' +
                    btnWorker +
                    '</td>' +
                    '</tr>';
            }).join('');
        } catch (e) {
            tbodyRep.innerHTML = '<tr><td colspan="6" class="text-danger small">' + String(e.message || e) + '</td></tr>';
        }
    }

    function alertar(titulo, texto, icono) {
        if (typeof Swal !== 'undefined') {
            Swal.fire(titulo, texto, icono || 'info');
        } else {
            window.alert(titulo + ': ' + texto);
        }
    }

    async function traerLog(lineas, opts) {
        opts = opts || {};
        var n = typeof lineas === 'number' && lineas > 0 ? Math.min(400, lineas) : 160;
        try {
            var r = await fetch('/gastoscobranza/logAgente?lines=' + n, {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (data.success && typeof data.contenido === 'string') {
                logPanel.textContent = data.contenido.length ? data.contenido : '(log vacío todavía)';
            } else {
                logPanel.textContent = data.mensaje || 'No se pudo leer el log (¿agente caído?).';
            }
            if (opts.scrollBottom) {
                try {
                    logPanel.scrollTop = logPanel.scrollHeight;
                } catch (e2) { /* ignorar */ }
            }
        } catch (e) {
            logPanel.textContent = String(e.message || e);
        }
    }

    async function vaciarLogAgente() {
        if (typeof Swal !== 'undefined') {
            var c = await Swal.fire({
                icon: 'warning',
                title: 'Vaciar log del agente',
                html: '<p class="mb-0 small text-start">Se borrará el historial del archivo de log en la máquina donde corre el agente. No afecta reportes Excel ni la base de datos.</p>',
                showCancelButton: true,
                confirmButtonText: 'Sí, vaciar',
                cancelButtonText: 'Cancelar'
            });
            if (!c.isConfirmed) return;
        } else if (!window.confirm('¿Vaciar el log del agente en disco?')) {
            return;
        }
        try {
            var r = await fetch('/gastoscobranza/vaciarLogAgente', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                body: '{}'
            });
            var data = await r.json();
            if (data.success) {
                alertar('Listo', data.mensaje || 'Log vaciado.', 'success');
                traerLog(160, { scrollBottom: true });
            } else {
                alertar('No se pudo vaciar', data.mensaje || 'Error', 'warning');
            }
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        }
    }

    function comenzarLogRapidoEcWorker() {
        if (ivLogEcWorker) clearInterval(ivLogEcWorker);
        traerLog(380, { scrollBottom: true });
        ivLogEcWorker = setInterval(function () {
            traerLog(380, { scrollBottom: true });
        }, 2500);
    }

    function detenerLogRapidoEcWorker() {
        if (ivLogEcWorker) {
            clearInterval(ivLogEcWorker);
            ivLogEcWorker = null;
        }
    }

    function construirPayloadCargaVerificacion(nombreArchivo, cargaOpts) {
        cargaOpts = cargaOpts || {};
        var payload = {
            archivo: nombreArchivo,
            dryRun: !!(cargaVerifDry && cargaVerifDry.checked),
            estatus: cargaVerifEstatus ? parseInt(cargaVerifEstatus.value, 10) : 2,
            tipoReporteNulo: true,
            megaPhpDefaults: true
        };
        if (cargaOpts.origenCarpeta === 'reporte') {
            payload.origenCarpeta = 'reporte';
        }
        if (cargaVerifMensaje && cargaVerifMensaje.value.trim()) {
            payload.mensaje = cargaVerifMensaje.value.trim();
        }
        return payload;
    }

    async function invocarCargaVerificacionAgente(nombreArchivo, cargaOpts) {
        var r2 = await fetch('/gastoscobranza/ejecutarCargaVerificacionSemana', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify(construirPayloadCargaVerificacion(nombreArchivo, cargaOpts))
        });
        return r2.json();
    }

    /**
     * Worker o enrich vía agente; si es worker y termina (cód. 0 o 2), carga lista negra con el mismo Excel.
     * cargaOpts: {} para archivo en ec-uploads; { origenCarpeta: 'reporte' } para Excel ya en reporte/.
     */
    async function ejecutarPayloadEcYListaNegra(payloadEc, cargaOpts) {
        comenzarLogRapidoEcWorker();
        try {
            var r2 = await fetch('/gastoscobranza/ejecutarEcLauncher', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                body: JSON.stringify(payloadEc)
            });
            var raw = await r2.text();
            var data;
            try {
                data = JSON.parse(raw);
            } catch (eParse) {
                alertar('Error', 'El servidor no devolvió JSON en worker/enrich. Suele ser sesión caducada o error PHP. Inicio de respuesta: ' +
                    String(raw).slice(0, 200).replace(/\s+/g, ' '), 'error');
                return;
            }
            var ok = !!data.success;
            var msg = data.mensaje || (ok ? 'Proceso EC terminado.' : 'Error en worker / enrich.');
            var codigoSalida = (typeof data.codigo_salida === 'number') ? data.codigo_salida : -1;
            var workerLlegoAlFin = payloadEc.tipo === 'worker' && (codigoSalida === 0 || codigoSalida === 2);
            if (data.stdout || data.stderr) {
                ecOutPre.textContent = (data.stdout || '') + (data.stderr ? '\n--- stderr ---\n' + data.stderr : '');
                ecOutWrap.classList.remove('d-none');
            }
            if (data.errores_reintento_csv && ecErroresReintentoBanner && ecErroresReintentoLink) {
                ecErroresReintentoLink.href = '/gastoscobranza/descargarErroresReintento?nombre=' +
                    encodeURIComponent(String(data.errores_reintento_csv));
                ecErroresReintentoBanner.classList.remove('d-none');
            }
            if (workerLlegoAlFin) {
                var dataCarga = await invocarCargaVerificacionAgente(payloadEc.nombre, cargaOpts);
                var okC = !!dataCarga.success;
                if (dataCarga.stdout || dataCarga.stderr) {
                    var bloqueCarga = '\n\n--- Lista negra (automático, mismo Excel) ---\n' +
                        (dataCarga.stdout || '') +
                        (dataCarga.stderr ? '\n--- stderr ---\n' + dataCarga.stderr : '');
                    ecOutPre.textContent = (ecOutPre.textContent || '') + bloqueCarga;
                    ecOutWrap.classList.remove('d-none');
                    if (cargaVerifOutPre) {
                        cargaVerifOutPre.textContent = (dataCarga.stdout || '') +
                            (dataCarga.stderr ? '\n--- stderr ---\n' + dataCarga.stderr : '');
                    }
                    if (cargaVerifOutWrap) cargaVerifOutWrap.classList.remove('d-none');
                }
                if (okC) {
                    if (codigoSalida === 0) {
                        alertar('Listo', 'Worker completado y lista negra actualizada con el mismo Excel.', 'success');
                    } else {
                        alertar('Lista negra actualizada',
                            'El worker terminó con uno o más créditos en error (código ' + codigoSalida +
                            '), pero la lista negra se cargó igual con el mismo Excel. Revise el log y el CSV de reintento si aplica.',
                            'warning');
                    }
                } else {
                    alertar('Atención', 'El worker terminó, pero la carga a lista negra falló: ' +
                        (dataCarga.mensaje || 'revisa salida y log.'), 'warning');
                }
            } else if (ok) {
                alertar('EC listo', msg, 'success');
            } else {
                alertar('EC con errores', msg, 'error');
            }
            await traerLog(380, { scrollBottom: true });
            await traerListaReportes();
        } finally {
            detenerLogRapidoEcWorker();
        }
    }

    async function refrescarEstado(opts) {
        opts = opts || {};
        var sil = !!opts.silencioso;
        if (!sil) {
            badge.className = 'badge bg-label-warning';
            badge.textContent = 'Comprobando…';
            detalle.textContent = '—';
            btnRun.disabled = true;
            if (btnEcLauncher) btnEcLauncher.disabled = true;
            if (btnCargaVerif) btnCargaVerif.disabled = true;
            if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
        }
        try {
            var r = await fetch('/gastoscobranza/estadoAgente', {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (!data.success) {
                badge.className = 'badge bg-label-danger';
                badge.textContent = 'Error';
                detalle.textContent = data.mensaje || 'Error';
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
                return;
            }
            if (!data.agente_configurado) {
                badge.className = 'badge bg-label-secondary';
                badge.textContent = 'INI desactivado';
                detalle.innerHTML = data.detalle || '';
                tbodyRep.innerHTML = '<tr><td colspan="6" class="text-muted small">Habilite el agente en <code>config.ini</code> para listar reportes.</td></tr>';
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
                return;
            }
            if (data.agente_online) {
                badge.className = 'badge bg-label-success';
                badge.textContent = 'Agente en línea';
                var a = data.agente || {};
                var partes = [data.detalle || ''];
                if (a.script_configurado === false) partes.push('Python: <strong>no configurado</strong> (modo prueba disponible).');
                else if (a.script_bundled) partes.push('Python: <strong>scripts/reporte_cobranza.py</strong> (incluido en el agente).');
                else partes.push('Python: <strong>configurado</strong> (ruta en .env).');
                if (a.demo_sin_script) partes.push('Demo sin script: <strong>activo</strong>.');
                if (a.script_carga_verificacion_semana) {
                    partes.push('Carga verificación: <strong>script listo</strong>.');
                } else {
                    partes.push('Carga verificación: <strong>sin script</strong>.');
                }
                if (a.script_descargo_estatus3) {
                    partes.push('Descargo estatus 3: <strong>script listo</strong>.');
                } else {
                    partes.push('Descargo estatus 3: <strong>sin script</strong>.');
                }
                detalle.innerHTML = partes.filter(Boolean).join(' · ');
                btnRun.disabled = false;
                if (btnEcLauncher) btnEcLauncher.disabled = false;
                if (btnCargaVerif) btnCargaVerif.disabled = !a.script_carga_verificacion_semana;
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = !a.script_descargo_estatus3;
                if (!sil) {
                    traerLog();
                    traerListaReportes();
                }
            } else {
                badge.className = 'badge bg-label-danger';
                badge.textContent = 'Sin conexión';
                detalle.textContent = data.detalle || '';
                logPanel.textContent = 'Levante el agente (npm start en gastos-cobranza-agent, puerto 3120).';
                tbodyRep.innerHTML = '<tr><td colspan="6" class="text-muted small">Agente fuera de línea — sin listado.</td></tr>';
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
            }
        } catch (e) {
            badge.className = 'badge bg-label-danger';
            badge.textContent = 'Error red';
            detalle.textContent = String(e.message || e);
            if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
        }
    }

    function setDescargoDescargando(enCurso) {
        if (descargoSpinner) descargoSpinner.classList.toggle('d-none', !enCurso);
    }

    async function ejecutarDescargoEstatus3Flujo() {
        if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
        setDescargoDescargando(true);
        try {
            var r = await fetch('/gastoscobranza/descargoEstatus3EjecutarYDescargar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                body: JSON.stringify({
                    megaPhpDefaults: true,
                    desdeCero: !!(chkDescargoDesdeCero && chkDescargoDesdeCero.checked),
                    sinActualizarGuia: !!(chkDescargoSinActualizarGuia && chkDescargoSinActualizarGuia.checked),
                }),
            });
            var ct = (r.headers.get('content-type') || '').toLowerCase();
            if (ct.indexOf('application/json') !== -1) {
                var data = await r.json();
                await traerLog();
                if (data.sin_excel) {
                    alertar('Descargo', data.mensaje || 'No se generó Excel en esta corrida.', 'info');
                } else if (data.success) {
                    alertar('Descargo', data.mensaje || 'Listo.', 'success');
                } else {
                    var det = data.mensaje || 'Error en el descargo.';
                    if (data.stderr) det += '\n\n' + String(data.stderr).slice(-2000);
                    alertar('Descargo con errores', det, 'error');
                }
                return;
            }
            if (!r.ok) {
                await traerLog();
                alertar('Descargo', 'Respuesta HTTP ' + r.status, 'error');
                return;
            }
            var blob = await r.blob();
            var urlObj = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = urlObj;
            a.download = 'descargo_estatus3.xlsx';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(urlObj);
            await traerLog();
            alertar('Listo', 'Se descargó descargo_estatus3.xlsx', 'success');
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        } finally {
            setDescargoDescargando(false);
            refrescarEstado();
        }
    }

    async function ejecutarEcLauncherFlujo() {
        if (!ecFile || !ecFile.files || !ecFile.files[0]) {
            alertar('Falta archivo', 'Seleccione un Excel .xlsx.', 'warning');
            return;
        }
        if (!ecFecha || !ecFecha.value) {
            alertar('Fecha', 'Indique la fecha de corte.', 'warning');
            return;
        }
        btnEcLauncher.disabled = true;
        ecOutWrap.classList.add('d-none');
        if (ecErroresReintentoBanner) ecErroresReintentoBanner.classList.add('d-none');
        try {
            var fd = new FormData();
            fd.append('archivo', ecFile.files[0]);
            var up = await fetch('/gastoscobranza/subirExcelEc', {
                method: 'POST',
                body: fd,
                headers: { 'Front-Request': 'true' }
            });
            var ju = await up.json();
            if (!ju.success || !ju.nombre) {
                alertar('Subida', ju.mensaje || 'No se pudo subir el archivo.', 'error');
                refrescarEstado();
                return;
            }
            var payloadEc = {
                nombre: ju.nombre,
                tipo: ecEnrich && ecEnrich.checked ? 'enrich' : 'worker',
                fechaCorte: ecFecha.value,
                column: ecCol ? ecCol.value.trim() || 'ID CREDITO' : 'ID CREDITO',
                omitir: ecOmitir ? parseInt(ecOmitir.value, 10) || 0 : 0,
                soloColumnas: false
            };
            await ejecutarPayloadEcYListaNegra(payloadEc, {});
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        }
        refrescarEstado();
    }

    async function ejecutarCargaVerificacionFlujo() {
        if (!cargaVerifFile || !cargaVerifFile.files || !cargaVerifFile.files[0]) {
            alertar('Falta archivo', 'Seleccione un Excel .xlsx.', 'warning');
            return;
        }
        if (btnCargaVerif) btnCargaVerif.disabled = true;
        if (cargaVerifOutWrap) cargaVerifOutWrap.classList.add('d-none');
        try {
            var fd = new FormData();
            fd.append('archivo', cargaVerifFile.files[0]);
            var up = await fetch('/gastoscobranza/subirExcelEc', {
                method: 'POST',
                body: fd,
                headers: { 'Front-Request': 'true' }
            });
            var ju = await up.json();
            if (!ju.success || !ju.nombre) {
                alertar('Subida', ju.mensaje || 'No se pudo subir el archivo.', 'error');
                refrescarEstado();
                return;
            }
            var data = await invocarCargaVerificacionAgente(ju.nombre);
            var ok = !!data.success;
            var msg = data.mensaje || (ok ? 'Carga terminada.' : 'Error en la carga.');
            if (data.stdout || data.stderr) {
                if (cargaVerifOutPre) {
                    cargaVerifOutPre.textContent = (data.stdout || '') + (data.stderr ? '\n--- stderr ---\n' + data.stderr : '');
                }
                if (cargaVerifOutWrap) cargaVerifOutWrap.classList.remove('d-none');
            }
            alertar(ok ? 'Carga listo' : 'Carga con errores', msg, ok ? 'success' : 'error');
            await traerLog();
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        }
        refrescarEstado();
    }

    async function ejecutar() {
        var hoyCdmx = fechaCalendarioCdmxYmd();
        var yaMarcadoLocal = false;
        try {
            yaMarcadoLocal = localStorage.getItem(LS_REPORTE_OK_YMD) === hoyCdmx;
        } catch (e) { /* localStorage no disponible */ }

        var yaExisteArchivoHoy = yaMarcadoLocal;
        if (!yaExisteArchivoHoy) {
            yaExisteArchivoHoy = await hayReporteCobranzaHoyEnServidor();
        }
        if (yaExisteArchivoHoy) {
            var acc = await dialogoReporteYaGeneradoHoy();
            if (acc !== 'forzar') {
                return;
            }
        }

        btnRun.disabled = true;
        outWrap.classList.add('d-none');
        try {
            var r = await fetch('/gastoscobranza/ejecutarReporte', {
                method: 'POST',
                headers: { 'Front-Request': 'true' }
            });
            var rawRep = await r.text();
            var data;
            try {
                data = JSON.parse(rawRep);
            } catch (eJson) {
                alertar('Error',
                    'El servidor no devolvió JSON al generar el reporte. Lo habitual es que PHP cortara por tiempo de espera (ya se extendió el límite en el controlador; revise php.ini si persiste) o que la sesión haya expirado. Inicio de respuesta: ' +
                    String(rawRep).slice(0, 200).replace(/\s+/g, ' '), 'error');
            }
            if (data) {
                var ok = !!data.success;
                var msg = data.mensaje || (ok ? 'Proceso terminado.' : 'Falló la ejecución.');
                if (data.demo) {
                    msg = 'Modo prueba: ' + msg;
                }
                if (data.stdout || data.stderr) {
                    outPre.textContent = (data.stdout || '') + (data.stderr ? '\n--- stderr ---\n' + data.stderr : '');
                    outWrap.classList.remove('d-none');
                }
                alertar(ok ? 'Listo' : 'Atención', msg, ok ? 'success' : 'warning');
                if (ok && !data.demo) {
                    try {
                        localStorage.setItem(LS_REPORTE_OK_YMD, fechaCalendarioCdmxYmd());
                    } catch (e2) { /* ignorar */ }
                }
                await traerLog();
                await traerListaReportes();
            }
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        } finally {
            refrescarEstado();
        }
    }

    async function ejecutarWorkerDesdeReporte(nombreArchivo) {
        if (!ecFecha || !ecFecha.value) {
            alertar('Fecha de corte', 'Indique la fecha de corte S2 en la sección «EC Worker / Excel enriquecido» (la misma que usaría al subir el Excel).', 'warning');
            if (ecFecha) ecFecha.focus();
            return;
        }
        if (btnEcLauncher) btnEcLauncher.disabled = true;
        ecOutWrap.classList.add('d-none');
        if (ecErroresReintentoBanner) ecErroresReintentoBanner.classList.add('d-none');
        try {
            var payloadEc = {
                nombre: nombreArchivo,
                tipo: 'worker',
                fechaCorte: ecFecha.value,
                column: ecCol ? ecCol.value.trim() || 'ID CREDITO' : 'ID CREDITO',
                omitir: ecOmitir ? parseInt(ecOmitir.value, 10) || 0 : 0,
                soloColumnas: false,
                origenCarpeta: 'reporte'
            };
            await ejecutarPayloadEcYListaNegra(payloadEc, { origenCarpeta: 'reporte' });
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        }
        refrescarEstado();
    }

    function programarPoll() {
        if (ivEstado) clearInterval(ivEstado);
        ivEstado = setInterval(function () {
            if (document.hidden) return;
            refrescarEstado({ silencioso: true });
        }, 15000);
        if (ivLog) clearInterval(ivLog);
        ivLog = setInterval(function () {
            if (document.hidden || !chkLog.checked) return;
            if (ivLogEcWorker) return;
            traerLog();
        }, 4000);
        if (ivRep) clearInterval(ivRep);
        ivRep = setInterval(function () {
            if (document.hidden) return;
            traerListaReportes();
        }, 12000);
    }

    chkLog.addEventListener('change', function () {
        if (chkLog.checked) traerLog();
    });
    btnLog.addEventListener('click', traerLog);
    if (btnLogVaciar) btnLogVaciar.addEventListener('click', vaciarLogAgente);
    if (tbodyRep) {
        tbodyRep.addEventListener('click', function (ev) {
            var btnW = ev.target.closest('.btn-gc-worker-reporte');
            if (!btnW || !tbodyRep.contains(btnW)) return;
            var enc = btnW.getAttribute('data-nombre-enc');
            if (!enc) return;
            ejecutarWorkerDesdeReporte(decodeURIComponent(enc));
        });
    }
    btnListarRep.addEventListener('click', traerListaReportes);
    btnRef.addEventListener('click', refrescarEstado);
    btnRun.addEventListener('click', ejecutar);
    if (ecFecha) ecFecha.value = fechaCalendarioCdmxYmd();
    if (btnEcLauncher) btnEcLauncher.addEventListener('click', ejecutarEcLauncherFlujo);
    if (btnCargaVerif) btnCargaVerif.addEventListener('click', ejecutarCargaVerificacionFlujo);
    if (btnDescargoEstatus3) btnDescargoEstatus3.addEventListener('click', ejecutarDescargoEstatus3Flujo);
    if (chkMostrarCargaVerifManual && wrapCargaVerifManual) {
        chkMostrarCargaVerifManual.addEventListener('change', function () {
            wrapCargaVerifManual.classList.toggle('d-none', !chkMostrarCargaVerifManual.checked);
        });
    }
    refrescarEstado();
    programarPoll();
})();
</script>
