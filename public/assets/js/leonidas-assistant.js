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
        firstMessage.textContent = 'Hola, ' + firstName + '. ¿Qué batallas tendremos hoy?';
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
    }

    function responseFor(message) {
        var value = message.toLowerCase();
        if (/hola|buenas|saludos/.test(value)) {
            return 'Aquí estoy. Dime qué parte de Sparta quieres revisar y preparo el siguiente paso.';
        }
        if (/revis|analiz|buscar|consult/.test(value)) {
            return 'Entendido. Esta primera fase registra tu solicitud y te ayudará a preparar la revisión. La conexión con IA y consultas seguras será el siguiente paso.';
        }
        if (/cambia|actualiza|elimina|guarda|ejecut/.test(value)) {
            return 'Puedo preparar esa tarea, pero todavía no ejecutaré cambios. Las acciones se habilitarán con confirmación explícita y permisos controlados.';
        }
        return 'Recibí tu instrucción. Aún estoy en mi primera fase: converso y preparo tareas; pronto podremos conectarme a la IA y a herramientas autorizadas de Sparta.';
    }

    toggle.addEventListener('click', function () {
        root.classList.contains('is-open') ? closePanel(false) : openPanel(true);
    });
    function startHoverGreeting() {
        triggerGreeting();
    }

    toggle.addEventListener('mouseenter', startHoverGreeting);
    toggle.addEventListener('pointerenter', startHoverGreeting);
    close.addEventListener('click', function () { closePanel(false); });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var value = input.value.trim();
        if (!value) return;
        addMessage(value, 'user');
        input.value = '';
        root.classList.add('is-thinking');
        window.setTimeout(function () {
            root.classList.remove('is-thinking');
            addMessage(responseFor(value), 'assistant');
        }, 420);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && root.classList.contains('is-open')) closePanel(true);
    });

    if (localStorage.getItem(storageKey) === '1') openPanel(false);
    else {
        triggerGreeting();
    }
})();
