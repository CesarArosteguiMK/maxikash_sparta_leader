(function () {
  'use strict';

  var app = document.getElementById('monitorApp');
  if (!app) return;
  var wallMode = app.getAttribute('data-wall-mode') === '1';

  var refs = {
    grid: document.getElementById('monitorGrid'),
    updated: document.getElementById('monitorUpdated'),
    next: document.getElementById('monitorNextRefresh'),
    refresh: document.getElementById('monitorRefresh'),
    interval: document.getElementById('monitorInterval'),
    auto: document.getElementById('monitorAutoToggle'),
    alerts: document.getElementById('monitorAlerts'),
    wallClock: document.getElementById('monitorWallClock'),
    fullscreen: document.getElementById('monitorFullscreen'),
    timeline: document.getElementById('monitorTimeline'),
    activityOpen: document.getElementById('monitorActivityOpen'),
    activityCount: document.getElementById('monitorActivityCount'),
    activityDialog: document.getElementById('monitorActivityDialog'),
    activityClose: document.getElementById('monitorActivityClose'),
    activityFull: document.getElementById('monitorActivityFull'),
    latencyEmpty: document.getElementById('monitorLatencyEmpty'),
    drawer: document.getElementById('monitorDrawer'),
    drawerOverlay: document.getElementById('monitorDrawerOverlay'),
    drawerClose: document.getElementById('monitorDrawerClose'),
    drawerTitle: document.getElementById('monitorDrawerTitle'),
    drawerSubtitle: document.getElementById('monitorDrawerSubtitle'),
    drawerKicker: document.getElementById('monitorDrawerKicker'),
    overview: document.getElementById('monitorOverviewPanel'),
    diagnostics: document.getElementById('monitorDiagnosticsPanel'),
    diagnosticsBadge: document.getElementById('monitorDiagnosticsBadge'),
    endpointList: document.getElementById('monitorEndpointList'),
    endpointCount: document.getElementById('monitorEndpointCount'),
    endpointSelected: document.getElementById('monitorEndpointSelected'),
    parameterFields: document.getElementById('monitorParameterFields'),
    changes: document.getElementById('monitorChangesPanel'),
    logsTab: document.getElementById('monitorLogsTab'),
    logSelect: document.getElementById('monitorLogSelect'),
    logRefresh: document.getElementById('monitorLogRefresh'),
    terminalLive: document.getElementById('monitorTerminalLive'),
    logDownload: document.getElementById('monitorLogDownload'),
    logConsole: document.getElementById('monitorLogConsole'),
    logMeta: document.getElementById('monitorLogMeta'),
    terminalStatus: document.getElementById('monitorTerminalStatus'),
    terminalPath: document.getElementById('monitorTerminalPath'),
    terminalCommand: document.getElementById('monitorTerminalCommand'),
    terminalMeta: document.getElementById('monitorTerminalMeta'),
    terminalFollow: document.getElementById('monitorTerminalFollow'),
    tester: document.getElementById('monitorTester'),
    testMethod: document.getElementById('monitorTestMethod'),
    testPath: document.getElementById('monitorTestPath'),
    testQuery: document.getElementById('monitorTestQuery'),
    testBody: document.getElementById('monitorTestBody'),
    bodyField: document.getElementById('monitorBodyField'),
    authStatus: document.getElementById('monitorAuthStatus'),
    authMode: document.getElementById('monitorAuthMode'),
    authTokenField: document.getElementById('monitorAuthTokenField'),
    authToken: document.getElementById('monitorAuthToken'),
    authHeaderFields: document.getElementById('monitorAuthHeaderFields'),
    authHeader: document.getElementById('monitorAuthHeader'),
    authPrefixField: document.getElementById('monitorAuthPrefixField'),
    authPrefix: document.getElementById('monitorAuthPrefix'),
    authHint: document.getElementById('monitorAuthHint'),
    connectionCheck: document.getElementById('monitorConnectionCheck'),
    usageHelp: document.getElementById('monitorUsageHelp'),
    curlPreview: document.getElementById('monitorCurlPreview'),
    copyCurl: document.getElementById('monitorCopyCurl'),
    mutationConfirm: document.getElementById('monitorMutationConfirm'),
    mutationCheckbox: document.getElementById('monitorMutationCheckbox'),
    testSubmit: document.getElementById('monitorTestSubmit'),
    testResponse: document.getElementById('monitorTestResponse'),
    testResponseMeta: document.getElementById('monitorTestResponseMeta'),
    testResponseBody: document.getElementById('monitorTestResponseBody'),
    copyResponse: document.getElementById('monitorCopyResponse'),
    confirmDialog: document.getElementById('monitorConfirmDialog'),
    confirmTitle: document.getElementById('monitorConfirmTitle'),
    confirmText: document.getElementById('monitorConfirmText'),
    confirmAccept: document.getElementById('monitorConfirmAccept'),
    toasts: document.getElementById('monitorToasts')
  };

  var state = {
    data: null,
    services: {},
    order: [],
    currentServiceId: null,
    loading: false,
    paused: false,
    intervalSeconds: Number(localStorage.getItem('spartaMonitorInterval') || (wallMode ? 30 : 60)),
    nextAt: 0,
    charts: {},
    alertsEnabled: localStorage.getItem('spartaMonitorAlerts') === '1',
    previousStatuses: null,
    seenAlertIds: null,
    responseText: '',
    terminalTimer: null,
    terminalPending: false,
    terminalLastOutput: '',
    selectedEndpoint: null,
    selectedEndpointServiceId: null,
    selectedParameters: [],
    curlText: '',
    wallMode: wallMode
  };

  if ([30, 60, 300].indexOf(state.intervalSeconds) < 0) state.intervalSeconds = 60;
  refs.interval.value = String(state.intervalSeconds);

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }
  function parseResponse(response) {
    return response.text().then(function (body) {
      var data;
      try { data = JSON.parse(body); }
      catch (error) { throw new Error('Sparta devolvió una respuesta inválida.'); }
      if (!response.ok || !data.success) throw new Error(data.message || ('HTTP ' + response.status));
      return data;
    });
  }
  function apiFetch(url, options) {
    options = options || {};
    options.credentials = 'same-origin';
    options.headers = Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, options.headers || {});
    return fetch(url, options).then(parseResponse);
  }
  function setBusy(button, busy) {
    if (!button) return;
    button.disabled = busy;
    var icon = button.querySelector('i');
    if (icon) icon.classList.toggle('fa-spin', busy);
  }
  function formatDate(value, withSeconds) {
    if (!value) return 'No disponible';
    var date = new Date(value);
    if (isNaN(date.getTime())) return String(value);
    try {
      return date.toLocaleString('es-MX', withSeconds
        ? { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit', second:'2-digit' }
        : { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' });
    } catch (error) { return String(value); }
  }
  function formatDuration(seconds) {
    if (seconds == null) return 'No disponible';
    seconds = Math.max(0, Number(seconds) || 0);
    var days = Math.floor(seconds / 86400);
    var hours = Math.floor((seconds % 86400) / 3600);
    var minutes = Math.floor((seconds % 3600) / 60);
    return (days ? days + ' d ' : '') + (hours ? hours + ' h ' : '') + minutes + ' min';
  }
  function formatBytes(bytes) {
    bytes = Number(bytes) || 0;
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
  }
  function statusText(status) {
    return status === 'stable' ? 'Estable' : (status === 'degraded' ? 'Revisar' : 'Sin conexión');
  }
  function serviceName(id) {
    return state.services[id] ? state.services[id].name : id;
  }
  function toast(title, message, type) {
    var item = document.createElement('div');
    item.className = 'monitor-toast ' + (type || 'info');
    var icon = type === 'error' ? 'fa-circle-xmark' : (type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-check');
    item.innerHTML = '<i class="fa-solid ' + icon + '"></i><div><strong>' + esc(title) + '</strong><p>' + esc(message) + '</p></div><button type="button" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>';
    item.querySelector('button').addEventListener('click', function () { item.remove(); });
    refs.toasts.appendChild(item);
    window.setTimeout(function () { if (item.isConnected) item.remove(); }, 6500);
  }

  function renderKpis(data) {
    var metrics = data.metrics || {};
    var summary = data.summary || {};
    document.getElementById('monitorKpiStable').textContent = (summary.stable || 0) + ' / ' + (summary.total || 0);
    document.getElementById('monitorKpiStableSub').textContent = (summary.degraded || 0) + ' por revisar · ' + (summary.offline || 0) + ' sin conexión';
    document.getElementById('monitorKpiAvailability').textContent = metrics.availability_24h == null ? '—' : metrics.availability_24h.toFixed(1) + '%';
    document.getElementById('monitorKpiLatency').textContent = metrics.average_latency_24h == null ? '—' : metrics.average_latency_24h + ' ms';
    document.getElementById('monitorKpiIncidents').textContent = String(metrics.incidents_24h || 0);
    document.getElementById('monitorKpiSamples').textContent = String(metrics.samples_24h || 0);
  }

  function primaryLatency(service) {
    var source = service.remote || service.localhost || {};
    return source.latency_ms == null ? '—' : source.latency_ms + ' ms';
  }
  function currentLocation(service) {
    if (service.remote) return 'Cloud Run';
    var local = service.localhost || {};
    return local.port ? 'Puerto ' + local.port : 'Localhost';
  }
  function serviceSparkline(service) {
    if (!state.wallMode) return '';
    var values = (service.history || [])
      .map(function (point) { return Number(point.latency_ms); })
      .filter(function (value) { return isFinite(value); })
      .slice(-24);
    if (values.length < 2) {
      return '<div class="monitor-service-spark empty"><span>Tendencia de latencia</span><small>Recopilando muestras</small></div>';
    }
    var width = 320;
    var height = 58;
    var min = Math.min.apply(Math, values);
    var max = Math.max.apply(Math, values);
    var range = Math.max(1, max - min);
    var points = values.map(function (value, index) {
      var x = 4 + (index * (width - 8) / Math.max(1, values.length - 1));
      var y = height - 5 - ((value - min) / range * (height - 12));
      return x.toFixed(1) + ',' + y.toFixed(1);
    }).join(' ');
    return '<div class="monitor-service-spark"><div><span>Tendencia de latencia</span><small>' + esc(values.length) + ' muestras · ' + esc(min) + '–' + esc(max) + ' ms</small></div><svg viewBox="0 0 ' + width + ' ' + height + '" preserveAspectRatio="none" aria-hidden="true"><polyline points="' + points + '"></polyline></svg></div>';
  }
  function renderGrid() {
    var html = state.order.map(function (id) {
      var service = state.services[id];
      if (!service) return '';
      var repo = service.repository || {};
      var error = service.remote ? service.remote.error : (service.localhost || {}).error;
      var availability = service.availability_24h == null ? '—' : Number(service.availability_24h).toFixed(1) + '%';
      var branch = repo.branch || 'Sin rama Git';
      var source = service.remote || service.localhost || {};
      var wallContext = state.wallMode
        ? '<div class="monitor-wall-service-context"><span><i class="fa-solid fa-location-dot"></i>' + esc(currentLocation(service)) + '</span><span><i class="fa-solid fa-globe"></i>HTTP ' + esc(source.http_status || '—') + '</span></div>' + serviceSparkline(service)
        : '';
      var actions = '';
      if (service.id === 'condonaciones') actions += '<button type="button" class="monitor-service-btn" data-monitor-localhost="condonaciones"><i class="fa-solid fa-terminal"></i>Localhost</button>';
      if (service.docs_url) actions += '<a class="monitor-service-btn" href="' + esc(service.docs_url) + '" target="_blank" rel="noopener"><i class="fa-solid fa-book-open"></i>Swagger</a>';
      actions += '<button type="button" class="monitor-service-btn" data-monitor-health="' + esc(id) + '"><i class="fa-solid fa-stethoscope"></i>Probar</button>';
      actions += '<button type="button" class="monitor-service-btn" data-monitor-open="' + esc(id) + '">Detalle<i class="fa-solid fa-arrow-right"></i></button>';
      return '<article class="monitor-service-card ' + esc(service.status) + '">' +
        '<div class="monitor-service-top"><div class="monitor-service-title-row"><h3>' + esc(service.name) + '</h3><span class="monitor-state ' + esc(service.status) + '">' + esc(statusText(service.status)) + '</span></div><p class="monitor-service-desc">' + esc(service.description) + '</p></div>' +
        '<div class="monitor-service-metrics"><div class="monitor-service-metric"><span>Latencia</span><strong>' + esc(primaryLatency(service)) + '</strong></div><div class="monitor-service-metric"><span>Disponibilidad</span><strong>' + esc(availability) + '</strong></div><div class="monitor-service-metric"><span>Endpoints</span><strong>' + esc(service.endpoint_count || 0) + '</strong></div></div>' +
        wallContext +
        (service.status !== 'stable' && error ? '<div class="monitor-service-alert"><i class="fa-solid fa-triangle-exclamation"></i> ' + esc(error) + '</div>' : '') +
        '<div class="monitor-repo-row"><i class="fa-solid fa-code-branch"></i><span title="' + esc(branch) + '">' + esc(branch) + '</span>' + (repo.dirty ? '<span class="monitor-repo-dirty">' + esc(repo.changed_files || 0) + ' cambios locales</span>' : '<span class="monitor-repo-dirty" style="color:#15803d">Limpio</span>') + '</div>' +
        '<div class="monitor-service-actions">' + actions + '</div></article>';
    }).join('');
    refs.grid.innerHTML = html || '<div class="monitor-empty">No hay servicios configurados.</div>';
  }

  function chartColors() {
    var dark = document.body.classList.contains('dark-mode');
    return { text: dark ? '#cbd5e1' : '#475569', grid: dark ? 'rgba(148,163,184,.16)' : 'rgba(148,163,184,.2)' };
  }
  function renderCharts() {
    if (typeof window.Chart === 'undefined') {
      window.setTimeout(renderCharts, 250);
      return;
    }
    var colors = chartColors();
    var palette = ['#1a52a8', '#d28b14', '#dd6b35'];
    var dash = [[], [6, 3], [2, 3]];
    var timestamps = {};
    state.order.forEach(function (id) {
      (state.services[id].history || []).forEach(function (point) { timestamps[String(point.timestamp)] = point.at; });
    });
    var ordered = Object.keys(timestamps).map(Number).sort(function (a, b) { return a - b; }).slice(-72);
    var labels = ordered.map(function (timestamp) {
      var date = new Date(timestamp * 1000);
      return date.toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit' });
    });
    var datasets = state.order.map(function (id, index) {
      var map = {};
      (state.services[id].history || []).forEach(function (point) { map[String(point.timestamp)] = point.latency_ms; });
      return {
        label: state.services[id].name,
        data: ordered.map(function (timestamp) { return map[String(timestamp)] == null ? null : map[String(timestamp)]; }),
        borderColor: palette[index % palette.length],
        backgroundColor: palette[index % palette.length],
        borderWidth: 2,
        borderDash: dash[index % dash.length],
        pointRadius: ordered.length < 16 ? 2.5 : 0,
        pointHoverRadius: 4,
        tension: .28,
        spanGaps: true
      };
    });
    if (state.charts.latency) state.charts.latency.destroy();
    state.charts.latency = new Chart(document.getElementById('monitorLatencyChart'), {
      type: 'line', data: { labels: labels, datasets: datasets },
      options: {
        responsive: true, maintainAspectRatio: false, animation: { duration: 250 }, interaction: { mode:'index', intersect:false },
        plugins: { legend: { position:'bottom', labels:{ color:colors.text, usePointStyle:true, boxWidth:8, font:{ size:9 } } }, tooltip:{ callbacks:{ label:function(ctx){ return ctx.dataset.label + ': ' + (ctx.parsed.y == null ? 'sin dato' : ctx.parsed.y + ' ms'); } } } },
        scales: { x:{ ticks:{ color:colors.text, maxTicksLimit:8, font:{ size:8 } }, grid:{ display:false } }, y:{ beginAtZero:true, title:{ display:true, text:'ms', color:colors.text, font:{size:9} }, ticks:{ color:colors.text, font:{size:8} }, grid:{ color:colors.grid } } }
      }
    });
    refs.latencyEmpty.hidden = ordered.length >= 2;
    if (ordered.length >= 2 && ordered.length < 8) refs.latencyEmpty.textContent = 'Historial en formación: hay ' + ordered.length + ' muestras; la tendencia se consolidará a partir de 8.';

    if (state.charts.availability) state.charts.availability.destroy();
    state.charts.availability = new Chart(document.getElementById('monitorAvailabilityChart'), {
      type: 'bar',
      data: { labels: state.order.map(function (id) { return state.services[id].name.replace('API ', ''); }), datasets:[{ label:'Disponibilidad', data:state.order.map(function(id){ return state.services[id].availability_24h == null ? 0 : state.services[id].availability_24h; }), backgroundColor:'#1a52a8', borderColor:'#123d83', borderWidth:1, borderRadius:5 }] },
      options: { indexAxis:'y', responsive:true, maintainAspectRatio:false, animation:{duration:250}, plugins:{ legend:{display:false}, tooltip:{callbacks:{label:function(ctx){return Number(ctx.parsed.x).toFixed(1)+'%';}}} }, scales:{x:{beginAtZero:true,max:100,ticks:{color:colors.text,callback:function(v){return v+'%';},font:{size:8}},grid:{color:colors.grid}},y:{ticks:{color:colors.text,font:{size:8}},grid:{display:false}}} }
    });
  }

  function eventMarkup(event) {
    return '<article class="monitor-event ' + esc(event.severity || 'info') + '"><span class="monitor-event-dot"></span><div><strong>' + esc(serviceName(event.service)) + '</strong><p>' + esc(event.message) + '</p></div><time datetime="' + esc(event.at) + '">' + esc(formatDate(event.at, true)) + '</time></article>';
  }
  function renderTimeline(events) {
    events = Array.isArray(events) ? events : [];
    if (refs.activityCount) refs.activityCount.textContent = String(events.length);
    if (refs.activityOpen) refs.activityOpen.disabled = events.length === 0;
    if (!events.length) {
      refs.timeline.innerHTML = '<div class="monitor-empty">Todavía no hay eventos registrados.</div>';
      if (refs.activityFull) refs.activityFull.innerHTML = '<div class="monitor-empty">Todavía no hay eventos registrados.</div>';
      return;
    }
    refs.timeline.innerHTML = events.slice(0, state.wallMode ? 3 : 16).map(eventMarkup).join('');
    if (refs.activityFull) refs.activityFull.innerHTML = events.slice(0, 40).map(eventMarkup).join('');
  }

  function renderOverview(service) {
    var remote = service.remote || null;
    var local = service.localhost || {};
    var process = service.process || {};
    var source = remote || local;
    var processPanel = '';
    if (service.id === 'condonaciones') {
      var buttons = '';
      if (service.status === 'offline') {
        buttons += '<button type="button" class="monitor-primary-btn" data-process-action="iniciar"><i class="fa-solid fa-play"></i>Iniciar Python</button>';
      } else if (process.owned) {
        buttons += '<button type="button" class="monitor-secondary-btn" data-process-action="reiniciar"><i class="fa-solid fa-rotate"></i>Reiniciar</button><button type="button" class="monitor-danger-btn" data-process-action="detener"><i class="fa-solid fa-stop"></i>Detener</button>';
      } else {
        buttons += '<button type="button" class="monitor-secondary-btn" disabled><i class="fa-solid fa-shield-halved"></i>Proceso externo: solo lectura</button>';
      }
      processPanel = '<section class="monitor-process-panel"><div class="monitor-process-head"><div><h3>Proceso Python local</h3><p>' + esc(process.source || 'Externo o no identificado') + (process.pid ? ' · PID ' + esc(process.pid) : '') + '</p></div><span class="monitor-state ' + esc(local.status || 'offline') + '">' + esc(statusText(local.status || 'offline')) + '</span></div>' +
        '<div class="monitor-detail-grid"><div class="monitor-detail-card"><span>Puerto</span><strong>' + esc(local.port || '—') + '</strong></div><div class="monitor-detail-card"><span>Inicio</span><strong>' + esc(process.started_at ? formatDate(process.started_at) : 'No identificado') + '</strong></div><div class="monitor-detail-card"><span>Tiempo activo</span><strong>' + esc(formatDuration(process.uptime_seconds)) + '</strong></div></div>' +
        '<div class="monitor-process-actions">' + buttons + '</div><div class="monitor-inline-note"><i class="fa-solid fa-circle-info"></i> Sparta solo puede detener o reiniciar procesos que fueron iniciados desde este módulo.</div></section>';
    }
    var links = '';
    if (service.id === 'condonaciones') links += '<button type="button" class="monitor-primary-btn" data-monitor-localhost="condonaciones"><i class="fa-solid fa-terminal"></i>Localhost / Terminal</button>';
    if (service.docs_url) links += '<a class="monitor-secondary-btn" href="' + esc(service.docs_url) + '" target="_blank" rel="noopener"><i class="fa-solid fa-book-open"></i>Abrir Swagger</a>';
    refs.overview.innerHTML = '<div class="monitor-overview-hero"><div><h3>' + esc(statusText(service.status)) + '</h3><p>Última lectura desde ' + esc(currentLocation(service)) + '</p></div><span class="monitor-state ' + esc(service.status) + '">' + esc(statusText(service.status)) + '</span></div>' +
      '<div class="monitor-detail-grid"><div class="monitor-detail-card"><span>HTTP</span><strong>' + esc(source.http_status || '—') + '</strong></div><div class="monitor-detail-card"><span>Latencia actual</span><strong>' + esc(primaryLatency(service)) + '</strong></div><div class="monitor-detail-card"><span>Disponibilidad 24 h</span><strong>' + esc(service.availability_24h == null ? '—' : Number(service.availability_24h).toFixed(1) + '%') + '</strong></div><div class="monitor-detail-card"><span>Versión API</span><strong>' + esc(service.api_version || '—') + '</strong></div><div class="monitor-detail-card"><span>Muestras 24 h</span><strong>' + esc(service.samples_24h || 0) + '</strong></div><div class="monitor-detail-card"><span>Última caída</span><strong>' + esc(formatDate(service.last_outage_at)) + '</strong></div></div>' +
      '<div class="monitor-process-actions">' + links + '</div>' + processPanel;
  }

  function renderEndpoints(service) {
    var endpoints = Array.isArray(service.endpoints) ? service.endpoints : [];
    refs.endpointCount.textContent = endpoints.length + ' endpoints';
    var previousKey = state.selectedEndpoint ? endpointKey(state.selectedEndpoint) : '';
    var sameService = state.selectedEndpointServiceId === service.id;
    var selectedIndex = sameService ? endpoints.findIndex(function (endpoint) { return endpointKey(endpoint) === previousKey; }) : -1;
    if (selectedIndex < 0 && endpoints.length) {
      selectedIndex = endpoints.findIndex(function (endpoint) { return String(endpoint.method).toUpperCase() === 'GET' && endpoint.path === '/health'; });
      if (selectedIndex < 0) selectedIndex = endpoints.findIndex(function (endpoint) { return String(endpoint.method).toUpperCase() === 'GET'; });
      if (selectedIndex < 0) selectedIndex = 0;
    }
    refs.endpointList.innerHTML = endpoints.length ? endpoints.map(function (endpoint, index) {
      var method = String(endpoint.method || 'GET').toUpperCase();
      var parameters = endpointParameters(endpoint);
      var authRequired = !!endpoint.security_required || parameters.some(function (parameter) { return parameter.auth_parameter; });
      var meta = [];
      var editableCount = parameters.filter(function (parameter) { return !parameter.auth_parameter && (parameter.in === 'path' || parameter.in === 'query'); }).length;
      if (editableCount) meta.push('<span><i class="fa-solid fa-sliders"></i>' + editableCount + ' campos</span>');
      if (endpoint.request_body) meta.push('<span><i class="fa-solid fa-brackets-curly"></i>JSON</span>');
      if (authRequired) meta.push('<span><i class="fa-solid fa-key"></i>Token</span>');
      return '<button type="button" class="monitor-endpoint-row' + (index === selectedIndex ? ' selected' : '') + '" data-endpoint-index="' + index + '"><span class="monitor-method ' + esc(method) + '">' + esc(method) + '</span><span class="monitor-endpoint-path">' + esc(endpoint.path || '/') + (endpoint.summary ? '<small>' + esc(endpoint.summary) + '</small>' : '') + (meta.length ? '<span class="monitor-endpoint-meta">' + meta.join('') + '</span>' : '') + '</span><i class="fa-solid fa-chevron-right monitor-endpoint-arrow"></i></button>';
    }).join('') : '<div class="monitor-empty">No se encontró inventario OpenAPI.</div>';
    if (selectedIndex >= 0) {
      state.selectedEndpoint = endpoints[selectedIndex];
      state.selectedEndpointServiceId = service.id;
      if (!sameService || previousKey !== endpointKey(endpoints[selectedIndex])) configureEndpointTester(service, endpoints[selectedIndex], !sameService);
    }
  }

  function endpointKey(endpoint) {
    return String((endpoint || {}).method || '').toUpperCase() + ' ' + String((endpoint || {}).path || '');
  }

  function endpointParameters(endpoint) {
    var parameters = Array.isArray((endpoint || {}).parameters) ? endpoint.parameters.slice() : [];
    var names = {};
    parameters.forEach(function (parameter) { names[String(parameter.in) + ':' + String(parameter.name)] = true; });
    var matches = String((endpoint || {}).path || '').match(/\{[^}]+\}/g) || [];
    matches.forEach(function (match) {
      var name = match.slice(1, -1);
      if (!names['path:' + name]) parameters.push({name:name, in:'path', required:true, type:'string', description:'Parámetro requerido por la ruta.'});
    });
    return parameters;
  }

  function selectEndpoint(index) {
    var service = state.services[state.currentServiceId];
    if (!service || !Array.isArray(service.endpoints) || !service.endpoints[index]) return;
    state.selectedEndpoint = service.endpoints[index];
    state.selectedEndpointServiceId = service.id;
    refs.endpointList.querySelectorAll('[data-endpoint-index]').forEach(function (row) { row.classList.toggle('selected', Number(row.getAttribute('data-endpoint-index')) === index); });
    configureEndpointTester(service, state.selectedEndpoint, false);
  }

  function parameterInitialValue(parameter) {
    if (parameter.example !== undefined && parameter.example !== null) return parameter.example;
    if (parameter.default !== undefined && parameter.default !== null) return parameter.default;
    if (Array.isArray(parameter.enum) && parameter.enum.length) return parameter.required ? parameter.enum[0] : '';
    return '';
  }

  function renderParameterFields(endpoint) {
    state.selectedParameters = endpointParameters(endpoint).filter(function (parameter) { return !parameter.auth_parameter && (parameter.in === 'path' || parameter.in === 'query'); });
    if (!state.selectedParameters.length) {
      refs.parameterFields.innerHTML = '<div class="monitor-parameter-empty"><i class="fa-solid fa-circle-check"></i>Este endpoint no requiere parámetros de ruta o query.</div>';
      return;
    }
    refs.parameterFields.innerHTML = '<div class="monitor-fields-title"><span>Campos de la solicitud</span><small>' + state.selectedParameters.length + ' detectados por OpenAPI</small></div>' + state.selectedParameters.map(function (parameter, index) {
      var required = parameter.required ? ' required' : '';
      var badge = '<em class="' + esc(parameter.in) + '">' + esc(String(parameter.in).toUpperCase()) + '</em>';
      var initial = parameterInitialValue(parameter);
      var input;
      if (Array.isArray(parameter.enum) && parameter.enum.length) {
        input = '<select class="monitor-select" data-monitor-param-index="' + index + '"' + required + '><option value="">Selecciona…</option>' + parameter.enum.map(function (value) { return '<option value="' + esc(value) + '"' + (String(value) === String(initial) ? ' selected' : '') + '>' + esc(value) + '</option>'; }).join('') + '</select>';
      } else if (parameter.type === 'boolean') {
        input = '<select class="monitor-select" data-monitor-param-index="' + index + '"' + required + '><option value="">Selecciona…</option><option value="true"' + (initial === true ? ' selected' : '') + '>true</option><option value="false"' + (initial === false ? ' selected' : '') + '>false</option></select>';
      } else {
        var type = parameter.type === 'integer' || parameter.type === 'number' ? 'number' : 'text';
        var step = parameter.type === 'number' ? ' step="any"' : '';
        var range = (parameter.minimum !== undefined && parameter.minimum !== null ? ' min="' + esc(parameter.minimum) + '"' : '') + (parameter.maximum !== undefined && parameter.maximum !== null ? ' max="' + esc(parameter.maximum) + '"' : '');
        input = '<input class="monitor-input" data-monitor-param-index="' + index + '" type="' + type + '"' + step + range + required + ' value="' + esc(initial) + '" placeholder="' + esc(parameter.name) + '">';
      }
      return '<label class="monitor-parameter-field"><span>' + esc(parameter.name) + (parameter.required ? '<b>*</b>' : '') + badge + '</span>' + input + (parameter.description ? '<small>' + esc(parameter.description) + '</small>' : '') + '</label>';
    }).join('');
  }

  function configureEndpointTester(service, endpoint, clearToken) {
    if (clearToken) refs.authToken.value = '';
    refs.endpointSelected.hidden = false;
    refs.endpointSelected.innerHTML = '<span class="monitor-method ' + esc(String(endpoint.method || 'GET').toUpperCase()) + '">' + esc(String(endpoint.method || 'GET').toUpperCase()) + '</span><div><strong>' + esc(endpoint.path || '/') + '</strong><small>' + esc(endpoint.summary || endpoint.description || 'Endpoint documentado por OpenAPI') + '</small></div>';
    refs.testMethod.value = String(endpoint.method || 'GET').toUpperCase();
    var executable = ['GET', 'POST', 'PUT', 'PATCH'].indexOf(refs.testMethod.value) >= 0;
    refs.testSubmit.disabled = !executable;
    refs.testSubmit.innerHTML = executable ? '<i class="fa-solid fa-play"></i>Ejecutar prueba completa' : '<i class="fa-solid fa-shield-halved"></i>DELETE sólo documentación';
    renderParameterFields(endpoint);
    var requestBody = endpoint.request_body || null;
    refs.testBody.value = requestBody ? JSON.stringify(requestBody.example == null ? {} : requestBody.example, null, 2) : '{}';
    configureEndpointAuth(service, endpoint);
    syncParameterFields();
    updateTesterMutation();
    setAuthStatus('idle', 'Sin verificar');
    refs.testResponse.hidden = true;
  }

  function syncParameterFields() {
    if (!state.selectedEndpoint) return;
    var path = String(state.selectedEndpoint.path || '/');
    var query = {};
    refs.parameterFields.querySelectorAll('[data-monitor-param-index]').forEach(function (input) {
      var parameter = state.selectedParameters[Number(input.getAttribute('data-monitor-param-index'))];
      if (!parameter) return;
      var value = String(input.value == null ? '' : input.value).trim();
      if (parameter.in === 'path') {
        if (value !== '') path = path.split('{' + parameter.name + '}').join(encodeURIComponent(value));
      } else if (parameter.in === 'query' && value !== '') {
        query[parameter.name] = coerceParameterValue(value, parameter.type);
      }
    });
    refs.testPath.value = path;
    refs.testQuery.value = JSON.stringify(query, null, 2);
    updateCurlPreview();
  }

  function coerceParameterValue(value, type) {
    if (type === 'integer') return parseInt(value, 10);
    if (type === 'number') return Number(value);
    if (type === 'boolean') return value === 'true';
    return value;
  }
  function renderChanges(service) {
    var repo = service.repository || {};
    var commits = service.modifications || [];
    refs.changes.innerHTML = '<div class="monitor-repo-summary"><span class="monitor-repo-chip">Rama<strong>' + esc(repo.branch || '—') + '</strong></span><span class="monitor-repo-chip">Estado<strong>' + (repo.dirty ? esc((repo.changed_files || 0) + ' cambios locales') : 'Limpio') + '</strong></span><span class="monitor-repo-chip">Adelante<strong>' + esc(repo.ahead || 0) + '</strong></span><span class="monitor-repo-chip">Atrás<strong>' + esc(repo.behind || 0) + '</strong></span></div>' +
      '<div class="monitor-panel-title"><h3>Últimos commits</h3><span>' + commits.length + ' registros</span></div>' +
      (commits.length ? commits.map(function (commit) { return '<article class="monitor-commit"><span class="monitor-commit-hash">' + esc(commit.hash || '') + '</span><span><span class="monitor-commit-subject">' + esc(commit.subject || 'Sin descripción') + '</span><span class="monitor-commit-meta">' + esc(formatDate(commit.date)) + ' · ' + esc(commit.author || 'Autor no disponible') + '</span></span></article>'; }).join('') : '<div class="monitor-empty">El historial Git no está disponible.</div>');
  }

  function renderDiagnostics(service) {
    var diagnostic = service.diagnostic || {};
    var incidents = Array.isArray(service.incidents) ? service.incidents : [];
    var active = service.active_incident || incidents.find(function (incident) { return incident.status === 'active'; }) || null;
    var level = diagnostic.level || (service.status === 'offline' ? 'critical' : service.status === 'degraded' ? 'warning' : 'ok');
    var evidence = Array.isArray(diagnostic.evidence) ? diagnostic.evidence : [];
    var actions = Array.isArray(diagnostic.actions) ? diagnostic.actions : [];
    var channels = (state.data && state.data.notification_channels) || {};
    var chat = channels.google_chat || {};
    var email = channels.email_fallback || {};
    var diagnosisLabel = diagnostic.confirmed ? 'Hallazgo confirmado' : 'Causa probable';
    var incidentHeadline = active
      ? '<div class="monitor-active-incident"><span><i class="fa-solid fa-triangle-exclamation"></i>Incidente activo</span><strong>' + esc(active.id || '') + '</strong><small>Detectado ' + esc(formatDate(active.opened_at, true)) + '</small></div>'
      : '<div class="monitor-no-incident"><i class="fa-solid fa-circle-check"></i><span><strong>Sin caída activa</strong><small>El diagnóstico corresponde a la lectura más reciente.</small></span></div>';
    var evidenceHtml = evidence.length
      ? evidence.map(function (item) { return '<li><i class="fa-solid fa-magnifying-glass"></i><span>' + esc(item) + '</span></li>'; }).join('')
      : '<li><i class="fa-solid fa-hourglass-half"></i><span>Esperando evidencia de la siguiente comprobación.</span></li>';
    var actionsHtml = actions.length
      ? actions.map(function (item, index) { return '<li><b>' + (index + 1) + '</b><span>' + esc(item) + '</span></li>'; }).join('')
      : '<li><b>1</b><span>Volver a comprobar el servicio.</span></li>';
    var historyHtml = incidents.length ? incidents.slice(0, 12).map(function (incident) {
      var itemDiagnostic = incident.diagnostic || {};
      var resolved = incident.status === 'resolved';
      var duration = resolved ? formatDuration(incident.duration_seconds) : 'En curso';
      var delivery = incident.notifications || {};
      var chatStatus = (delivery.google_chat || {}).status;
      var notifyText = chatStatus === 'sent' ? 'Google Chat enviado' : chatStatus === 'failed' ? 'Google Chat falló' : 'Sin envío externo';
      return '<article class="monitor-incident-row ' + (resolved ? 'resolved' : 'active') + '"><span class="monitor-incident-icon"><i class="fa-solid ' + (resolved ? 'fa-circle-check' : 'fa-triangle-exclamation') + '"></i></span><div><div class="monitor-incident-row-head"><strong>' + esc(incident.id || 'Incidente') + '</strong><span>' + (resolved ? 'Resuelto' : 'Activo') + '</span></div><p>' + esc(itemDiagnostic.title || 'Causa no determinada') + '</p><small>' + esc(formatDate(incident.opened_at, true)) + ' · ' + esc(duration) + ' · ' + esc(notifyText) + '</small></div></article>';
    }).join('') : '<div class="monitor-empty monitor-diagnostic-empty">No hay caídas registradas para este servicio.</div>';

    refs.diagnosticsBadge.hidden = !active;
    refs.diagnosticsBadge.textContent = active ? '1' : '0';
    refs.diagnostics.innerHTML = incidentHeadline +
      '<section class="monitor-diagnostic-hero ' + esc(level) + '"><div class="monitor-diagnostic-icon"><i class="fa-solid ' + (level === 'critical' ? 'fa-circle-xmark' : level === 'warning' ? 'fa-triangle-exclamation' : 'fa-shield-heart') + '"></i></div><div><span>' + esc(diagnosisLabel) + ' · ' + esc(diagnostic.confidence_label || 'Confianza por determinar') + '</span><h3>' + esc(diagnostic.title || 'Diagnóstico no disponible') + '</h3><p>' + esc(diagnostic.summary || 'Vuelve a comprobar para generar el diagnóstico.') + '</p></div></section>' +
      '<div class="monitor-diagnostic-columns"><section class="monitor-diagnostic-block"><div class="monitor-panel-title"><h3>Evidencia encontrada</h3><span>Lectura actual</span></div><ul class="monitor-evidence-list">' + evidenceHtml + '</ul></section><section class="monitor-diagnostic-block"><div class="monitor-panel-title"><h3>Qué hacer ahora</h3><span>En orden</span></div><ol class="monitor-action-list">' + actionsHtml + '</ol></section></div>' +
      '<section class="monitor-notification-block"><div class="monitor-panel-title"><h3>Avisos externos</h3><span>Sólo caída y reactivación</span></div><div class="monitor-channel-grid"><article class="' + (chat.enabled ? 'ready' : 'pending') + '"><i class="fa-brands fa-google"></i><div><strong>Google Chat</strong><span>' + esc(chat.label || 'Pendiente de webhook') + '</span></div></article><article class="' + (email.enabled ? 'ready' : 'pending') + '"><i class="fa-solid fa-envelope"></i><div><strong>Correo de respaldo</strong><span>' + esc(email.label || 'Pendiente de configuración') + '</span></div></article></div><p><i class="fa-solid fa-circle-info"></i> El correo se intentará únicamente cuando un webhook ya configurado falle.</p></section>' +
      '<div class="monitor-diagnostic-actions"><button type="button" class="monitor-primary-btn" data-diagnostic-refresh="' + esc(service.id) + '"><i class="fa-solid fa-stethoscope"></i>Volver a diagnosticar</button>' + (service.id === 'condonaciones' ? '<button type="button" class="monitor-secondary-btn" data-diagnostic-logs><i class="fa-solid fa-terminal"></i>Abrir log local</button>' : '') + '<a class="monitor-secondary-btn" href="' + esc((state.data && state.data.incident_log_url) || '/monitoreo/incidentesLog') + '"><i class="fa-solid fa-file-arrow-down"></i>Descargar .log de incidentes</a></div>' +
      '<section class="monitor-incident-history"><div class="monitor-panel-title"><h3>Historial de caídas</h3><span>' + incidents.length + ' registros</span></div>' + historyHtml + '</section>';
  }

  function renderDrawer() {
    var service = state.services[state.currentServiceId];
    if (!service) return;
    refs.drawerTitle.textContent = service.name;
    refs.drawerSubtitle.textContent = service.description;
    refs.drawerKicker.textContent = statusText(service.status) + ' · ' + currentLocation(service);
    refs.logsTab.hidden = service.id !== 'condonaciones';
    renderOverview(service);
    renderDiagnostics(service);
    renderEndpoints(service);
    renderChanges(service);
  }
  function openDrawer(id, tab) {
    if (!state.services[id]) return;
    state.currentServiceId = id;
    renderDrawer();
    refs.drawerOverlay.hidden = false;
    refs.drawer.classList.add('open');
    refs.drawer.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    switchTab(tab || (state.services[id].status === 'offline' ? 'diagnostics' : 'overview'));
  }
  function closeDrawer() {
    stopTerminalStreaming();
    refs.drawer.classList.remove('open');
    refs.drawer.setAttribute('aria-hidden', 'true');
    refs.drawerOverlay.hidden = true;
    document.body.style.overflow = '';
  }
  function switchTab(name) {
    if (name === 'logs' && state.currentServiceId !== 'condonaciones') name = 'overview';
    document.querySelectorAll('[data-monitor-tab]').forEach(function (button) { button.classList.toggle('active', button.getAttribute('data-monitor-tab') === name); });
    document.querySelectorAll('[data-monitor-panel]').forEach(function (panel) { panel.classList.toggle('active', panel.getAttribute('data-monitor-panel') === name); });
    if (name === 'logs') {
      loadLogs('', false);
      startTerminalStreaming(true);
    } else {
      stopTerminalStreaming();
    }
  }

  function renderTerminal(data) {
    if (!data) return;
    var terminalStatus = data.status || 'stopped';
    refs.terminalStatus.className = 'monitor-terminal-status ' + terminalStatus;
    refs.terminalStatus.innerHTML = '<i></i>' + esc(data.status_label || (data.running ? 'Activo' : 'Detenido'));
    refs.terminalPath.textContent = data.workspace || 'Ruta no disponible';
    refs.terminalCommand.textContent = data.command || 'python main.py';

    var output = data.output || '';
    if (!output) {
      output = data.running
        ? 'Proceso activo. Esperando nueva salida de python main.py...'
        : 'El proceso no está activo. Pulsa Localhost para ejecutar python main.py.';
    }
    if (output !== state.terminalLastOutput) {
      state.terminalLastOutput = output;
      refs.logConsole.textContent = output;
      if (refs.terminalFollow.checked) refs.logConsole.scrollTop = refs.logConsole.scrollHeight;
    }

    var details = [data.live_output ? 'Salida en vivo' : (data.status === 'external' ? 'Último log disponible' : 'Salida guardada')];
    details.push(data.status_label || 'Sin estado');
    if (data.pid) details.push('PID ' + data.pid);
    if (data.log_size != null) details.push(formatBytes(data.log_size));
    if (data.updated_at) details.push('Actualizado ' + formatDate(data.updated_at, true));
    refs.terminalMeta.textContent = details.join(' · ');

    if (data.download_url) {
      refs.logDownload.href = data.download_url;
      refs.logDownload.classList.remove('disabled');
    }
  }

  function pollTerminal() {
    if (state.terminalPending || state.currentServiceId !== 'condonaciones') return;
    state.terminalPending = true;
    apiFetch('/monitoreo/terminal')
      .then(renderTerminal)
      .catch(function (error) {
        refs.terminalStatus.className = 'monitor-terminal-status stopped';
        refs.terminalStatus.innerHTML = '<i></i>Sin conexión';
        refs.terminalMeta.textContent = error.message;
      })
      .finally(function () { state.terminalPending = false; });
  }

  function startTerminalStreaming(immediate) {
    stopTerminalStreaming();
    if (immediate !== false) pollTerminal();
    state.terminalTimer = window.setInterval(pollTerminal, 1000);
  }

  function stopTerminalStreaming() {
    if (state.terminalTimer) window.clearInterval(state.terminalTimer);
    state.terminalTimer = null;
  }

  function loadLogs(file, displayContent) {
    if (displayContent) refs.logConsole.textContent = 'Consultando archivo de log…';
    setBusy(refs.logRefresh, true);
    apiFetch('/monitoreo/logs' + (file ? '?archivo=' + encodeURIComponent(file) : ''))
      .then(function (data) {
        refs.logSelect.innerHTML = data.files.length ? data.files.map(function (item) { return '<option value="' + esc(item.name) + '"' + (item.name === data.selected ? ' selected' : '') + '>' + esc(item.name) + ' · ' + esc(formatBytes(item.size)) + '</option>'; }).join('') : '<option value="">Sin archivos disponibles</option>';
        var selected = (data.files || []).find(function (item) { return item.name === data.selected; });
        refs.logMeta.textContent = selected ? formatBytes(selected.size) + ' · Modificado ' + formatDate(selected.modified_at, true) + ' · Se muestran los últimos 40 KB' : 'No hay logs generados por el módulo.';
        if (displayContent) {
          refs.logConsole.textContent = data.content || 'El archivo está vacío o todavía no existe.';
          state.terminalLastOutput = refs.logConsole.textContent;
          refs.terminalStatus.className = 'monitor-terminal-status external';
          refs.terminalStatus.innerHTML = '<i></i>Archivo histórico';
          refs.terminalMeta.textContent = selected ? 'Lectura histórica · ' + formatBytes(selected.size) + ' · ' + formatDate(selected.modified_at, true) : 'Sin archivo seleccionado';
          if (refs.terminalFollow.checked) refs.logConsole.scrollTop = refs.logConsole.scrollHeight;
        }
        if (data.selected) {
          refs.logDownload.href = '/monitoreo/log?archivo=' + encodeURIComponent(data.selected);
          refs.logDownload.classList.remove('disabled');
        } else {
          refs.logDownload.href = '#'; refs.logDownload.classList.add('disabled');
        }
      })
      .catch(function (error) { if (displayContent) refs.logConsole.textContent = 'Error: ' + error.message; toast('No se pudieron cargar los logs', error.message, 'error'); })
      .finally(function () { setBusy(refs.logRefresh, false); });
  }

  function runLocalhost(button) {
    openDrawer('condonaciones', 'logs');
    refs.terminalStatus.className = 'monitor-terminal-status external';
    refs.terminalStatus.innerHTML = '<i></i>Iniciando';
    refs.terminalMeta.textContent = 'Ejecutando python main.py desde el workspace configurado…';
    setBusy(button, true);
    apiFetch('/monitoreo/accion', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({servicio:'condonaciones', accion:'iniciar'})
    })
      .then(function (data) {
        if (data.terminal) renderTerminal(data.terminal);
        toast('Terminal de Condonaciones', data.message || 'Proceso local listo.', 'info');
        loadLogs('', false);
        return loadAll(false);
      })
      .catch(function (error) {
        toast('No se pudo iniciar localhost', error.message, 'error');
        pollTerminal();
      })
      .finally(function () {
        setBusy(button, false);
        startTerminalStreaming(true);
      });
  }

  function confirmAction(action) {
    var labels = { iniciar:['Iniciar API local','Sparta ejecutará python main.py y esperará la respuesta de /health.'], reiniciar:['Reiniciar API local','El proceso controlado por Sparta se detendrá y volverá a iniciarse.'], detener:['Detener API local','Se cerrará el proceso Python y localhost dejará de responder.'] };
    var data = labels[action] || ['Confirmar acción','La acción afectará el proceso local.'];
    if (!refs.confirmDialog || typeof refs.confirmDialog.showModal !== 'function') return Promise.resolve(window.confirm(data[1]));
    refs.confirmTitle.textContent = data[0]; refs.confirmText.textContent = data[1];
    refs.confirmAccept.className = action === 'iniciar' ? 'monitor-primary-btn' : 'monitor-danger-btn';
    refs.confirmDialog.showModal();
    return new Promise(function (resolve) {
      refs.confirmDialog.addEventListener('close', function onClose() {
        refs.confirmDialog.removeEventListener('close', onClose);
        resolve(refs.confirmDialog.returnValue === 'confirm');
      });
    });
  }
  function processAction(action, button) {
    confirmAction(action).then(function (confirmed) {
      if (!confirmed) return;
      setBusy(button, true);
      refs.updated.textContent = (action === 'iniciar' ? 'Iniciando' : action === 'reiniciar' ? 'Reiniciando' : 'Deteniendo') + ' Condonaciones…';
      return apiFetch('/monitoreo/accion', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({servicio:'condonaciones', accion:action}) })
        .then(function (data) { toast('Proceso actualizado', data.message || 'Acción completada.', 'info'); return loadAll(false); })
        .catch(function (error) { toast('No se completó la acción', error.message, 'error'); })
        .finally(function () { setBusy(button, false); });
    });
  }

  function configureEndpointAuth(service, endpoint) {
    var schemes = Array.isArray(service.security_schemes) ? service.security_schemes : [];
    var requiredKeys = Array.isArray(endpoint.security) ? endpoint.security : [];
    var candidates = requiredKeys.length ? schemes.filter(function (scheme) { return requiredKeys.indexOf(scheme.key) >= 0; }) : [];
    var authParameters = endpointParameters(endpoint).filter(function (parameter) { return parameter.auth_parameter; });
    var authorizationParameter = authParameters.find(function (parameter) { return String(parameter.name).toLowerCase() === 'authorization'; });
    var apiKeyParameter = authParameters.find(function (parameter) { return String(parameter.name).toLowerCase() !== 'authorization'; });
    var mode = 'none';
    var header = 'Authorization';
    var apiKeyHeader = '';
    var prefix = '';
    var descriptions = [];
    if (apiKeyParameter) apiKeyHeader = apiKeyParameter.name;

    candidates.forEach(function (scheme) {
      if (scheme.type === 'http' && scheme.scheme === 'bearer') {
        descriptions.push('Bearer');
        if (mode === 'none') mode = 'bearer';
      } else if (scheme.type === 'apikey' && scheme.header) {
        descriptions.push(scheme.header);
        apiKeyHeader = apiKeyHeader || scheme.header;
        if (mode === 'none') { mode = 'api_key'; header = scheme.header; }
      }
    });
    authParameters.forEach(function (parameter) {
      if (descriptions.indexOf(parameter.name) < 0) descriptions.push(parameter.name);
    });
    if (mode === 'none' && authorizationParameter) {
      header = authorizationParameter.name || 'Authorization';
      mode = 'bearer';
    } else if (mode === 'none' && apiKeyParameter) {
      header = apiKeyParameter.name;
      mode = 'api_key';
    }

    refs.authMode.value = mode;
    refs.authHeader.value = header;
    refs.authHeader.dataset.suggested = apiKeyHeader || header;
    refs.authPrefix.value = prefix;
    if (endpoint.security_required) {
      refs.authHint.textContent = 'Requerido por OpenAPI' + (descriptions.length ? ': ' + descriptions.join(' o ') + '.' : '.');
    } else if (descriptions.length) {
      refs.authHint.textContent = 'Autenticación disponible: ' + descriptions.join(' o ') + '.';
    } else {
      refs.authHint.textContent = 'Este endpoint no declara autenticación obligatoria.';
    }
    updateAuthUi(false);
  }

  function updateAuthUi(resetStatus) {
    var mode = refs.authMode.value;
    refs.authTokenField.hidden = mode === 'none';
    refs.authHeaderFields.hidden = mode !== 'api_key' && mode !== 'http';
    refs.authPrefixField.hidden = mode !== 'http';
    if (mode === 'api_key' && (!refs.authHeader.value || refs.authHeader.value === 'Authorization')) refs.authHeader.value = refs.authHeader.dataset.suggested || 'X-API-Key';
    if (mode === 'http' && !refs.authHeader.value) refs.authHeader.value = 'Authorization';
    if (resetStatus !== false) setAuthStatus('idle', 'Sin verificar');
    updateCurlPreview();
  }

  function setAuthStatus(status, label) {
    refs.authStatus.className = 'monitor-auth-status ' + status;
    refs.authStatus.innerHTML = '<i class="fa-solid ' + (status === 'ok' ? 'fa-circle-check' : status === 'fail' ? 'fa-circle-xmark' : status === 'warn' ? 'fa-circle-exclamation' : 'fa-circle') + '"></i>' + esc(label);
  }

  function authenticationPayload() {
    return {
      mode: refs.authMode.value,
      token: refs.authMode.value === 'none' ? '' : refs.authToken.value,
      header: refs.authHeader.value.trim(),
      prefix: refs.authPrefix.value.trim()
    };
  }

  function endpointBaseUrl(service) {
    if (service && service.remote && service.remote.base_url) return String(service.remote.base_url).replace(/\/$/, '');
    if (service && service.localhost && service.localhost.base_url) return String(service.localhost.base_url).replace(/\/$/, '');
    return 'https://SERVICIO';
  }

  function shellQuote(value) {
    return String(value).replace(/'/g, "'\\''");
  }

  function updateCurlPreview() {
    if (!state.selectedEndpoint || !state.currentServiceId) return;
    var service = state.services[state.currentServiceId];
    var query = {};
    try { query = jsonField(refs.testQuery.value, 'Query'); } catch (error) {}
    var queryString = Object.keys(query).map(function (key) { return encodeURIComponent(key) + '=' + encodeURIComponent(query[key]); }).join('&');
    var url = endpointBaseUrl(service) + (refs.testPath.value.trim() || state.selectedEndpoint.path || '/');
    if (queryString) url += '?' + queryString;
    var parts = ['curl --request ' + refs.testMethod.value, "--url '" + shellQuote(url) + "'", "--header 'Accept: application/json'"];
    var mode = refs.authMode.value;
    if (mode === 'bearer') parts.push("--header 'Authorization: Bearer $TOKEN'");
    if (mode === 'api_key') parts.push("--header '" + shellQuote(refs.authHeader.value || 'X-API-Key') + ": $TOKEN'");
    if (mode === 'http') parts.push("--header '" + shellQuote(refs.authHeader.value || 'Authorization') + ': ' + (refs.authPrefix.value.trim() ? shellQuote(refs.authPrefix.value.trim()) + ' ' : '') + "$TOKEN'");
    if (!refs.bodyField.hidden) {
      parts.push("--header 'Content-Type: application/json'");
      parts.push("--data-raw '" + shellQuote(refs.testBody.value || '{}') + "'");
    }
    state.curlText = parts.join(' \\\n  ');
    refs.curlPreview.textContent = state.curlText;
    var fields = state.selectedParameters.filter(function (parameter) { return parameter.required; }).length;
    refs.usageHelp.textContent = fields ? 'Completa ' + fields + ' campo' + (fields === 1 ? '' : 's') + ' obligatorio' + (fields === 1 ? '' : 's') + ' y sustituye $TOKEN si aplica.' : 'El ejemplo está listo; sustituye $TOKEN si el endpoint requiere autenticación.';
  }

  function updateTesterMutation() {
    var mutates = refs.testMethod.value !== 'GET';
    var executable = ['GET', 'POST', 'PUT', 'PATCH'].indexOf(refs.testMethod.value) >= 0;
    refs.testSubmit.disabled = !executable;
    refs.testSubmit.innerHTML = executable ? '<i class="fa-solid fa-play"></i>Ejecutar prueba completa' : '<i class="fa-solid fa-shield-halved"></i>DELETE sólo documentación';
    var hasDocumentedBody = !!(state.selectedEndpoint && state.selectedEndpoint.request_body);
    refs.bodyField.hidden = !mutates && !hasDocumentedBody;
    refs.mutationConfirm.hidden = !mutates;
    if (!mutates) refs.mutationCheckbox.checked = false;
    updateCurlPreview();
  }
  function jsonField(text, label) {
    var value;
    try { value = JSON.parse(text || '{}'); }
    catch (error) { throw new Error(label + ' no contiene JSON válido.'); }
    if (value === null || typeof value !== 'object' || Array.isArray(value)) throw new Error(label + ' debe ser un objeto JSON.');
    return value;
  }
  function jsonBodyField(text) {
    try { return JSON.parse(text || '{}'); }
    catch (error) { throw new Error('Body no contiene JSON válido.'); }
  }
  function runEndpointTest(event) {
    event.preventDefault();
    executeEndpointRequest(false, refs.testSubmit);
  }

  function checkEndpointConnection() {
    if (refs.testMethod.value !== 'GET') {
      toast('Verificación segura', 'Selecciona un endpoint GET para comprobar conexión y credenciales sin modificar datos.', 'warning');
      return;
    }
    executeEndpointRequest(true, refs.connectionCheck);
  }

  function executeEndpointRequest(checkOnly, button) {
    if (!state.currentServiceId) return;
    var query, body = null;
    try {
      var invalid = refs.parameterFields.querySelector(':invalid');
      if (invalid) { invalid.reportValidity(); return; }
      query = jsonField(refs.testQuery.value, 'Query');
      if (!refs.bodyField.hidden) body = jsonBodyField(refs.testBody.value);
      if (/\{[^}]+\}/.test(refs.testPath.value)) throw new Error('Completa todos los parámetros requeridos de la ruta.');
      if (refs.authMode.value !== 'none' && !refs.authToken.value.trim()) throw new Error('Captura el token o API key para esta prueba.');
    } catch (error) { toast('Revisa la solicitud', error.message, 'warning'); return; }
    setBusy(button, true);
    refs.testResponse.hidden = false;
    refs.testResponseMeta.textContent = checkOnly ? 'Verificando conexión y credenciales…' : 'Ejecutando desde Sparta…';
    refs.testResponseBody.textContent = '';
    apiFetch('/monitoreo/probar', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ servicio:state.currentServiceId, metodo:refs.testMethod.value, path:refs.testPath.value.trim(), query:query, body:body, confirmar_mutacion:refs.mutationCheckbox.checked, auth:authenticationPayload() }) })
      .then(function (data) {
        state.responseText = typeof data.response === 'string' ? data.response : JSON.stringify(data.response, null, 2);
        refs.testResponseMeta.textContent = 'HTTP ' + (data.status || '—') + ' · ' + data.latency_ms + ' ms' + (data.truncated ? ' · respuesta truncada' : '');
        refs.testResponseBody.textContent = state.responseText || '(respuesta vacía)';
        if (data.connection_status === 'connected') setAuthStatus('ok', 'Conecta');
        else if (data.connection_status === 'auth_failed') setAuthStatus('fail', 'Token rechazado');
        else if (data.connection_status === 'network_error') setAuthStatus('fail', 'Sin conexión');
        else setAuthStatus('warn', 'Conecta · HTTP ' + (data.status || '—'));
        toast(data.ok_http ? 'Prueba completada' : 'La API respondió con error', refs.testResponseMeta.textContent, data.ok_http ? 'info' : 'warning');
      })
      .catch(function (error) { state.responseText = error.message; refs.testResponseMeta.textContent = 'No se pudo ejecutar'; refs.testResponseBody.textContent = error.message; setAuthStatus('fail', 'No verificado'); toast('Prueba rechazada', error.message, 'error'); })
      .finally(function () { setBusy(button, false); });
  }

  function showBrowserNotification(title, body) {
    if (!state.alertsEnabled || !('Notification' in window) || Notification.permission !== 'granted') return;
    try { new Notification(title, { body:body, icon:'/assets/img/favicon/favicon.ico', tag:'sparta-monitor-' + title }); } catch (error) {}
  }
  function processAlerts(data) {
    var statuses = {};
    (data.services || []).forEach(function (service) { statuses[service.id] = service.status; });
    if (state.previousStatuses) {
      Object.keys(statuses).forEach(function (id) {
        if (state.previousStatuses[id] && state.previousStatuses[id] !== statuses[id]) showBrowserNotification(serviceName(id), 'Cambió a ' + statusText(statuses[id]) + '.');
      });
    }
    state.previousStatuses = statuses;
    var ids = new Set((data.alerts || []).map(function (alert) { return alert.id; }));
    if (state.seenAlertIds) {
      (data.alerts || []).forEach(function (alert) {
        if (!state.seenAlertIds.has(alert.id)) showBrowserNotification(serviceName(alert.service), alert.message);
      });
    }
    state.seenAlertIds = ids;
    var count = (data.alerts || []).length;
    refs.alerts.querySelector('span').textContent = count ? 'Alertas ' + count : 'Alertas';
  }
  function syncAlertButton() {
    refs.alerts.setAttribute('aria-pressed', state.alertsEnabled ? 'true' : 'false');
    refs.alerts.querySelector('i').className = state.alertsEnabled ? 'fa-solid fa-bell' : 'fa-regular fa-bell';
  }

  function applyData(data) {
    state.data = data; state.services = {}; state.order = [];
    (data.services || []).forEach(function (service) { state.services[service.id] = service; state.order.push(service.id); });
    renderKpis(data); renderGrid(); renderCharts(); renderTimeline(data.events || []); processAlerts(data);
    refs.updated.textContent = 'Comprobado desde Sparta: ' + (data.generated_at || 'ahora');
    if (state.currentServiceId && state.services[state.currentServiceId]) renderDrawer();
  }
  function resetCountdown() {
    state.nextAt = Date.now() + state.intervalSeconds * 1000;
  }
  function loadAll(showSpinner) {
    if (state.loading) return Promise.resolve();
    state.loading = true;
    if (showSpinner !== false) setBusy(refs.refresh, true);
    refs.updated.textContent = 'Comprobando salud, OpenAPI y workspaces…';
    return apiFetch('/monitoreo/estado')
      .then(function (data) { applyData(data); })
      .catch(function (error) { refs.updated.textContent = 'No se pudo completar la comprobación.'; toast('Error de monitoreo', error.message, 'error'); if (!state.data) refs.grid.innerHTML = '<div class="monitor-empty"><i class="fa-solid fa-circle-exclamation"></i> ' + esc(error.message) + '</div>'; })
      .finally(function () { state.loading = false; setBusy(refs.refresh, false); resetCountdown(); });
  }
  function tick() {
    if (refs.wallClock) {
      refs.wallClock.textContent = new Date().toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false });
    }
    if (state.paused) { refs.next.textContent = 'Actualización automática pausada'; return; }
    var remaining = Math.max(0, Math.ceil((state.nextAt - Date.now()) / 1000));
    refs.next.textContent = 'Siguiente actualización en ' + remaining + ' s';
    if (remaining <= 0 && !state.loading) loadAll(false);
  }

  refs.refresh.addEventListener('click', function () { loadAll(true); });
  refs.interval.addEventListener('change', function () { state.intervalSeconds = Number(this.value) || 60; localStorage.setItem('spartaMonitorInterval', String(state.intervalSeconds)); resetCountdown(); tick(); });
  refs.auto.addEventListener('click', function () { state.paused = !state.paused; this.setAttribute('aria-pressed', state.paused ? 'false' : 'true'); this.querySelector('i').className = state.paused ? 'fa-solid fa-play' : 'fa-solid fa-pause'; this.querySelector('span').textContent = state.paused ? 'Reanudar' : 'Pausar'; if (!state.paused) resetCountdown(); tick(); });
  refs.alerts.addEventListener('click', function () {
    if (state.alertsEnabled) { state.alertsEnabled = false; localStorage.setItem('spartaMonitorAlerts', '0'); syncAlertButton(); toast('Alertas desactivadas', 'El dashboard seguirá registrando eventos dentro del módulo.', 'info'); return; }
    if (!('Notification' in window)) { toast('Alertas no disponibles', 'Este navegador no soporta notificaciones del sistema.', 'warning'); return; }
    Notification.requestPermission().then(function (permission) { state.alertsEnabled = permission === 'granted'; localStorage.setItem('spartaMonitorAlerts', state.alertsEnabled ? '1' : '0'); syncAlertButton(); toast(state.alertsEnabled ? 'Alertas activadas' : 'Permiso no concedido', state.alertsEnabled ? 'Se avisarán caídas y cambios importantes.' : 'Puedes habilitarlas después desde el navegador.', state.alertsEnabled ? 'info' : 'warning'); });
  });
  if (refs.fullscreen) {
    refs.fullscreen.addEventListener('click', function () {
      if (document.fullscreenElement) {
        document.exitFullscreen().catch(function () {});
      } else {
        document.documentElement.requestFullscreen().catch(function () {
          toast('Pantalla completa', 'El navegador bloqueó el cambio; inténtalo nuevamente.', 'warning');
        });
      }
    });
    document.addEventListener('fullscreenchange', function () {
      var active = !!document.fullscreenElement;
      refs.fullscreen.setAttribute('aria-pressed', active ? 'true' : 'false');
      refs.fullscreen.querySelector('i').className = active ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
      refs.fullscreen.querySelector('span').textContent = active ? 'Salir de pantalla completa' : 'Pantalla completa';
    });
  }
  if (refs.activityOpen && refs.activityDialog) {
    refs.activityOpen.addEventListener('click', function () {
      if (typeof refs.activityDialog.showModal === 'function') refs.activityDialog.showModal();
    });
    refs.activityClose.addEventListener('click', function () { refs.activityDialog.close(); });
    refs.activityDialog.addEventListener('click', function (event) {
      if (event.target === refs.activityDialog) refs.activityDialog.close();
    });
  }
  refs.grid.addEventListener('click', function (event) {
    var button = event.target.closest('[data-monitor-localhost]');
    if (button) { runLocalhost(button); return; }
    button = event.target.closest('[data-monitor-open]');
    if (button) { openDrawer(button.getAttribute('data-monitor-open')); return; }
    button = event.target.closest('[data-monitor-health]');
    if (button) {
      var id = button.getAttribute('data-monitor-health'); setBusy(button, true); refs.updated.textContent = 'Probando ' + serviceName(id) + ' desde Sparta…';
      apiFetch('/monitoreo/estado?servicio=' + encodeURIComponent(id)).then(function (data) { var service = data.services && data.services[0]; if (service) { state.services[id] = service; renderGrid(); renderCharts(); toast('Diagnóstico completado', service.name + ': ' + statusText(service.status), service.status === 'offline' ? 'error' : service.status === 'degraded' ? 'warning' : 'info'); } }).catch(function(error){toast('Falló la prueba',error.message,'error');}).finally(function(){setBusy(button,false);resetCountdown();});
    }
  });
  refs.drawerClose.addEventListener('click', closeDrawer); refs.drawerOverlay.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && refs.drawer.classList.contains('open')) closeDrawer(); });
  document.querySelector('.monitor-tabs').addEventListener('click', function (event) { var button = event.target.closest('[data-monitor-tab]'); if (button) switchTab(button.getAttribute('data-monitor-tab')); });
  refs.overview.addEventListener('click', function (event) {
    var button = event.target.closest('[data-monitor-localhost]');
    if (button) { runLocalhost(button); return; }
    button = event.target.closest('[data-process-action]');
    if (button) processAction(button.getAttribute('data-process-action'), button);
  });
  refs.diagnostics.addEventListener('click', function (event) {
    var logsButton = event.target.closest('[data-diagnostic-logs]');
    if (logsButton) {
      switchTab('logs');
      return;
    }
    var button = event.target.closest('[data-diagnostic-refresh]');
    if (!button) return;
    var id = button.getAttribute('data-diagnostic-refresh');
    setBusy(button, true);
    refs.updated.textContent = 'Generando diagnóstico de ' + serviceName(id) + '…';
    apiFetch('/monitoreo/estado?servicio=' + encodeURIComponent(id))
      .then(function (data) {
        var service = data.services && data.services[0];
        if (!service) throw new Error('El servicio no devolvió un diagnóstico.');
        state.services[id] = service;
        if (state.data) {
          state.data.incidents = data.incidents || state.data.incidents;
          state.data.events = data.events || state.data.events;
          state.data.alerts = data.alerts || state.data.alerts;
          state.data.notification_channels = data.notification_channels || state.data.notification_channels;
          state.data.incident_log_url = data.incident_log_url || state.data.incident_log_url;
        }
        renderGrid();
        renderDrawer();
        renderTimeline(data.events || []);
        toast('Diagnóstico actualizado', (service.diagnostic || {}).title || statusText(service.status), service.status === 'offline' ? 'error' : service.status === 'degraded' ? 'warning' : 'info');
      })
      .catch(function (error) { toast('No se pudo diagnosticar', error.message, 'error'); })
      .finally(function () { setBusy(button, false); resetCountdown(); });
  });
  refs.endpointList.addEventListener('click', function (event) { var button = event.target.closest('[data-endpoint-index]'); if (!button) return; selectEndpoint(Number(button.getAttribute('data-endpoint-index'))); });
  refs.parameterFields.addEventListener('input', syncParameterFields); refs.parameterFields.addEventListener('change', syncParameterFields);
  refs.testMethod.addEventListener('change', updateTesterMutation); refs.tester.addEventListener('submit', runEndpointTest);
  refs.testPath.addEventListener('input', updateCurlPreview); refs.testQuery.addEventListener('input', updateCurlPreview); refs.testBody.addEventListener('input', updateCurlPreview);
  refs.authMode.addEventListener('change', function () { updateAuthUi(true); });
  refs.authToken.addEventListener('input', function () { setAuthStatus('idle', 'Sin verificar'); });
  refs.authHeader.addEventListener('input', function () { setAuthStatus('idle', 'Sin verificar'); updateCurlPreview(); });
  refs.authPrefix.addEventListener('input', function () { setAuthStatus('idle', 'Sin verificar'); updateCurlPreview(); });
  refs.connectionCheck.addEventListener('click', checkEndpointConnection);
  refs.copyCurl.addEventListener('click', function () { if (!state.curlText) return; navigator.clipboard.writeText(state.curlText).then(function(){toast('cURL copiado','El ejemplo no incluye el valor real del token.','info');}).catch(function(){toast('No se pudo copiar','Selecciona el comando manualmente.','warning');}); });
  refs.copyResponse.addEventListener('click', function () { if (!state.responseText) return; navigator.clipboard.writeText(state.responseText).then(function(){toast('Respuesta copiada','El contenido está en el portapapeles.','info');}).catch(function(){toast('No se pudo copiar','Selecciona el texto manualmente.','warning');}); });
  refs.terminalLive.addEventListener('click', function () { startTerminalStreaming(true); loadLogs('', false); });
  refs.logRefresh.addEventListener('click', function () { stopTerminalStreaming(); loadLogs(refs.logSelect.value, true); });
  refs.logSelect.addEventListener('change', function () { stopTerminalStreaming(); loadLogs(this.value, true); });
  document.addEventListener('visibilitychange', function () { if (!document.hidden && !state.paused && Date.now() >= state.nextAt) loadAll(false); });

  syncAlertButton(); updateTesterMutation(); resetCountdown(); window.setInterval(tick, 1000); tick(); loadAll(true);
})();
