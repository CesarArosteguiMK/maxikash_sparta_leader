(function () {
    'use strict';

    var root = document.getElementById('leonidasAssistant');
    if (!root) return;

    var panel = root.querySelector('.leonidas-panel');
    var toggle = root.querySelector('[data-leonidas-toggle]');
    var close = root.querySelector('[data-leonidas-close]');
    var voiceButton = root.querySelector('[data-leonidas-voice]');
    var form = root.querySelector('[data-leonidas-form]');
    var input = root.querySelector('[data-leonidas-input]');
    var fileInput = root.querySelector('[data-leonidas-file]');
    var attachmentBox = root.querySelector('[data-leonidas-attachment]');
    var attachButton = root.querySelector('.leonidas-composer__attach');
    var messages = root.querySelector('[data-leonidas-messages]');
    var sendButton = form ? form.querySelector('button[type="submit"]') : null;
    var isOwner = root.getAttribute('data-leonidas-owner') === '1';
    var personaId = root.getAttribute('data-leonidas-persona') || '0';
    var storageKey = 'sparta.leonidas.open.' + personaId;
    var conversationKey = 'sparta.leonidas.conversation.' + personaId;
    var voiceStorageKey = 'sparta.leonidas.voice.' + personaId;
    var userName = (root.getAttribute('data-leonidas-user') || 'comandante').trim();
    var firstName = (userName.split(/\s+/)[0] || 'comandante').toLocaleLowerCase('es-MX');
    var polling = false;
    var currentDeliveryId = null;
    var restoringConversation = false;
    var firstMessage = messages ? messages.querySelector('.leonidas-message--assistant') : null;
    var voiceEnabled = localStorage.getItem(voiceStorageKey) !== '0';
    var voiceRequestSerial = 0;
    var currentAudio = null;
    var voiceAudioContext = null;
    var voiceStreamController = null;
    var voiceStreamSources = [];
    var pendingVoiceText = '';
    var welcomeSpoken = false;
    var currentAttachment = null;
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

    function updateVoiceButton() {
        if (!voiceButton) return;
        voiceButton.classList.toggle('is-enabled', voiceEnabled);
        voiceButton.classList.remove('is-loading', 'has-error');
        voiceButton.setAttribute('aria-pressed', voiceEnabled ? 'true' : 'false');
        voiceButton.setAttribute('aria-label', voiceEnabled ? 'Silenciar voz de Leónidas' : 'Activar voz de Leónidas');
        voiceButton.title = voiceEnabled ? 'Silenciar voz' : 'Activar voz';
        voiceButton.innerHTML = voiceEnabled
            ? '<i class="fa-solid fa-volume-high"></i>'
            : '<i class="fa-solid fa-volume-xmark"></i>';
    }

    function cancelVoicePlayback() {
        if (voiceStreamController) {
            voiceStreamController.abort();
            voiceStreamController = null;
        }
        voiceStreamSources.forEach(function (source) {
            try { source.stop(0); } catch (ignore) {}
        });
        voiceStreamSources = [];
        if (currentAudio) {
            currentAudio.pause();
            currentAudio = null;
        }
        root.classList.remove('is-speaking');
        if (voiceButton) voiceButton.classList.remove('is-loading');
    }

    function stopVoice() {
        voiceRequestSerial += 1;
        cancelVoicePlayback();
    }

    function setVoiceEnabled(enabled) {
        voiceEnabled = enabled === true;
        localStorage.setItem(voiceStorageKey, voiceEnabled ? '1' : '0');
        if (!voiceEnabled) stopVoice();
        updateVoiceButton();
    }

    function showVoiceError() {
        if (!voiceButton) return;
        voiceButton.classList.remove('is-loading');
        voiceButton.classList.add('has-error');
        voiceButton.title = 'La voz no está disponible temporalmente';
        window.setTimeout(function () {
            if (voiceEnabled) updateVoiceButton();
        }, 3500);
    }

    function ensureVoiceContext() {
        var AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return null;
        if (!voiceAudioContext) {
            voiceAudioContext = new AudioContextClass();
            root.setAttribute('data-leonidas-voice-state', voiceAudioContext.state);
            voiceAudioContext.addEventListener('statechange', function () {
                root.setAttribute('data-leonidas-voice-state', voiceAudioContext.state);
            });
        }
        return voiceAudioContext;
    }

    function unlockVoiceContext() {
        if (!voiceEnabled) return Promise.resolve(false);
        var context = ensureVoiceContext();
        if (!context) return Promise.resolve(false);
        root.setAttribute('data-leonidas-voice-mode', 'unlocking');
        var resumed = context.state === 'suspended' ? context.resume() : Promise.resolve();
        return resumed.then(function () {
            root.setAttribute('data-leonidas-voice-state', context.state);
            if (context.state !== 'running') {
                root.setAttribute('data-leonidas-voice-mode', 'blocked');
                return false;
            }
            root.setAttribute('data-leonidas-voice-mode', 'ready');
            if (pendingVoiceText) {
                var queuedText = pendingVoiceText;
                pendingVoiceText = '';
                speakText(queuedText);
            }
            return true;
        }).catch(function () {
            root.setAttribute('data-leonidas-voice-mode', 'blocked');
            return false;
        });
    }

    function playVoiceAudio(audioUrl, serial) {
        var context = ensureVoiceContext();
        if (!context || context.state !== 'running') {
            throw new Error('El navegador aun no ha habilitado el audio.');
        }
        return window.fetch(audioUrl, { credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) throw new Error('No se pudo descargar el audio generado.');
                return response.arrayBuffer();
            })
            .then(function (audioBytes) {
                return context.decodeAudioData(audioBytes);
            })
            .then(function (audioBuffer) {
                if (!voiceEnabled || serial !== voiceRequestSerial) return;
                var source = context.createBufferSource();
                var gain = context.createGain();
                source.buffer = audioBuffer;
                gain.gain.value = 0.92;
                source.connect(gain);
                gain.connect(context.destination);
                currentAudio = {
                    pause: function () {
                        try { source.stop(0); } catch (ignore) {}
                    }
                };
                source.onended = function () {
                    if (serial !== voiceRequestSerial) return;
                    currentAudio = null;
                    root.classList.remove('is-speaking');
                };
                root.classList.add('is-speaking');
                root.setAttribute('data-leonidas-voice-mode', 'fallback-playing');
                if (voiceButton) voiceButton.classList.remove('is-loading', 'has-error');
                source.start(0);
            });
    }

    function decodePcmBytes(encoded) {
        var binary = window.atob(String(encoded || ''));
        var bytes = new Uint8Array(binary.length);
        for (var index = 0; index < binary.length; index += 1) {
            bytes[index] = binary.charCodeAt(index);
        }
        return bytes;
    }

    function removeStreamSource(source) {
        var index = voiceStreamSources.indexOf(source);
        if (index >= 0) voiceStreamSources.splice(index, 1);
    }

    function schedulePcmChunk(encoded, state, serial) {
        if (!voiceEnabled || serial !== voiceRequestSerial) return;
        var context = ensureVoiceContext();
        if (!context || context.state !== 'running') {
            throw new Error('El navegador aun no ha habilitado el audio.');
        }

        var incoming = decodePcmBytes(encoded);
        var bytes = incoming;
        if (state.carry !== null) {
            bytes = new Uint8Array(incoming.length + 1);
            bytes[0] = state.carry;
            bytes.set(incoming, 1);
            state.carry = null;
        }
        if (bytes.length % 2 !== 0) {
            state.carry = bytes[bytes.length - 1];
            bytes = bytes.subarray(0, bytes.length - 1);
        }
        if (bytes.length === 0) return;

        var samples = bytes.length / 2;
        var audioBuffer = context.createBuffer(1, samples, state.sampleRate);
        var channel = audioBuffer.getChannelData(0);
        var view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
        for (var sample = 0; sample < samples; sample += 1) {
            channel[sample] = view.getInt16(sample * 2, true) / 32768;
        }

        var source = context.createBufferSource();
        var gain = context.createGain();
        source.buffer = audioBuffer;
        gain.gain.value = 0.92;
        source.connect(gain);
        gain.connect(context.destination);
        var startAt = Math.max(context.currentTime + 0.055, state.nextStartAt);
        state.nextStartAt = startAt + audioBuffer.duration;
        state.pending += 1;
        voiceStreamSources.push(source);
        source.onended = function () {
            removeStreamSource(source);
            state.pending = Math.max(0, state.pending - 1);
            if (serial === voiceRequestSerial && state.done && state.pending === 0) {
                root.classList.remove('is-speaking');
            }
        };
        source.start(startAt);

        if (!state.hadAudio) {
            state.hadAudio = true;
            state.firstAudioAt = window.performance ? window.performance.now() : Date.now();
            root.classList.add('is-speaking');
            root.setAttribute('data-leonidas-voice-mode', 'realtime-playing');
            root.setAttribute('data-leonidas-voice-latency', String(Math.round(state.firstAudioAt - state.requestedAt)));
            if (voiceButton) voiceButton.classList.remove('is-loading', 'has-error');
        }
    }

    function playRealtimeVoice(text, serial) {
        var context = ensureVoiceContext();
        if (!context || context.state !== 'running' || !window.ReadableStream || !window.TextDecoder) {
            return Promise.reject(new Error('Streaming de audio no disponible.'));
        }

        var controller = new AbortController();
        var state = {
            sampleRate: 24000,
            nextStartAt: 0,
            pending: 0,
            done: false,
            hadAudio: false,
            carry: null,
            requestedAt: window.performance ? window.performance.now() : Date.now()
        };
        voiceStreamController = controller;

        return window.fetch('/Leonidas/vozTiempoReal', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/x-ndjson' },
            body: JSON.stringify({ texto: text }),
            signal: controller.signal
        }).then(function (response) {
            if (!response.ok || !response.body) {
                throw new Error('La voz en tiempo real no respondio correctamente.');
            }
            var reader = response.body.getReader();
            var decoder = new TextDecoder('utf-8');
            var buffered = '';

            function processLine(line) {
                line = String(line || '').trim();
                if (line === '') return;
                var event = JSON.parse(line);
                if (event.type === 'meta' && Number(event.sample_rate) > 0) {
                    state.sampleRate = Number(event.sample_rate);
                } else if (event.type === 'audio' && event.delta) {
                    schedulePcmChunk(event.delta, state, serial);
                } else if (event.type === 'error') {
                    var streamError = new Error(event.message || 'La voz en tiempo real fallo.');
                    streamError.hadAudio = state.hadAudio;
                    throw streamError;
                } else if (event.type === 'done') {
                    state.done = true;
                }
            }

            function consume() {
                return reader.read().then(function (result) {
                    if (result.done) {
                        buffered += decoder.decode();
                        if (buffered.trim() !== '') processLine(buffered);
                        state.done = true;
                        if (state.pending === 0 && serial === voiceRequestSerial) {
                            root.classList.remove('is-speaking');
                        }
                        return state;
                    }
                    buffered += decoder.decode(result.value, { stream: true });
                    var lines = buffered.split('\n');
                    buffered = lines.pop() || '';
                    lines.forEach(processLine);
                    return consume();
                });
            }

            return consume();
        }).then(function () {
            if (voiceStreamController === controller) voiceStreamController = null;
            return state;
        }).catch(function (error) {
            if (voiceStreamController === controller) voiceStreamController = null;
            error.hadAudio = error.hadAudio === true || state.hadAudio;
            throw error;
        });
    }

    function playFallbackVoice(text, serial) {
        return request('/Leonidas/voz', { texto: text }, 50000)
            .then(function (voice) {
                if (!voiceEnabled || serial !== voiceRequestSerial || !voice.audio_url) return;
                return playVoiceAudio(voice.audio_url, serial);
            });
    }

    function speakText(text) {
        text = String(text || '').trim();
        if (!voiceEnabled || text === '') {
            triggerSpeaking();
            return;
        }

        var context = ensureVoiceContext();
        if (context && context.state !== 'running') {
            pendingVoiceText = text;
            root.setAttribute('data-leonidas-voice-state', context.state);
            root.setAttribute('data-leonidas-voice-mode', 'pending-unlock');
            if (voiceButton) voiceButton.classList.remove('is-loading', 'has-error');
            return;
        }

        var serial = ++voiceRequestSerial;
        cancelVoicePlayback();
        root.setAttribute('data-leonidas-voice-mode', 'realtime-request');
        root.removeAttribute('data-leonidas-voice-error');
        if (voiceButton) voiceButton.classList.add('is-loading');

        playRealtimeVoice(text, serial)
            .catch(function (streamError) {
                if (serial !== voiceRequestSerial || streamError.name === 'AbortError') return;
                if (streamError.hadAudio) throw streamError;
                root.setAttribute('data-leonidas-voice-mode', 'fallback-request');
                return playFallbackVoice(text, serial);
            })
            .catch(function (error) {
                if (serial !== voiceRequestSerial) return;
                root.setAttribute('data-leonidas-voice-mode', 'error');
                root.setAttribute('data-leonidas-voice-error', String(error && error.message ? error.message : 'unknown'));
                triggerSpeaking();
                showVoiceError();
            });
    }

    function triggerDeliveryWalk(direction) {
        if (!root.classList.contains('is-3d-ready')) return;
        root.dispatchEvent(new CustomEvent('leonidas:delivery-walk', {
            detail: { direction: direction === 'arrive' ? 'arrive' : 'depart' }
        }));
    }

    function openPanel(celebrate) {
        root.classList.remove('is-recipient-idle');
        root.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        if (isOwner) localStorage.setItem(storageKey, '1');
        if (celebrate) triggerVictory();
        else if (!root.classList.contains('is-delivering')) triggerGreeting();
        if (!welcomeSpoken && firstMessage && voiceEnabled) {
            welcomeSpoken = true;
            window.setTimeout(function () {
                speakText((firstMessage.textContent || '').trim());
            }, 220);
        }
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

    function addMessage(text, type, silent) {
        var item = document.createElement('article');
        item.className = 'leonidas-message leonidas-message--' + type;
        item.textContent = text;
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
        if (type === 'assistant' && !silent) speakText(text);
        persistConversation();
        return item;
    }

    function persistConversation(arrivalName) {
        if (!isOwner || !messages || restoringConversation) return;
        try {
            var rows = Array.prototype.slice.call(messages.querySelectorAll('.leonidas-message'))
                .filter(function (item) {
                    return !item.classList.contains('leonidas-message--thinking');
                })
                .map(function (item) {
                    return {
                        type: item.classList.contains('leonidas-message--user') ? 'user' : 'assistant',
                        text: (item.textContent || '').trim()
                    };
                })
                .filter(function (item) { return item.text !== ''; })
                .slice(-40);

            var previous = {};
            try {
                previous = JSON.parse(sessionStorage.getItem(conversationKey) || '{}') || {};
            } catch (ignore) {
                previous = {};
            }
            sessionStorage.setItem(conversationKey, JSON.stringify({
                messages: rows,
                arrival: typeof arrivalName === 'string' ? arrivalName : (previous.arrival || '')
            }));
        } catch (ignore) {
            // La conversación sigue funcionando aunque el navegador bloquee sessionStorage.
        }
    }

    function restoreConversation() {
        if (!isOwner || !messages) return false;
        var state;
        try {
            state = JSON.parse(sessionStorage.getItem(conversationKey) || 'null');
        } catch (ignore) {
            state = null;
        }
        if (!state || !Array.isArray(state.messages) || !state.messages.length) return false;

        restoringConversation = true;
        messages.innerHTML = '';
        state.messages.slice(-40).forEach(function (item) {
            if (!item || (item.type !== 'user' && item.type !== 'assistant')) return;
            addMessage(String(item.text || ''), item.type, true);
        });
        if (state.arrival) {
            addMessage('Listo, ya estás en ' + state.arrival + '.', 'assistant', true);
            state.arrival = '';
        }
        restoringConversation = false;
        persistConversation('');
        messages.scrollTop = messages.scrollHeight;
        return true;
    }

    function addThinkingIndicator() {
        var item = document.createElement('article');
        var label = document.createElement('span');
        var dots = document.createElement('span');
        item.className = 'leonidas-message leonidas-message--assistant leonidas-message--thinking';
        item.setAttribute('role', 'status');
        item.setAttribute('aria-live', 'polite');
        item.setAttribute('aria-label', 'Leónidas está consultando');
        label.className = 'leonidas-visually-hidden';
        label.textContent = 'Leónidas está consultando';
        dots.className = 'leonidas-thinking-dots';
        dots.setAttribute('aria-hidden', 'true');
        for (var index = 0; index < 3; index += 1) {
            dots.appendChild(document.createElement('span'));
        }
        item.appendChild(label);
        item.appendChild(dots);
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

    function addChart(chart) {
        if (!chart || !Array.isArray(chart.series) || !chart.series.length) return;
        var values = chart.series.map(function (item) { return Math.max(0, Number(item.valor) || 0); });
        var max = Math.max.apply(Math, values.concat([1]));
        var wrapper = document.createElement('section');
        var title = document.createElement('strong');
        var plot = document.createElement('div');
        wrapper.className = 'leonidas-chart';
        title.className = 'leonidas-chart__title';
        title.textContent = chart.titulo || 'Gráfica de resultados';
        plot.className = 'leonidas-chart__plot';

        chart.series.slice(0, 12).forEach(function (series, index) {
            var row = document.createElement('div');
            var meta = document.createElement('div');
            var label = document.createElement('span');
            var value = document.createElement('strong');
            var track = document.createElement('div');
            var bar = document.createElement('span');
            var number = Number(series.valor) || 0;
            row.className = 'leonidas-chart__row';
            meta.className = 'leonidas-chart__meta';
            label.textContent = series.etiqueta || ('Dato ' + (index + 1));
            value.textContent = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 2 }).format(number);
            track.className = 'leonidas-chart__track';
            bar.className = 'leonidas-chart__bar';
            bar.style.width = Math.max(number > 0 ? 4 : 0, (number / max) * 100) + '%';
            meta.appendChild(label);
            meta.appendChild(value);
            track.appendChild(bar);
            row.appendChild(meta);
            row.appendChild(track);
            plot.appendChild(row);
        });

        wrapper.appendChild(title);
        wrapper.appendChild(plot);
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;
    }

    function request(endpoint, payload, timeoutMs) {
        var controller = typeof window.AbortController === 'function'
            ? new window.AbortController()
            : null;
        var requestTimeout = Number(timeoutMs) > 0
            ? Number(timeoutMs)
            : (endpoint === '/Leonidas/bandeja' ? 8000 : 35000);
        var timeoutId = controller ? window.setTimeout(function () {
            controller.abort();
        }, requestTimeout) : null;

        return window.fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload || {}),
            signal: controller ? controller.signal : undefined
        }).then(function (response) {
            return response.json().catch(function () {
                return { success: false, error: 'El servidor devolvió una respuesta no válida.' };
            });
        }).then(function (data) {
            if (!data || data.success !== true) {
                throw new Error((data && data.error) || 'Leónidas no pudo completar la solicitud.');
            }
            return data.respuesta || {};
        }).catch(function (error) {
            if (error && error.name === 'AbortError') {
                throw new Error('La consulta tardó más de lo esperado. Intenta nuevamente; no se realizó ningún cambio.');
            }
            throw error;
        }).finally(function () {
            if (timeoutId !== null) window.clearTimeout(timeoutId);
        });
    }

    function renderAttachment() {
        if (!attachmentBox) return;
        attachmentBox.replaceChildren();
        attachmentBox.hidden = !currentAttachment;
        if (!currentAttachment) return;

        var label = document.createElement('span');
        var remove = document.createElement('button');
        label.innerHTML = '<i class="fa-solid fa-file-excel" aria-hidden="true"></i> ';
        label.appendChild(document.createTextNode(currentAttachment.nombre || 'Excel adjunto'));
        remove.type = 'button';
        remove.setAttribute('aria-label', 'Quitar archivo adjunto');
        remove.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
        remove.addEventListener('click', function () {
            currentAttachment = null;
            if (fileInput) fileInput.value = '';
            renderAttachment();
        });
        attachmentBox.appendChild(label);
        attachmentBox.appendChild(remove);
    }

    function uploadExcel(file) {
        var data = new FormData();
        data.append('archivo', file);
        if (attachButton) attachButton.classList.add('is-uploading');
        if (fileInput) fileInput.disabled = true;

        return window.fetch('/Leonidas/adjuntar', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            body: data
        }).then(function (response) {
            return response.json().catch(function () {
                return { success: false, error: 'El servidor no devolvio una respuesta valida.' };
            });
        }).then(function (payload) {
            if (!payload || payload.success !== true || !payload.respuesta || !payload.respuesta.token) {
                throw new Error((payload && payload.error) || 'No se pudo adjuntar el Excel.');
            }
            currentAttachment = payload.respuesta;
            renderAttachment();
            if (input) input.focus();
        }).finally(function () {
            if (attachButton) attachButton.classList.remove('is-uploading');
            if (fileInput) fileInput.disabled = false;
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
                    if (proposal.accion === 'mensaje') triggerDeliveryWalk('depart');
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
        addChart(response.grafica);
        addProposal(response.propuesta);
        if (response.navegar_a) {
            persistConversation(response.navegar_nombre || 'el módulo solicitado');
            window.setTimeout(function () { window.location.assign(response.navegar_a); }, 450);
        }
    }

    function setSending(sending) {
        root.classList.toggle('is-thinking', sending);
        root.classList.toggle('is-consulting', sending);
        if (input) input.disabled = sending;
        if (sendButton) sendButton.disabled = sending;
        if (fileInput) fileInput.disabled = sending;
    }

    function reactionLabel(code) {
        return { like: '👍', love: '❤️', laugh: '😂', ok: '✅' }[code] || '✅';
    }

    function renderDelivery(delivery) {
        if (!delivery || !delivery.id || Number(delivery.id) === Number(currentDeliveryId)) return;
        currentDeliveryId = Number(delivery.id);
        root.classList.remove('is-recipient-idle');
        root.classList.add('is-delivering');
        triggerDeliveryWalk('arrive');
        openPanel(false);
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
    if (voiceButton) {
        updateVoiceButton();
        voiceButton.addEventListener('click', function () {
            setVoiceEnabled(!voiceEnabled);
            if (voiceEnabled) unlockVoiceContext();
        });
    }

    document.addEventListener('pointerdown', unlockVoiceContext, true);
    document.addEventListener('keydown', unlockVoiceContext, true);

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

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                var file = fileInput.files && fileInput.files[0];
                if (!file) return;
                uploadExcel(file).catch(function (error) {
                    currentAttachment = null;
                    renderAttachment();
                    addMessage(error.message || 'No se pudo adjuntar el Excel.', 'assistant');
                    fileInput.value = '';
                });
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            // Conserva el permiso de audio dentro del gesto del usuario. Algunos
            // navegadores suspenden el AudioContext si solo se reanuda desde un
            // listener global y terminan reproduciendo hasta el siguiente clic.
            unlockVoiceContext();
            var value = input.value.trim();
            if (!value) return;
            addMessage(value, 'user');
            input.value = '';
            resizeComposer();
            setSending(true);
            var thinkingIndicator = addThinkingIndicator();
            var requestAttachment = currentAttachment;
            request('/Leonidas/conversar', {
                mensaje: value,
                archivo_token: requestAttachment ? requestAttachment.token : null
            }, requestAttachment ? 90000 : undefined)
                .then(function (response) {
                    thinkingIndicator.remove();
                    renderResponse(response);
                    if (requestAttachment && currentAttachment && currentAttachment.token === requestAttachment.token) {
                        currentAttachment = null;
                        if (fileInput) fileInput.value = '';
                        renderAttachment();
                    }
                })
                .catch(function (error) {
                    thinkingIndicator.remove();
                    addMessage(error.message || 'No pude procesar la instrucción. No se realizó ningún cambio.', 'assistant');
                })
                .finally(function () {
                    thinkingIndicator.remove();
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

    restoreConversation();
    if (isOwner && localStorage.getItem(storageKey) === '1') openPanel(false);
    else if (isOwner) triggerGreeting();
    pollMailbox();
    window.setInterval(pollMailbox, 6000);
})();
