<div class="container-fluid py-4 ch-carta-gestor-page">
    <style>
        .ch-carta-gestor-page { color:#22303e; }
        .ch-carta-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .ch-carta-title { display:flex; align-items:center; gap:.65rem; color:#22303e; font-size:1.35rem; font-weight:800; margin:0; }
        .ch-carta-title i { color:#26344e; }
        .ch-carta-subtitle { color:#6b7280; font-size:.88rem; font-weight:600; margin:.2rem 0 0; max-width:760px; }
        .ch-carta-actions { display:flex; align-items:center; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; }
        .ch-carta-actions .btn { min-height:2.25rem; display:inline-flex; align-items:center; justify-content:center; gap:.4rem; font-weight:700; }
        .ch-carta-summary { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .ch-carta-kpi { border:1px solid #e2e8f0; border-radius:.65rem; background:#fff; padding:.85rem .95rem; min-height:5rem; }
        .ch-carta-kpi-label { color:#64748b; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.025em; }
        .ch-carta-kpi-value { color:#22303e; font-size:1.55rem; font-weight:900; line-height:1.1; margin-top:.25rem; }
        .ch-carta-toolbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:1rem; }
        .ch-carta-search { max-width:360px; min-width:240px; }
        .ch-carta-name { min-width:260px; }
        .ch-carta-name strong { display:block; color:#1f2937; font-size:.92rem; line-height:1.25; text-transform:uppercase; }
        .ch-carta-name small { color:#64748b; font-weight:700; }
        .ch-carta-meta { color:#475569; font-size:.82rem; font-weight:700; line-height:1.45; min-width:240px; }
        .ch-carta-meta span { display:block; }
        .ch-carta-badge { border-radius:999px; display:inline-flex; align-items:center; gap:.35rem; font-size:.75rem; font-weight:800; padding:.35rem .65rem; }
        .ch-carta-badge-pending { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
        .ch-carta-row-actions { display:flex; align-items:center; justify-content:flex-end; gap:.35rem; min-width:220px; }
        .ch-carta-row-actions .btn { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; font-weight:800; }
        @media (max-width: 767.98px) {
            .ch-carta-head,
            .ch-carta-actions,
            .ch-carta-toolbar { align-items:stretch; flex-direction:column; }
            .ch-carta-summary { grid-template-columns:1fr; }
            .ch-carta-actions .btn,
            .ch-carta-search { width:100%; max-width:none; }
            .ch-carta-row-actions { justify-content:flex-start; min-width:0; flex-wrap:wrap; }
        }
    </style>

    <div class="ch-carta-head">
        <div>
            <h1 class="ch-carta-title"><i class="fa-solid fa-file-signature"></i><span>Carta compromiso Gestor</span></h1>
            <p class="ch-carta-subtitle">Gestores activos que aun no tienen integrada la Carta de compromiso del Gestor en su expediente. Al cargar el documento, salen automaticamente de esta lista.</p>
        </div>
        <div class="ch-carta-actions">
            <button class="btn btn-label-secondary" type="button" id="btnActualizarCartaGestor" title="Actualizar">
                <i class="fa-solid fa-rotate"></i><span>Actualizar</span>
            </button>
        </div>
    </div>

    <div class="ch-carta-summary">
        <div class="ch-carta-kpi">
            <div class="ch-carta-kpi-label">Pendientes</div>
            <div class="ch-carta-kpi-value" id="cartaGestorTotal">0</div>
        </div>
        <div class="ch-carta-kpi">
            <div class="ch-carta-kpi-label">Con correo</div>
            <div class="ch-carta-kpi-value" id="cartaGestorConCorreo">0</div>
        </div>
        <div class="ch-carta-kpi">
            <div class="ch-carta-kpi-label">Sin correo</div>
            <div class="ch-carta-kpi-value" id="cartaGestorSinCorreo">0</div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="ch-carta-toolbar">
                <div>
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-list-check me-2"></i>Gestores pendientes</h5>
                    <div class="text-muted small fw-semibold" id="cartaGestorInfo">Cargando informacion...</div>
                </div>
                <input type="search" class="form-control ch-carta-search" id="cartaGestorBuscar" placeholder="Buscar gestor, puesto o departamento">
            </div>

            <div class="table-responsive">
                <table class="table align-middle border-top">
                    <thead>
                        <tr>
                            <th>Gestor</th>
                            <th>Puesto / estructura</th>
                            <th>Jefe</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cartaGestorRows">
                        <tr><td colspan="5" class="text-center text-muted fw-semibold py-4">Cargando gestores...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const state = { rows: [], filtro: '' };
    const els = {
        rows: document.getElementById('cartaGestorRows'),
        info: document.getElementById('cartaGestorInfo'),
        total: document.getElementById('cartaGestorTotal'),
        conCorreo: document.getElementById('cartaGestorConCorreo'),
        sinCorreo: document.getElementById('cartaGestorSinCorreo'),
        buscar: document.getElementById('cartaGestorBuscar'),
        actualizar: document.getElementById('btnActualizarCartaGestor')
    };

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[ch];
        });
    }

    function notify(icon, title, text) {
        if (window.Swal) {
            Swal.fire({ icon, title, text, timer: icon === 'success' ? 2200 : undefined, showConfirmButton: icon !== 'success' });
        } else {
            alert((title ? title + '\n' : '') + (text || ''));
        }
    }

    function filteredRows() {
        const filtro = state.filtro.trim().toLowerCase();
        if (!filtro) return state.rows;
        return state.rows.filter(function (row) {
            return [
                row.nombre_completo,
                row.numero_empleado,
                row.correo,
                row.puestos,
                row.departamentos,
                row.areas,
                row.direcciones,
                row.jefe
            ].join(' ').toLowerCase().includes(filtro);
        });
    }

    function render() {
        const rows = filteredRows();
        const conCorreo = state.rows.filter(row => String(row.correo || '').includes('@')).length;
        els.total.textContent = state.rows.length;
        els.conCorreo.textContent = conCorreo;
        els.sinCorreo.textContent = Math.max(0, state.rows.length - conCorreo);
        els.info.textContent = rows.length === state.rows.length
            ? `${state.rows.length} gestor(es) pendientes.`
            : `${rows.length} de ${state.rows.length} gestor(es) visibles.`;

        if (!rows.length) {
            els.rows.innerHTML = '<tr><td colspan="5" class="text-center text-muted fw-semibold py-4">No hay gestores pendientes por Carta de compromiso.</td></tr>';
            return;
        }

        els.rows.innerHTML = rows.map(function (row) {
            const correo = row.correo || 'Sin correo registrado';
            const telefono = row.telefono || '';
            const disabledCorreo = String(row.correo || '').includes('@') ? '' : 'disabled';
            return `
                <tr>
                    <td class="ch-carta-name">
                        <strong>${escapeHtml(row.nombre_completo || 'Sin nombre')}</strong>
                        <small>${escapeHtml(row.numero_empleado || 'Sin numero de empleado')}</small><br>
                        <small>${escapeHtml(correo)}</small>${telefono ? `<br><small>${escapeHtml(telefono)}</small>` : ''}
                    </td>
                    <td class="ch-carta-meta">
                        <span><i class="fa-solid fa-briefcase me-1"></i>${escapeHtml(row.puestos || 'Gestor')}</span>
                        <span><i class="fa-solid fa-building me-1"></i>${escapeHtml(row.departamentos || 'Sin departamento')}</span>
                        <span><i class="fa-solid fa-sitemap me-1"></i>${escapeHtml(row.areas || 'Sin area')}</span>
                        <span><i class="fa-solid fa-location-dot me-1"></i>${escapeHtml(row.direcciones || 'Sin direccion')}</span>
                    </td>
                    <td class="text-muted fw-semibold">${escapeHtml(row.jefe || 'Sin jefe asignado')}</td>
                    <td class="text-center">
                        <span class="ch-carta-badge ch-carta-badge-pending"><i class="fa-solid fa-clock"></i>Pendiente</span>
                    </td>
                    <td class="text-end">
                        <div class="ch-carta-row-actions">
                            <button type="button" class="btn btn-sm btn-primary" data-action="email" data-id="${Number(row.id_persona || 0)}" ${disabledCorreo}>
                                <i class="fa-solid fa-envelope"></i><span>Enviar</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-label-secondary" data-action="copy" data-link="${escapeHtml(row.url_subida || '')}" title="Copiar enlace">
                                <i class="fa-solid fa-link"></i>
                            </button>
                            <a class="btn btn-sm btn-label-secondary" href="${escapeHtml(row.url_subida || '#')}" target="_blank" rel="noopener" title="Abrir enlace">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function cargar() {
        els.rows.innerHTML = '<tr><td colspan="5" class="text-center text-muted fw-semibold py-4">Cargando gestores...</td></tr>';
        try {
            const res = await fetch('/caphum/getGestoresPendientesCartaCompromiso', { credentials: 'same-origin' });
            const data = await res.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo cargar la lista.');
            state.rows = Array.isArray(data.datos) ? data.datos : [];
            render();
        } catch (err) {
            els.rows.innerHTML = '<tr><td colspan="5" class="text-center text-danger fw-semibold py-4">No se pudo cargar la informacion.</td></tr>';
            els.info.textContent = err.message || 'Error al cargar.';
        }
    }

    async function enviarRecordatorio(idPersona, button) {
        if (!idPersona) return;
        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Enviando</span>';
        try {
            const res = await fetch('/caphum/enviarRecordatorioCartaCompromisoGestor', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_persona: idPersona })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo enviar el correo.');
            notify('success', 'Recordatorio enviado', data.mensaje || 'Correo enviado correctamente.');
            await cargar();
        } catch (err) {
            notify('error', 'No se pudo enviar', err.message || 'Intenta nuevamente.');
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    }

    async function copiar(text) {
        if (!text) return;
        try {
            await navigator.clipboard.writeText(text);
            notify('success', 'Enlace copiado', 'Ya puedes compartirlo para pruebas.');
        } catch (err) {
            notify('error', 'No se pudo copiar', text);
        }
    }

    els.buscar.addEventListener('input', function () {
        state.filtro = this.value || '';
        render();
    });
    els.actualizar.addEventListener('click', cargar);
    els.rows.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-action]');
        if (!btn) return;
        if (btn.dataset.action === 'email') {
            enviarRecordatorio(Number(btn.dataset.id || 0), btn);
        } else if (btn.dataset.action === 'copy') {
            copiar(btn.dataset.link || '');
        }
    });

    cargar();
})();
</script>
