(function () {
    var idFormulario = parseInt(window.FORM_BUILDER_FORMULARIO_ID, 10) || 0;
    if (idFormulario < 1) return;

    var FIELD_TYPES = [
        { value: 'abierta', label: 'Abierta', icon: 'fa-solid fa-pen-to-square', color: '#26344e', bg: '#dcdfe3' },
        { value: 'multiple', label: 'Múltiple', icon: 'fa-solid fa-square-check', color: '#2563eb', bg: '#dbeafe' },
        { value: 'cerrada', label: 'Cerrada', icon: 'fa-solid fa-circle-dot', color: '#7c3aed', bg: '#ede9fe' },
        { value: 'escala', label: 'Escala', icon: 'fa-solid fa-sliders', color: '#d97706', bg: '#fef3c7' },
        { value: 'numerica', label: 'Numérica', icon: 'fa-solid fa-hashtag', color: '#065f46', bg: '#d1fae5' },
        { value: 'fecha', label: 'Fecha', icon: 'fa-solid fa-calendar-day', color: '#92400e', bg: '#fef3c7' },
    ];
    var TYPE_TO_BACKEND = { numerica: 'numero' };
    var BACKEND_TO_UI = { numero: 'numerica', lista: 'cerrada', email: 'abierta' };
    var LABEL_EJ_BY_TYPE = {
        abierta: '¿Con quién vive actualmente?',
        multiple: '¿Cuáles son sus fuentes de ingreso?',
        cerrada: '¿Es propietario de su vivienda?',
        escala: '¿Qué tan satisfecho está con el servicio?',
        numerica: '¿Cuántos dependientes económicos tiene?',
        fecha: '¿Cuál es su fecha de nacimiento?'
    };

    function getLabelPlaceholder(type) {
        return 'Ej: ' + (LABEL_EJ_BY_TYPE[type] || '¿Con quién vive actualmente?');
    }

    function backendType(uiType) {
        return TYPE_TO_BACKEND[uiType] || uiType;
    }
    function uiType(backendType) {
        return BACKEND_TO_UI[backendType] || backendType;
    }

    function newQ() {
        return {
            id: 't_' + Date.now() + '_' + Math.random().toString(36).slice(2),
            backendId: null,
            type: 'abierta',
            label: '',
            placeholder: '',
            required: false,
            incluir: true,
            options: ['Opción 1', 'Opción 2', 'Opción 3'],
            escalaMax: 5,
            orden: 0,
        };
    }

    var state = {
        title: document.getElementById('formBuilderTitle') ? document.getElementById('formBuilderTitle').value : 'Cuestionario de Validación',
        desc: document.getElementById('formBuilderDesc') ? document.getElementById('formBuilderDesc').value : '',
        questions: [],
        activeId: null,
        tab: 'editor',
    };

    function getTypeInfo(t) {
        for (var i = 0; i < FIELD_TYPES.length; i++) {
            if (FIELD_TYPES[i].value === t) return FIELD_TYPES[i];
        }
        return FIELD_TYPES[0];
    }

    function esc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
    function safeId(s) {
        return String(s).replace(/[^a-zA-Z0-9_-]/g, '_');
    }
    function apiRequest(cfg) {
        cfg = cfg || {};
        if (typeof http !== 'undefined' && http && typeof http.request === 'function') {
            http.request(cfg);
            return;
        }
        var endpoint = cfg.endpoint || '';
        var metodo = (cfg.metodo || 'GET').toUpperCase();
        var headers = {};
        var body = undefined;
        if (cfg.contentType) headers['Content-Type'] = cfg.contentType;
        if (cfg.data != null) body = cfg.data;
        fetch(endpoint, {
            method: metodo,
            headers: headers,
            body: metodo === 'GET' ? undefined : body,
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json().catch(function () { return {}; }); })
            .then(function (resp) {
                if (resp && (resp.success === false || resp.error)) {
                    if (cfg.onError) cfg.onError(resp);
                    return;
                }
                if (cfg.onSuccess) cfg.onSuccess(resp || {});
            })
            .catch(function (err) {
                if (cfg.onError) cfg.onError({ mensaje: err && err.message ? err.message : 'Error de red' });
            });
    }

    function saveFormData() {
        var title = document.getElementById('formBuilderTitle').value.trim();
        var desc = document.getElementById('formBuilderDesc').value.trim();
        state.title = title;
        state.desc = desc;
        return new Promise(function (resolve, reject) {
            apiRequest({
                endpoint: '/validaciones/actualizarFormulario',
                metodo: 'POST',
                data: JSON.stringify({ id: idFormulario, nombre: title, descripcion: desc }),
                contentType: 'application/json',
                processData: false,
                onSuccess: function () {
                    updatePreview();
                    resolve();
                },
                onError: function (err) {
                    reject(err || { mensaje: 'Error al guardar formulario' });
                },
            });
        });
    }

    function loadQuestions() {
        var url = '/validaciones/getPreguntasFormulario?id_formulario=' + idFormulario;
        apiRequest({
            endpoint: url,
            metodo: 'GET',
            onSuccess: function (resp) {
                var datos = Array.isArray(resp.datos) ? resp.datos : [];
                var personalizadas = datos.filter(function (p) {
                    return p.es_predefinida === 0 || p.es_predefinida === false;
                });
                state.questions = personalizadas.map(function (p, idx) {
                    var opts = p.opciones && Array.isArray(p.opciones) ? p.opciones : ['Opción 1', 'Opción 2'];
                    var escalaMax = 5;
                    if (p.tipo === 'escala' && p.escala_max) {
                        var n = parseInt(p.escala_max, 10);
                        if ([3, 5, 7, 10].indexOf(n) >= 0) escalaMax = n;
                    }
                    return {
                        id: 'b_' + (p.id || ''),
                        backendId: p.id,
                        type: uiType(p.tipo),
                        label: p.texto || '',
                        placeholder: '',
                        required: false,
                        incluir: p.activa === 1 || p.activa === true,
                        options: opts,
                        escalaMax: escalaMax,
                        orden: idx,
                    };
                });
                if (state.questions.length && !state.activeId) state.activeId = state.questions[0].id;
                render();
                updatePreview();
            },
            onError: function () {
                render();
            },
        });
    }

    function addQuestion() {
        var q = newQ();
        q.orden = state.questions.length;
        state.questions.push(q);
        state.activeId = q.id;
        render();
        updatePreview();
        var listEl = document.getElementById('formBuilderQuestionsList');
        if (listEl) listEl.scrollTop = listEl.scrollHeight;
    }

    function saveQuestion(q) {
        var tipo = backendType(q.type);
        var payload = {
            id_formulario: idFormulario,
            tipo: tipo,
            texto: (q.label || '').trim() || 'Sin etiqueta',
            es_predefinida: 0,
            activa: q.incluir ? 1 : 0,
            orden: q.orden,
        };
        if (['cerrada', 'multiple'].indexOf(q.type) >= 0 && q.options && q.options.length) {
            payload.opciones = q.options;
        }
        if (q.type === 'escala') {
            payload.escala_min = '1';
            payload.escala_max = String(q.escalaMax || 5);
        }
        if (q.type === 'numerica') {
            payload.num_min = null;
            payload.num_max = null;
        }
        if (q.backendId) payload.id = q.backendId;

        return new Promise(function (resolve, reject) {
            apiRequest({
                endpoint: '/validaciones/guardarPreguntaFormulario',
                metodo: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                processData: false,
                onSuccess: function (resp) {
                    if (resp.success && resp.datos && resp.datos.id && !q.backendId) {
                        q.backendId = resp.datos.id;
                        q.id = 'b_' + resp.datos.id;
                    }
                    updatePreview();
                    resolve(resp);
                },
                onError: function (err) {
                    reject(err || { mensaje: 'Error al guardar pregunta' });
                },
            });
        });
    }

    function guardarTodo() {
        var submitBtn = document.getElementById('formBuilderBtnSubmit');
        var prevHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px"></i>Guardando...';
        }
        saveFormData()
            .then(function () {
                var promesas = state.questions.map(function (q) { return saveQuestion(q); });
                return Promise.all(promesas);
            })
            .then(function () {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Guardado', text: 'Formulario y preguntas guardados correctamente.' });
            })
            .catch(function (err) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) ? err.mensaje : 'Error al guardar' });
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = prevHtml || 'Guardar cuestionario';
                }
            });
    }

    function toggleIncluir(q) {
        q.incluir = !q.incluir;
        render();
        updatePreview();
    }

    function deleteQuestion(q) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar pregunta?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
            }).then(function (r) {
                if (r.isConfirmed) doDelete(q);
            });
        } else {
            if (confirm('¿Eliminar esta pregunta?')) doDelete(q);
        }
    }

    function doDelete(q) {
        var idx = state.questions.map(function (x) { return x.id; }).indexOf(q.id);
        if (idx < 0) return;
        if (q.backendId) {
            apiRequest({
                endpoint: '/validaciones/eliminarPreguntaFormulario',
                metodo: 'POST',
                data: JSON.stringify({ id: q.backendId }),
                contentType: 'application/json',
                processData: false,
                onSuccess: function () {
                    state.questions.splice(idx, 1);
                    if (state.activeId === q.id) state.activeId = state.questions[0] ? state.questions[0].id : null;
                    render();
                    updatePreview();
                },
            });
        } else {
            state.questions.splice(idx, 1);
            if (state.activeId === q.id) state.activeId = state.questions[0] ? state.questions[0].id : null;
            render();
            updatePreview();
        }
    }

    function moveQuestion(index, delta) {
        var to = index + delta;
        if (to < 0 || to >= state.questions.length) return;
        var arr = state.questions.slice();
        var t = arr[index];
        arr[index] = arr[to];
        arr[to] = t;
        state.questions = arr;
        state.questions.forEach(function (q, i) { q.orden = i; });
        render();
        updatePreview();
        state.questions.forEach(function (q) { saveQuestion(q); });
    }

    function renderQuestionCard(q, index) {
        var total = state.questions.length;
        var active = state.activeId === q.id;
        var t = getTypeInfo(q.type);
        var needsOpts = ['multiple', 'cerrada'].indexOf(q.type) >= 0;
        var card = document.createElement('div');
        card.className = 'form-builder-qcard' + (active ? ' active' : '');
        card.setAttribute('data-qid', q.id);
        card.setAttribute('data-index', String(index));
        card.innerHTML =
            '<div class="form-builder-qcard-row" style="display: flex; align-items: center; gap: 8px;">' +
            '<div class="form-builder-drag-handle" title="Arrastrar para reordenar" draggable="true">⋮⋮</div>' +
            '<div class="form-builder-qcard-num">' + (index + 1) + '</div>' +
            '<span class="form-builder-typebadge" style="background:' + t.bg + ';color:' + t.color + ';border:1px solid ' + t.color + '30"><i class="' + t.icon + '" style="margin-right:4px"></i>' + t.label + '</span>' +
            '<span style="flex:1;font-size:13px;font-weight:' + (q.label ? '500' : '400') + ';color:' + (q.label ? '#111827' : '#9ca3af') + ';overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(q.label || 'Sin etiqueta...') + (q.required ? ' <span style="color:#dc2626;font-weight:700">*</span>' : '') + '</span>' +
            '<div style="display:flex;gap:4px;">' +
            (index > 0 ? '<button type="button" class="form-builder-ibtn" data-move="-1">↑</button>' : '') +
            (index < total - 1 ? '<button type="button" class="form-builder-ibtn" data-move="1">↓</button>' : '') +
            '<button type="button" class="form-builder-ibtn" data-toggle-expand title="Contraer o expandir">' + (active ? '▼' : '▶') + '</button>' +
            '<button type="button" class="form-builder-ibtn danger" data-del><i class="fa-solid fa-trash-can"></i></button>' +
            '</div>' +
            '</div>';
        if (active) {
            var sid = safeId(q.id);
            var expand = document.createElement('div');
            expand.style.cssText = 'margin-top:14px;border-top:1px solid #f3f4f6;padding-top:14px';
            var html = '';
            html += '<div class="form-builder-lbl">Tipo de pregunta</div>';
            html += '<div class="form-builder-type-row" id="fbTypes_' + sid + '"></div>';
            html += '<div class="form-builder-lbl">Pregunta / Etiqueta</div>';
            html += '<input type="text" class="form-builder-input fb-label" placeholder="' + esc(getLabelPlaceholder(q.type)) + '" value="' + esc(q.label) + '">';

            if (['abierta', 'numerica'].indexOf(q.type) >= 0) {
                html += '<div class="form-builder-lbl">Placeholder (texto de ayuda)</div><input type="text" class="form-builder-input fb-placeholder" placeholder="Ej: Escribe aquí..." value="' + esc(q.placeholder) + '">';
            }
            if (needsOpts) {
                html += '<div class="form-builder-lbl">Opciones de respuesta <span style="font-weight:400;color:#9ca3af">(círculo = una opción, cuadrado = varias)</span></div><div id="fbOpts_' + sid + '"></div><button type="button" class="form-builder-btn-outline" style="margin-top:8px" id="fbAddOpt_' + sid + '"><i class="fa-solid fa-plus me-1"></i>Añadir opción</button>';
            }
            if (q.type === 'escala') {
                html += '<div class="form-builder-lbl">Máximo de la escala</div><div style="display:flex;gap:6px;margin-bottom:14px" id="fbEscala_' + sid + '"></div>';
            }
            html += '<div style="display:flex;align-items:center;gap:20px;margin-top:14px"><label style="display:flex;align-items:center;gap:8px;cursor:pointer"><div class="form-builder-toggle' + (q.required ? ' on blue' : '') + '" id="fbReq_' + sid + '"><span class="form-builder-toggle-knob"></span></div><span style="font-size:12px;color:#6b7280">Obligatorio</span></label><label style="display:flex;align-items:center;gap:8px;cursor:pointer"><div class="form-builder-toggle' + (q.incluir ? ' on' : '') + '" id="fbIncl_' + sid + '"><span class="form-builder-toggle-knob"></span></div><span style="font-size:12px;color:#6b7280">Incluir en cuestionario</span></label></div>';
            expand.innerHTML = html;

            var typesWrap = expand.querySelector('#fbTypes_' + sid);
            FIELD_TYPES.forEach(function (ft) {
                var sel = q.type === ft.value;
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'form-builder-type-chip';
                btn.style.cssText = 'border:2px solid ' + (sel ? ft.color : '#e5e7eb') + ';background:' + (sel ? ft.bg : '#f9fafb') + ';color:' + (sel ? ft.color : '#6b7280') + ';font-weight:' + (sel ? '800' : '600') + ';';
                btn.innerHTML = '<i class="' + ft.icon + '" style="margin-right:4px"></i>' + ft.label;
                btn.addEventListener('click', function () {
                    q.type = ft.value;
                    var lblInput = expand.querySelector('.fb-label');
                    if (lblInput) lblInput.placeholder = getLabelPlaceholder(ft.value);
                    render();
                    updatePreview();
                });
                typesWrap.appendChild(btn);
            });

            var labelInput = expand.querySelector('.fb-label');
            if (labelInput) {
                labelInput.addEventListener('input', function () {
                    q.label = this.value;
                    card.querySelector('span[style*="flex:1"]').textContent = q.label || 'Sin etiqueta...';
                });
            }

            var ph = expand.querySelector('.fb-placeholder');
            if (ph) ph.addEventListener('input', function () { q.placeholder = this.value; });

            if (needsOpts) {
                var optsCont = expand.querySelector('#fbOpts_' + sid);
                var optHint = q.type === 'cerrada' ? 'En el cuestionario el encuestado elegirá una opción.' : 'En el cuestionario el encuestado podrá marcar varias opciones.';
                function defaultLabelForIndex(i) { return 'Opción ' + (i + 1); }
                function isDefaultForIndex(val, i) { return !val || String(val).trim() === defaultLabelForIndex(i); }
                function renderOpts() {
                    optsCont.innerHTML = '';
                    (q.options || []).forEach(function (opt, i) {
                        var row = document.createElement('div');
                        row.style.cssText = 'display:flex;gap:6px;margin-bottom:6px;align-items:center';
                        var placeHolder = defaultLabelForIndex(i);
                        var displayVal = isDefaultForIndex(opt, i) ? '' : opt;
                        row.innerHTML = '<div class="fb-opt-bullet" style="width:14px;height:14px;border-radius:' + (q.type === 'multiple' ? '3px' : '50%') + ';border:2px solid #d1d5db;flex-shrink:0" title="' + esc(optHint) + '"></div>' +
                            '<input type="text" class="form-builder-input fb-opt-input" style="flex:1;margin-bottom:0" placeholder="' + esc(placeHolder) + '" value="' + esc(displayVal) + '">' +
                            (q.options.length > 1 ? '<button type="button" class="form-builder-ibtn danger" data-rmopt="' + i + '">✕</button>' : '');
                        var inp = row.querySelector('.fb-opt-input');
                        inp.addEventListener('input', function () { q.options[i] = this.value.trim() || placeHolder; });
                        inp.addEventListener('blur', function () {
                            var v = this.value.trim();
                            if (!v) { this.value = ''; q.options[i] = placeHolder; }
                            else q.options[i] = v;
                        });
                        var rm = row.querySelector('[data-rmopt]');
                        if (rm) rm.addEventListener('click', function () {
                            q.options.splice(parseInt(rm.getAttribute('data-rmopt'), 10), 1);
                            renderOpts();
                        });
                        optsCont.appendChild(row);
                    });
                }
                renderOpts();
                var addOpt = expand.querySelector('#fbAddOpt_' + sid);
                if (addOpt) addOpt.addEventListener('click', function () {
                    q.options = q.options || [];
                    q.options.push('Opción ' + (q.options.length + 1));
                    renderOpts();
                });
            }

            if (q.type === 'escala') {
                var escalaWrap = expand.querySelector('#fbEscala_' + sid);
                [3, 5, 7, 10].forEach(function (n) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.style.cssText = 'padding:5px 14px;border-radius:6px;cursor:pointer;border:1.5px solid ' + (q.escalaMax === n ? '#d97706' : '#e5e7eb') + ';background:' + (q.escalaMax === n ? '#fef3c7' : '#f9fafb') + ';color:' + (q.escalaMax === n ? '#92400e' : '#6b7280') + ';font-size:12px;font-weight:600';
                    btn.textContent = '1–' + n;
                    btn.addEventListener('click', function () {
                        q.escalaMax = n;
                        render();
                        updatePreview();
                    });
                    escalaWrap.appendChild(btn);
                });
            }

            var reqTog = expand.querySelector('#fbReq_' + sid);
            if (reqTog) {
                reqTog.addEventListener('click', function () {
                    q.required = !q.required;
                    reqTog.classList.toggle('on', q.required);
                    var lbl = card.querySelector('span[style*="flex:1"]');
                    if (lbl) lbl.innerHTML = esc(q.label || 'Sin etiqueta...') + (q.required ? ' <span style="color:#dc2626;font-weight:700">*</span>' : '');
                    updatePreview();
                });
            }
            var inclTog = expand.querySelector('#fbIncl_' + sid);
            if (inclTog) {
                inclTog.addEventListener('click', function () { toggleIncluir(q); });
                inclTog.classList.toggle('on', q.incluir);
            }
            card.appendChild(expand);
        }
        card.addEventListener('click', function (e) {
            if (e.target.closest('button') || e.target.closest('input') || e.target.closest('textarea')) return;
            state.activeId = q.id;
            render();
        });
        card.querySelectorAll('[data-move]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                moveQuestion(index, parseInt(btn.getAttribute('data-move'), 10));
            });
        });
        var delBtn = card.querySelector('[data-del]');
        if (delBtn) delBtn.addEventListener('click', function (e) { e.stopPropagation(); deleteQuestion(q); });
        var toggleExpandBtn = card.querySelector('[data-toggle-expand]');
        if (toggleExpandBtn) {
            toggleExpandBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                state.activeId = state.activeId === q.id ? null : q.id;
                render();
            });
        }

        var handle = card.querySelector('.form-builder-drag-handle');
        if (handle) {
            handle.addEventListener('dragstart', function (e) {
                e.stopPropagation();
                e.dataTransfer.setData('application/x-form-builder-qindex', String(index));
                e.dataTransfer.effectAllowed = 'move';
                card.classList.add('form-builder-dragging');
                if (e.dataTransfer.setDragImage) e.dataTransfer.setDragImage(card, 20, 20);
            });
            handle.addEventListener('dragend', function () {
                card.classList.remove('form-builder-dragging');
                var list = document.getElementById('formBuilderQuestionsList');
                if (list) list.querySelectorAll('.form-builder-qcard').forEach(function (c) { c.classList.remove('form-builder-drag-over'); });
            });
        }
        card.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            var fromIdx = e.dataTransfer.getData('application/x-form-builder-qindex');
            if (fromIdx === '' || fromIdx === String(index)) return;
            card.classList.add('form-builder-drag-over');
        });
        card.addEventListener('dragleave', function (e) {
            if (!card.contains(e.relatedTarget)) card.classList.remove('form-builder-drag-over');
        });
        card.addEventListener('drop', function (e) {
            e.preventDefault();
            card.classList.remove('form-builder-drag-over');
            var fromIdx = parseInt(e.dataTransfer.getData('application/x-form-builder-qindex'), 10);
            if (isNaN(fromIdx) || fromIdx === index) return;
            var arr = state.questions.slice();
            var item = arr.splice(fromIdx, 1)[0];
            arr.splice(index, 0, item);
            state.questions = arr;
            state.questions.forEach(function (qu, i) { qu.orden = i; });
            render();
            updatePreview();
        });

        return card;
    }

    function render() {
        var titleEl = document.getElementById('formBuilderTitle');
        var descEl = document.getElementById('formBuilderDesc');
        if (titleEl) state.title = titleEl.value;
        if (descEl) state.desc = descEl.value;

        var listEl = document.getElementById('formBuilderQuestionsList');
        var emptyEl = document.getElementById('formBuilderEmpty');
        if (!listEl) return;
        listEl.innerHTML = '';
        if (state.questions.length === 0) {
            emptyEl.style.display = 'block';
        } else {
            emptyEl.style.display = 'none';
            state.questions.forEach(function (q, i) {
                listEl.appendChild(renderQuestionCard(q, i));
            });
        }
    }

    function updatePreview() {
        var titleEl = document.getElementById('formBuilderTitle');
        var descEl = document.getElementById('formBuilderDesc');
        var t = titleEl ? titleEl.value.trim() : state.title;
        var d = descEl ? descEl.value.trim() : state.desc;
        var included = state.questions.filter(function (q) { return q.incluir; });
        var titlePreview = document.getElementById('formBuilderPreviewTitle');
        var formularioNombreLabel = document.getElementById('formBuilderFormularioNombre');
        var descPreview = document.getElementById('formBuilderPreviewDesc');
        var fieldsCont = document.getElementById('formBuilderPreviewFields');
        var emptyPreview = document.getElementById('formBuilderPreviewEmpty');
        var submitBtn = document.getElementById('formBuilderBtnSubmit');
        var subText = document.getElementById('formBuilderPreviewSub');
        var badge = document.getElementById('formBuilderBadge');
        if (titlePreview) titlePreview.textContent = t || 'Sin título';
        if (formularioNombreLabel) formularioNombreLabel.textContent = t || 'Sin título';
        if (descPreview) descPreview.textContent = d;
        if (badge) badge.textContent = included.length + ' pregunta' + (included.length !== 1 ? 's' : '') + ' incluidas';
        if (subText) subText.textContent = 'Vista previa en tiempo real · ' + included.length + '/' + state.questions.length + ' preguntas incluidas';
        if (!fieldsCont) return;
        fieldsCont.innerHTML = '';
        if (included.length === 0) {
            emptyPreview.style.display = 'block';
            if (submitBtn) submitBtn.style.display = 'none';
        } else {
            emptyPreview.style.display = 'none';
            if (submitBtn) submitBtn.style.display = 'block';
            included.forEach(function (q, i) {
                var label = q.label || ('Pregunta ' + (i + 1));
                var t = getTypeInfo(q.type);
                var div = document.createElement('div');
                div.style.marginBottom = '20px';
                div.innerHTML = '<div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:8px"><div style="min-width:22px;height:22px;border-radius:50%;background:#26344e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800">' + (i + 1) + '</div><div><div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap"><span style="font-size:14px;font-weight:600;color:#111827">' + esc(label) + (q.required ? ' <span style="color:#dc2626">*</span>' : '') + '</span><span class="form-builder-typebadge" style="background:' + t.bg + ';color:' + t.color + '"><i class="' + t.icon + '" style="margin-right:4px"></i>' + t.label + '</span></div></div></div>';
                if (['abierta', 'email', 'numerica'].indexOf(q.type) >= 0) {
                    var ph = q.placeholder || (q.type === 'email' ? 'ejemplo@correo.com' : q.type === 'numerica' ? '0' : 'Escribe tu respuesta...');
                    div.innerHTML += '<input type="' + (q.type === 'numerica' ? 'number' : 'text') + '" placeholder="' + esc(ph) + '" style="width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:10px 14px;font-size:13px;color:#374151;background:#fff;box-sizing:border-box">';
                }
                if (q.type === 'fecha') div.innerHTML += '<input type="date" style="border:1.5px solid #e5e7eb;border-radius:8px;padding:10px 14px;font-size:13px;background:#fff">';
                if (q.type === 'lista') {
                    div.innerHTML += '<select style="width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:10px 14px;font-size:13px;background:#fff;color:#374151"><option value="">Seleccionar...</option>' + (q.options || []).map(function (o) { return '<option>' + esc(o) + '</option>'; }).join('') + '</select>';
                }
                if (['cerrada', 'multiple'].indexOf(q.type) >= 0) {
                    var gname = 'fbpv_' + safeId(q.id) + '_' + i;
                    var optsWrap = document.createElement('div');
                    optsWrap.style.cssText = 'display:flex;flex-direction:column;gap:7px';
                    (q.options || []).forEach(function (o, oi) {
                        var lid = gname + '_' + oi;
                        var lab = document.createElement('label');
                        lab.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fafafa;cursor:pointer';
                        lab.setAttribute('for', lid);
                        if (q.type === 'cerrada') {
                            var r = document.createElement('input');
                            r.type = 'radio';
                            r.name = gname;
                            r.id = lid;
                            r.value = String(oi);
                            r.style.cssText = 'width:16px;height:16px;accent-color:#16a34a;cursor:pointer;flex-shrink:0';
                            lab.appendChild(r);
                        } else {
                            var c = document.createElement('input');
                            c.type = 'checkbox';
                            c.id = lid;
                            c.value = String(oi);
                            c.style.cssText = 'width:16px;height:16px;accent-color:#16a34a;cursor:pointer;flex-shrink:0';
                            lab.appendChild(c);
                        }
                        var sp = document.createElement('span');
                        sp.style.cssText = 'font-size:13px;color:#374151';
                        sp.textContent = o;
                        lab.appendChild(sp);
                        optsWrap.appendChild(lab);
                    });
                    div.appendChild(optsWrap);
                }
                if (q.type === 'escala') {
                    var max = q.escalaMax || 5;
                    var escWrap = document.createElement('div');
                    escWrap.style.cssText = 'display:flex;gap:7px;flex-wrap:wrap';
                    for (var n = 1; n <= max; n++) {
                        (function (num) {
                            var b = document.createElement('button');
                            b.type = 'button';
                            b.textContent = String(num);
                            b.style.cssText = 'width:38px;height:38px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fafafa;color:#374151;font-size:13px;font-weight:600;cursor:pointer';
                            b.addEventListener('click', function () {
                                escWrap.querySelectorAll('button').forEach(function (x) {
                                    x.style.background = '#fafafa';
                                    x.style.borderColor = '#e5e7eb';
                                    x.style.color = '#374151';
                                });
                                b.style.background = '#dcdfe3';
                                b.style.borderColor = '#26344e';
                                b.style.color = '#26344e';
                            });
                            escWrap.appendChild(b);
                        })(n);
                    }
                    div.appendChild(escWrap);
                }
                fieldsCont.appendChild(div);
            });
        }
    }

    function setTab(tab) {
        state.tab = tab;
        var editorPanel = document.getElementById('formBuilderEditorPanel');
        var previewPanel = document.getElementById('formBuilderPreviewPanel');
        var tabs = document.querySelectorAll('.form-builder-tab');
        tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-tab') === tab); });
        if (tab === 'editor') {
            editorPanel.classList.remove('hide');
            previewPanel.classList.remove('full');
        } else {
            editorPanel.classList.add('hide');
            previewPanel.classList.add('full');
        }
    }

    var formDataSaveTimer;
    function scheduleSaveFormData() {
        clearTimeout(formDataSaveTimer);
        formDataSaveTimer = setTimeout(function () {
            saveFormData();
        }, 600);
    }
    document.getElementById('formBuilderTitle').addEventListener('input', function () {
        state.title = this.value;
        updatePreview();
        scheduleSaveFormData();
    });
    document.getElementById('formBuilderDesc').addEventListener('input', function () {
        state.desc = this.value;
        updatePreview();
        scheduleSaveFormData();
    });
    var guardarBtn = document.getElementById('formBuilderBtnGuardar');
    if (guardarBtn) guardarBtn.addEventListener('click', guardarTodo);
    document.getElementById('formBuilderBtnNew').addEventListener('click', addQuestion);
    document.querySelectorAll('.form-builder-tab').forEach(function (btn) {
        btn.addEventListener('click', function () { setTab(btn.getAttribute('data-tab')); });
    });
    document.getElementById('formBuilderBtnSubmit').addEventListener('click', guardarTodo);

    var backLink = document.querySelector('.form-builder-back');
    if (backLink) {
        backLink.addEventListener('click', function (e) {
            if (window.self !== window.top) {
                e.preventDefault();
                window.parent.postMessage('formBuilderClose', '*');
            }
        });
    }

    loadQuestions();
})();
