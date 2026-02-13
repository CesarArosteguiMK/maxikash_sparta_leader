/**
 * Analítica determinística: botones Ubicaciones, Gestiones/Pagos, Cumplimiento Gestor.
 * Fetch a GET /api/analytics/{type}/{idCredito}?force=true
 * Sin referencia a IA.
 */
(function () {
    'use strict';

    function getIdCredito() {
        if (typeof idCreditoRastreoActual !== 'undefined' && idCreditoRastreoActual) {
            return parseInt(idCreditoRastreoActual, 10);
        }
        return 0;
    }

    function fetchAnalytics(type, force, extraQuery) {
        var id = getIdCredito();
        if (!id) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Analítica', text: 'Seleccione un crédito (abra el rastreo primero).' });
            }
            return Promise.reject(new Error('Sin id_credito'));
        }
        var url = '/api/analytics/' + type + '/' + id + (force ? '?force=true' : '');
        if (extraQuery) {
            url += (url.indexOf('?') !== -1 ? '&' : '?') + extraQuery;
        }
        return fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); });
    }

    function showSpinner(modalBody) {
        if (!modalBody) return;
        modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status" aria-hidden="true"></div><p class="mt-2 small text-muted">Cargando...</p></div>';
    }

    /** Si distancia >= 1000 m devuelve "X km", si no "X m" (siempre con unidad al final). */
    function formatDistancia(metros) {
        if (metros == null || metros === '' || isNaN(parseFloat(metros))) return '—';
        var m = parseFloat(metros);
        if (m >= 1000) return (Math.round(m / 100) / 10) + ' km';
        return Math.round(m) + ' m';
    }

    function escapeHtml(s) {
        if (s == null || s === undefined) return '';
        var t = String(s);
        return t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function renderSpatial(data) {
        if (!data) return '';
        var html = '';
        var dirMegareporte = data.direccion_megareporte || '';
        if (dirMegareporte) {
            html += '<p class="small text-muted mb-2"><strong>Su casa</strong> es la <strong>Dirección megareporte</strong>: ' + escapeHtml(dirMegareporte) + '. Las distancias mostradas son a esa casa. Si la distancia es menor a ~100 m, es posible que el punto sea su casa.</p>';
        } else {
            html += '<p class="small text-muted mb-2">Las distancias mostradas son <strong>a la casa del acreditado</strong> (domicilio o ubicación más visitada). Si la distancia es menor a ~100 m, es posible que el punto sea su casa.</p>';
        }
        var summary = 'Sin datos de ubicaciones.';
        if (data.ultima_apertura && data.ultima_apertura.timestamp) {
            var ts = data.ultima_apertura.timestamp;
            var dist = formatDistancia(data.ultima_apertura.distancia_a_casa_m);
            summary = 'Última apertura de la app: ' + (ts ? new Date(ts).toLocaleString('es-MX') : '—') + '. <strong>Distancia a su casa:</strong> ' + dist + '.';
        }
        var a5 = data.aperturas_ultimos_5_dias || {};
        summary += ' Total de aperturas (GPS) en los últimos 5 días: ' + (a5.total_aperturas || 0) + '.';
        html += '<p class="small mb-3" role="status">' + summary + '</p>';
        html += '<table class="table table-sm table-bordered"><thead><tr><th>Ubicación</th><th>Visitas</th><th>Última fecha</th><th>Distancia a su casa</th></tr></thead><tbody>';
        (data.distancias_a_casa || []).forEach(function (row, idx) {
            var distCell = formatDistancia(row.distancia_m);
            var ultimaFecha = row.ultima_fecha ? (typeof row.ultima_fecha === 'string' && row.ultima_fecha.match(/^\d{4}-/) ? new Date(row.ultima_fecha).toLocaleDateString('es-MX') : row.ultima_fecha) : '—';
            var ubicacionLabel = (row.label || '—') + ' (u' + (idx + 1) + ')';
            html += '<tr><td>' + ubicacionLabel + '</td><td>' + (row.visitas_count ?? '—') + '</td><td>' + ultimaFecha + '</td><td>' + distCell + '</td></tr>';
        });
        html += '</tbody></table>';
        if (a5.resumen_por_dia && a5.resumen_por_dia.length) {
            html += '<p class="small fw-semibold mt-2">Aperturas por día (últimos 5 días)</p><ul class="list-unstyled small">';
            a5.resumen_por_dia.forEach(function (d) {
                html += '<li>' + (d.fecha || '') + ': ' + (d.total || 0) + '</li>';
            });
            html += '</ul>';
        }
        return html;
    }

    function renderPayments(data) {
        if (!data) return '';
        var html = '';
        html += '<p class="small text-muted mb-2">Resumen de <strong>gestiones con dictamen de pago</strong>: cuántos pagos se registraron, cada cuántos días en promedio, y si hay un día de la semana o patrón recurrente.</p>';
        var summary = 'Total de pagos registrados: ' + (data.total_pagos || 0) + '. Días entre un pago y el siguiente (promedio): ' + (data.intervalo_promedio_dias != null ? data.intervalo_promedio_dias : '—') + '. Patrón detectado: ' + (data.patron_pago || '—') + '.';
        if (data.patron_pago === 'insuficiente_datos') {
            summary = 'Se necesitan al menos 3 pagos para analizar patrón. Total de pagos registrados: ' + (data.total_pagos || 0) + '.';
        }
        html += '<p class="small mb-3" role="status">' + summary + '</p>';
        html += '<table class="table table-sm table-bordered"><thead><tr><th>Concepto</th><th>Valor</th></tr></thead><tbody>';
        html += '<tr><td><strong>Día más frecuente</strong><br><span class="text-muted small">Día de la semana en que más suele pagar</span></td><td>' + (data.dia_mas_frecuente || '—') + '</td></tr>';
        var consistenciaPct = (data.consistencia_dia != null && !isNaN(parseFloat(data.consistencia_dia))) ? (parseFloat(data.consistencia_dia) <= 1 ? Math.round(parseFloat(data.consistencia_dia) * 1000) / 10 : Math.round(parseFloat(data.consistencia_dia) * 10) / 10) : null;
        html += '<tr><td><strong>Consistencia del día</strong><br><span class="text-muted small">Proporción de pagos en ese día</span></td><td>' + (consistenciaPct != null ? consistenciaPct + '%' : '—') + '</td></tr>';
        html += '<tr><td><strong>Patrón de pago</strong><br><span class="text-muted small">Resumen del comportamiento (regular, irregular, etc.)</span></td><td>' + (data.patron_pago || '—') + '</td></tr></tbody></table>';
        return html;
    }

    function renderCompliance(data) {
        if (!data) return '';
        var html = '';
        html += '<p class="small text-muted mb-2">Cada fila es una <strong>visita del gestor</strong> (con GPS). Se compara la ubicación del gestor con las ubicaciones frecuentes del acreditado: si estuvo a menos de 100 m se considera "cerca" (cumplimiento).</p>';
        var summary = 'Porcentaje de cumplimiento: ' + (data.porcentaje_cumplimiento != null ? data.porcentaje_cumplimiento + '%' : '—') + '. Visitas cercanas al acreditado: ' + (data.visitas_cercanas ?? 0) + ', visitas lejanas: ' + (data.visitas_lejanas ?? 0) + '.';
        html += '<p class="small mb-3" role="status">' + summary + '</p>';
        if (data.alertas && data.alertas.length) {
            html += '<ul class="small text-warning">';
            data.alertas.forEach(function (a) {
                html += '<li>' + (a || '') + '</li>';
            });
            html += '</ul>';
        }
        if (data.detalles && data.detalles.length) {
            html += '<table class="table table-sm table-bordered"><thead><tr><th>Gestor</th><th>Fecha y hora</th><th>Distancia</th><th>Tipo</th></tr></thead><tbody>';
            data.detalles.forEach(function (d) {
                var ts = d.timestamp ? new Date(d.timestamp).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
                var dist = formatDistancia(d.distancia_m);
                var tipo = (d.tipo_contacto || '—').trim() || '—';
                html += '<tr><td>' + (d.gestor_nombre || '—') + '</td><td>' + ts + '</td><td>' + dist + '</td><td>' + tipo + '</td></tr>';
            });
            html += '</tbody></table>';
        }
        return html;
    }

    function openModal(modalId, title, force, type, renderFn) {
        var modal = document.getElementById(modalId);
        if (!modal) return;
        var body = modal.querySelector('.modal-body');
        var headerTitle = modal.querySelector('.modal-title');
        if (headerTitle) headerTitle.textContent = title;
        showSpinner(body);
        var Modal = typeof bootstrap !== 'undefined' && bootstrap.Modal ? new bootstrap.Modal(modal) : null;
        if (Modal) Modal.show();
        fetchAnalytics(type, force)
            .then(function (res) {
                if (res.success && res.data) {
                    body.innerHTML = '<div class="analytics-summary">' + renderFn(res.data) + '</div>';
                    // Oculto: Ver más (JSON) — descomentar la línea siguiente para volver a mostrarlo
                    // body.innerHTML += '<details class="mt-3"><summary class="small">Ver más (JSON)</summary><pre class="small bg-light p-2 mt-2 overflow-auto" style="max-height:200px">' + (JSON.stringify(res.data, null, 2) || '') + '</pre></details>';
                } else {
                    body.innerHTML = '<p class="text-danger small">' + (res.mensaje || 'Error al cargar.') + '</p>';
                }
            })
            .catch(function (err) {
                body.innerHTML = '<p class="text-danger small">Error: ' + (err.message || 'No se pudo conectar.') + '</p>';
            });
    }

    function bindButtons() {
        var btnUbic = document.getElementById('btnAnaliticaUbicaciones');
        var btnPagos = document.getElementById('btnAnaliticaPagos');
        var btnCumpl = document.getElementById('btnAnaliticaCumplimiento');
        if (btnUbic) {
            btnUbic.addEventListener('click', function () {
                openModal('modalAnaliticaSpatial', 'Analítica: Ubicaciones', false, 'spatial', renderSpatial);
            });
        }
        if (btnPagos) {
            btnPagos.addEventListener('click', function () {
                openModal('modalAnaliticaPayments', 'Analítica: Gestiones / Pagos', false, 'payments', renderPayments);
            });
        }
        if (btnCumpl) {
            btnCumpl.addEventListener('click', function () {
                openModal('modalAnaliticaCompliance', 'Analítica: Cumplimiento Gestor', false, 'compliance', renderCompliance);
            });
        }
        document.querySelectorAll('[data-analytics-force]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var modalId = btn.getAttribute('data-analytics-modal');
                var type = btn.getAttribute('data-analytics-type');
                var title = btn.getAttribute('data-analytics-title') || 'Analítica';
                var renderFn = type === 'spatial' ? renderSpatial : (type === 'payments' ? renderPayments : renderCompliance);
                openModal(modalId, title, true, type, renderFn);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindButtons);
    } else {
        bindButtons();
    }
})();
