<style>
    .legacy-sync-page {
        color: #22303e;
    }
    .legacy-sync-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: .85rem;
        background: linear-gradient(135deg, #26344e 0%, #36527a 100%);
        color: #fff;
    }
    .legacy-sync-header h4 {
        margin: 0;
        color: #fff;
        font-size: 1.35rem;
        font-weight: 800;
    }
    .legacy-sync-header p {
        margin: .15rem 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: .88rem;
    }
    .legacy-sync-card {
        border: 1px solid #dbe4ef;
        border-radius: .75rem;
        background: #fff;
        padding: 1rem;
    }
    .legacy-sync-title {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin: 0 0 .35rem;
        color: #26344e;
        font-size: .95rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .legacy-sync-note {
        margin: 0;
        color: #64748b;
        font-size: .84rem;
        line-height: 1.45;
    }
    .legacy-sync-actionbar {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    .legacy-sync-btn {
        border: 0;
        border-radius: 999px;
        background: #26344e;
        color: #fff;
        padding: .68rem 1.25rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        box-shadow: 0 8px 18px rgba(38, 52, 78, .18);
    }
    .legacy-sync-btn:hover {
        background: #1d293f;
        color: #fff;
    }
    .legacy-sync-result {
        margin-top: 1rem;
        display: none;
    }
    .legacy-sync-summary {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: .65rem;
        margin-bottom: 1rem;
    }
    .legacy-sync-kpi {
        border: 1px solid #e2e8f0;
        border-radius: .65rem;
        padding: .75rem;
        background: #f8fafc;
    }
    .legacy-sync-kpi span {
        display: block;
        color: #64748b;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .035em;
    }
    .legacy-sync-kpi strong {
        display: block;
        margin-top: .2rem;
        color: #26344e;
        font-size: 1.35rem;
        line-height: 1;
    }
    .legacy-sync-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        overflow: hidden;
        background: #fff;
    }
    .legacy-sync-table {
        width: 100%;
        margin: 0;
        font-size: .84rem;
    }
    .legacy-sync-table th {
        background: #f8fafc;
        color: #566a7f;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .legacy-sync-table td {
        vertical-align: middle;
        color: #475569;
    }
    .legacy-sync-user strong,
    .legacy-sync-role {
        color: #26344e;
        font-weight: 800;
    }
    .legacy-sync-muted {
        color: #94a3b8;
        font-size: .78rem;
        font-weight: 700;
    }
    .legacy-sync-pill {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border-radius: 999px;
        padding: .18rem .55rem;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .legacy-sync-pill-ok { background: #dcfce7; color: #15803d; }
    .legacy-sync-pill-warn { background: #fef3c7; color: #b45309; }
    .legacy-sync-pill-error { background: #fee2e2; color: #b91c1c; }
    .legacy-sync-pill-info { background: #e0f2fe; color: #0369a1; }
    .legacy-sync-motivos {
        display: flex;
        flex-wrap: wrap;
        gap: .3rem;
    }
    @media (max-width: 1199.98px) {
        .legacy-sync-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .legacy-sync-header { align-items: flex-start; flex-direction: column; }
        .legacy-sync-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>

<div class="container-fluid py-4 legacy-sync-page">
    <div class="legacy-sync-header">
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-arrows-rotate fa-2x"></i>
            <div>
                <h4>Sincroniza Legacy</h4>
                <p>Sincronizacion completa de usuarios Spartan hacia Legacy: alta, usuario, contrasena, rol y jerarquia.</p>
            </div>
        </div>
        <span class="badge bg-label-light text-dark">
            <i class="fa-solid fa-shield-halved me-1"></i>Organizacion
        </span>
    </div>

    <div class="legacy-sync-card">
        <h5 class="legacy-sync-title">
            <i class="fa-solid fa-database"></i> Reproceso masivo
        </h5>
        <p class="legacy-sync-note">
            Ejecuta una sincronizacion forzada para todos los usuarios dentro del alcance configurado.
            El backend divide el proceso en lotes internos y entrega un resumen global; no necesitas escoger
            cuantos usuarios revisar. Actualiza o crea el usuario en Legacy, sincroniza usuario/contrasena,
            asigna el rol equivalente y recalcula supervisor, subgerente, gerente y subdirector.
        </p>

        <div class="legacy-sync-actionbar">
            <div class="legacy-sync-note">
                <i class="fa-solid fa-layer-group me-1"></i>
                Se procesan todos los candidatos detectados, por lotes de backend.
            </div>
            <button type="button" class="legacy-sync-btn" id="legacy-sync-btn">
                <i class="fa-solid fa-cloud-arrow-up"></i> Sincronizar todo con Legacy
            </button>
        </div>
    </div>

    <div class="legacy-sync-result" id="legacy-sync-result">
        <div class="legacy-sync-summary" id="legacy-sync-summary"></div>
        <div class="legacy-sync-table-wrap">
            <table class="table table-hover legacy-sync-table" id="legacy-sync-table">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Usuario</th>
                        <th>Spartan</th>
                        <th>Legacy</th>
                        <th>Motivo</th>
                        <th>Resultado</th>
                    </tr>
                </thead>
                <tbody id="legacy-sync-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const btn = document.getElementById('legacy-sync-btn');
    const result = document.getElementById('legacy-sync-result');
    const summary = document.getElementById('legacy-sync-summary');
    const tbody = document.getElementById('legacy-sync-tbody');

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function motivoLabel(motivo) {
        const map = {
            no_existe_en_legacy: 'No existe en Legacy',
            baja_en_legacy_con_spartan_activo: 'Baja en Legacy',
            role_desalineado: 'Rol desalineado',
            usuario_desalineado: 'Usuario desalineado',
            sincronizacion_forzada: 'Recalculo forzado',
            baja_spartan_activa_en_legacy: 'Baja Spartan activa en Legacy'
        };
        return map[motivo] || motivo;
    }

    function resultadoMeta(resultado) {
        if (resultado === 'actualizado') return { cls: 'legacy-sync-pill-ok', icon: 'fa-circle-check', label: 'Actualizado' };
        if (resultado === 'sin_cambios') return { cls: 'legacy-sync-pill-info', icon: 'fa-circle-info', label: 'Sin cambios' };
        if (resultado === 'omitido') return { cls: 'legacy-sync-pill-warn', icon: 'fa-triangle-exclamation', label: 'Omitido' };
        if (resultado === 'error') return { cls: 'legacy-sync-pill-error', icon: 'fa-circle-xmark', label: 'Error' };
        return { cls: 'legacy-sync-pill-info', icon: 'fa-circle-info', label: resultado || 'Revisado' };
    }

    function kpi(label, value) {
        return '<div class="legacy-sync-kpi"><span>' + esc(label) + '</span><strong>' + esc(value) + '</strong></div>';
    }

    function renderResumen(resumen) {
        const r = resumen || {};
        summary.innerHTML = [
            kpi('Revisados', r.revisados || 0),
            kpi('Detectados', r.pendientes_detectados || 0),
            kpi('Actualizados', r.actualizados || 0),
            kpi('Sin cambios', r.sin_cambios || 0),
            kpi('Lotes', r.lotes || 0),
            kpi('Errores', r.errores || 0)
        ].join('');
    }

    function renderTabla(datos) {
        const rows = Array.isArray(datos) ? datos : [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hubo usuarios por sincronizar.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            const sync = row.sync || {};
            const meta = resultadoMeta(sync.resultado || '');
            const motivos = (row.motivos || []).map(function (m) {
                return '<span class="legacy-sync-pill legacy-sync-pill-info">' + esc(motivoLabel(m)) + '</span>';
            }).join('');
            return '<tr>' +
                '<td><span class="legacy-sync-pill legacy-sync-pill-info">' + esc(row.lote || '-') + '</span></td>' +
                '<td class="legacy-sync-user"><strong>' + esc(row.nombre || 'Sin nombre') + '</strong><div class="legacy-sync-muted"># ' + esc(row.external_id || '-') + '</div></td>' +
                '<td><div class="legacy-sync-role">' + esc(row.puesto || '-') + '</div><div class="legacy-sync-muted">' + esc(row.departamento || '-') + '</div></td>' +
                '<td><span class="legacy-sync-role">' + esc(row.role_legacy || '-') + '</span></td>' +
                '<td><div class="legacy-sync-motivos">' + motivos + '</div></td>' +
                '<td><span class="legacy-sync-pill ' + meta.cls + '"><i class="fa-solid ' + meta.icon + '"></i>' + esc(meta.label) + '</span>' +
                    (sync.mensaje ? '<div class="legacy-sync-muted mt-1">' + esc(sync.mensaje) + '</div>' : '') +
                '</td>' +
            '</tr>';
        }).join('');
    }

    async function sincronizar() {
        btn.disabled = true;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Sincronizando Legacy',
                html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-3 mb-0">Procesando todos los usuarios detectados por lotes...</p>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
        }
        try {
            const resp = await fetch('/CapHum/sincronizarLegacyPendientesRrhh', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ todos: true, forzar: true, tamano_lote: 100 })
            });
            const data = await resp.json();
            renderResumen(data.resumen || {});
            renderTabla(data.datos || []);
            result.style.display = 'block';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: data.success ? 'success' : 'warning',
                    title: data.success ? 'Sincronizacion terminada' : 'Sincronizacion con observaciones',
                    text: data.mensaje || 'Proceso finalizado.'
                });
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo ejecutar la sincronizacion masiva.' });
            } else {
                window.alert('No se pudo ejecutar la sincronizacion masiva.');
            }
        } finally {
            btn.disabled = false;
        }
    }

    if (btn) {
        btn.addEventListener('click', sincronizar);
    }
})();
</script>
