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
                                Agente para <code>reporte_cobranza.py</code>, correos, programación y cadena worker → lista negra.
                                <span class="badge bg-label-info ms-1">Fase 1 · pantalla base</span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted d-block">
                                <i class="fa fa-shield-alt me-1"></i>Solo usuarios con módulo asignado (id 31)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-list-check text-primary me-2"></i>Capacidades previstas</h5>
                    <ul class="mb-0 ps-3">
                        <li class="mb-2"><strong>Ejecución por agente</strong> — programada o por evento</li>
                        <li class="mb-2"><strong>Ejecución manual</strong> — desde esta interfaz si falla el agente</li>
                        <li class="mb-2"><strong>Progreso en tiempo real</strong> — logs del proceso</li>
                        <li><strong>Descarga de Excels</strong> — artefactos al terminar</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-road text-warning me-2"></i>Siguientes iteraciones</h5>
                    <ol class="mb-0 ps-3 small text-muted">
                        <li class="mb-1">Servicio/agente (API + API key, estilo segundómetro)</li>
                        <li class="mb-1">Botón ejecutar ahora + estado /health</li>
                        <li class="mb-1">Streaming o polling de log</li>
                        <li class="mb-1">Descarga segura de <code>reporte_cobranza.xlsx</code></li>
                        <li>Correos configurables + cron interno o Task Scheduler</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-secondary mb-0" role="alert">
                <strong>URL directa:</strong>
                <code>/gastoscobranza/shell</code>
                — En el menú Configuración enlaza a esta ruta y asigna el módulo <strong>31</strong> a quien deba verlo.
            </div>
        </div>
    </div>

</div>
