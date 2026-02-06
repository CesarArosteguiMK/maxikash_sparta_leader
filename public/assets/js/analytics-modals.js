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

    function renderSpatial(data) {
        if (!data) return '';
        var html = '';
        var summary = 'Sin datos de ubicaciones.';
        if (data.ultima_apertura && data.ultima_apertura.timestamp) {
            var ts = data.ultima_apertura.timestamp;
            var dist = data.ultima_apertura.distancia_a_casa_m != null ? data.ultima_apertura.distancia_a_casa_m + ' m' : '—';
            summary = 'Última apertura: ' + (ts ? new Date(ts).toLocaleString('es-MX') : '—') + '; distancia a casa: ' + dist + '.';
        }
        var a5 = data.aperturas_ultimos_5_dias || {};
        summary += ' Total aperturas últimos 5 días: ' + (a5.total_aperturas || 0) + '.';
        html += '<p class="small text-muted mb-3" role="status">' + summary + '</p>';
        html += '<table class="table table-sm table-bordered"><thead><tr><th>Label</th><th>Visitas</th><th>Última fecha</th><th>Distancia (m)</th></tr></thead><tbody>';
        (data.distancias_a_casa || []).forEach(function (row) {
            html += '<tr><td>' + (row.label || '—') + '</td><td>' + (row.visitas_count ?? '—') + '</td><td>' + (row.ultima_fecha || '—') + '</td><td>' + (row.distancia_m != null ? row.distancia_m : '—') + '</td></tr>';
        });
        html += '</tbody></table>';
        if (a5.resumen_por_dia && a5.resumen_por_dia.length) {
            html += '<p class="small fw-semibold mt-2">Resumen por día</p><ul class="list-unstyled small">';
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
        var summary = 'Total pagos: ' + (data.total_pagos || 0) + '. Intervalo promedio: ' + (data.intervalo_promedio_dias != null ? data.intervalo_promedio_dias : '—') + ' días. Patrón: ' + (data.patron_pago || '—') + '.';
        if (data.patron_pago === 'insuficiente_datos') {
            summary = 'Datos insuficientes para analizar patrón (menos de 3 pagos). Total: ' + (data.total_pagos || 0) + '.';
        }
        html += '<p class="small text-muted mb-3" role="status">' + summary + '</p>';
        html += '<table class="table table-sm"><tr><td class="text-muted">Intervalo promedio (días)</td><td>' + (data.intervalo_promedio_dias != null ? data.intervalo_promedio_dias : '—') + '</td></tr>';
        html += '<tr><td class="text-muted">Desviación intervalos</td><td>' + (data.desviacion_intervalos != null ? data.desviacion_intervalos : '—') + '</td></tr>';
        html += '<tr><td class="text-muted">Día más frecuente</td><td>' + (data.dia_mas_frecuente || '—') + '</td></tr>';
        html += '<tr><td class="text-muted">Consistencia día <span class="text-muted" title="Porcentaje de pagos en el día más frecuente">(0..1)</span></td><td>' + (data.consistencia_dia != null ? data.consistencia_dia : '—') + '</td></tr>';
        html += '<tr><td class="text-muted">Patrón pago</td><td>' + (data.patron_pago || '—') + '</td></tr></table>';
        return html;
    }

    function renderCompliance(data) {
        if (!data) return '';
        var html = '';
        var summary = 'Cumplimiento: ' + (data.porcentaje_cumplimiento != null ? data.porcentaje_cumplimiento + '%' : '—') + '. Visitas cercanas: ' + (data.visitas_cercanas ?? 0) + ', lejanas: ' + (data.visitas_lejanas ?? 0) + '.';
        html += '<p class="small text-muted mb-3" role="status">' + summary + '</p>';
        if (data.alertas && data.alertas.length) {
            html += '<ul class="small text-warning">';
            data.alertas.forEach(function (a) {
                html += '<li>' + (a || '') + '</li>';
            });
            html += '</ul>';
        }
        if (data.detalles && data.detalles.length) {
            html += '<table class="table table-sm table-bordered"><thead><tr><th>Evento</th><th>Timestamp</th><th>Distancia (m)</th><th>Ubicación</th><th>Cerca</th></tr></thead><tbody>';
            data.detalles.forEach(function (d) {
                html += '<tr><td>' + (d.gestor_event_id || '—') + '</td><td>' + (d.timestamp || '—') + '</td><td>' + (d.distancia_m != null ? d.distancia_m : '—') + '</td><td>' + (d.ubicacion_id || '—') + '</td><td>' + (d.cerca ? 'Sí' : 'No') + '</td></tr>';
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
                    body.innerHTML = '<div class="analytics-summary">' + renderFn(res.data) + '</div>' +
                        '<details class="mt-3"><summary class="small">Ver más (JSON)</summary><pre class="small bg-light p-2 mt-2 overflow-auto" style="max-height:200px">' + (JSON.stringify(res.data, null, 2) || '') + '</pre></details>';
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
