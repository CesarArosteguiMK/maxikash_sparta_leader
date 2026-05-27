<style>
    .org-builder {
        color: #102a43;
    }
    .org-builder-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }
    .org-builder-title {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #24324a;
        letter-spacing: 0;
    }
    .org-builder-shell {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 14px;
        min-height: calc(100vh - 210px);
    }
    .org-card {
        background: #fff;
        border: 1px solid #dce6f2;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
    }
    .org-toolbar {
        display: grid;
        grid-template-columns: minmax(220px, 320px) auto auto;
        align-items: end;
        gap: 12px;
        padding: 16px;
        margin-bottom: 14px;
    }
    .org-field label {
        display: block;
        margin-bottom: 6px;
        color: #42526e;
        font-size: 13px;
        font-weight: 700;
    }
    .org-field select,
    .org-field input {
        width: 100%;
        height: 42px;
        border: 1px solid #cbd7e3;
        border-radius: 10px;
        padding: 0 12px;
        color: #102a43;
        background: #fff;
        outline: none;
    }
    .org-btn {
        height: 42px;
        border: 0;
        border-radius: 10px;
        padding: 0 16px;
        font-weight: 800;
        color: #fff;
        background: #24324a;
        box-shadow: 0 6px 14px rgba(36, 50, 74, 0.18);
    }
    .org-btn.secondary {
        color: #24324a;
        background: #edf4ff;
        box-shadow: none;
    }
    .org-canvas-card {
        position: relative;
        overflow: hidden;
        min-height: 650px;
    }
    .org-canvas-head,
    .org-side-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        border-bottom: 1px solid #edf2f7;
    }
    .org-canvas-head h2,
    .org-side-head h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #102a43;
    }
    .org-muted {
        color: #62748a;
        font-size: 13px;
    }
    .org-canvas {
        position: relative;
        min-height: 590px;
        overflow: auto;
        background:
            linear-gradient(#eef4fb 1px, transparent 1px),
            linear-gradient(90deg, #eef4fb 1px, transparent 1px);
        background-size: 28px 28px;
    }
    .org-canvas-inner {
        position: relative;
        min-width: 1200px;
        min-height: 820px;
    }
    .org-lines {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        overflow: visible;
    }
    .org-node {
        position: absolute;
        width: 230px;
        border: 1px solid #c9d7e8;
        border-left: 5px solid #16a3b8;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(16, 42, 67, 0.12);
        cursor: grab;
        user-select: none;
    }
    .org-node:active {
        cursor: grabbing;
    }
    .org-node-title {
        margin: 0 0 8px;
        min-height: 38px;
        color: #102a43;
        font-size: 14px;
        font-weight: 850;
        line-height: 1.25;
    }
    .org-node-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 9px;
    }
    .org-chip {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        border-radius: 999px;
        padding: 3px 8px;
        color: #31506f;
        background: #edf4ff;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .org-node-actions {
        display: grid;
        grid-template-columns: 1fr 34px;
        gap: 8px;
        align-items: center;
    }
    .org-node select {
        width: 100%;
        height: 34px;
        border: 1px solid #d5e1ef;
        border-radius: 8px;
        padding: 0 8px;
        font-size: 12px;
        color: #102a43;
        background: #f8fbff;
    }
    .org-node-remove {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 9px;
        color: #e11d48;
        background: #ffe7ec;
    }
    .org-side {
        display: flex;
        flex-direction: column;
        min-height: 650px;
        overflow: hidden;
    }
    .org-filters {
        display: grid;
        gap: 10px;
        padding: 14px 16px;
        border-bottom: 1px solid #edf2f7;
    }
    .org-list {
        overflow: auto;
        padding: 14px 16px 18px;
    }
    .org-puesto {
        border: 1px solid #d9e5f2;
        border-radius: 12px;
        padding: 11px 12px;
        margin-bottom: 10px;
        background: #fbfdff;
        cursor: grab;
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }
    .org-puesto:hover {
        transform: translateY(-1px);
        border-color: #16a3b8;
        box-shadow: 0 8px 20px rgba(16, 42, 67, 0.08);
    }
    .org-puesto-title {
        font-size: 13px;
        font-weight: 850;
        color: #102a43;
        line-height: 1.25;
    }
    .org-puesto-small {
        margin-top: 4px;
        color: #66788a;
        font-size: 11px;
        line-height: 1.3;
    }
    .org-empty {
        padding: 28px 18px;
        color: #62748a;
        text-align: center;
        border: 1px dashed #cbd7e3;
        border-radius: 12px;
        background: #f8fbff;
    }
    .org-status {
        min-height: 20px;
        color: #62748a;
        font-size: 13px;
        font-weight: 700;
    }
    .org-status.ok {
        color: #169b62;
    }
    .org-status.err {
        color: #d92d20;
    }
    @media (max-width: 1100px) {
        .org-builder-shell {
            grid-template-columns: 1fr;
        }
        .org-side {
            min-height: 420px;
        }
        .org-toolbar {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="org-builder">
    <div class="org-builder-hero">
        <div>
            <h1 class="org-builder-title">
                <i class="fa fa-sitemap me-2"></i>Constructor de Estructura Organizacional
            </h1>
            <div class="org-muted">Construye el mapa jerarquico por pais, puesto y nivel organizacional.</div>
        </div>
        <button type="button" class="org-btn secondary" onclick="history.back()">
            <i class="fa fa-arrow-left me-1"></i>Volver
        </button>
    </div>

    <section class="org-card org-toolbar">
        <div class="org-field">
            <label for="orgPais">Pais</label>
            <select id="orgPais"></select>
        </div>
        <button type="button" class="org-btn secondary" id="orgReiniciar">
            <i class="fa fa-rotate-left me-1"></i>Reacomodar
        </button>
        <button type="button" class="org-btn" id="orgGuardar">
            <i class="fa fa-floppy-disk me-1"></i>Guardar mapa
        </button>
        <div class="org-status" id="orgStatus"></div>
    </section>

    <div class="org-builder-shell">
        <section class="org-card org-canvas-card">
            <div class="org-canvas-head">
                <div>
                    <h2>Mapa jerarquico</h2>
                    <div class="org-muted">Arrastra puestos aqui. Define el jefe directo en cada nodo.</div>
                </div>
                <span class="org-chip" id="orgNodeCount">0 puestos</span>
            </div>
            <div class="org-canvas" id="orgCanvas">
                <div class="org-canvas-inner" id="orgCanvasInner">
                    <svg class="org-lines" id="orgLines"></svg>
                    <div class="org-empty" id="orgCanvasEmpty" style="position:absolute; left:32px; top:32px; width:360px;">
                        Selecciona un pais y arrastra puestos desde el panel derecho para empezar.
                    </div>
                </div>
            </div>
        </section>

        <aside class="org-card org-side">
            <div class="org-side-head">
                <div>
                    <h2>Puestos disponibles</h2>
                    <div class="org-muted">Catalogo separado del mapa.</div>
                </div>
                <span class="org-chip" id="orgAvailableCount">0</span>
            </div>
            <div class="org-filters">
                <div class="org-field">
                    <label for="orgNivel">Nivel organizacional</label>
                    <select id="orgNivel"><option value="">Todos</option></select>
                </div>
                <div class="org-field">
                    <label for="orgArea">Area organizacional</label>
                    <select id="orgArea"><option value="">Todas</option></select>
                </div>
                <div class="org-field">
                    <label for="orgDepartamento">Departamento</label>
                    <select id="orgDepartamento"><option value="">Todos</option></select>
                </div>
                <div class="org-field">
                    <label for="orgBuscar">Buscar puesto</label>
                    <input id="orgBuscar" type="search" placeholder="Nombre, clave, area o departamento">
                </div>
            </div>
            <div class="org-list" id="orgPuestos"></div>
        </aside>
    </div>
</div>

<script>
(function () {
    const state = {
        idPais: 0,
        paises: [],
        niveles: [],
        areas: [],
        departamentos: [],
        puestos: [],
        nodes: new Map(),
        draggingNode: null,
        nodeOffset: { x: 0, y: 0 }
    };

    const els = {
        pais: document.getElementById('orgPais'),
        nivel: document.getElementById('orgNivel'),
        area: document.getElementById('orgArea'),
        departamento: document.getElementById('orgDepartamento'),
        buscar: document.getElementById('orgBuscar'),
        puestos: document.getElementById('orgPuestos'),
        canvas: document.getElementById('orgCanvas'),
        inner: document.getElementById('orgCanvasInner'),
        lines: document.getElementById('orgLines'),
        status: document.getElementById('orgStatus'),
        nodeCount: document.getElementById('orgNodeCount'),
        availableCount: document.getElementById('orgAvailableCount'),
        empty: document.getElementById('orgCanvasEmpty'),
        guardar: document.getElementById('orgGuardar'),
        reiniciar: document.getElementById('orgReiniciar')
    };

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[ch]));

    function setStatus(text, type) {
        els.status.textContent = text || '';
        els.status.className = 'org-status' + (type ? ' ' + type : '');
    }

    function optionHtml(value, text) {
        return '<option value="' + esc(value) + '">' + esc(text) + '</option>';
    }

    async function cargar(idPais) {
        setStatus('Cargando estructura...', '');
        const url = '/caphum/getEstructuraOrganizacionalJson' + (idPais ? ('?id_pais=' + encodeURIComponent(idPais)) : '');
        const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const json = await resp.json();
        if (!json.success) {
            throw new Error(json.mensaje || 'No se pudo cargar la estructura.');
        }
        const datos = json.datos || {};
        state.idPais = Number(datos.id_pais || 0);
        state.paises = datos.paises || [];
        state.niveles = datos.niveles || [];
        state.areas = datos.areas || [];
        state.departamentos = datos.departamentos || [];
        state.puestos = datos.puestos || [];
        state.nodes = new Map();
        (datos.mapa || []).forEach(row => {
            state.nodes.set(String(row.id_puesto), normalizarNodo(row));
        });
        renderCatalogos();
        renderAll();
        setStatus('Listo', 'ok');
    }

    function normalizarNodo(row) {
        return {
            id_puesto: Number(row.id_puesto),
            id_puesto_padre: row.id_puesto_padre ? Number(row.id_puesto_padre) : null,
            puesto: row.puesto || '',
            clave: row.clave || '',
            departamento: row.departamento || '',
            area_organizacional: row.area_organizacional || '',
            nivel_organizacional: row.nivel_organizacional || 'Sin nivel',
            posicion_x: Number(row.posicion_x || 120),
            posicion_y: Number(row.posicion_y || 120)
        };
    }

    function renderCatalogos() {
        els.pais.innerHTML = state.paises.map(p => optionHtml(p.id, p.nombre)).join('');
        els.pais.value = String(state.idPais || '');
        els.nivel.innerHTML = '<option value="">Todos</option>' + state.niveles.map(n => optionHtml(n.id, n.nombre)).join('');
        els.area.innerHTML = '<option value="">Todas</option>' + state.areas.map(a => optionHtml(a.id, a.nombre)).join('');
        renderDepartamentos();
    }

    function renderDepartamentos() {
        const idArea = els.area.value;
        const deps = state.departamentos.filter(d => !idArea || String(d.id_departamento_organizacional) === String(idArea));
        const actual = els.departamento.value;
        els.departamento.innerHTML = '<option value="">Todos</option>' + deps.map(d => optionHtml(d.id, d.nombre)).join('');
        if (actual && deps.some(d => String(d.id) === String(actual))) {
            els.departamento.value = actual;
        }
    }

    function getPuestosFiltrados() {
        const idNivel = els.nivel.value;
        const idArea = els.area.value;
        const idDep = els.departamento.value;
        const term = els.buscar.value.trim().toLowerCase();
        return state.puestos.filter(p => {
            if (state.nodes.has(String(p.id_puesto))) return false;
            if (idNivel && String(p.id_nivel_organizacional || '') !== String(idNivel)) return false;
            if (idArea && String(p.id_area_organizacional || '') !== String(idArea)) return false;
            if (idDep && String(p.id_departamento || '') !== String(idDep)) return false;
            if (term) {
                const hay = [p.puesto, p.clave, p.departamento, p.area_organizacional, p.nivel_organizacional]
                    .join(' ').toLowerCase();
                if (!hay.includes(term)) return false;
            }
            return true;
        });
    }

    function renderPuestos() {
        const puestos = getPuestosFiltrados();
        els.availableCount.textContent = String(puestos.length);
        if (!puestos.length) {
            els.puestos.innerHTML = '<div class="org-empty">No hay puestos disponibles con estos filtros.</div>';
            return;
        }
        els.puestos.innerHTML = puestos.map(p => `
            <div class="org-puesto" draggable="true" data-id="${esc(p.id_puesto)}">
                <div class="org-puesto-title">${esc(p.puesto)}</div>
                <div class="org-puesto-small">${esc(p.nivel_organizacional || 'Sin nivel')} · ${esc(p.departamento || 'Sin departamento')}</div>
            </div>
        `).join('');
        els.puestos.querySelectorAll('.org-puesto').forEach(el => {
            el.addEventListener('dragstart', ev => {
                ev.dataTransfer.setData('text/plain', el.dataset.id);
                ev.dataTransfer.effectAllowed = 'copy';
            });
        });
    }

    function renderAll() {
        renderPuestos();
        renderNodes();
        renderLines();
        els.nodeCount.textContent = state.nodes.size + (state.nodes.size === 1 ? ' puesto' : ' puestos');
        els.empty.style.display = state.nodes.size ? 'none' : 'block';
    }

    function renderNodes() {
        els.inner.querySelectorAll('.org-node').forEach(el => el.remove());
        const nodeList = Array.from(state.nodes.values());
        nodeList.forEach(node => {
            const div = document.createElement('div');
            div.className = 'org-node';
            div.dataset.id = String(node.id_puesto);
            div.style.left = node.posicion_x + 'px';
            div.style.top = node.posicion_y + 'px';
            div.innerHTML = `
                <p class="org-node-title">${esc(node.puesto)}</p>
                <div class="org-node-meta">
                    <span class="org-chip">${esc(node.nivel_organizacional || 'Sin nivel')}</span>
                    <span class="org-chip">${esc(node.departamento || 'Sin departamento')}</span>
                </div>
                <div class="org-node-actions">
                    <select class="org-parent-select" title="Jefe directo"></select>
                    <button type="button" class="org-node-remove" title="Eliminar del mapa"><i class="fa fa-xmark"></i></button>
                </div>
            `;
            const sel = div.querySelector('.org-parent-select');
            sel.innerHTML = '<option value="">Sin superior</option>' + nodeList
                .filter(n => n.id_puesto !== node.id_puesto)
                .map(n => optionHtml(n.id_puesto, n.puesto))
                .join('');
            sel.value = node.id_puesto_padre ? String(node.id_puesto_padre) : '';
            sel.addEventListener('change', () => {
                node.id_puesto_padre = sel.value ? Number(sel.value) : null;
                renderLines();
            });
            div.querySelector('.org-node-remove').addEventListener('click', () => {
                eliminarNodo(node.id_puesto);
            });
            div.addEventListener('pointerdown', iniciarArrastreNodo);
            els.inner.appendChild(div);
        });
    }

    function renderLines() {
        const width = Math.max(els.inner.scrollWidth, 1200);
        const height = Math.max(els.inner.scrollHeight, 820);
        els.lines.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
        els.lines.innerHTML = '';
        state.nodes.forEach(node => {
            if (!node.id_puesto_padre || !state.nodes.has(String(node.id_puesto_padre))) return;
            const parent = state.nodes.get(String(node.id_puesto_padre));
            const x1 = parent.posicion_x + 115;
            const y1 = parent.posicion_y + 112;
            const x2 = node.posicion_x + 115;
            const y2 = node.posicion_y;
            const mid = Math.round((y1 + y2) / 2);
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', `M ${x1} ${y1} C ${x1} ${mid}, ${x2} ${mid}, ${x2} ${y2}`);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', '#16a3b8');
            path.setAttribute('stroke-width', '3');
            path.setAttribute('stroke-linecap', 'round');
            els.lines.appendChild(path);
        });
    }

    function iniciarArrastreNodo(ev) {
        if (ev.target.closest('select') || ev.target.closest('button')) return;
        const el = ev.currentTarget;
        const node = state.nodes.get(String(el.dataset.id));
        if (!node) return;
        state.draggingNode = { el, node };
        const rect = el.getBoundingClientRect();
        state.nodeOffset.x = ev.clientX - rect.left;
        state.nodeOffset.y = ev.clientY - rect.top;
        el.setPointerCapture(ev.pointerId);
    }

    function moverNodo(ev) {
        if (!state.draggingNode) return;
        const rect = els.inner.getBoundingClientRect();
        const node = state.draggingNode.node;
        node.posicion_x = Math.max(8, Math.round(ev.clientX - rect.left + els.canvas.scrollLeft - state.nodeOffset.x));
        node.posicion_y = Math.max(8, Math.round(ev.clientY - rect.top + els.canvas.scrollTop - state.nodeOffset.y));
        state.draggingNode.el.style.left = node.posicion_x + 'px';
        state.draggingNode.el.style.top = node.posicion_y + 'px';
        renderLines();
    }

    function finalizarArrastreNodo() {
        state.draggingNode = null;
    }

    function agregarNodo(idPuesto, x, y) {
        const p = state.puestos.find(item => String(item.id_puesto) === String(idPuesto));
        if (!p || state.nodes.has(String(idPuesto))) return;
        state.nodes.set(String(idPuesto), normalizarNodo({
            ...p,
            id_puesto: p.id_puesto,
            posicion_x: x,
            posicion_y: y
        }));
        renderAll();
    }

    function eliminarNodo(idPuesto) {
        state.nodes.delete(String(idPuesto));
        state.nodes.forEach(n => {
            if (String(n.id_puesto_padre) === String(idPuesto)) {
                n.id_puesto_padre = null;
            }
        });
        renderAll();
    }

    function reacomodar() {
        const nodes = Array.from(state.nodes.values());
        nodes.forEach((node, i) => {
            node.posicion_x = 80 + (i % 4) * 280;
            node.posicion_y = 80 + Math.floor(i / 4) * 170;
        });
        renderAll();
    }

    async function guardar() {
        if (!state.idPais) {
            setStatus('Selecciona un pais.', 'err');
            return;
        }
        setStatus('Guardando mapa...', '');
        const nodos = Array.from(state.nodes.values()).map(n => ({
            id_puesto: n.id_puesto,
            id_puesto_padre: n.id_puesto_padre,
            posicion_x: n.posicion_x,
            posicion_y: n.posicion_y
        }));
        const resp = await fetch('/caphum/guardarEstructuraOrganizacionalJson', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ id_pais: state.idPais, nodos })
        });
        const json = await resp.json();
        if (!json.success) {
            throw new Error(json.mensaje || 'No se pudo guardar el mapa.');
        }
        setStatus('Mapa guardado correctamente.', 'ok');
        await cargar(state.idPais);
    }

    els.canvas.addEventListener('dragover', ev => {
        ev.preventDefault();
        ev.dataTransfer.dropEffect = 'copy';
    });
    els.canvas.addEventListener('drop', ev => {
        ev.preventDefault();
        const id = ev.dataTransfer.getData('text/plain');
        const rect = els.inner.getBoundingClientRect();
        const x = Math.round(ev.clientX - rect.left + els.canvas.scrollLeft - 115);
        const y = Math.round(ev.clientY - rect.top + els.canvas.scrollTop - 40);
        agregarNodo(id, Math.max(8, x), Math.max(8, y));
    });
    els.inner.addEventListener('pointermove', moverNodo);
    els.inner.addEventListener('pointerup', finalizarArrastreNodo);
    els.inner.addEventListener('pointercancel', finalizarArrastreNodo);
    els.pais.addEventListener('change', () => cargar(els.pais.value).catch(err => setStatus(err.message, 'err')));
    els.nivel.addEventListener('change', renderPuestos);
    els.area.addEventListener('change', () => { renderDepartamentos(); renderPuestos(); });
    els.departamento.addEventListener('change', renderPuestos);
    els.buscar.addEventListener('input', renderPuestos);
    els.reiniciar.addEventListener('click', reacomodar);
    els.guardar.addEventListener('click', () => guardar().catch(err => setStatus(err.message, 'err')));

    cargar(0).catch(err => setStatus(err.message, 'err'));
})();
</script>
