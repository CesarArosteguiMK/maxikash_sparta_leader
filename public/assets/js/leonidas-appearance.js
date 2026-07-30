(function () {
    'use strict';

    var root = document.getElementById('leonidasAssistant');
    var modalElement = document.getElementById('leonidasAppearanceModal');
    if (!root || !modalElement) return;

    var openButtons = document.querySelectorAll('[data-leonidas-appearance-open]');
    var themesContainer = modalElement.querySelector('[data-leonidas-appearance-themes]');
    var previewPanel = modalElement.querySelector('.leonidas-appearance-preview');
    var liveModelHost = modalElement.querySelector('[data-leonidas-appearance-model]');
    var previewCanvas = modalElement.querySelector('[data-leonidas-appearance-preview]');
    var appearanceName = modalElement.querySelector('[data-leonidas-appearance-name]');
    var statusBox = modalElement.querySelector('[data-leonidas-appearance-status]');
    var saveButton = modalElement.querySelector('[data-leonidas-appearance-save]');
    var resetButton = modalElement.querySelector('[data-leonidas-appearance-reset]');
    var colorInputs = modalElement.querySelectorAll('[data-leonidas-color]');
    var gearControls = modalElement.querySelector('[data-leonidas-gear-controls]');
    var gearNote = modalElement.querySelector('[data-leonidas-gear-note]');
    var gearInputs = modalElement.querySelectorAll('[data-leonidas-part]');
    var fallbackImage = root.querySelector('[data-leonidas-fallback]');
    var liveModelCanvas = root.querySelector('[data-leonidas-canvas]');
    var personaId = root.getAttribute('data-leonidas-persona') || '0';
    var cacheKey = 'sparta.leonidas.appearance.' + personaId;
    var modal = null;
    var acceptClose = false;
    var renderTimer = null;
    var editorOpen = false;
    var liveModelHome = null;
    var liveModelNextSibling = null;
    var originalImage = new Image();
    var themes = [];
    var savedAppearance = {
        id: 'corporativo',
        nombre: 'Corporativo',
        color_principal: '#0048B7',
        color_secundario: '#D2D854',
        color_metal: '#D7E0EA',
        casco_visible: true,
        pechera_visible: true,
        cabello_visible: true,
        escudo_visible: true,
        lanza_visible: true
    };
    var draftAppearance = copyAppearance(savedAppearance);

    function copyAppearance(appearance) {
        return {
            id: String(appearance && appearance.id || 'corporativo'),
            nombre: String(appearance && appearance.nombre || 'Personalizado'),
            descripcion: String(appearance && appearance.descripcion || ''),
            color_principal: normalizeHex(appearance && appearance.color_principal, '#0048B7'),
            color_secundario: normalizeHex(appearance && appearance.color_secundario, '#D2D854'),
            color_metal: normalizeHex(appearance && appearance.color_metal, '#D7E0EA'),
            casco_visible: normalizeVisibility(appearance && appearance.casco_visible),
            pechera_visible: normalizeVisibility(appearance && appearance.pechera_visible),
            cabello_visible: normalizeVisibility(appearance && appearance.cabello_visible),
            escudo_visible: normalizeVisibility(appearance && appearance.escudo_visible),
            lanza_visible: normalizeVisibility(appearance && appearance.lanza_visible),
            personalizada: Boolean(appearance && appearance.personalizada)
        };
    }

    function normalizeVisibility(value) {
        return value !== false && value !== 0 && value !== '0';
    }

    function normalizeHex(value, fallback) {
        var color = String(value || '').trim().toUpperCase();
        return /^#[0-9A-F]{6}$/.test(color) ? color : fallback;
    }

    function hexToRgb(value) {
        var color = normalizeHex(value, '#000000');
        return [
            parseInt(color.slice(1, 3), 16),
            parseInt(color.slice(3, 5), 16),
            parseInt(color.slice(5, 7), 16)
        ];
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
                throw new Error((data && data.error) || 'No se pudo actualizar el vestuario.');
            }
            return data.respuesta || {};
        });
    }

    function setStatus(message, type) {
        if (!statusBox) return;
        statusBox.textContent = message || '';
        statusBox.className = 'leonidas-appearance-status' + (type ? ' is-' + type : '');
    }

    function setBusy(busy) {
        if (saveButton) saveButton.disabled = busy;
        if (resetButton) resetButton.disabled = busy;
        colorInputs.forEach(function (input) { input.disabled = busy; });
        gearInputs.forEach(function (input) { input.disabled = busy; });
        if (themesContainer) {
            themesContainer.querySelectorAll('button').forEach(function (button) {
                button.disabled = busy;
            });
        }
    }

    function updateControls() {
        colorInputs.forEach(function (input) {
            var field = input.getAttribute('data-leonidas-color');
            var value = normalizeHex(draftAppearance[field], input.value);
            input.value = value;
            var output = modalElement.querySelector('[data-leonidas-color-output="' + field + '"]');
            if (output) output.textContent = value;
        });
        gearInputs.forEach(function (input) {
            var field = input.getAttribute('data-leonidas-part');
            input.checked = normalizeVisibility(draftAppearance[field]);
        });
        if (appearanceName) appearanceName.textContent = draftAppearance.nombre || 'Personalizado';
        if (themesContainer) {
            themesContainer.querySelectorAll('[data-leonidas-theme]').forEach(function (button) {
                var selected = button.getAttribute('data-leonidas-theme') === draftAppearance.id;
                button.classList.toggle('is-selected', selected);
                button.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });
        }
    }

    function shadeColor(target, luminance, strength) {
        var factor = Math.max(0.3, Math.min(1.35, 0.42 + luminance * 1.18));
        return [
            Math.min(255, target[0] * factor * strength),
            Math.min(255, target[1] * factor * strength),
            Math.min(255, target[2] * factor * strength)
        ];
    }

    function mixChannel(source, target, amount) {
        return Math.round(source * (1 - amount) + target * amount);
    }

    /**
     * Recolorea la ilustración de respaldo sin tocar piel, rostro ni cabello.
     * El PNG tiene zonas rojas para capa/faldón y grises para metal, por lo que
     * puede segmentarse de forma determinista conservando luces y sombras.
     */
    function recolorIllustration(source, targetCanvas, appearance, displayWidth, displayHeight) {
        if (!source.naturalWidth || !targetCanvas) return;
        var width = displayWidth || source.naturalWidth;
        var height = displayHeight || source.naturalHeight;
        targetCanvas.width = width;
        targetCanvas.height = height;
        var context = targetCanvas.getContext('2d', { willReadFrequently: true });
        if (!context) return;
        context.clearRect(0, 0, width, height);
        context.drawImage(source, 0, 0, width, height);
        var frame = context.getImageData(0, 0, width, height);
        var pixels = frame.data;
        var primary = hexToRgb(appearance.color_principal);
        var secondary = hexToRgb(appearance.color_secundario);
        var metal = hexToRgb(appearance.color_metal);

        for (var index = 0; index < pixels.length; index += 4) {
            if (pixels[index + 3] < 12) continue;
            var pixelIndex = index / 4;
            var normalizedX = (pixelIndex % width) / width;
            var normalizedY = Math.floor(pixelIndex / width) / height;
            var redByte = pixels[index];
            var greenByte = pixels[index + 1];
            var blueByte = pixels[index + 2];
            var red = redByte / 255;
            var green = greenByte / 255;
            var blue = blueByte / 255;
            var max = Math.max(red, green, blue);
            var min = Math.min(red, green, blue);
            var saturation = max > 0 ? (max - min) / max : 0;
            var luminance = red * 0.299 + green * 0.587 + blue * 0.114;
            var target = null;
            var amount = 0;

            // YCbCr separates peach skin from the highly saturated red cloth
            // much more reliably than checking only which RGB channel wins.
            var cb = 128 - (0.168736 * redByte) - (0.331264 * greenByte) + (0.5 * blueByte);
            var cr = 128 + (0.5 * redByte) - (0.418688 * greenByte) - (0.081312 * blueByte);
            var redCloth = red > green * 1.55 && red > blue * 1.4
                && saturation > 0.56 && green < 0.42 && cr > 182;
            var redMaterial = red > green * 1.18 && red > blue * 1.12
                && saturation > 0.28 && cr > 168;
            var capeZone = normalizedY > 0.2 && normalizedY < 0.76
                && (normalizedX < 0.34 || normalizedX > 0.66 || normalizedY > 0.57);
            var capeMaterial = capeZone && red > green * 1.08 && red > blue * 1.04
                && saturation > 0.14;
            var protectedFace = normalizedX > 0.36 && normalizedX < 0.64
                && normalizedY > 0.13 && normalizedY < 0.255;
            var anatomicalSkinZone = (
                (normalizedX > 0.285 && normalizedX < 0.665
                    && normalizedY > 0.235 && normalizedY < 0.455)
                || (((normalizedX > 0.205 && normalizedX < 0.36)
                        || (normalizedX > 0.625 && normalizedX < 0.755))
                    && normalizedY > 0.225 && normalizedY < 0.59)
                || (normalizedX > 0.29 && normalizedX < 0.68
                    && normalizedY > 0.55 && normalizedY < 0.73)
                || (normalizedX > 0.2 && normalizedX < 0.72
                    && normalizedY > 0.86)
            );
            var anatomicalSkin = anatomicalSkinZone
                && redByte > 72 && greenByte > 42 && blueByte > 26
                && red > green * 1.02 && green > blue * 0.72;
            var skin = redByte > 72 && greenByte > 28 && blueByte > 16
                && redByte > greenByte && greenByte >= blueByte * 0.82
                && cb > 72 && cb < 137 && cr > 132 && cr < 180;
            if ((protectedFace || anatomicalSkin || skin) && !redCloth) continue;
            var metalZone = (
                (normalizedY < 0.2 && normalizedX > 0.36 && normalizedX < 0.65)
                || (normalizedY > 0.38 && normalizedY < 0.56
                    && ((normalizedX > 0.19 && normalizedX < 0.37)
                        || (normalizedX > 0.63 && normalizedX < 0.79)))
                || (normalizedY > 0.63 && normalizedY < 0.91
                    && normalizedX > 0.23 && normalizedX < 0.73)
                || (normalizedY > 0.4 && normalizedY < 0.5
                    && normalizedX > 0.38 && normalizedX < 0.61)
            );
            var detailZone = (
                (normalizedX > 0.3 && normalizedX < 0.66
                    && normalizedY > 0.24 && normalizedY < 0.45)
                || (normalizedX > 0.27 && normalizedX < 0.7
                    && normalizedY > 0.4 && normalizedY < 0.62)
                || (normalizedX > 0.21 && normalizedX < 0.73
                    && normalizedY > 0.67 && normalizedY < 0.96)
            );
            var neutralMetal = saturation < 0.24 && luminance > 0.2;
            var leatherOrDarkDetail = (
                (red > green * 1.12 && green > blue * 0.82 && luminance < 0.55)
                || (saturation < 0.3 && luminance < 0.34)
            );

            if (redMaterial || capeMaterial) {
                target = primary;
                amount = 0.9;
            } else if (metalZone && neutralMetal) {
                target = metal;
                amount = 0.84;
            } else if (detailZone && leatherOrDarkDetail) {
                target = secondary;
                amount = 0.64;
            }
            if (!target) continue;
            var shaded = shadeColor(target, luminance, 1);
            pixels[index] = mixChannel(pixels[index], shaded[0], amount);
            pixels[index + 1] = mixChannel(pixels[index + 1], shaded[1], amount);
            pixels[index + 2] = mixChannel(pixels[index + 2], shaded[2], amount);
        }
        context.putImageData(frame, 0, 0);
    }

    function renderIllustrations() {
        if (!originalImage.naturalWidth) return;
        if (previewCanvas) {
            recolorIllustration(originalImage, previewCanvas, draftAppearance, 300, 410);
        }
        if (fallbackImage) {
            var canvas = document.createElement('canvas');
            recolorIllustration(originalImage, canvas, draftAppearance);
            try {
                fallbackImage.src = canvas.toDataURL('image/png');
            } catch (ignore) {
                fallbackImage.src = fallbackImage.getAttribute('data-leonidas-original-src') || originalImage.src;
            }
        }
    }

    function applyAppearance(appearance, updateEditor) {
        draftAppearance = copyAppearance(appearance);
        root._leonidasAppearance = copyAppearance(draftAppearance);
        root.dataset.leonidasAppearance = draftAppearance.id;
        root.style.setProperty('--leonidas-cloth-primary', draftAppearance.color_principal);
        root.style.setProperty('--leonidas-cloth-secondary', draftAppearance.color_secundario);
        root.style.setProperty('--leonidas-metal-color', draftAppearance.color_metal);
        root.dispatchEvent(new CustomEvent('leonidas:appearance', {
            detail: copyAppearance(draftAppearance)
        }));
        if (updateEditor !== false) updateControls();
        window.clearTimeout(renderTimer);
        renderTimer = window.setTimeout(renderIllustrations, 45);
    }

    function cacheAppearance(appearance) {
        try {
            localStorage.setItem(cacheKey, JSON.stringify(copyAppearance(appearance)));
        } catch (ignore) {}
    }

    function readCachedAppearance() {
        try {
            var cached = JSON.parse(localStorage.getItem(cacheKey) || 'null');
            return cached && cached.color_principal ? copyAppearance(cached) : null;
        } catch (ignore) {
            return null;
        }
    }

    function renderThemes() {
        if (!themesContainer) return;
        themesContainer.replaceChildren();
        themes.forEach(function (theme) {
            var button = document.createElement('button');
            var swatches = document.createElement('span');
            var text = document.createElement('span');
            var title = document.createElement('strong');
            var description = document.createElement('small');
            button.type = 'button';
            button.className = 'leonidas-appearance-theme';
            button.setAttribute('data-leonidas-theme', theme.id);
            button.setAttribute('aria-pressed', 'false');
            swatches.className = 'leonidas-appearance-theme__swatches';
            [theme.color_principal, theme.color_secundario, theme.color_metal].forEach(function (color) {
                var swatch = document.createElement('i');
                swatch.style.backgroundColor = color;
                swatches.appendChild(swatch);
            });
            text.className = 'leonidas-appearance-theme__text';
            title.textContent = theme.nombre;
            description.textContent = theme.descripcion || '';
            text.appendChild(title);
            text.appendChild(description);
            button.appendChild(swatches);
            button.appendChild(text);
            button.addEventListener('click', function () {
                var selectedTheme = copyAppearance(theme);
                selectedTheme.casco_visible = draftAppearance.casco_visible;
                selectedTheme.pechera_visible = draftAppearance.pechera_visible;
                selectedTheme.cabello_visible = draftAppearance.cabello_visible;
                selectedTheme.escudo_visible = draftAppearance.escudo_visible;
                selectedTheme.lanza_visible = draftAppearance.lanza_visible;
                setStatus('');
                applyAppearance(selectedTheme);
            });
            themesContainer.appendChild(button);
        });
        updateControls();
    }

    function refreshModelLayout() {
        window.requestAnimationFrame(function () {
            root.dispatchEvent(new CustomEvent('leonidas:preview-layout'));
        });
    }

    function attachLiveModel() {
        if (!editorOpen || !previewPanel || !liveModelHost || !liveModelCanvas) return;
        if (!root.classList.contains('is-3d-ready') || root.classList.contains('is-3d-fallback')) return;
        if (liveModelCanvas.parentNode === liveModelHost) {
            refreshModelLayout();
            return;
        }
        liveModelHome = liveModelCanvas.parentNode;
        liveModelNextSibling = liveModelCanvas.nextSibling;
        liveModelHost.appendChild(liveModelCanvas);
        previewPanel.classList.add('has-live-model');
        root.classList.add('is-appearance-preview-live');
        refreshModelLayout();
    }

    function detachLiveModel() {
        if (!liveModelCanvas || !liveModelHome || liveModelCanvas.parentNode !== liveModelHost) return;
        root.classList.remove('is-appearance-preview-live');
        if (liveModelNextSibling && liveModelNextSibling.parentNode === liveModelHome) {
            liveModelHome.insertBefore(liveModelCanvas, liveModelNextSibling);
        } else {
            liveModelHome.appendChild(liveModelCanvas);
        }
        if (previewPanel) previewPanel.classList.remove('has-live-model');
        liveModelHome = null;
        liveModelNextSibling = null;
        refreshModelLayout();
    }

    function showEditor() {
        editorOpen = true;
        acceptClose = false;
        setStatus('');
        applyAppearance(savedAppearance);
        root.classList.add('is-editing-appearance');
        if (window.bootstrap && window.bootstrap.Modal) {
            modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
            return;
        }
        modalElement.classList.add('show');
        modalElement.style.display = 'block';
        modalElement.removeAttribute('aria-hidden');
        attachLiveModel();
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', showEditor);
    });

    colorInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            var field = input.getAttribute('data-leonidas-color');
            draftAppearance[field] = normalizeHex(input.value, draftAppearance[field]);
            draftAppearance.id = 'personalizado';
            draftAppearance.nombre = 'Personalizado';
            draftAppearance.descripcion = 'Paleta elegida por el usuario.';
            setStatus('');
            applyAppearance(draftAppearance);
        });
    });

    gearInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            var field = input.getAttribute('data-leonidas-part');
            draftAppearance[field] = Boolean(input.checked);
            setStatus('');
            applyAppearance(draftAppearance);
        });
    });

    root.addEventListener('leonidas:capabilities', function (event) {
        var capabilities = event.detail || {};
        var supportsGear = capabilities.validated === true
            && capabilities.helmet === true
            && capabilities.chest === true
            && capabilities.hair === true
            && capabilities.shield === true
            && capabilities.spear === true;
        if (gearControls) gearControls.hidden = !supportsGear;
        if (gearNote) {
            gearNote.hidden = supportsGear;
        }
        updateControls();
    });

    if (saveButton) {
        saveButton.addEventListener('click', function () {
            setBusy(true);
            setStatus('Guardando tu vestuario…', 'loading');
            request('/Leonidas/guardarApariencia', {
                tema: draftAppearance.id,
                color_principal: draftAppearance.color_principal,
                color_secundario: draftAppearance.color_secundario,
                color_metal: draftAppearance.color_metal,
                casco_visible: draftAppearance.casco_visible,
                pechera_visible: draftAppearance.pechera_visible,
                cabello_visible: draftAppearance.cabello_visible,
                escudo_visible: draftAppearance.escudo_visible,
                lanza_visible: draftAppearance.lanza_visible
            }).then(function (response) {
                savedAppearance = copyAppearance(response.apariencia || draftAppearance);
                cacheAppearance(savedAppearance);
                acceptClose = true;
                applyAppearance(savedAppearance);
                setStatus(response.mensaje || 'Vestuario guardado.', 'success');
                window.setTimeout(function () {
                    if (modal) modal.hide();
                }, 280);
            }).catch(function (error) {
                setStatus(error.message || 'No se pudo guardar el vestuario.', 'error');
            }).finally(function () {
                setBusy(false);
            });
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            setBusy(true);
            setStatus('Restableciendo el uniforme corporativo…', 'loading');
            request('/Leonidas/restablecerApariencia', {}).then(function (response) {
                savedAppearance = copyAppearance(response.apariencia || savedAppearance);
                cacheAppearance(savedAppearance);
                acceptClose = true;
                applyAppearance(savedAppearance);
                setStatus(response.mensaje || 'Vestuario restablecido.', 'success');
                window.setTimeout(function () {
                    if (modal) modal.hide();
                }, 280);
            }).catch(function (error) {
                setStatus(error.message || 'No se pudo restablecer el vestuario.', 'error');
            }).finally(function () {
                setBusy(false);
            });
        });
    }

    modalElement.addEventListener('shown.bs.modal', attachLiveModel);

    modalElement.addEventListener('hidden.bs.modal', function () {
        editorOpen = false;
        detachLiveModel();
        if (!acceptClose) applyAppearance(savedAppearance);
        acceptClose = false;
        root.classList.remove('is-editing-appearance');
        setStatus('');
    });

    root.addEventListener('leonidas:model-ready', attachLiveModel);

    if (fallbackImage) {
        var originalSource = fallbackImage.getAttribute('src');
        fallbackImage.setAttribute('data-leonidas-original-src', originalSource);
        originalImage.onload = renderIllustrations;
        originalImage.src = originalSource;
    }

    var cachedAppearance = readCachedAppearance();
    if (cachedAppearance) {
        savedAppearance = cachedAppearance;
        applyAppearance(savedAppearance);
    } else {
        applyAppearance(savedAppearance);
    }

    request('/Leonidas/obtenerApariencia', {}).then(function (response) {
        if (Array.isArray(response.temas) && response.temas.length) {
            themes = response.temas.map(copyAppearance);
            renderThemes();
        }
        savedAppearance = copyAppearance(response.apariencia || savedAppearance);
        cacheAppearance(savedAppearance);
        applyAppearance(savedAppearance);
    }).catch(function () {
        // La personalización es accesoria: una falla no debe impedir usar el agente.
        themes = themes.length ? themes : [copyAppearance(savedAppearance)];
        renderThemes();
    });
})();
