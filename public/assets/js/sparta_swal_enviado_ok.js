/**
 * SweetAlert2: diálogo unificado "Enviado" + mensaje por vista (Atención a clientes, etc.).
 * Uso: spartaSwalEnviadoOk('Evidencias enviadas correctamente.');
 *      spartaSwalEnviadoOk(null, { html: '…<small>…</small>', title: 'Enviado' });
 */
(function (global) {
    'use strict';

    global.spartaSwalEnviadoOk = function (mensaje, opts) {
        opts = opts && typeof opts === 'object' ? opts : {};
        if (typeof Swal === 'undefined') {
            var t = opts.title != null ? opts.title : 'Enviado';
            var body = opts.html
                ? String(opts.html).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
                : (mensaje != null ? String(mensaje) : '');
            window.alert(t + '\n\n' + body);
            return Promise.resolve();
        }

        var customClass = Object.assign(
            { popup: 'swal-enviado-ok-popup', confirmButton: 'swal-enviado-ok-btn' },
            opts.customClass || {}
        );

        var cfg = {
            icon: 'success',
            title: opts.title != null ? opts.title : 'Enviado',
            confirmButtonText: opts.confirmButtonText != null ? opts.confirmButtonText : 'OK',
            confirmButtonColor: opts.confirmButtonColor != null ? opts.confirmButtonColor : '#0f172a',
            buttonsStyling: true,
            customClass: customClass,
        };

        if (opts.html != null && opts.html !== '') {
            cfg.html = opts.html;
        } else {
            cfg.text = mensaje != null ? String(mensaje) : '';
        }

        var skip = { title: 1, confirmButtonText: 1, confirmButtonColor: 1, customClass: 1, html: 1, text: 1 };
        Object.keys(opts).forEach(function (k) {
            if (skip[k]) return;
            cfg[k] = opts[k];
        });

        return Swal.fire(cfg);
    };
})(typeof window !== 'undefined' ? window : this);
