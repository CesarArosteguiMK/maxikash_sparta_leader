(function () {
    'use strict';

    var root = document.getElementById('leonidasAssistant');
    if (!root) return;

    var panel = root.querySelector('.leonidas-panel');
    var toggle = root.querySelector('[data-leonidas-toggle]');
    var close = root.querySelector('[data-leonidas-close]');
    var form = root.querySelector('[data-leonidas-form]');
    var input = root.querySelector('[data-leonidas-input]');
    var messages = root.querySelector('[data-leonidas-messages]');
    var sendButton = form ? form.querySelector('button[type="submit"]') : null;
    var isOwner = root.getAttribute('data-leonidas-owner') === '1';
    var personaId = root.getAttribute('data-leonidas-persona') || '0';
    var storageKey = 'sparta.leonidas.open.' + personaId;
    var userName = (root.getAttribute('data-leonidas-user') || 'comandante').trim();
    var firstName = (userName.split(/\s+/)[0] || 'comandante').toLocaleLowerCase('es-MX');
    var polling = false;
    var currentDeliveryId = null;
    var firstMessage = messages ? messages.querySelector('.leonidas-message--assistant') : null;
    firstName = firstName.charAt(0).toLocaleUpperCase('es-MX') + firstName.slice(1);

    if (form && !isOwner) form.hidden = true;
    if (firstMessage) {
        firstMessage.textContent = isOwner
            ? 'Hola, ' + firstName + '. ¿Qué batallas tendremos hoy?'
            : 'Leónidas te mostrará aquí los mensajes que recibas.';
    }

    function triggerGreeting() {
        if (!root.classList.contains('is-3d-ready')) {
            root._leonidasGreetingPending = true;
            return;
        }
        root._leonidasGreetingPending = false;
        root.classList.remove('is-victory');
        root.classList.add('is-greeting');
        window.clearTimeout(root._leonidasGreetingTimer);
        root._leonidasGreetingTimer = window.setTimeout(function () {
            root.classList.remove('is-greeting');
        }, 2600);
    }

    function triggerVictory() {
        if (!root.classList.contains('is-3d-ready')) {
            root._leonidasVictoryPending = true;
            return;
        }
        root._leonidasVictoryPending = false;
        root.classList.remove('is-greeting');
        root.classList.add('is-victory');
        window.clearTimeout(root._leonidasVictoryTimer);
        root._leonidasVictoryTimer = window.setTimeout(function () {
            root.classList.remove('is-victory');
        }, 3000);
    }

    function triggerSpeaking() {
        root.classList.add('is-speaking');
        window.clearTimeout(root._leonidasSpeakingTimer);
        root._leonidasSpeakingTimer = window.setTimeout(function () {
            root.classList.remove('is-speaking');
        }, 2100);
    }

    function openPanel(celebrate) {
        root.classList.remove('is-recipient-idle');
        root.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        if (isOwner) localStorage.setItem(storageKey, '1');
        if (celebrate) triggerVictory();
        else triggerGreeting();
        if (isOwner && input) {
            window.setTimeout(function () { input.focus(); }, 180);
        }
    }

    function closePanel(restoreFocus) {
        root.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        if (isOwner) localStorage.removeItem(storageKey);
        if (restoreFocus && !root.classList.contains('is-recipient-idle')) toggle.focus();
    }

    function hideRecipientSoon() {
        if (isOwner) return;
        window.setTimeout(function () {
            closePanel(false);
            root.classList.add('is-recipient-idle');
        }, 2200);
    }

    function addMessage(text, type) {
        var item = document.createElement('article');
        item.className = 'leonidas-message leonidas-message--' + type;
        item.textContent = text;
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
        if (type === 'assistant') triggerSpeaking();
        return item;
    }

    function addPeople(people) {
        if (!Array.isArray(people) || !people.length) return;
        var list = document.createElement('div');
        list.className = 'leonidas-results';
        people.forEach(function (person) {
            var row = document.createElement('div');
            row.className = 'leonidas-result';
            var name = document.createElement('strong');
            var details = document.createElement('small');
            name.textContent = person.nombre || 'Sin nombre';
            details.textContent = 'No. empleado: ' + (person.numero_empleado || 'sin asignar') + ' | ' + (person.estatus || 'Sin estatus');
            row.appendChild(name);
            row.appendChild(details);
            list.appendChild(row);
        });
        messages.appendChild(list);
        messages.scrollTop = messages.scrollHeight;
    }

    function addReport(report) {
        if (!report || !Array.isArray(report.filas)) return;
        var wrapper = document.createElement('section');
        wrapper.className = 'leonidas-report';
        var heading = document.createElement('div');
        heading.className = 'leonidas-report__heading';
        var title = document.createElement('strong');
        var total = document.createElement('span');
        var reportTotal = Number(report.total || report.filas.length);
        title.textContent = report.titulo || 'Reporte';
        total.textContent = String(reportTotal) + (reportTotal === 1 ? ' registro' : ' registros');
        heading.appendChild(title);
        heading.appendChild(total);
        wrapper.appendChild(heading);

        var list = document.createElement('div');
        list.className = 'leonidas-report__list';
        report.filas.slice(0, 12).forEach(function (row) {
            var item = document.createElement('div');
            item.className = 'leonidas-report__row';
            var name = document.createElement('strong');
            var details = document.createElement('small');
            var ignored = { id: true, nombre: true, etiqueta: true, detalle: true };
            var titleValue = row.nombre || row.etiqueta || '';
            if (!titleValue) {
                Object.keys(row).some(function (key) {
                    if (!ignored[key] && row[key] !== '' && row[key] !== null && typeof row[key] !== 'object') {
                        titleValue = String(row[key]);
                        ignored[key] = true;
                        return true;
                    }
                    return false;
                });
            }
            var detailParts = [];
            if (row.detalle) detailParts.push(String(row.detalle));
            Object.keys(row).forEach(function (key) {
                if (detailParts.length >= 3 || ignored[key] || row[key] === '' || row[key] === null || typeof row[key] === 'object') return;
                detailParts.push(key.replace(/_/g, ' ') + ': ' + String(row[key]));
            });
            name.textContent = titleValue || 'Sin dato';
            details.textContent = detailParts.join(' | ') || 'Sin detalle adicional';
            item.appendChild(name);
            item.appendChild(details);
            list.appendChild(item);
        });
        wrapper.appendChild(list);

        if (reportTotal > 12) {
            var remainder = document.createElement('small');
            remainder.className = 'leonidas-report__remainder';
            remainder.textContent = 'Se muestran ' + Math.min(12, report.filas.length) + ' de ' + reportTotal + ' registros.';
            wrapper.appendChild(remainder);
        }
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;
    }

    function request(endpoint, payload) {
        return window.fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload || {})
        }).then(function (response) {
            return response.json().catch(function () {
                return { success: false, error: 'El servidor devolvió una respuesta no válida.' };
            });
        }).then(function (data) {
            if (!data || data.success !== true) {
                throw new Error((data && data.error) || 'Leónidas no pudo completar la solicitud.');
            }
            return data.respuesta || {};
        });
    }

    function addProposal(proposal) {
        if (!proposal || !proposal.token || !proposal.requiere_confirmacion) return;
        var wrapper = document.createElement('div');
        wrapper.className = 'leonidas-proposal';
        var label = document.createElement('span');
        var actions = document.createElement('div');
        var cancelButton = document.createElement('button');
        var editButton = null;
        var confirmButton = document.createElement('button');
        actions.className = 'leonidas-proposal__actions';
        label.textContent = proposal.resumen || 'Acción sensible';
        cancelButton.type = 'button';
        cancelButton.className = 'leonidas-proposal__cancel';
        cancelButton.textContent = 'Cancelar';
        confirmButton.type = 'button';
        confirmButton.textContent = 'Confirmar';

        if (proposal.accion === 'mensaje') {
            editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'leonidas-proposal__edit';
            editButton.textContent = 'Redactar de nuevo';
        }

        function disableProposal(disabled) {
            cancelButton.disabled = disabled;
            if (editButton) editButton.disabled = disabled;
            confirmButton.disabled = disabled;
        }

        cancelButton.addEventListener('click', function () {
            disableProposal(true);
            request('/Leonidas/cancelar', { token: proposal.token })
                .then(function (response) {
                    addMessage(response.mensaje || 'Solicitud cancelada.', 'assistant');
                    wrapper.remove();
                })
                .catch(function (error) {
                    addMessage(error.message || 'No se pudo cancelar la solicitud.', 'assistant');
                    disableProposal(false);
                });
        });
        if (editButton) {
            editButton.addEventListener('click', function () {
                disableProposal(true);
                request('/Leonidas/editarMensaje', { token: proposal.token })
                    .then(function (response) {
                        wrapper.remove();
                        renderResponse(response);
                        if (input) input.focus();
                    })
                    .catch(function (error) {
                        addMessage(error.message || 'No se pudo preparar la nueva redaccion.', 'assistant');
                        disableProposal(false);
                    });
            });
        }
        confirmButton.addEventListener('click', function () {
            disableProposal(true);
            request('/Leonidas/confirmar', { token: proposal.token })
                .then(function (response) {
                    addMessage(response.mensaje || 'Confirmación registrada.', 'assistant');
                    wrapper.remove();
                    pollMailbox();
                })
                .catch(function (error) {
                    addMessage(error.message || 'No se pudo confirmar la solicitud.', 'assistant');
                    disableProposal(false);
                });
        });
        actions.appendChild(cancelButton);
        if (editButton) actions.appendChild(editButton);
        actions.appendChild(confirmButton);
        wrapper.appendChild(label);
        wrapper.appendChild(actions);
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;
    }

    function renderResponse(response) {
        if (response.reemplaza_propuesta) {
            messages.querySelectorAll('.leonidas-proposal').forEach(function (proposal) {
                proposal.remove();
            });
        }
        addMessage(response.mensaje || 'Leónidas preparó una respuesta sin cambios.', 'assistant');
        addPeople(response.personas);
        addReport(response.reporte);
        addProposal(response.propuesta);
        if (response.navegar_a) {
            window.setTimeout(function () { window.location.assign(response.navegar_a); }, 450);
        }
    }

    function setSending(sending) {
        root.classList.toggle('is-thinking', sending);
        if (input) input.disabled = sending;
        if (sendButton) sendButton.disabled = sending;
    }

    function reactionLabel(code) {
        return { like: '👍', love: '❤️', laugh: '😂', ok: '✅' }[code] || '✅';
    }

    function renderDelivery(delivery) {
        if (!delivery || !delivery.id || Number(delivery.id) === Number(currentDeliveryId)) return;
        currentDeliveryId = Number(delivery.id);
        root.classList.remove('is-recipient-idle');
        root.classList.add('is-delivering');
        openPanel(false);
        triggerSpeaking();
        addMessage('Hola, ' + firstName + '. ' + delivery.remitente + ' te mandó decir:', 'assistant');

        var quote = document.createElement('blockquote');
        quote.className = 'leonidas-delivery__quote';
        quote.textContent = delivery.mensaje || '';
        messages.appendChild(quote);

        var actions = document.createElement('section');
        var prompt = document.createElement('strong');
        var responseRow = document.createElement('div');
        var responseInput = document.createElement('input');
        var responseButton = document.createElement('button');
        var reactions = document.createElement('div');
        var dismissButton = document.createElement('button');
        actions.className = 'leonidas-delivery';
        prompt.textContent = '¿Deseas responder, reaccionar o dejarlo sin respuesta?';
        responseRow.className = 'leonidas-delivery__response';
        responseInput.type = 'text';
        responseInput.maxLength = 1000;
        responseInput.placeholder = 'Escribe una respuesta...';
        responseButton.type = 'button';
        responseButton.textContent = 'Responder';
        reactions.className = 'leonidas-delivery__reactions';
        dismissButton.type = 'button';
        dismissButton.className = 'leonidas-delivery__dismiss';
        dismissButton.textContent = 'Nada por ahora';

        function disableActions(disabled) {
            actions.querySelectorAll('button, input').forEach(function (element) {
                element.disabled = disabled;
            });
        }

        function submitResponse(type, content) {
            disableActions(true);
            request('/Leonidas/responder', {
                mensaje_id: delivery.id,
                tipo: type,
                contenido: content || ''
            }).then(function (response) {
                actions.remove();
                addMessage(response.mensaje || 'Respuesta registrada.', 'assistant');
                currentDeliveryId = null;
                root.classList.remove('is-delivering', 'is-speaking');
                hideRecipientSoon();
            }).catch(function (error) {
                addMessage(error.message || 'No se pudo registrar tu respuesta.', 'assistant');
                disableActions(false);
            });
        }

        responseButton.addEventListener('click', function () {
            var value = responseInput.value.trim();
            if (!value) {
                responseInput.focus();
                return;
            }
            submitResponse('respuesta', value);
        });
        responseInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                responseButton.click();
            }
        });
        ['like', 'love', 'laugh', 'ok'].forEach(function (code) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'leonidas-delivery__reaction';
            button.textContent = reactionLabel(code);
            button.setAttribute('aria-label', 'Reaccionar con ' + reactionLabel(code));
            button.addEventListener('click', function () { submitResponse('reaccion', code); });
            reactions.appendChild(button);
        });
        dismissButton.addEventListener('click', function () { submitResponse('descartado', ''); });
        responseRow.appendChild(responseInput);
        responseRow.appendChild(responseButton);
        actions.appendChild(prompt);
        actions.appendChild(responseRow);
        actions.appendChild(reactions);
        actions.appendChild(dismissButton);
        messages.appendChild(actions);
        messages.scrollTop = messages.scrollHeight;
        window.setTimeout(function () { responseInput.focus(); }, 180);
    }

    function renderNews(news) {
        if (!Array.isArray(news) || !news.length) return;
        root.classList.remove('is-recipient-idle');
        openPanel(false);
        news.forEach(function (item) {
            var text;
            if (item.tipo === 'respuesta') {
                text = 'Volví con una respuesta de ' + item.destinatario + ': “' + item.contenido + '”';
            } else if (item.tipo === 'reaccion') {
                text = item.destinatario + ' reaccionó ' + reactionLabel(item.contenido) + ' a tu mensaje.';
            } else {
                text = item.destinatario + ' vio tu mensaje y decidió no responder por ahora.';
            }
            addMessage(text, 'assistant');
        });
        triggerGreeting();
    }

    function pollMailbox() {
        if (polling || document.hidden) return;
        polling = true;
        request('/Leonidas/bandeja', {})
            .then(function (mailbox) {
                renderDelivery(mailbox.entrega);
                renderNews(mailbox.novedades);
            })
            .catch(function () {
                // El sondeo es silencioso para no llenar el chat durante una falla temporal.
            })
            .finally(function () { polling = false; });
    }

    toggle.addEventListener('click', function () {
        if (root.classList.contains('is-open')) {
            triggerVictory();
            closePanel(false);
            return;
        }
        openPanel(true);
    });
    toggle.addEventListener('pointerenter', triggerGreeting);
    toggle.addEventListener('focus', triggerGreeting);
    root.addEventListener('leonidas:model-ready', function () {
        if (root._leonidasVictoryPending) {
            triggerVictory();
            return;
        }
        triggerGreeting();
    });
    close.addEventListener('click', function () {
        closePanel(false);
        if (!isOwner && currentDeliveryId === null) root.classList.add('is-recipient-idle');
    });

    if (form && isOwner) {
        function resizeComposer() {
            if (!input || input.tagName !== 'TEXTAREA') return;
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 96) + 'px';
        }

        input.addEventListener('input', resizeComposer);
        input.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') return;
            if (event.ctrlKey || event.metaKey || event.shiftKey) {
                window.setTimeout(resizeComposer, 0);
                return;
            }
            event.preventDefault();
            form.requestSubmit();
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var value = input.value.trim();
            if (!value) return;
            addMessage(value, 'user');
            input.value = '';
            resizeComposer();
            setSending(true);
            request('/Leonidas/conversar', { mensaje: value })
                .then(renderResponse)
                .catch(function (error) {
                    addMessage(error.message || 'No pude procesar la instrucción. No se realizó ningún cambio.', 'assistant');
                })
                .finally(function () {
                    setSending(false);
                    input.focus();
                });
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && root.classList.contains('is-open')) closePanel(true);
    });
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) pollMailbox();
    });

    if (isOwner && localStorage.getItem(storageKey) === '1') openPanel(false);
    else if (isOwner) triggerGreeting();
    pollMailbox();
    window.setInterval(pollMailbox, 6000);
})();
