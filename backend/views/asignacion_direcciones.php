<style>
  .dir-page { padding-top: .35rem; }
  .dir-page .dir-title { display: flex; align-items: center; gap: .85rem; color: #2f3d4d; font-weight: 600; margin-bottom: 1.25rem; }
  .dir-page .dir-title i { font-size: 1.35rem; color: #2f3d4d; }
  .dir-page .dir-top-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .dir-page .dir-query-card { width: min(452px, 100%); border: 0; border-radius: 12px; box-shadow: 0 8px 24px rgba(15,23,42,.06); background: #fff; }
  .dir-page .dir-query-title { display: flex; align-items: center; gap: .65rem; font-size: 1.05rem; font-weight: 600; color: #334155; margin-bottom: 1.15rem; }
  .dir-page .dir-query-title i { color: #334155; }
  .dir-page .dir-query-actions { display: grid; grid-template-columns: 1fr auto; gap: .75rem; align-items: end; }
  .dir-page .dir-query-actions .form-control { height: 43px; border-radius: 7px !important; }
  .dir-page .dir-sync-side { margin-left: auto; min-width: 300px; max-width: 390px; align-self: flex-start; }
  .dir-page .dir-service-card { border: 1px solid #e2e8f0; background: rgba(255,255,255,.88); box-shadow: 0 8px 24px rgba(15,23,42,.05); border-radius: 12px; padding: .9rem; }
  .dir-page .dir-service-head { display: flex; align-items: center; gap: .55rem; color: #334155; font-weight: 700; margin-bottom: .65rem; }
  .dir-page .dir-service-head .dir-service-icon { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; color: #1f2d4d; background: #eef4ff; }
  .dir-page .dir-results-card { border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 2px 10px rgba(15,23,42,.04); }
  .dir-page .dir-toolbar { display: flex; justify-content: flex-end; gap: .75rem; align-items: center; }
  .dir-page .dir-sync-status { color: #64748b; font-size: .82rem; margin-top: .7rem; min-height: 1.1rem; }
  .dir-page .dir-list { display: grid; gap: .75rem; }
  .dir-page .dir-item { border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; padding: .9rem; display: grid; grid-template-columns: 28px 1fr auto; gap: .75rem; align-items: start; cursor: grab; }
  .dir-page .dir-item.is-principal { border-color: #1a52a8; background: #f8fbff; }
  .dir-page .dir-item:active { cursor: grabbing; }
  .dir-page .dir-item.sortable-ghost { opacity: .65; }
  .dir-page .dir-handle { color: #94a3b8; padding-top: .35rem; pointer-events: none; }
  .dir-page .dir-badge { font-size: .72rem; border-radius: 999px; padding: .25rem .55rem; font-weight: 700; }
  .dir-page .dir-empty { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 2rem; text-align: center; color: #64748b; background: #f8fafc; }
  .dir-page .dir-meta { color: #64748b; font-size: .82rem; }
  .dir-page .dir-sync-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; }
  @media (max-width: 768px) {
    .dir-page .dir-query-actions { grid-template-columns: 1fr; }
    .dir-page .dir-query-actions .btn { width: 100%; }
    .dir-page .dir-sync-side { width: 100%; max-width: none; margin-left: 0; }
    .dir-page .dir-sync-side .btn { width: 100%; }
    .dir-page .dir-toolbar { justify-content: stretch; flex-direction: column; align-items: stretch; }
    .dir-page .dir-item { grid-template-columns: 24px 1fr; }
    .dir-page .dir-item > .dir-actions { grid-column: 2; }
  }
</style>

<div class="dir-page">
  <h3 class="dir-title">
    <i class="fa-solid fa-briefcase"></i>
    <span>Actualizaci&oacute;n de direcci&oacute;n</span>
  </h3>

  <div class="dir-top-row">
    <div class="card dir-query-card">
      <div class="card-body p-4">
        <div class="dir-query-title">
          <i class="fa-solid fa-user-tie"></i>
          <span>Datos</span>
        </div>

        <div class="dir-query-actions">
          <div class="min-w-0">
            <label class="form-label fw-semibold">ID de cr&eacute;dito</label>
            <input type="number" min="1" class="form-control" id="dirCreditoInput" placeholder="Seleccione...">
          </div>
          <button type="button" class="btn btn-primary" id="dirBuscarBtn"><i class="fa-solid fa-search me-1"></i>Consultar</button>
        </div>
      </div>
    </div>
    <div class="dir-sync-side">
      <div class="dir-service-card">
        <div class="dir-service-head">
          <span class="dir-service-icon"><i class="fa-solid fa-gears"></i></span>
          <span>Servicio</span>
        </div>
        <button type="button" class="btn w-100 fw-semibold d-inline-flex align-items-center justify-content-center gap-2" id="dirSyncBtn"
                style="height:38px; background:#f0fdfa; border:1px solid #5eead4; color:#0d9488; border-radius:8px; box-shadow:none;"
                title="Revisa los cr&eacute;ditos de Segund&oacute;metro semanal y agrega en direcciones los que falten.">
          <i class="fa-solid fa-rotate me-1"></i>Actualizar desde Segund&oacute;metro
        </button>
        <div class="dir-sync-status" id="dirSyncResultado"></div>
      </div>
    </div>
  </div>

  <div class="card dir-results-card mb-4 d-none" id="dirResultadosCard">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h5 class="mb-1"><i class="fa-solid fa-location-dot text-primary me-2"></i>Direcciones por cr&eacute;dito</h5>
        <div class="text-muted small">La primera direcci&oacute;n de la lista queda como principal en base de datos.</div>
      </div>
      <div class="dir-toolbar">
        <button type="button" class="btn btn-outline-primary btn-sm" id="dirAgregarBtn" disabled><i class="fa-solid fa-plus me-1"></i>Agregar direcci&oacute;n</button>
      </div>
    </div>
    <div class="card-body">
      <div id="dirResumen" class="alert alert-info d-none mb-3"></div>
      <div id="dirLista" class="dir-list"></div>
    </div>
  </div>

</div>

<div class="modal fade" id="dirModalAgregar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="dirFormAgregar">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Agregar direcci&oacute;n</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_credito" id="dirFormCredito">
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">C.P.</label><input class="form-control" name="codigo_postal"></div>
          <div class="col-md-3"><label class="form-label">Calle/n&uacute;mero</label><input class="form-control" name="calle_numero"></div>
          <div class="col-md-6"><label class="form-label">Direcci&oacute;n *</label><input class="form-control" name="direccion" required></div>
          <div class="col-md-4"><label class="form-label">Colonia</label><input class="form-control" name="colonia"></div>
          <div class="col-md-4"><label class="form-label">Ciudad</label><input class="form-control" name="ciudad"></div>
          <div class="col-md-4"><label class="form-label">Estado</label><input class="form-control" name="estado"></div>
          <div class="col-md-4"><label class="form-label">Tel&eacute;fono celular</label><input class="form-control" name="telefono_celular"></div>
          <div class="col-md-4"><label class="form-label">Referencia 1</label><input class="form-control" name="referencia_1"></div>
          <div class="col-md-2"><label class="form-label">Parentesco 1</label><input class="form-control" name="parentesco_referencia_1"></div>
          <div class="col-md-2"><label class="form-label">Tel. ref. 1</label><input class="form-control" name="telefono_referencia_1"></div>
          <div class="col-md-4"><label class="form-label">Referencia 2</label><input class="form-control" name="referencia_2"></div>
          <div class="col-md-2"><label class="form-label">Parentesco 2</label><input class="form-control" name="parentesco_referencia_2"></div>
          <div class="col-md-2"><label class="form-label">Tel. ref. 2</label><input class="form-control" name="telefono_referencia_2"></div>
          <div class="col-md-4"><label class="form-label">Etapa</label><input class="form-control" name="etapa"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Guardar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
  const state = { idCredito: 0, direcciones: [], sortable: null };
  const input = document.getElementById('dirCreditoInput');
  const list = document.getElementById('dirLista');
  const resumen = document.getElementById('dirResumen');
  const addBtn = document.getElementById('dirAgregarBtn');
  const form = document.getElementById('dirFormAgregar');
  const resultadosCard = document.getElementById('dirResultadosCard');

  function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  }
  function labelTipo(d) {
    if (Number(d.es_principal) === 1) return 'Principal';
    if (d.tipo_direccion === 'secundaria') return 'Secundaria';
    if (d.tipo_direccion === 'terciaria') return 'Terciaria';
    return 'Adicional';
  }
  function direccionHtml(d) {
    const principal = Number(d.es_principal) === 1;
    return `<div class="dir-item ${principal ? 'is-principal' : ''}" data-id="${Number(d.id)}">
      <i class="fa-solid fa-grip-vertical dir-handle" title="Arrastrar"></i>
      <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
          <span class="dir-badge ${principal ? 'bg-primary text-white' : 'bg-label-secondary text-secondary'}">${esc(labelTipo(d))}</span>
          <strong>${esc(d.direccion || 'Sin direccion')}</strong>
        </div>
        <div class="dir-meta">${esc([d.calle_numero, d.colonia, d.ciudad, d.estado, d.codigo_postal ? 'C.P. ' + d.codigo_postal : ''].filter(Boolean).join(' · '))}</div>
        <div class="dir-meta mt-1">${esc([d.telefono_celular ? 'Cel. ' + d.telefono_celular : '', d.referencia_1 ? 'Ref. 1: ' + d.referencia_1 : '', d.telefono_referencia_1 ? 'Tel. ' + d.telefono_referencia_1 : ''].filter(Boolean).join(' · '))}</div>
      </div>
      <div class="dir-actions text-end">
        <span class="badge bg-label-info">${esc(d.origen_detalle || d.origen || '')}</span>
      </div>
    </div>`;
  }
  function render() {
    resultadosCard.classList.toggle('d-none', !state.idCredito);
    addBtn.disabled = !state.idCredito;
    resumen.classList.toggle('d-none', !state.idCredito);
    resumen.innerHTML = state.idCredito
      ? `<strong>Credito ${esc(state.idCredito)}</strong> · ${state.direcciones.length} direccion(es). Arrastra una direccion arriba para marcarla como principal.`
      : '';
    list.innerHTML = state.direcciones.length
      ? state.direcciones.map(direccionHtml).join('')
      : '<div class="dir-empty">No hay direcciones registradas para este credito.</div>';
    if (state.sortable) state.sortable.destroy();
    if (state.direcciones.length && window.Sortable) {
      state.sortable = Sortable.create(list, {
        animation: 150,
        onEnd: guardarOrden
      });
    }
  }
  function aplicarOrdenLocal(ids) {
    const porId = new Map(state.direcciones.map(d => [Number(d.id), d]));
    state.direcciones = ids
      .map((id, idx) => {
        const item = porId.get(Number(id));
        if (!item) return null;
        const orden = idx + 1;
        return {
          ...item,
          orden_direccion: orden,
          tipo_direccion: orden === 1 ? 'principal' : (orden === 2 ? 'secundaria' : (orden === 3 ? 'terciaria' : 'adicional')),
          es_principal: orden === 1 ? 1 : 0
        };
      })
      .filter(Boolean);
  }
  async function post(url, data) {
    const r = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(data || {})
    });
    return r.json();
  }
  async function buscar() {
    const id = Number(input.value || 0);
    if (!id) {
      Swal.fire('Actualización de dirección', 'Captura un ID de crédito válido.', 'info');
      return;
    }
    const data = await post('/analitica/getAsignacionDireccionesCredito', { id_credito: id });
    if (!data.success) {
      Swal.fire('Error', data.mensaje || 'No se pudo consultar.', 'error');
      return;
    }
    state.idCredito = id;
    state.direcciones = Array.isArray(data.direcciones) ? data.direcciones : [];
    render();
  }
  async function guardarOrden() {
    const ids = Array.from(list.querySelectorAll('.dir-item')).map(el => Number(el.dataset.id));
    const respaldo = state.direcciones.map(d => ({ ...d }));
    aplicarOrdenLocal(ids);
    render();
    const data = await post('/analitica/postAsignacionDireccionesOrden', { id_credito: state.idCredito, ids });
    if (!data.success) {
      state.direcciones = respaldo;
      render();
      Swal.fire('Error', data.mensaje || 'No se pudo guardar el orden.', 'error');
      return;
    }
  }
  document.getElementById('dirBuscarBtn').addEventListener('click', buscar);
  input.addEventListener('keydown', ev => { if (ev.key === 'Enter') buscar(); });
  addBtn.addEventListener('click', function () {
    form.reset();
    document.getElementById('dirFormCredito').value = state.idCredito;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('dirModalAgregar')).show();
  });
  form.addEventListener('submit', async function (ev) {
    ev.preventDefault();
    const data = Object.fromEntries(new FormData(form).entries());
    const res = await post('/analitica/postAsignacionDireccion', data);
    if (!res.success) {
      Swal.fire('Error', res.mensaje || 'No se pudo guardar.', 'error');
      return;
    }
    bootstrap.Modal.getInstance(document.getElementById('dirModalAgregar'))?.hide();
    await buscar();
  });
  document.getElementById('dirSyncBtn').addEventListener('click', async function () {
    const btn = this;
    const salida = document.getElementById('dirSyncResultado');
    const confirmacion = await Swal.fire({
      title: 'Actualizar desde Segundómetro',
      text: 'Se revisarán los créditos de Segundómetro semanal. Los que no existan en direcciones se insertarán con las direcciones distintas encontradas en Segundómetro y MaxiProd.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, actualizar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#1f2d4d'
    });
    if (!confirmacion.isConfirmed) return;
    btn.disabled = true;
    salida.textContent = 'Revisando créditos de Segundómetro semanal que faltan en direcciones...';
    const data = await post('/analitica/postAsignacionDireccionesSync', {});
    btn.disabled = false;
    salida.textContent = data.success
      ? `Listo. Créditos faltantes revisados: ${data.revisados || 0}. Direcciones insertadas: ${data.insertados || 0}. Sin dirección encontrada: ${data.sin_datos || 0}.`
      : (data.mensaje || 'No se pudo sincronizar.');
  });
  render();
})();
</script>
