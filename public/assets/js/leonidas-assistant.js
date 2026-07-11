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
    var firstMessage = messages.querySelector('.leonidas-message--assistant');
    var sendButton = form.querySelector('button[type="submit"]');
    var storageKey = 'sparta.leonidas.open';
    var userName = (root.getAttribute('data-leonidas-user') || 'comandante').trim();
    var firstName = (userName.split(/\s+/)[0] || 'comandante').toLocaleLowerCase('es-MX');
    firstName = firstName.charAt(0).toLocaleUpperCase('es-MX') + firstName.slice(1);

    function triggerGreeting() {
        root.classList.add('is-greeting');
        window.clearTimeout(root._leonidasGreetingTimer);
        root._leonidasGreetingTimer = window.setTimeout(function () {
            root.classList.remove('is-greeting');
        }, 1800);
    }

    function triggerVictory() {
        root.classList.remove('is-greeting');
        root.classList.add('is-victory');
        window.clearTimeout(root._leonidasVictoryTimer);
        root._leonidasVictoryTimer = window.setTimeout(function () {
            root.classList.remove('is-victory');
        }, 2200);
    }

    if (firstMessage) {
        firstMessage.textContent = 'Hola, ' + firstName + '. Que batallas tendremos hoy?';
    }

    function openPanel(celebrate) {
        root.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        localStorage.setItem(storageKey, '1');
        if (celebrate) triggerVictory();
        else triggerGreeting();
        window.setTimeout(function () { input.focus(); }, 180);
    }

    function closePanel(restoreFocus) {
        root.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        localStorage.removeItem(storageKey);
        if (restoreFocus) toggle.focus();
    }

    function addMessage(text, type) {
        var item = document.createElement('article');
        item.className = 'leonidas-message leonidas-message--' + type;
        item.textContent = text;
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
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

    function request(endpoint, payload) {
        return window.fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (response) {
            return response.json().catch(function () {
                return { success: false, error: 'El servidor devolvio una respuesta no valida.' };
            });
        }).then(function (data) {
            if (!data || data.success !== true) {
                throw new Error((data && data.error) || 'Leonidas no pudo completar la solicitud.');
            }
            return data.respuesta || {};
        });
    }

    function addProposal(proposal) {
        if (!proposal || !proposal.token || !proposal.requiere_confirmacion) return;
        var wrapper = document.createElement('div');
        wrapper.className = 'leonidas-proposal';
        var label = document.createElement('span');
        var button = document.createElement('button');
        label.textContent = proposal.resumen || 'Accion sensible';
        button.type = 'button';
        button.textContent = 'Confirmar';
        button.addEventListener('click', function () {
            button.disabled = true;
            request('/Leonidas/confirmar', { token: proposal.token })
                .then(function (response) {
                    addMessage(response.mensaje || 'Confirmacion registrada.', 'assistant');
                    wrapper.remove();
                })
                .catch(function (error) {
                    addMessage(error.message || 'No se pudo confirmar la solicitud.', 'assistant');
                    button.disabled = false;
                });
        });
        wrapper.appendChild(label);
        wrapper.appendChild(button);
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;
    }

    function renderResponse(response) {
        addMessage(response.mensaje || 'Leonidas preparo una respuesta sin cambios.', 'assistant');
        addPeople(response.personas);
        addProposal(response.propuesta);
        if (response.navegar_a) {
            window.setTimeout(function () {
                window.location.assign(response.navegar_a);
            }, 450);
        }
    }

    function setSending(sending) {
        root.classList.toggle('is-thinking', sending);
        input.disabled = sending;
        sendButton.disabled = sending;
    }

    toggle.addEventListener('click', function () {
        root.classList.contains('is-open') ? closePanel(false) : openPanel(true);
    });
    toggle.addEventListener('mouseenter', triggerGreeting);
    toggle.addEventListener('pointerenter', triggerGreeting);
    close.addEventListener('click', function () { closePanel(false); });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var value = input.value.trim();
        if (!value) return;
        addMessage(value, 'user');
        input.value = '';
        setSending(true);
        request('/Leonidas/conversar', { mensaje: value })
            .then(renderResponse)
            .catch(function (error) {
                addMessage(error.message || 'No pude procesar la instruccion. No se realizo ningun cambio.', 'assistant');
            })
            .finally(function () {
                setSending(false);
                input.focus();
            });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && root.classList.contains('is-open')) closePanel(true);
    });

    if (localStorage.getItem(storageKey) === '1') openPanel(false);
    else triggerGreeting();
})();
