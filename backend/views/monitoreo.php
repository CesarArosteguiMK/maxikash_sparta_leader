<?php
$cssPath = dirname(__DIR__, 2) . '/public/assets/css/monitoreo.css';
$jsPath = dirname(__DIR__, 2) . '/public/assets/js/monitoreo.js';
$cssVersion = is_file($cssPath) ? filemtime($cssPath) : time();
$jsVersion = is_file($jsPath) ? filemtime($jsPath) : time();
$modoPlaneador = !empty($modoPlaneador);
?>
<link rel="stylesheet" href="/assets/css/monitoreo.css?v=<?= (int) $cssVersion ?>">
<?php if ($modoPlaneador): ?>
<script>
  document.documentElement.classList.add('monitor-wall-active');
  document.body.classList.add('monitor-wall-active');
</script>
<?php endif; ?>

<main class="monitor-page<?= $modoPlaneador ? ' monitor-page-wall' : '' ?>" id="monitorApp" data-wall-mode="<?= $modoPlaneador ? '1' : '0' ?>">
  <header class="monitor-hero">
    <div class="monitor-hero-main">
      <div class="monitor-nav">
        <a class="monitor-back" href="<?= $modoPlaneador ? '/monitoreo' : '/inicio' ?>"><i class="fa-solid fa-arrow-left"></i><?= $modoPlaneador ? 'Salir del modo planeador' : 'Inicio' ?></a>
        <span class="monitor-private"><i class="fa-solid fa-lock"></i>Acceso privado · Pedro</span>
        <?php if ($modoPlaneador): ?>
        <span class="monitor-wall-live"><i></i>EN VIVO</span>
        <?php endif; ?>
      </div>
      <p class="monitor-eyebrow">Centro de operaciones</p>
      <h1 class="monitor-title"><?= $modoPlaneador ? 'Centro de operaciones web' : 'Monitoreo de servicios web' ?></h1>
      <p class="monitor-subtitle">Disponibilidad, latencia, cambios, pruebas OpenAPI y control seguro del servicio local desde un solo lugar.</p>
    </div>
    <?php if ($modoPlaneador): ?>
    <div class="monitor-wall-clock" aria-label="Hora actual">
      <span>Hora local</span><strong id="monitorWallClock">--:--:--</strong>
    </div>
    <?php endif; ?>
    <div class="monitor-hero-controls">
      <label class="monitor-interval-label" for="monitorInterval">Actualizar cada</label>
      <select class="monitor-select monitor-select-dark" id="monitorInterval">
        <option value="30">30 segundos</option>
        <option value="60" selected>1 minuto</option>
        <option value="300">5 minutos</option>
      </select>
      <button type="button" class="monitor-ghost-btn" id="monitorAutoToggle" aria-pressed="true">
        <i class="fa-solid fa-pause"></i><span>Pausar</span>
      </button>
      <button type="button" class="monitor-ghost-btn" id="monitorAlerts" aria-pressed="false">
        <i class="fa-regular fa-bell"></i><span>Alertas</span>
      </button>
      <button type="button" class="monitor-refresh" id="monitorRefresh">
        <i class="fa-solid fa-arrows-rotate"></i><span>Comprobar ahora</span>
      </button>
      <?php if ($modoPlaneador): ?>
      <button type="button" class="monitor-ghost-btn monitor-fullscreen" id="monitorFullscreen" aria-pressed="false">
        <i class="fa-solid fa-expand"></i><span>Pantalla completa</span>
      </button>
      <?php endif; ?>
    </div>
  </header>

  <section class="monitor-freshness" aria-live="polite">
    <span><i class="fa-solid fa-satellite-dish"></i><span id="monitorUpdated">Preparando primera comprobación…</span></span>
    <span class="monitor-next" id="monitorNextRefresh">Siguiente actualización en 60 s</span>
  </section>

  <section class="monitor-kpis" aria-label="Indicadores principales">
    <article class="monitor-kpi monitor-kpi-primary">
      <div class="monitor-kpi-head"><span>Servicios estables</span><i class="fa-solid fa-heart-pulse"></i></div>
      <strong id="monitorKpiStable">—</strong>
      <small id="monitorKpiStableSub">De 3 servicios configurados</small>
    </article>
    <article class="monitor-kpi">
      <div class="monitor-kpi-head"><span>Disponibilidad 24 h</span><i class="fa-solid fa-shield-halved"></i></div>
      <strong id="monitorKpiAvailability">—</strong>
      <small>Porcentaje de muestras con respuesta</small>
    </article>
    <article class="monitor-kpi monitor-kpi-latency">
      <div class="monitor-kpi-head"><span>Latencia promedio</span><i class="fa-solid fa-gauge-high"></i></div>
      <strong id="monitorKpiLatency">—</strong>
      <small>Promedio de las últimas 24 horas</small>
      <details class="monitor-kpi-help">
        <summary><span>¿Cómo leer la latencia?</span><i class="fa-solid fa-chevron-down"></i></summary>
        <div class="monitor-kpi-help-panel">
          <p><b>Latencia:</b> tiempo que tarda la API en responder.</p>
          <ul>
            <li class="good"><b>Buena</b><span>Menos de 300 ms</span></li>
            <li class="average"><b>Promedio</b><span>300 a 800 ms</span></li>
            <li class="slow"><b>Lenta</b><span>801 a 1,500 ms</span></li>
            <li class="bad"><b>Mala</b><span>Más de 1,500 ms o no responde</span></li>
          </ul>
        </div>
      </details>
    </article>
    <article class="monitor-kpi">
      <div class="monitor-kpi-head"><span>Incidentes 24 h</span><i class="fa-solid fa-triangle-exclamation"></i></div>
      <strong id="monitorKpiIncidents">—</strong>
      <small>Caídas y cambios de OpenAPI</small>
    </article>
    <article class="monitor-kpi">
      <div class="monitor-kpi-head"><span>Muestras</span><i class="fa-solid fa-chart-simple"></i></div>
      <strong id="monitorKpiSamples">—</strong>
      <small>Historial rotativo, sin base de datos</small>
    </article>
  </section>

  <section class="monitor-charts" aria-label="Tendencias operativas">
    <article class="monitor-chart-card">
      <div class="monitor-section-head">
        <div><h2>Latencia por servicio</h2><p>Milisegundos observados durante las últimas 24 horas.</p></div>
        <span class="monitor-source-chip"><i class="fa-solid fa-server"></i>Servidor Sparta</span>
      </div>
      <div class="monitor-chart-wrap"><canvas id="monitorLatencyChart"></canvas><div class="monitor-chart-empty" id="monitorLatencyEmpty">Recopilando historial; la tendencia será más útil después de 8 muestras.</div></div>
    </article>
    <article class="monitor-chart-card">
      <div class="monitor-section-head">
        <div><h2>Disponibilidad por servicio</h2><p>Porcentaje de muestras con respuesta en la ventana de 24 horas.</p></div>
      </div>
      <div class="monitor-chart-wrap"><canvas id="monitorAvailabilityChart"></canvas></div>
    </article>
  </section>

  <section class="monitor-services-section">
    <div class="monitor-section-head">
      <div><h2>Servicios administrados</h2><p>Abre un servicio para consultar diagnóstico, endpoints, cambios y controles.</p></div>
      <div class="monitor-status-legend">
        <span><i class="stable"></i>Estable</span><span><i class="degraded"></i>Revisar</span><span><i class="offline"></i>Sin conexión</span>
      </div>
    </div>
    <div class="monitor-grid" id="monitorGrid">
      <div class="monitor-loading"><i class="fa-solid fa-spinner fa-spin"></i>Consultando salud, OpenAPI y workspaces…</div>
    </div>
  </section>

  <section class="monitor-activity">
    <div class="monitor-section-head">
      <div><h2>Actividad reciente</h2><p>Cambios de estado, OpenAPI, controles de Python y pruebas ejecutadas.</p></div>
      <span class="monitor-source-chip"><i class="fa-regular fa-clock"></i>Últimos eventos</span>
    </div>
    <div class="monitor-timeline" id="monitorTimeline">
      <div class="monitor-empty">Todavía no hay eventos registrados.</div>
    </div>
  </section>
</main>

<div class="monitor-drawer-overlay" id="monitorDrawerOverlay" hidden></div>
<aside class="monitor-drawer" id="monitorDrawer" aria-hidden="true" aria-labelledby="monitorDrawerTitle">
  <header class="monitor-drawer-head">
    <div>
      <span class="monitor-drawer-kicker" id="monitorDrawerKicker">Detalle operativo</span>
      <h2 id="monitorDrawerTitle">Servicio</h2>
      <p id="monitorDrawerSubtitle">—</p>
    </div>
    <button type="button" class="monitor-icon-btn" id="monitorDrawerClose" aria-label="Cerrar detalle"><i class="fa-solid fa-xmark"></i></button>
  </header>
  <nav class="monitor-tabs" aria-label="Secciones del servicio">
    <button type="button" class="active" data-monitor-tab="overview">Resumen</button>
    <button type="button" data-monitor-tab="endpoints">Endpoints</button>
    <button type="button" data-monitor-tab="changes">Cambios</button>
    <button type="button" data-monitor-tab="logs" id="monitorLogsTab">Logs</button>
  </nav>
  <div class="monitor-drawer-body">
    <section class="monitor-tab-panel active" data-monitor-panel="overview" id="monitorOverviewPanel"></section>
    <section class="monitor-tab-panel" data-monitor-panel="endpoints">
      <div class="monitor-endpoint-layout">
        <div>
          <div class="monitor-panel-title"><h3>Inventario OpenAPI</h3><span id="monitorEndpointCount">0 endpoints</span></div>
          <div class="monitor-endpoint-list" id="monitorEndpointList"></div>
        </div>
        <form class="monitor-tester" id="monitorTester">
          <div class="monitor-panel-title"><h3>Probar desde Sparta</h3><span class="monitor-safe-badge"><i class="fa-solid fa-lock"></i>Allowlist OpenAPI</span></div>
          <p class="monitor-form-help">Selecciona un endpoint; Sparta generará sus campos, autenticación y ejemplo de uso desde OpenAPI.</p>
          <div class="monitor-endpoint-selected" id="monitorEndpointSelected" hidden></div>
          <div class="monitor-request-line">
            <select class="monitor-select" id="monitorTestMethod" aria-label="Método HTTP">
              <option>GET</option><option>POST</option><option>PUT</option><option>PATCH</option><option>DELETE</option>
            </select>
            <input class="monitor-input" id="monitorTestPath" placeholder="/health" required>
          </div>
          <div class="monitor-parameter-fields" id="monitorParameterFields"></div>
          <details class="monitor-advanced-request">
            <summary><i class="fa-solid fa-code"></i>Query JSON avanzado</summary>
            <label class="monitor-field"><span>Query JSON</span><textarea class="monitor-textarea" id="monitorTestQuery" rows="4" spellcheck="false">{}</textarea></label>
          </details>
          <label class="monitor-field" id="monitorBodyField" hidden><span>Body JSON</span><textarea class="monitor-textarea" id="monitorTestBody" rows="5" spellcheck="false">{}</textarea></label>

          <section class="monitor-auth-panel">
            <div class="monitor-auth-head">
              <div><h4>Autenticación</h4><p>El token se usa sólo en esta prueba; no se guarda ni aparece en logs.</p></div>
              <span class="monitor-auth-status idle" id="monitorAuthStatus"><i class="fa-solid fa-circle"></i>Sin verificar</span>
            </div>
            <label class="monitor-field"><span>Tipo</span><select class="monitor-select" id="monitorAuthMode">
              <option value="none">Sin autenticación</option>
              <option value="bearer">Bearer token</option>
              <option value="api_key">API key en header</option>
              <option value="http">Token HTTP personalizado</option>
            </select></label>
            <label class="monitor-field" id="monitorAuthTokenField" hidden><span>Token / API key</span><input class="monitor-input" id="monitorAuthToken" type="password" autocomplete="new-password" placeholder="Secreto temporal"></label>
            <div class="monitor-auth-row" id="monitorAuthHeaderFields" hidden>
              <label class="monitor-field"><span>Header</span><input class="monitor-input" id="monitorAuthHeader" value="Authorization" placeholder="Authorization"></label>
              <label class="monitor-field" id="monitorAuthPrefixField"><span>Prefijo opcional</span><input class="monitor-input" id="monitorAuthPrefix" placeholder="Bearer"></label>
            </div>
            <p class="monitor-auth-hint" id="monitorAuthHint">Este endpoint no declara autenticación obligatoria.</p>
            <button type="button" class="monitor-secondary-btn monitor-check-btn" id="monitorConnectionCheck"><i class="fa-solid fa-plug-circle-check"></i>Verificar conexión</button>
          </section>

          <section class="monitor-usage-panel">
            <div class="monitor-usage-head"><div><h4>Cómo usar este endpoint</h4><p id="monitorUsageHelp">Selecciona un endpoint del inventario.</p></div><button type="button" id="monitorCopyCurl"><i class="fa-regular fa-copy"></i>Copiar cURL</button></div>
            <pre id="monitorCurlPreview">Selecciona un endpoint para generar el ejemplo.</pre>
          </section>

          <label class="monitor-confirm-row" id="monitorMutationConfirm" hidden><input type="checkbox" id="monitorMutationCheckbox"><span>Confirmo que esta petición puede modificar datos.</span></label>
          <button type="submit" class="monitor-primary-btn" id="monitorTestSubmit"><i class="fa-solid fa-play"></i>Ejecutar prueba completa</button>
          <div class="monitor-response" id="monitorTestResponse" hidden>
            <div class="monitor-response-head"><span id="monitorTestResponseMeta">Respuesta</span><button type="button" id="monitorCopyResponse"><i class="fa-regular fa-copy"></i>Copiar</button></div>
            <pre id="monitorTestResponseBody"></pre>
          </div>
        </form>
      </div>
    </section>
    <section class="monitor-tab-panel" data-monitor-panel="changes" id="monitorChangesPanel"></section>
    <section class="monitor-tab-panel" data-monitor-panel="logs">
      <div class="monitor-terminal-shell">
        <div class="monitor-terminal-titlebar">
          <span class="monitor-terminal-dots" aria-hidden="true"><i></i><i></i><i></i></span>
          <strong>Windows PowerShell · Condonaciones</strong>
          <span class="monitor-terminal-status" id="monitorTerminalStatus"><i></i>Consultando</span>
        </div>
        <div class="monitor-terminal-context">
          <div><span>Ruta</span><code id="monitorTerminalPath">—</code></div>
          <div><span>Comando</span><code id="monitorTerminalCommand">python main.py</code></div>
        </div>
        <pre class="monitor-log-console" id="monitorLogConsole">Preparando terminal…</pre>
        <div class="monitor-terminal-footer">
          <span id="monitorTerminalMeta">Esperando información del proceso.</span>
          <label><input type="checkbox" id="monitorTerminalFollow" checked> Seguir salida</label>
        </div>
      </div>
      <div class="monitor-panel-title monitor-log-history-title"><h3>Archivos de log</h3><span>Historial rotativo</span></div>
      <div class="monitor-log-toolbar">
        <select class="monitor-select" id="monitorLogSelect"><option value="">Selecciona un log</option></select>
        <button type="button" class="monitor-secondary-btn" id="monitorTerminalLive"><i class="fa-solid fa-satellite-dish"></i>En vivo</button>
        <button type="button" class="monitor-secondary-btn" id="monitorLogRefresh"><i class="fa-solid fa-arrows-rotate"></i>Actualizar</button>
        <a class="monitor-secondary-btn disabled" id="monitorLogDownload" href="#"><i class="fa-solid fa-download"></i>Descargar</a>
      </div>
      <div class="monitor-log-meta" id="monitorLogMeta">Selecciona un archivo para cargarlo en la terminal.</div>
    </section>
  </div>
</aside>

<dialog class="monitor-dialog" id="monitorConfirmDialog">
  <form method="dialog">
    <span class="monitor-dialog-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <h2 id="monitorConfirmTitle">Confirmar acción</h2>
    <p id="monitorConfirmText">Esta acción afectará el proceso local.</p>
    <div class="monitor-dialog-actions">
      <button value="cancel" class="monitor-secondary-btn">Cancelar</button>
      <button value="confirm" class="monitor-danger-btn" id="monitorConfirmAccept">Confirmar</button>
    </div>
  </form>
</dialog>

<div class="monitor-toasts" id="monitorToasts" aria-live="polite" aria-atomic="true"></div>

<script src="/assets/js/monitoreo.js?v=<?= (int) $jsVersion ?>" defer></script>
