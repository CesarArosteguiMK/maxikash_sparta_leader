<?php
/**
 * Mismo CSS + modales + JS inline inicial del panel Sabueso (rastreo), para Estado de cuenta.
 * El <script> grande (maps, APIs) va aparte vía Sabueso::getPaneladminScriptSoloConsultaParaEstadoCuenta() en el footer.
 */
if (!isset($panel_admin_modulo_urls_json) || $panel_admin_modulo_urls_json === '') {
    $urlsModPanelEc = [];
    foreach (\Core\TicketsPanelModuloHelper::MODULOS as $k => $m) {
        $urlsModPanelEc[$k] = $m['url'];
    }
    $panel_admin_modulo_urls_json = json_encode($urlsModPanelEc, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
$panel_admin_solo_consulta_credito = true;
$panel_admin_chromeless_embed = false;
?>
<script>window.RASTREO_EMBED_ESTADO_CUENTA = true;</script>
<link rel="stylesheet" href="/assets/css/analitica-ia.css">
<style>
    .credito-modal-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .credito-modal-item { display: flex; flex-direction: column; gap: 0.15rem; }
    .credito-modal-item .fw-medium { word-break: break-word; }
    /* Cuerpo modal: 3 columnas con espacio para ver el fondo gris entre tarjetas */
    #modalRastreoCredito .rastreo-header-grid { flex-shrink: 0; }
    /* Grid: 3 columnas 34% | 34% | 32% = Direcciones maxi app | Direcciones alternas | Bitácora+Dictamen */
    #modalRastreoCredito .rastreo-grid { display: grid; grid-template-columns: minmax(0, 34%) minmax(0, 34%) minmax(0, 32%); gap: 1rem; align-items: stretch; min-height: auto; }
    @media (max-width: 1199px) {
        #modalRastreoCredito .rastreo-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr); }
    }
    @media (max-width: 992px) { #modalRastreoCredito .rastreo-grid { grid-template-columns: 1fr; } }
    #modalRastreoCredito .rastreo-col-izq,
    #modalRastreoCredito .rastreo-col-direcciones-alternas { display: flex; flex-direction: column; min-width: 0; min-height: 0; }
    #modalRastreoCredito .rastreo-col-izq .rastreo-seccion-direcciones,
    #modalRastreoCredito .rastreo-col-direcciones-alternas .rastreo-seccion-direcciones { display: flex; flex-direction: column; flex: 1; min-height: 200px; min-width: 0; }
    #modalRastreoCredito .rastreo-col-izq .rastreo-mapa-wrap { margin-top: auto; flex-shrink: 0; }
    /* Direcciones alternas: lista + mapa ocupan todo el bloque sin huecos */
    #modalRastreoCredito #rastreoDireccionesAlternas { display: flex; flex-direction: column; flex: 1; min-height: 0; padding-bottom: 0.5rem !important; }
    #modalRastreoCredito #rastreoDireccionesAlternasContenido { flex-shrink: 0; margin-bottom: 0.5rem !important; }
    #modalRastreoCredito #rastreoDireccionesAlternas .rastreo-mapa-wrap { flex: 1; min-height: 160px; margin-top: 0; display: flex; flex-direction: column; }
    #modalRastreoCredito #rastreoMapaAlternasWrap { flex: 1; min-height: 160px; position: relative; }
    #modalRastreoCredito #rastreoMapaAlternas { flex: 1; min-height: 160px !important; }
    /* Columna centro: misma altura que las demás; filas reparten el espacio. */
    #modalRastreoCredito .rastreo-col-centro { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 0.75rem; min-width: 0; min-height: 0; }
    #modalRastreoCredito .rastreo-col-centro .rastreo-ia-box { grid-column: 1; grid-row: 1; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }
    #modalRastreoCredito .rastreo-col-centro .rastreo-seccion-evidencias { grid-column: 1; grid-row: 2; min-height: 0; }
    #modalRastreoCredito .rastreo-col-centro .rastreo-seccion-gestiones { grid-column: 2; grid-row: 1 / -1; min-height: 0; }
    @media (max-width: 991px) {
        #modalRastreoCredito .rastreo-col-centro { grid-template-columns: 1fr; grid-template-rows: auto; }
        #modalRastreoCredito .rastreo-col-centro .rastreo-ia-box,
        #modalRastreoCredito .rastreo-col-centro .rastreo-seccion-evidencias,
        #modalRastreoCredito .rastreo-col-centro .rastreo-seccion-gestiones { grid-column: 1; grid-row: auto; height: auto; min-height: 140px; }
    }
    /* Columna derecha: dos bloques fijos 50% Bitácora + 50% Dictamen (grid 2 filas iguales) */
    #modalRastreoCredito .rastreo-grid { min-height: 420px; }
    #modalRastreoCredito .rastreo-col-bitacora-wrap { display: grid; grid-template-rows: 1fr 1fr; gap: 0.5rem; min-width: 0; min-height: 0; }
    #modalRastreoCredito .rastreo-col-bitacora-wrap .rastreo-seccion-bitacora { grid-row: 1; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }
    #modalRastreoCredito .rastreo-col-bitacora-wrap .rastreo-seccion-dictamen { grid-row: 2; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }
    #modalRastreoCredito .rastreo-block-full { grid-column: 1 / -1; }
    #modalRastreoCredito.modal .modal-dialog { max-width: 95vw; width: 95vw; height: 90vh; max-height: 90vh; margin: 2rem auto; }
    /* Liquid Glass: mismo criterio que modal-content-glass + departamentos (blur + rgba). Antes era #F1F5F9 sólido. */
    /* Modal Consultar por ID crédito: no recortar el botón cerrar */
    #modalConsultaCreditoPaso1 .modal-dialog,
    #modalConsultaCreditoPaso1 .modal-content { overflow: visible !important; }
    #modalConsultaCreditoPaso1 .modal-header { position: relative; }

    #modalRastreoCredito .modal-content.modal-content-glass {
        height: 100%;
        display: flex;
        flex-direction: column;
        border-radius: 16px;
        /* visible: la X del header no se recorta; el scroll queda en modal-body */
        overflow: visible;
        background: rgba(255, 255, 255, 0.82) !important;
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.55) !important;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(255, 255, 255, 0.35) inset;
    }
    body.dark-mode #modalRastreoCredito .modal-content.modal-content-glass {
        background: rgba(30, 41, 59, 0.88) !important;
        border-color: rgba(71, 85, 105, 0.45) !important;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(51, 65, 85, 0.25) inset;
    }
    #modalRastreoCredito .modal-body {
        flex: 1;
        overflow-x: hidden;
        overflow-y: auto;
        border-radius: 0;
        min-height: 0;
        -webkit-overflow-scrolling: touch;
        background: rgba(241, 245, 249, 0.45) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        padding: 1rem;
        -webkit-font-smoothing: antialiased;
        display: flex;
        flex-direction: column;
    }
    body.dark-mode #modalRastreoCredito .modal-body {
        background: rgba(15, 23, 42, 0.35) !important;
    }
    #modalRastreoCredito .modal-body .small { line-height: 1.5; letter-spacing: 0.01em; color: #374151; }
    /* Encabezado y pie: glass ligero (no blanco opaco) */
    #modalRastreoCredito .modal-header {
        background: rgba(255, 255, 255, 0.65) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.06) !important;
        /* Evitar que la X se recorte por border-radius/overflow del modal-content */
        padding-top: 0.85rem !important;
        padding-right: 1rem !important;
        padding-bottom: 0.65rem !important;
        padding-left: 1rem !important;
        flex-shrink: 0;
        border-radius: 16px 16px 0 0;
    }
    #modalRastreoCredito .modal-header .btn-close {
        padding: 0.5rem;
        margin-left: auto;
        /* Mantener la X dentro del área redondeada sin tirarla al borde */
        margin-right: 0;
        background-size: 0.65em;
        opacity: 0.9;
    }
    #modalRastreoCredito .modal-footer {
        background: rgba(255, 255, 255, 0.6) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-top: 1px solid rgba(0, 0, 0, 0.06) !important;
        border-radius: 0 0 16px 16px;
        flex-shrink: 0;
    }
    body.dark-mode #modalRastreoCredito .modal-header {
        background: rgba(30, 41, 59, 0.75) !important;
        border-bottom-color: rgba(51, 65, 85, 0.5) !important;
        border-radius: 16px 16px 0 0;
    }
    body.dark-mode #modalRastreoCredito .modal-footer {
        background: rgba(30, 41, 59, 0.7) !important;
        border-top-color: rgba(51, 65, 85, 0.5) !important;
    }
    /* Encabezado en dos columnas: izquierda = datos cliente + ticket; derecha = quién levantó / cuando / asignado */
    #modalRastreoCredito .rastreo-header-grid { display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: start; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(0,0,0,0.06); }
    #modalRastreoCredito .rastreo-header-left { display: flex; flex-direction: column; gap: 0.35rem; min-width: 0; }
    #modalRastreoCredito .rastreo-header-left .rastreo-datos-row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0 1rem; }
    /* Ticket levantado: estilo alerta informativa */
    #modalRastreoCredito .rastreo-tickets-cell { margin-top: 0.35rem; background: #eff6ff; border-radius: 10px; padding: 0.75rem 1rem; border: none; border-left: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(59, 130, 246, 0.08); }
    #modalRastreoCredito .rastreo-tickets-cell .fw-semibold.small.text-muted { color: #1e40af !important; font-weight: 600; }
    #modalRastreoCredito .rastreo-tickets-cell .credito-modal-list { color: #1e3a8a; }
    #modalRastreoCredito .rastreo-header-right { display: flex; flex-direction: column; gap: 0.5rem; padding-left: 1rem; border-left: 1px solid rgba(0,0,0,0.08); min-width: 180px; }
    /* Consulta solo por ID crédito: ocultar ticket, asignación, bitácora y dictamen; grid 2 columnas */
    #modalRastreoCredito.consulta-sin-ticket .rastreo-tickets-cell,
    #modalRastreoCredito.consulta-sin-ticket .rastreo-header-right { display: none !important; }
    #modalRastreoCredito.consulta-sin-ticket .rastreo-header-grid { grid-template-columns: 1fr; border-bottom: none; padding-bottom: 0.5rem; }
    #modalRastreoCredito.consulta-sin-ticket .rastreo-col-bitacora-wrap { display: none !important; }
    #modalRastreoCredito.consulta-sin-ticket .rastreo-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    #modalRastreoCredito.consulta-sin-ticket .modal-title::after { content: ' (solo consulta)'; font-weight: 400; font-size: 0.85em; opacity: 0.85; }
    #modalRastreoCredito.consulta-sin-ticket #btnAsignarRastreo { display: none !important; }
    /* Vista Analítica: solo consulta por ID — sin sufijo ni botón asignar en pie */
    #modalRastreoCredito.consulta-reporteria-solo .modal-title::after { content: none !important; }
    #modalRastreoCredito.consulta-reporteria-solo #btnAsignarRastreo { display: none !important; }
    #modalRastreoCredito .rastreo-ticket-info-col { display: flex; flex-direction: column; gap: 0.5rem; }
    /* Direcciones (maxi app): cada ubicación es una fila de 3 columnas (dirección | registros | fecha+distancia) */
    #modalRastreoCredito .rastreo-direcciones-lista { margin-bottom: 0.75rem; }
    #modalRastreoCredito .rastreo-direccion-item.rastreo-direccion-row { display: grid; grid-template-columns: 1fr auto auto; gap: 0.75rem 1rem; align-items: start; cursor: pointer; padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.08); transition: background 0.15s ease; }
    #modalRastreoCredito .rastreo-direccion-item.rastreo-direccion-row:last-of-type { border-bottom: none; }
    #modalRastreoCredito .rastreo-direccion-item.rastreo-direccion-row:hover { background: rgba(59, 130, 246, 0.08); }
    #modalRastreoCredito .rastreo-direccion-item .rastreo-col-direccion { min-width: 0; }
    #modalRastreoCredito .rastreo-direccion-item .direccion-linea { word-break: break-word; }
    #modalRastreoCredito .rastreo-direccion-item .rastreo-col-registros { white-space: nowrap; }
    #modalRastreoCredito .rastreo-direccion-item .rastreo-col-fecha-distancia { display: flex; flex-direction: column; gap: 0.15rem; white-space: nowrap; }
    /* Badge registros: sombra negra; muchos = primary, pocos = amarillo/ámbar */
    #modalRastreoCredito .rastreo-badge-registros { box-shadow: 0 1px 3px rgba(0,0,0,0.35); font-size: 0.75rem; }
    #modalRastreoCredito .rastreo-badge-registros-pocos { background-color: #f59e0b !important; color: #fff !important; }
    #modalRastreoCredito .rastreo-badge-registros-detalle { cursor: pointer; position: relative; z-index: 2; }
    #modalRastreoCredito .rastreo-badge-registros-detalle:hover { filter: brightness(1.08); }
    #modalRastreoCredito .rastreo-badge-registros-detalle:focus { outline: 2px solid rgba(59, 130, 246, 0.6); outline-offset: 2px; }
    @media (max-width: 768px) {
        #modalRastreoCredito .rastreo-direccion-item.rastreo-direccion-row { grid-template-columns: 1fr; gap: 0.25rem; }
        #modalRastreoCredito .rastreo-direccion-item .rastreo-col-fecha-distancia { flex-direction: row; flex-wrap: wrap; gap: 0 0.75rem; }
    }
    /* Título Donde firma (direcciones alternas) */
    #modalRastreoCredito .rastreo-donde-firma-titulo { font-size: 0.8rem; font-weight: 600; color: #4b5563; margin-bottom: 0.5rem; }
    /* Pin rosa geo (CASA): gota con parpadeo + icono casa */
    #modalRastreoCredito .rastreo-pin-rosa, #modalRastreoCredito .rastreo-pin-casa {
        display: inline-block;
        width: 14px;
        height: 20px;
        background: linear-gradient(180deg, #ec4899 0%, #be185d 100%);
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        margin-right: 6px;
        vertical-align: middle;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        animation: rastreo-pin-rosa-pulse 1.2s ease-in-out infinite;
    }
    #modalRastreoCredito .rastreo-geo-casa .rastreo-geo-icon { color: #be185d; margin-right: 4px; }
    /* Pin verde (OTRO DOMICILIO) */
    #modalRastreoCredito .rastreo-pin-verde {
        display: inline-block;
        width: 14px;
        height: 20px;
        background: linear-gradient(180deg, #22c55e 0%, #15803d 100%);
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        margin-right: 6px;
        vertical-align: middle;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    #modalRastreoCredito .rastreo-geo-otro .rastreo-geo-icon { color: #15803d; }
    #modalRastreoCredito .rastreo-geo-otro .rastreo-geo-link { color: #15803d; }
    /* Pin carmelita (AGENCIA) */
    #modalRastreoCredito .rastreo-pin-carmelita {
        display: inline-block;
        width: 14px;
        height: 20px;
        background: linear-gradient(180deg, #d4a574 0%, #b8860b 100%);
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        margin-right: 6px;
        vertical-align: middle;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    #modalRastreoCredito .rastreo-geo-agencia .rastreo-geo-icon { color: #b8860b; }
    #modalRastreoCredito .rastreo-geo-agencia .rastreo-geo-link { color: #b8860b; }
    @keyframes rastreo-pin-rosa-pulse {
        0%, 100% { opacity: 1; transform: rotate(-45deg) scale(1); }
        50% { opacity: 0.75; transform: rotate(-45deg) scale(1.08); }
    }
    /* Icono flotante sobre marcadores del mapa (círculo + icono arriba) */
    .rastreo-icono-flotante { display: flex; align-items: center; justify-content: center; min-width: 24px; height: 20px; padding: 0 3px; border-radius: 8px; border: 1px solid #f59e0b; background: rgba(255,255,255,0.98); box-shadow: 0 2px 6px rgba(71,85,105,0.22); animation: rastreo-icono-flotar 2s ease-in-out infinite; }
    .rastreo-icono-flotante .rastreo-icono-emoji { font-size: 1rem; line-height: 1; }
    @keyframes rastreo-icono-flotar {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }
    /* Panel lupa en clusters del mapa (círculo + mini mapa al costado) */
    .rastreo-lupa-panel { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); z-index: 9999; display: flex; flex-direction: column; align-items: center; gap: 6px; pointer-events: auto; }
    .rastreo-lupa-cerrar { position: absolute; top: -8px; right: -8px; width: 24px; height: 24px; border-radius: 50%; border: 1px solid #94a3b8; background: #fff; color: #475569; font-size: 1.2rem; line-height: 1; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.15); z-index: 1001; }
    .rastreo-lupa-cerrar:hover { background: #f1f5f9; color: #0f172a; }
    .rastreo-lupa-lente { width: 260px; height: 260px; border-radius: 50%; overflow: hidden; border: 3px solid #ea580c; box-shadow: 0 4px 20px rgba(0,0,0,0.25); background: #e2e8f0; }
    .rastreo-lupa-texto { font-size: 0.75rem; color: #475569; font-weight: 600; background: rgba(255,255,255,0.95); padding: 2px 8px; border-radius: 6px; }
    .rastreo-btn-lupa-mapa { position: absolute; bottom: 8px; left: 8px; z-index: 11; cursor: zoom-in; font-size: 0.8rem; padding: 4px 10px; border-radius: 8px; background: rgba(255,255,255,0.95); border: 1px solid #cbd5e1; box-shadow: 0 1px 4px rgba(0,0,0,0.15); }
    .rastreo-btn-lupa-mapa:hover { background: #f1f5f9; border-color: #94a3b8; }
    .rastreo-lupa-overlay-activo { position: absolute !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; z-index: 500 !important; cursor: zoom-in !important; pointer-events: auto !important; }
    #modalRastreoCredito .rastreo-geo-item { display: flex; align-items: flex-start; gap: 0.35rem; flex-wrap: wrap; padding: 0.25rem 0; }
    #modalRastreoCredito .rastreo-geo-item[data-indice-geo] { cursor: pointer; border-radius: 6px; transition: background 0.15s ease; }
    #modalRastreoCredito .rastreo-geo-item[data-indice-geo]:hover { background: rgba(0,0,0,0.04); }
    /* Parpadeo al seleccionar dirección alterna */
    @keyframes rastreo-geo-parpadeo {
        0%, 100% { opacity: 1; background: rgba(59, 130, 246, 0.2); box-shadow: none; }
        50% { opacity: 1; background: rgba(59, 130, 246, 0.45); box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); }
    }
    #modalRastreoCredito .rastreo-geo-item-parpadeo { animation: rastreo-geo-parpadeo 0.45s ease-in-out 5; }
    /* Parpadeo fila Direcciones maxi app */
    @keyframes rastreo-direccion-parpadeo {
        0%, 100% { opacity: 1; background: rgba(59, 130, 246, 0.2); box-shadow: none; }
        50% { opacity: 1; background: rgba(59, 130, 246, 0.45); box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); }
    }
    #modalRastreoCredito .rastreo-direccion-item-parpadeo { animation: rastreo-direccion-parpadeo 0.45s ease-in-out 5; }
    /* Tarjeta ubicación seleccionada (Direcciones alternas) - oculta, no se usa */
    #modalRastreoCredito .rastreo-geo-card-inner { border-color: rgba(59, 130, 246, 0.3) !important; }
    #modalRastreoCredito .rastreo-geo-link { color: #be185d; text-decoration: none; }
    #modalRastreoCredito .rastreo-geo-link:hover { text-decoration: underline; }
    /* Bloque de carga: ocupa todo el espacio de direcciones + mapa, spinner centrado y visible */
    #modalRastreoCredito #rastreoDireccionesContenido.rastreo-contenido-cargando {
        flex: 1; display: flex; flex-direction: column; min-height: 0;
    }
    #modalRastreoCredito .rastreo-cargando-bloque {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        flex: 1; min-height: 280px; gap: 0.75rem; color: #6b7280;
    }
    #modalRastreoCredito .rastreo-cargando-bloque .spinner-border { width: 2.5rem; height: 2.5rem; border-width: 0.2em; }
    #modalRastreoCredito .rastreo-cargando-bloque .rastreo-cargando-texto { font-size: 0.95rem; font-weight: 500; }
    /* Mismo bloque de carga para Direcciones alternas */
    #modalRastreoCredito #rastreoDireccionesAlternasContenido.rastreo-contenido-cargando {
        flex: 1; display: flex; flex-direction: column; min-height: 0;
    }
    /* Líneas de acento: border-top 5px azul en todas las tarjetas */
    #modalRastreoCredito .rastreo-seccion-direcciones,
    #modalRastreoCredito .rastreo-seccion-gestiones,
    #modalRastreoCredito .rastreo-seccion-evidencias,
    #modalRastreoCredito .rastreo-seccion-bitacora,
    #modalRastreoCredito .rastreo-seccion-dictamen {
        min-height: 120px;
        background-color: #FFFFFF !important;
        border: 1px solid #D1D5DB !important;
        border-top: 5px solid #4f46e5 !important;
        border-radius: 12px;
        margin-bottom: 20px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07) !important;
        padding: 1.25rem !important;
        transition: box-shadow 0.25s ease;
    }
    #modalRastreoCredito .rastreo-col-bitacora-wrap .rastreo-seccion-bitacora,
    #modalRastreoCredito .rastreo-col-bitacora-wrap .rastreo-seccion-dictamen { margin-bottom: 0 !important; border-radius: 12px; flex-shrink: 0; }
    #modalRastreoCredito .rastreo-col-bitacora-wrap .rastreo-seccion-dictamen { border-top: 5px solid #4f46e5; }
    #modalRastreoCredito .rastreo-seccion-direcciones:hover,
    #modalRastreoCredito .rastreo-seccion-gestiones:hover,
    #modalRastreoCredito .rastreo-seccion-evidencias:hover,
    #modalRastreoCredito .rastreo-seccion-bitacora:hover,
    #modalRastreoCredito .rastreo-seccion-dictamen:hover {
        box-shadow: 0 14px 20px -3px rgba(0, 0, 0, 0.1), 0 6px 10px -2px rgba(0, 0, 0, 0.06) !important;
    }
    #modalRastreoCredito .rastreo-seccion-dictamen.dictamen-solo-lectura select,
    #modalRastreoCredito .rastreo-seccion-dictamen.dictamen-solo-lectura textarea,
    #modalRastreoCredito .rastreo-seccion-dictamen.dictamen-solo-lectura .evidencia-slot-add,
    #modalRastreoCredito .rastreo-seccion-dictamen.dictamen-solo-lectura .evidencia-foto-quitar {
        pointer-events: none;
        opacity: 0.75;
    }
    #modalDictamenAmpliada .rastreo-dictamen-form-ampliada.dictamen-solo-lectura .evidencia-slot-add,
    #modalDictamenAmpliada .rastreo-dictamen-form-ampliada.dictamen-solo-lectura .evidencia-foto-quitar {
        pointer-events: none;
        opacity: 0.75;
    }
    #modalRastreoCredito .rastreo-seccion-dictamen.dictamen-solo-lectura { cursor: default; }
    /* Hero IA: SIN franja morada (obligatorio). Caja y encabezado sin borde superior morado. */
    #modalRastreoCredito .rastreo-col-centro .rastreo-ia-box,
    #modalRastreoCredito .rastreo-ia-box.rastreo-centro-card,
    #modalRastreoCredito .rastreo-ia-box {
        background-color: #FFFFFF !important;
        color: #334155;
        border: 1px solid #D1D5DB !important;
        border-top: none !important;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 20px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07) !important;
        position: relative;
        overflow: visible;
        min-height: 0;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        transition: box-shadow 0.25s ease;
    }
    #modalRastreoCredito .rastreo-ia-box .rastreo-card-header { border-top: none !important; }
    #modalRastreoCredito .rastreo-ia-box .ia-boton-wrap .btn-ia-sec { font-size: 0.7rem; padding: 0.3rem 0.5rem; line-height: 1.2; }
    #modalRastreoCredito .rastreo-ia-box:hover {
        box-shadow: 0 14px 20px -3px rgba(0, 0, 0, 0.1), 0 6px 10px -2px rgba(0, 0, 0, 0.06) !important;
    }
    #modalRastreoCredito .rastreo-ia-box::before { display: none; }
    /* Hero IA: icono destacado elegante */
    #modalRastreoCredito .rastreo-ia-box .ia-hero-wrap { display: flex; align-items: flex-start; gap: 1.25rem; }
    #modalRastreoCredito .rastreo-ia-box .ia-hero-icon {
        width: 56px; height: 56px; min-width: 56px; min-height: 56px;
        border-radius: 14px;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #818cf8 100%);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        flex-shrink: 0;
    }
    #modalRastreoCredito .rastreo-ia-box .ia-content { flex: 1; min-width: 0; }
    #modalRastreoCredito .rastreo-col-centro .rastreo-centro-card { display: flex; flex-direction: column; }
    /* Iconografía: iconos FontAwesome del modal en azul de marca #4f46e5 */
    #modalRastreoCredito .rastreo-seccion-direcciones i[class*="fa-"],
    #modalRastreoCredito .rastreo-seccion-gestiones i[class*="fa-"],
    #modalRastreoCredito .rastreo-seccion-evidencias i[class*="fa-"],
    #modalRastreoCredito .rastreo-seccion-bitacora > .d-flex.mb-2 i[class*="fa-"],
    #modalRastreoCredito .rastreo-seccion-dictamen > .d-flex.mb-2 i[class*="fa-"],
    #modalRastreoCredito .rastreo-ia-box .ia-title i[class*="fa-"],
    #modalRastreoCredito .rastreo-tickets-cell i[class*="fa-"],
    #modalRastreoCredito .rastreo-header-right i[class*="fa-user"] { color: #4f46e5 !important; }
    #modalRastreoCredito .rastreo-seccion-bitacora .bitacora-avatar { color: #fff !important; }
    #modalRastreoCredito .rastreo-ia-box .ia-hero-icon { color: #fff !important; }
    #modalRastreoCredito .btn-primary i, #modalRastreoCredito .btn-analizar-ia i { color: #fff !important; }
    #modalRastreoCredito .rastreo-header-right .text-success { color: var(--bs-success) !important; }
    #modalRastreoCredito .rastreo-analitica-bar { border-color: #E5E7EB; }
    /* Resumen analítico: mismo tamaño que los otros 3, color distinto (agrupa info de Ubicaciones, Gestiones/Pagos, Cumplimiento) */
    #modalRastreoCredito .rastreo-analitica-bar .btn-resumen-analitico {
        border: 1px solid #0d9488;
        color: #0d9488;
        background-color: transparent;
    }
    #modalRastreoCredito .rastreo-analitica-bar .btn-resumen-analitico:hover {
        background-color: #0d9488;
        color: #fff;
        border-color: #0d9488;
    }
    /* Scrim para modal Analítica IA: fondo oscuro detrás del modal, bloquea scroll body */
    .scrim { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1000; }
    /* Header oscuro para modal Análisis IA / Resumen analítico (título visible) */
    #modalPrediccionIA .modal-header-analitica-ia {
      background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%) !important;
      color: #fff !important;
      border-bottom-color: rgba(255,255,255,0.12) !important;
    }
    #modalPrediccionIA .modal-header-analitica-ia .modal-title,
    #modalPrediccionIA .modal-header-analitica-ia .modal-title i { color: #fff !important; }
    #modalPrediccionIA.modal-analitica-ia .modal-dialog { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: 1080; width: min(92vw, 1140px); max-width: 92vw; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
    #modalPrediccionIA.modal-analitica-ia .modal-content { max-height: 90vh; display: flex; flex-direction: column; }
    #modalPrediccionIA.modal-analitica-ia .modal-body { overflow-y: auto; flex: 1 1 auto; min-height: 0; padding: 1rem 1.25rem; }
    #modalRastreoCredito .rastreo-card-header { border-bottom-color: #F3F4F6; }
    /* Títulos: mayúsculas pequeñas, negrita, más letter-spacing */
    #modalRastreoCredito .rastreo-seccion-direcciones > .d-flex.mb-2,
    #modalRastreoCredito .rastreo-seccion-gestiones > .d-flex.mb-2,
    #modalRastreoCredito .rastreo-seccion-evidencias > .d-flex.mb-2,
    #modalRastreoCredito .rastreo-seccion-bitacora > .d-flex.mb-2 { font-weight: 700; color: #374151; letter-spacing: 0.04em; text-transform: uppercase; font-size: 0.75rem; border-bottom: 1px solid #F3F4F6; padding-bottom: 0.5rem; margin-bottom: 0.75rem; line-height: 1.4; }
    #modalRastreoCredito .rastreo-bitacora-titulo { border-bottom: 3px solid #334155 !important; padding-bottom: 0.5rem; margin-bottom: 0.5rem; }
    #modalRastreoCredito #btnBitacoraAmpliar { min-width: 32px; min-height: 32px; display: inline-flex; align-items: center; justify-content: center; }
    #modalRastreoCredito #btnBitacoraAmpliar:hover { color: #4f46e5; border-color: #4f46e5; }
    #modalRastreoCredito #btnDictamenAmpliar { min-width: 32px; min-height: 32px; display: inline-flex; align-items: center; justify-content: center; }
    #modalRastreoCredito #btnDictamenAmpliar:hover { color: #4f46e5; border-color: #4f46e5; }
    #modalRastreoCredito .rastreo-seccion-direcciones > .d-flex.mb-2 .fw-semibold.small.text-muted,
    #modalRastreoCredito .rastreo-seccion-gestiones > .d-flex.mb-2 .fw-semibold.small.text-muted,
    #modalRastreoCredito .rastreo-seccion-evidencias > .d-flex.mb-2 .fw-semibold.small.text-muted,
    #modalRastreoCredito .rastreo-seccion-bitacora > .d-flex.mb-2 .fw-semibold.small.text-muted { color: #374151 !important; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; }
    #modalRastreoCredito .rastreo-ia-box .ia-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; color: #374151; font-weight: 700; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #F3F4F6; line-height: 1.4; }
    #modalRastreoCredito .rastreo-ia-box .ia-desc { font-size: 0.9rem; color: #475569; margin-bottom: 0.5rem; line-height: 1.55; letter-spacing: 0.01em; flex: 1; min-height: 0; overflow: auto; }
    #modalRastreoCredito .rastreo-ia-box .ia-content { display: flex; flex-direction: column; flex: 1; min-height: 0; min-width: 0; }
    #modalRastreoCredito .rastreo-ia-box .ia-boton-wrap { margin-top: auto; display: flex; justify-content: flex-end; align-items: center; flex-wrap: wrap; gap: 0.35rem; padding-top: 0.5rem; padding-bottom: 0.15rem; max-width: 100%; min-width: 0; flex-shrink: 0; overflow-x: auto; overflow-y: visible; min-height: 2rem; }
    #modalRastreoCredito .rastreo-ia-box .ia-boton-wrap .btn { flex-shrink: 0; visibility: visible; }
    #modalRastreoCredito .rastreo-ia-box .btn-analizar-ia { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border: none; color: #fff; padding: 0.6rem 1.25rem; border-radius: 10px; font-weight: 600; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4); transition: transform 0.15s ease, box-shadow 0.15s ease; }
    #modalRastreoCredito .rastreo-ia-box .btn-analizar-ia:hover { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #fff; box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5); transform: translateY(-1px); }
    #modalRastreoCredito #rastreoAnalizarIAContenido { font-size: 0.85rem; line-height: 1.6; overflow-y: auto; min-height: 0; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid #e2e8f0; }
    #modalRastreoCredito .rastreo-seccion-evidencias { flex: 1; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }
    #modalRastreoCredito .rastreo-seccion-evidencias #rastreoEvidenciasSlots { overflow: auto; min-height: 0; }
    #modalRastreoCredito .rastreo-seccion-gestiones { flex: 1; min-height: 0; display: flex; flex-direction: column; overflow: hidden; min-height: 120px; }
    #modalRastreoCredito #rastreoGestionesContenido { max-height: 100%; overflow: auto; min-height: 60px; }
    /* Histórico de gestiones: tarjetas compactas y limpias */
    #modalRastreoCredito .gestion-card {
        border-left: 4px solid #4f46e5;
        background: #f8fafc;
        border-radius: 0 10px 10px 0;
        padding: 0.6rem 0.75rem;
        margin-bottom: 0.65rem;
        font-size: 0.8rem;
        line-height: 1.4;
    }
    #modalRastreoCredito .gestion-card:last-child { margin-bottom: 0; }
    #modalRastreoCredito .gestion-meta {
        display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
        margin-bottom: 0.4rem; padding-bottom: 0.35rem; border-bottom: 1px solid #e2e8f0;
    }
    #modalRastreoCredito .gestion-app { font-weight: 600; color: #4f46e5; }
    #modalRastreoCredito .gestion-fecha { font-size: 0.7rem; color: #64748b; }
    #modalRastreoCredito .gestion-row {
        display: flex; gap: 0.5rem; margin-bottom: 0.25rem; align-items: flex-start;
    }
    #modalRastreoCredito .gestion-row .gestion-label { flex-shrink: 0; font-weight: 600; color: #475569; min-width: 5rem; }
    #modalRastreoCredito .gestion-row .gestion-val { color: #334155; word-break: break-word; }
    #modalRastreoCredito .gestion-comentarios {
        margin-top: 0.35rem; padding: 0.4rem 0.5rem; background: #f1f5f9; border-radius: 6px;
        font-size: 0.75rem; color: #475569; border-left: 2px solid #cbd5e1;
        word-break: break-word; overflow-wrap: break-word; max-width: 100%;
    }
    @media (min-width: 992px) {
        #modalRastreoCredito .rastreo-col-gestiones,
        #modalRastreoCredito .rastreo-col-evidencias { border-left: none; }
    }
    /* Chat estilo Messenger/WhatsApp: bitácora; en columna derecha el 50% lo da el grid (1fr 1fr) */
    #modalRastreoCredito .rastreo-seccion-bitacora { background: #fff; min-width: 0; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
    #modalRastreoCredito .rastreo-seccion-bitacora > .d-flex.mb-2 { flex-shrink: 0; }
    #modalRastreoCredito #rastreoBitacoraContenido,
    #modalRastreoCredito #rastreoDictamenContenido { min-width: 0; overflow-wrap: break-word; word-break: break-word; padding: 0.25rem 0; overflow-y: auto; flex: 1; min-height: 0; }
    #modalRastreoCredito .bitacora-msg { display: flex; gap: 0.5rem; margin-bottom: 0.65rem; align-items: flex-end; }
    #modalRastreoCredito .bitacora-msg.bitacora-msg-mine { flex-direction: row-reverse; }
    #modalRastreoCredito .rastreo-seccion-bitacora .bitacora-avatar { background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); width: 28px; height: 28px; min-width: 28px; font-size: 0.65rem; }
    #modalRastreoCredito .bitacora-msg-mine .bitacora-avatar { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); }
    #modalRastreoCredito .bitacora-avatar { width: 28px; height: 28px; min-width: 28px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; flex-shrink: 0; }
    #modalRastreoCredito .bitacora-msg-body { flex: 1; min-width: 0; max-width: 85%; }
    #modalRastreoCredito .bitacora-msg-mine .bitacora-msg-body { display: flex; flex-direction: column; align-items: flex-end; }
    /* Burbuja otros (izq): gris claro */
    #modalRastreoCredito .bitacora-bubble { background: #f1f5f9; border-radius: 14px 14px 14px 4px; padding: 0.5rem 0.85rem; box-shadow: none; border: none; max-width: 100%; color: #334155; }
    /* Burbuja míos (der): azul suave */
    #modalRastreoCredito .bitacora-msg-mine .bitacora-bubble { background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%); color: #fff; border-radius: 14px 14px 4px 14px; }
    #modalRastreoCredito .bitacora-msg-mine .bitacora-msg-header { color: rgba(255,255,255,0.9); }
    #modalRastreoCredito .bitacora-msg-mine .bitacora-msg-header strong { color: #fff; }
    #modalRastreoCredito .bitacora-msg-header { font-size: 0.7rem; color: #64748b; margin-bottom: 0.2rem; }
    #modalRastreoCredito .bitacora-msg-header strong { color: #334155; font-size: 0.8rem; }
    #modalRastreoCredito .bitacora-btn-delete { flex-shrink: 0; opacity: 1; color: #dc2626 !important; }
    #modalRastreoCredito .bitacora-btn-delete i { color: #dc2626 !important; }
    #modalRastreoCredito .bitacora-btn-delete:hover { color: #b91c1c !important; }
    #modalRastreoCredito .bitacora-btn-delete:hover i { color: #b91c1c !important; }
    #modalRastreoCredito .bitacora-msg-mine .bitacora-btn-delete { color: #dc2626 !important; }
    #modalRastreoCredito .bitacora-msg-mine .bitacora-btn-delete i { color: #dc2626 !important; }
    /* Scrim entre modales: padre abajo, scrim en medio, hijo encima */
    #modalRastreoCredito.modal-below-scrim { z-index: 1045 !important; }
    .modal-scrim { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1060; pointer-events: auto; }
    .modal.modal-nested-open { z-index: 1070 !important; }
    /* Input chat: cápsula redondeada */
    #modalRastreoCredito #rastreoBitacoraInput { border-radius: 24px; border: 1px solid #e2e8f0; padding: 0.5rem 1rem; font-size: 0.9rem; }
    #modalRastreoCredito #rastreoBitacoraInput:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }
    #modalRastreoCredito #rastreoBitacoraEnviar { border-radius: 50%; width: 38px; height: 38px; min-width: 38px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    #modalRastreoCredito .rastreo-bitacora-input-wrap { padding: 0.25rem 0; margin-top: auto; flex-shrink: 0; }
    #modalRastreoCredito .rastreo-dictamen-form .form-label { margin-bottom: 0.25rem; }
    #modalRastreoCredito .rastreo-dictamen-form .evidencia-slot { width: 100%; aspect-ratio: 1; max-height: 100px; }
    .rastreo-domicilios-wrap { display: flex; flex-direction: column; gap: 0.5rem; }
    .rastreo-domicilios-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem; }
    .rastreo-domicilio-block { display: grid; grid-template-columns: 1fr auto; gap: 0.35rem; align-items: start; padding: 0.5rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
    .rastreo-domicilio-block .form-control-sm { font-size: 0.8rem; }
    .rastreo-btn-add-domicilio { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: #0ea5e9 !important; border-color: #0ea5e9 !important; color: #fff !important; }
    .rastreo-btn-add-domicilio:hover { background: #0284c7 !important; border-color: #0284c7 !important; color: #fff !important; }
    /* Botones Dictamen en panel (mismos colores que en modal ampliada) */
    #modalRastreoCredito .btn-dictamen-borrador { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border-color: #0d9488; color: #fff; }
    #modalRastreoCredito .btn-dictamen-borrador:hover { background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border-color: #0f766e; color: #fff; }
    #modalRastreoCredito .btn-dictamen-enviar { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); border-color: #4f46e5; color: #fff; }
    #modalRastreoCredito .btn-dictamen-enviar:hover { background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%); border-color: #4338ca; color: #fff; }
    /* Bloque Direcciones maxi app: todo (intro + lista + mapa) dentro del bloque con scroll; el mapa no se sale */
    #modalRastreoCredito #rastreoDirecciones { display: flex; flex-direction: column; min-height: 0; flex: 1; overflow-y: auto; overflow-x: hidden; }
    #modalRastreoCredito #rastreoDirecciones .rastreo-mapa-wrap { flex-shrink: 0; min-height: 200px; height: 200px; display: flex; flex-direction: column; margin-top: 0.5rem; }
    #modalRastreoCredito #rastreoDirecciones .rastreo-mapa-wrap #rastreoMapaLeaflet { width: 100%; height: 100%; min-height: 200px; border-radius: 10px; overflow: hidden; background: #e2e8f0; cursor: pointer; }
    #modalRastreoCredito #rastreoMapaLeaflet.leaflet-container { font-family: inherit; }
    .leaflet-tooltip-distancia { font-size: 11px; font-weight: bold; background: rgba(248,250,252,0.95); padding: 2px 6px; border-radius: 4px; white-space: nowrap; }
    /* Modal mapa grande: 90% pantalla */
    #modalMapaGrande .modal-dialog { max-width: 90vw; width: 90vw; height: 90vh; max-height: 90vh; margin: 1rem auto; }
    #modalMapaGrande .modal-content { display: flex; flex-direction: column; min-height: 80vh; }
    #modalMapaGrande .modal-body { flex: 1; min-height: 0; display: flex; flex-direction: column; }
    #modalMapaGrande #rastreoMapaGrandeContenedor { flex: 1; width: 100%; min-height: 400px; border-radius: 8px; background: #e2e8f0; }
    /* Mapa Direcciones alternas: clic para ampliar */
    #rastreoMapaAlternasWrap { position: relative; cursor: pointer; border-radius: 10px; overflow: hidden; }
    #rastreoMapaAlternasWrap .rastreo-mapa-ampliar-badge { position: absolute; bottom: 8px; right: 8px; z-index: 10; font-size: 0.75rem; background: rgba(0,0,0,0.65); color: #fff; padding: 4px 8px; border-radius: 6px; pointer-events: none; }
    #modalMapaAlternasGrande .modal-dialog { max-width: 90vw; width: 90vw; height: 90vh; max-height: 90vh; margin: 1rem auto; }
    #modalMapaAlternasGrande .modal-content { display: flex; flex-direction: column; min-height: 80vh; }
    #modalMapaAlternasGrande .modal-body { flex: 1; min-height: 0; display: flex; flex-direction: column; }
    #modalMapaAlternasGrande #rastreoMapaAlternasGrandeContenedor { flex: 1; width: 100%; min-height: 400px; border-radius: 8px; background: #e2e8f0; position: relative; }
    /* Dropzone evidencias: dashed, fondo casi transparente, ícono grande amigable */
    #modalRastreoCredito .evidencia-slot { width: 100%; aspect-ratio: 1; max-height: 120px; border: 2px dashed #cbd5e1; border-radius: 12px; background: rgba(241, 245, 249, 0.8); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem; cursor: pointer; overflow: hidden; transition: border-color 0.2s ease, background 0.2s ease; }
    #modalRastreoCredito .evidencia-slot i.fa-plus { font-size: 1.75rem; color: #94a3b8; }
    #modalRastreoCredito .evidencia-slot-label { font-size: 0.7rem; color: #94a3b8; }
    #modalRastreoCredito .evidencia-slot:hover .evidencia-slot-label { color: #6366f1; }
    #modalRastreoCredito .evidencia-slot img { width: 100%; height: 100%; object-fit: cover; }
    #modalRastreoCredito .evidencia-slot:hover { border-color: #6366f1; background: rgba(99, 102, 241, 0.06); }
    #modalRastreoCredito .evidencia-slot:hover i.fa-plus { color: #6366f1; }
    /* Modal Bitácora ampliada: mismos estilos de mensajes que en el panel */
    #modalBitacoraAmpliada .bitacora-msg { display: flex; gap: 0.5rem; margin-bottom: 0.65rem; align-items: flex-end; }
    #modalBitacoraAmpliada .bitacora-msg.bitacora-msg-mine { flex-direction: row-reverse; }
    #modalBitacoraAmpliada .bitacora-avatar { width: 28px; height: 28px; min-width: 28px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; flex-shrink: 0; font-size: 0.65rem; }
    #modalBitacoraAmpliada .rastreo-seccion-bitacora .bitacora-avatar,
    #modalBitacoraAmpliada .bitacora-msg:not(.bitacora-msg-mine) .bitacora-avatar { background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); }
    #modalBitacoraAmpliada .bitacora-msg-mine .bitacora-avatar { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); }
    #modalBitacoraAmpliada .bitacora-bubble { background: #f1f5f9; border-radius: 14px 14px 14px 4px; padding: 0.5rem 0.85rem; max-width: 100%; color: #334155; }
    #modalBitacoraAmpliada .bitacora-msg-mine .bitacora-bubble { background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%); color: #fff; border-radius: 14px 14px 4px 14px; }
    #modalBitacoraAmpliada .bitacora-msg-header { font-size: 0.7rem; color: #64748b; margin-bottom: 0.2rem; }
    #modalBitacoraAmpliada .bitacora-msg-header strong { color: #334155; font-size: 0.8rem; }
    #modalBitacoraAmpliada .bitacora-msg-mine .bitacora-msg-header { color: rgba(255,255,255,0.9); }
    #modalBitacoraAmpliada .bitacora-msg-mine .bitacora-msg-header strong { color: #fff; }
    /* Tamaño grande para el modal de bitácora ampliada (un poco más chico) */
    #modalBitacoraAmpliada .modal-dialog.modal-bitacora-ampliada { max-width: 80vw; width: 80vw; max-height: 78vh; height: 78vh; margin: 1rem auto; }
    #modalBitacoraAmpliada .modal-content { display: flex; flex-direction: column; min-height: 0; }
    #modalBitacoraAmpliada .modal-body { flex: 1; min-height: 0; display: flex; flex-direction: column; }
    #modalBitacoraAmpliada .bitacora-ampliada-mensajes { flex: 1; min-height: 320px; font-size: 1rem; line-height: 1.5; }
    #modalBitacoraAmpliada .bitacora-msg { margin-bottom: 1rem; }
    #modalBitacoraAmpliada .bitacora-avatar { width: 40px; height: 40px; min-width: 40px; font-size: 0.9rem; }
    #modalBitacoraAmpliada .bitacora-msg-header { font-size: 0.85rem; }
    #modalBitacoraAmpliada .bitacora-msg-header strong { font-size: 1rem; }
    #modalBitacoraAmpliada .bitacora-bubble { padding: 0.65rem 1rem; font-size: 1rem; }
    #modalBitacoraAmpliada .bitacora-btn-delete,
    #modalBitacoraAmpliada .bitacora-btn-delete i { color: #dc2626 !important; }
    #modalBitacoraAmpliada .bitacora-btn-delete:hover,
    #modalBitacoraAmpliada .bitacora-btn-delete:hover i { color: #b91c1c !important; }
    /* Modo oscuro: modal Bitácora ampliada */
    body.dark-mode #modalBitacoraAmpliada .modal-content { background: #1e293b; border-color: #334155; }
    body.dark-mode #modalBitacoraAmpliada .modal-header { background: #1e293b; border-bottom-color: #334155; color: #f1f5f9; }
    body.dark-mode #modalBitacoraAmpliada .modal-header .btn-close { filter: invert(1); opacity: 0.8; }
    body.dark-mode #modalBitacoraAmpliada .modal-body { background: #1e293b; color: #e2e8f0; }
    body.dark-mode #modalBitacoraAmpliada .bitacora-ampliada-mensajes { background: #1e293b; color: #e2e8f0; }
    body.dark-mode #modalBitacoraAmpliada .bitacora-bubble { background: #334155; color: #e2e8f0; border: 1px solid #475569; }
    body.dark-mode #modalBitacoraAmpliada .bitacora-msg-mine .bitacora-bubble { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #fff; border-color: transparent; }
    body.dark-mode #modalBitacoraAmpliada .bitacora-msg-header { color: #94a3b8; }
    body.dark-mode #modalBitacoraAmpliada .bitacora-msg-header strong { color: #f1f5f9; }
    body.dark-mode #modalBitacoraAmpliada .bitacora-msg-mine .bitacora-msg-header { color: rgba(255,255,255,0.9); }
    body.dark-mode #modalBitacoraAmpliada .bitacora-msg-mine .bitacora-msg-header strong { color: #fff; }
    body.dark-mode #modalBitacoraAmpliada .bitacora-msg:not(.bitacora-msg-mine) .bitacora-avatar { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }
    body.dark-mode #modalBitacoraAmpliada .bitacora-btn-delete,
    body.dark-mode #modalBitacoraAmpliada .bitacora-btn-delete i { color: #dc2626 !important; }
    body.dark-mode #modalBitacoraAmpliada .bitacora-btn-delete:hover,
    body.dark-mode #modalBitacoraAmpliada .bitacora-btn-delete:hover i { color: #b91c1c !important; }
    body.dark-mode #modalBitacoraAmpliada .bitacora-msg-mine .bitacora-avatar { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
    body.dark-mode #modalBitacoraAmpliada .border-top { border-color: #334155 !important; }
    body.dark-mode #modalBitacoraAmpliada .form-control { background: #334155; border-color: #475569; color: #f1f5f9; }
    body.dark-mode #modalBitacoraAmpliada .form-control::placeholder { color: #94a3b8; }
    body.dark-mode #modalBitacoraAmpliada .form-control:focus { background: #334155; border-color: #6366f1; color: #f1f5f9; }
    /* Modal Dictamen ampliada: más grande y botones con color */
    #modalDictamenAmpliada .modal-dialog.modal-dictamen-ampliada { max-width: 90vw; width: 800px; }
    @media (min-width: 992px) {
        #modalDictamenAmpliada .modal-dialog.modal-dictamen-ampliada { width: 840px; max-width: 90vw; }
    }
    @media (min-width: 1200px) {
        #modalDictamenAmpliada .modal-dialog.modal-dictamen-ampliada { width: 920px; }
    }
    #modalDictamenAmpliada .btn-dictamen-borrador { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border-color: #0d9488; color: #fff; }
    #modalDictamenAmpliada .btn-dictamen-borrador:hover { background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border-color: #0f766e; color: #fff; }
    #modalDictamenAmpliada .btn-dictamen-enviar { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); border-color: #4f46e5; color: #fff; }
    #modalDictamenAmpliada .btn-dictamen-enviar:hover { background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%); border-color: #4338ca; color: #fff; }
    /* Evidencia dinámica: un Añadir fijo + fotos que se van corriendo */
    .evidencia-dinamica-wrap { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 0.5rem; }
    .evidencia-slot-add { flex-shrink: 0; width: 100px; height: 100px; min-width: 100px; min-height: 100px; border: 2px dashed #cbd5e1; border-radius: 12px; background: rgba(241, 245, 249, 0.9); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.25rem; cursor: pointer; transition: border-color 0.2s, background 0.2s; }
    .evidencia-slot-add:hover { border-color: #6366f1; background: rgba(99, 102, 241, 0.08); }
    .evidencia-slot-add i.fa-plus { font-size: 1.5rem; color: #94a3b8; }
    .evidencia-slot-add:hover i.fa-plus { color: #6366f1; }
    .evidencia-slot-add .evidencia-slot-label { font-size: 0.7rem; color: #94a3b8; }
    .evidencia-slot-add:hover .evidencia-slot-label { color: #6366f1; }
    .evidencia-fotos-list { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: flex-start; min-height: 0; }
    .evidencia-foto-item { position: relative; flex-shrink: 0; width: 100px; height: 100px; min-width: 100px; min-height: 100px; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; background: #f1f5f9; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.25s ease, border-color 0.2s ease; }
    .evidencia-foto-item:hover { transform: scale(1.06); box-shadow: 0 8px 20px rgba(0,0,0,0.15); border-color: #6366f1; }
    .evidencia-foto-item img { width: 100%; height: 100%; object-fit: cover; display: block; pointer-events: none; }
    .evidencia-foto-item .evidencia-foto-ver { position: absolute; inset: 0; background: rgba(99, 102, 241, 0.35); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s ease; pointer-events: none; }
    .evidencia-foto-item .evidencia-foto-ver i { font-size: 1.75rem; color: #fff; text-shadow: 0 1px 4px rgba(0,0,0,0.4); }
    .evidencia-foto-item:hover .evidencia-foto-ver { opacity: 1; }
    .evidencia-foto-item .evidencia-foto-quitar { position: absolute; top: 2px; right: 2px; width: 22px; height: 22px; border-radius: 50%; background: rgba(0,0,0,0.6); color: #fff; border: none; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; cursor: pointer; opacity: 0.9; z-index: 2; }
    .evidencia-foto-item .evidencia-foto-quitar:hover { background: #dc2626; opacity: 1; }
    .evidencia-foto-item.evidencia-loading .evidencia-foto-cargando { display: flex; }
    .evidencia-foto-item.evidencia-loading img { opacity: 0; }
    .evidencia-foto-item .evidencia-foto-cargando { position: absolute; inset: 0; background: #f1f5f9; display: none; align-items: center; justify-content: center; font-size: 0.65rem; color: #94a3b8; pointer-events: none; }
    /* Modal ver evidencia dictamen (lightbox) */
    #modalVerEvidenciaDictamen .modal-content { border: none; border-radius: 12px; overflow: visible; box-shadow: 0 12px 40px rgba(0,0,0,0.25); }
    #modalVerEvidenciaDictamen #modalVerEvidenciaDictamenImg { transition: opacity 0.2s ease; }
    @keyframes evidenciaVerEntrada { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    /* Panel Admin: columnas y textos un poco más chicos; botones tipo menú gestión (btn-sm estándar) */
    #tablaTicketsPanel.table thead th {
        font-size: 0.78rem;
        letter-spacing: 0.02em;
    }
    #tablaTicketsPanel.table tbody td {
        font-size: 0.8125rem;
    }
    #tablaTicketsPanel.table tbody td .small,
    #tablaTicketsPanel.table tbody td small {
        font-size: 0.72rem;
    }
    /* Botones acción: mismo tamaño que analítica/gestión (btn-sm) */
    #tablaTicketsPanel.table tbody td:last-child .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8125rem;
        line-height: 1.35;
    }
    #tablaTicketsPanel th:nth-child(6) { min-width: 10rem; white-space: nowrap; }
    #tablaTicketsPanel .d-flex.flex-wrap.gap-1 { flex-wrap: wrap; gap: 0.4rem !important; }
    #tablaTicketsPanel .d-flex.flex-wrap.gap-1 .btn { flex-shrink: 0; }
    #tablaTicketsPanel.table tbody td:last-child .d-flex.flex-column {
        gap: 0.4rem !important;
        align-items: center;
    }
    /* Prórroga +12h: reloj ámbar + "2" al lado = segunda ventana */
    #tablaTicketsPanel .dictamen-countdown-prorroga .fa-clock { color: #d97706 !important; }
    #tablaTicketsPanel .dictamen-prorroga-marca,
    #tablaTickets .dictamen-prorroga-marca {
        font-size: 0.6rem;
        font-weight: 800;
        color: #b45309;
        margin-left: 2px;
        line-height: 1;
        vertical-align: super;
    }
    #tablaTickets .dictamen-countdown-prorroga .fa-clock { color: #d97706 !important; }
    #tablaTicketsPanel .dictamen-countdown-intensidad .fa-clock,
    #tablaTickets .dictamen-countdown-intensidad .fa-clock {
        color: var(--bs-info) !important;
    }
    #tablaTicketsPanel .dictamen-intensidad-marca,
    #tablaTickets .dictamen-intensidad-marca {
        font-size: 0.6rem;
        font-weight: 800;
        color: var(--bs-info);
        margin-left: 2px;
        line-height: 1;
        vertical-align: super;
    }
    #tablaTicketsPanel tr.fila-dictamen-enviado { border-left: 3px solid #0d6efd; }
    #tablaTicketsPanel .btn-dictamen-ojito { cursor: pointer; }
    @media (max-width: 768px) {
        #tablaTicketsPanel .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    }
    /* Easter egg Panel Admin: icono sabueso + toast */
    .sabueso-easter-icon { color: #94a3b8; font-size: 0.95rem; cursor: pointer; transition: transform 0.2s, color 0.2s; opacity: 0.85; }
    .sabueso-easter-icon:hover { color: #0d9488; transform: scale(1.15); opacity: 1; }
    body.dark-mode .sabueso-easter-icon { color: #64748b; }
    body.dark-mode .sabueso-easter-icon:hover { color: #2dd4bf; }
    .sabueso-easter-toast { position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 1060; background: linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #14b8a6 100%); color: #fff; padding: 20px 36px; border-radius: 16px; font-size: 1.1rem; font-weight: 600; box-shadow: 0 16px 48px rgba(13, 148, 136, 0.35); border: 2px solid rgba(255,255,255,0.3); opacity: 0; animation: sabuesoToastIn 0.35s ease forwards; pointer-events: none; text-align: center; }
    .sabueso-easter-toast .sabueso-toast-emoji { font-size: 2.5rem; display: block; margin-bottom: 8px; }
    @keyframes sabuesoToastIn { 0% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); } 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); } }
    @keyframes sabuesoToastOut { 0% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 100% { opacity: 0; transform: translate(-50%, -50%) scale(0.95); } }
    /* Modal evidencia: z-index se fija por JS al abrir para quedar por delante de Iniciar rastreo */
    #modalEvidenciaRastreo .modal-dialog {
        max-width: 95vw;
        width: 100%;
    }
    @media (min-width: 992px) {
        #modalEvidenciaRastreo .modal-dialog { max-width: 1000px; }
    }
    #modalEvidenciaRastreo .modal-content {
        border: none;
        border-radius: 8px;
       /* overflow: hidden;*/
        box-shadow: 0 8px 32px rgba(0,0,0,0.25);
    }
    #modalEvidenciaRastreo .modal-header {
        background: #1e3a5f;
        color: #e2e8f0;
        border-bottom: 1px solid #2d4a6f;
        padding: 0.5rem 0.75rem;
    }
    #modalEvidenciaRastreo .modal-header .modal-title {
        font-weight: 500;
        font-size: 0.9rem;
        color: #e2e8f0;
    }
    #modalEvidenciaRastreo .modal-header .btn-close-evidencia {
        width: auto;
        height: auto;
        padding: 0.35rem 0.65rem;
        margin: 0;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 6px;
        color: #fff;
        font-size: 0.85rem;
        opacity: 1;
    }
    #modalEvidenciaRastreo .modal-header .btn-close-evidencia:hover {
        background: rgba(255,255,255,0.3);
        color: #fff;
    }
    #modalEvidenciaRastreo .modal-body {
        background: #2d3748;
        padding: 0.75rem;
        min-height: 80px;
    }
    #modalEvidenciaRastreo .modal-body img {
        border-radius: 6px;
        max-height: 85vh;
        width: auto;
        max-width: 100%;
        display: inline-block;
    }
    #modalEvidenciaRastreo .modal-body .text-muted {
        color: #94a3b8 !important;
        font-size: 0.85rem;
    }
    #modalEvidenciaRastreo .modal-footer {
        background: #1e293b;
        border-top: 1px solid #334155;
        padding: 0.5rem 0.75rem;
    }
    #modalEvidenciaRastreo .modal-footer .btn-danger {
        background: #7f1d1d;
        border-color: #7f1d1d;
        color: #fecaca;
    }
    #modalEvidenciaRastreo .modal-footer .btn-danger:hover {
        background: #991b1b;
        border-color: #991b1b;
        color: #fff;
    }
    #modalEvidenciaRastreo .modal-footer .btn-secondary {
        background: #475569;
        border-color: #475569;
        color: #e2e8f0;
    }
    #modalEvidenciaRastreo .modal-footer .btn-secondary:hover {
        background: #64748b;
        border-color: #64748b;
        color: #fff;
    }

    /* ========== RESPONSIVE: celular, laptop y zoom ========== */
    /* Base: permitir zoom (no usar maximum-scale=1 en viewport) y reflow con unidades relativas */
    html { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }
    @media (max-width: 992px) {
        #modalRastreoCredito .modal-body { overflow-y: auto; -webkit-overflow-scrolling: touch; }
        #modalRastreoCredito .rastreo-grid { min-height: auto; }
        #modalRastreoCredito .rastreo-seccion-direcciones,
        #modalRastreoCredito .rastreo-seccion-gestiones,
        #modalRastreoCredito .rastreo-seccion-evidencias,
        #modalRastreoCredito .rastreo-seccion-bitacora,
        #modalRastreoCredito .rastreo-seccion-dictamen { margin-bottom: 0.75rem !important; padding: 0.75rem !important; }
        #modalRastreoCredito .rastreo-header-grid { grid-template-columns: 1fr; padding-left: 0; }
        #modalRastreoCredito .rastreo-header-right { border-left: none; border-top: 1px solid rgba(0,0,0,0.08); padding-top: 0.75rem; margin-top: 0.5rem; padding-left: 0; }
        #modalRastreoCredito .rastreo-header-left .rastreo-datos-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        #modalRastreoCredito.modal .modal-dialog { width: 100%; max-width: 100%; height: 100%; max-height: 100%; margin: 0; border-radius: 0; }
        #modalRastreoCredito .modal-content { border-radius: 0; }
        #modalRastreoCredito .modal-body { padding: 0.75rem; }
        #modalRastreoCredito .rastreo-header-left .rastreo-datos-row { grid-template-columns: 1fr; gap: 0.5rem 0; }
        #modalRastreoCredito #rastreoDirecciones .rastreo-mapa-wrap #rastreoMapaLeaflet { min-height: 140px; }
        #modalRastreoCredito .rastreo-ia-box { padding: 1rem; min-height: auto; }
        #modalRastreoCredito .rastreo-ia-box .ia-hero-icon { width: 44px; height: 44px; min-width: 44px; min-height: 44px; font-size: 1.25rem; }
        .card { margin-left: 0.25rem; margin-right: 0.25rem; }
        #tablaTicketsPanel .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    }
    @media (max-width: 576px) {
        #modalRastreoCredito .modal-body { padding: 0.5rem; }
        #modalRastreoCredito .rastreo-header-grid { margin-bottom: 0.75rem; padding-bottom: 0.75rem; }
        #modalRastreoCredito .rastreo-seccion-direcciones,
        #modalRastreoCredito .rastreo-seccion-gestiones,
        #modalRastreoCredito .rastreo-seccion-evidencias,
        #modalRastreoCredito .rastreo-seccion-bitacora,
        #modalRastreoCredito .rastreo-seccion-dictamen { padding: 0.6rem !important; min-height: 80px; }
        #modalRastreoCredito #rastreoDirecciones .rastreo-mapa-wrap #rastreoMapaLeaflet { min-height: 120px; }
        /* Touch: botones mínimo 44px para dedo */
        #modalRastreoCredito #rastreoBitacoraEnviar,
        #modalRastreoCredito .rastreo-bitacora-input-wrap .btn,
        #modalRastreoCredito .rastreo-dictamen-form .btn { min-width: 44px; min-height: 44px; }
        #modalRastreoCredito #rastreoBitacoraInput { min-height: 44px; padding: 0.5rem 1rem; }
        #modalRastreoCredito .btn-analizar-ia,
        #modalRastreoCredito #btnResumirUbicacionesIA,
        #modalRastreoCredito .modal-footer .btn { min-height: 44px; padding: 0.5rem 1rem; }
        #modalRastreoCredito .evidencia-slot { min-height: 80px; }
        #modalRastreoCredito .gestion-row .gestion-label { min-width: 4rem; }
        .card-header, .card { padding-left: 0.5rem; padding-right: 0.5rem; }
    }
    body.panel-admin-primer-cargando #wrapTablaTicketsPanel {
        position: relative;
        min-height: 240px;
    }
    body.panel-admin-primer-cargando #tablaTicketsPanel {
        visibility: hidden !important;
    }
    body.panel-admin-primer-cargando #wrapTablaTicketsPanel::before {
        content: "Cargando datos de la tabla...";
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        color: #6b7280;
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(255,255,255,0.96));
        border-top: 1px solid #eef0f2;
        z-index: 2;
    }
    body.panel-admin-primer-cargando #tablaTicketsPanel_info,
    body.panel-admin-primer-cargando #tablaTicketsPanel_paginate { visibility: hidden !important; }
</style>
<!-- Modal consulta solo por ID crédito (Rastreo): paso 1 pide ID; luego reusa vista rastreo sin bitácora/dictamen/asignar -->
<div class="modal fade" id="modalConsultaCreditoPaso1" tabindex="-1" aria-labelledby="modalConsultaCreditoPaso1Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 pb-0 rounded-top-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">
                <div class="w-100 text-center py-3">
                    <div class="rounded-circle bg-white bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px;">
                        <i class="fa-solid fa-magnifying-glass-chart fa-xl text-white"></i>
                    </div>
                    <h5 class="modal-title text-white mb-1" id="modalConsultaCreditoPaso1Label">Rastreo</h5>
                    <?php if (!$panel_admin_solo_consulta_credito): ?>
                    <p class="small text-white-50 mb-0 px-3">Vista de direcciones, mapas y analítica sin levantar ticket. Solo lectura.</p>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-4 pb-4 px-4">
                <label for="inputConsultaIdCredito" class="form-label fw-semibold">ID de crédito</label>
                <div class="input-group input-group-lg mb-3">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-hashtag text-muted"></i></span>
                    <input type="text" class="form-control form-control-lg" id="inputConsultaIdCredito" placeholder="Ej. 799811" inputmode="numeric" autocomplete="off" maxlength="12">
                    <button type="button" class="btn btn-primary px-4" id="btnConsultaCreditoIr">
                        <i class="fa-solid fa-arrow-right me-1"></i>Consultar
                    </button>
                </div>
                <div id="consultaCreditoPaso1Error" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <?php if (!$panel_admin_solo_consulta_credito): ?>
                <p class="small text-muted mb-0"><i class="fa-solid fa-circle-info me-1"></i>Se cargarán megareporte, direcciones alternas, mapas y analítica igual que en rastreo, sin bitácora ni dictamen.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal datos del crédito (desde ticket = rastreo; desde Analítica = solo consulta por ID) -->
<div class="modal fade<?= $panel_admin_solo_consulta_credito ? ' consulta-sin-ticket consulta-reporteria-solo' : '' ?>" id="modalRastreoCredito" tabindex="-1" aria-labelledby="modalRastreoCreditoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-glass shadow-sm">
            <div class="modal-header py-2 border-bottom d-flex align-items-center">
                <h6 class="modal-title text-primary mb-0" id="modalRastreoCreditoLabel">
                    <?php if ($panel_admin_solo_consulta_credito): ?>
                    <i class="fa-solid fa-magnifying-glass-chart me-2"></i>Consulta de crédito
                    <?php else: ?>
                    <i class="fa-solid fa-magnifying-glass-plus me-2"></i>Iniciar rastreo – Datos del crédito
                    <?php endif; ?>
                </h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <input type="hidden" id="rastreoIdTicketActual" value="" data-id-ticket="">
                <!-- Encabezado: columna izquierda = datos cliente + ticket; columna derecha = quién levantó / cuando / asignado -->
                <div class="rastreo-header-grid">
                    <div class="rastreo-header-left">
                        <div class="rastreo-datos-row" id="rastreoTopLeft">
                            <!-- Se llena por JS: 4 celdas = ID crédito, Nombre, Teléfono, Dirección megareporte -->
                        </div>
                        <div class="rastreo-tickets-cell" id="rastreoTicketsWrap">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-ticket text-primary"></i>
                                <span class="fw-semibold small text-muted">Ticket(s) levantado(s)</span>
                            </div>
                            <div id="rastreoTickets" class="credito-modal-list">
                                <!-- Se llena por JS: solo el ticket actual -->
                            </div>
                        </div>
                    </div>
                    <div class="rastreo-header-right" id="rastreoTopRight">
                        <!-- Se llena por JS: Quién levantó el ticket, Cuando se levantó, Asignado a -->
                    </div>
                </div>
                <!-- Botones de analítica determinística (independientes de IA) -->
                <div class="rastreo-analitica-bar mb-3 d-flex align-items-center gap-2 flex-wrap border-bottom pb-2">
                    <span class="small text-muted me-1">Analítica:</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btnAnaliticaUbicaciones" title="Ver distancias a casa, última apertura y aperturas últimos 5 días" aria-label="Abrir analítica de ubicaciones" aria-hidden="true">
                        <i class="fa-solid fa-location-dot me-1"></i>Ubicaciones
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAnaliticaPagos" title="Ver intervalo, desviación, día más frecuente y patrón de pagos" aria-label="Abrir analítica de gestiones y pagos">
                        <i class="fa-solid fa-calendar-check me-1"></i>Gestiones / Pagos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAnaliticaCumplimiento" title="Ver cumplimiento del gestor (visitas cercanas vs lejanas)" aria-label="Abrir analítica de cumplimiento del gestor">
                        <i class="fa-solid fa-user-check me-1"></i>Cumplimiento Gestor
                    </button>
                    <button type="button" class="btn btn-sm btn-resumen-analitico" id="btnResumenAnaliticaIA" title="Ver resumen analítico por reglas (cómo localizar al acreditado)" style="display: none;">
                        <i class="fa-solid fa-chart-line me-1"></i>Resumen analítico
                    </button>
                </div>
                <!-- Abajo: 3 columnas 34% | 34% | 32% = Direcciones maxi app | Direcciones alternas | Bitácora+Dictamen -->
                <div class="rastreo-grid">
                    <div class="rastreo-col-izq">
                        <div class="rastreo-seccion-direcciones p-3 h-100" id="rastreoDirecciones">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-magnifying-glass text-primary"></i>
                                <span class="fw-semibold small text-muted">Direcciones (maxi app)</span>
                            </div>
                            <div id="rastreoDireccionesContenido" class="small mb-3">
                                <!-- Se llena por JS -->
                            </div>
<?php /* Resumir ubicaciones con IA y Lectura/Borrar: ocultos; solo se muestra Analizar. */ ?>
                            <div class="rastreo-mapa-wrap">
                                <div id="rastreoMapaLeaflet"></div>
                            </div>
                        </div>
                    </div>
                    <div class="rastreo-col-direcciones-alternas">
                        <div class="rastreo-seccion-direcciones p-3 h-100" id="rastreoDireccionesAlternas">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-location-dot text-primary"></i>
                                <span class="fw-semibold small text-muted">Direcciones alternas</span>
                            </div>
                            <div id="rastreoGeoSeleccionadaCard" class="rastreo-geo-card mb-2 small" style="display:none;"></div>
                            <div id="rastreoDireccionesAlternasContenido" class="small">
                                <!-- Donde firma + mapa; margen controlado por CSS -->
                            </div>
                            <div class="rastreo-mapa-wrap rastreo-mapa-alternas-wrap" id="rastreoMapaAlternasWrap" title="Clic para ampliar">
                                <div id="rastreoMapaAlternas" style="width: 100%; height: 100%; min-height: 160px; border-radius: 10px;"></div>
                                <span class="rastreo-mapa-ampliar-badge"><i class="fa-solid fa-expand me-1"></i>Clic para ampliar</span>
                            </div>
                        </div>
                    </div>
                    <div class="rastreo-col-bitacora-wrap">
                        <div class="rastreo-seccion-bitacora p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 rastreo-bitacora-titulo">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-comments text-primary"></i>
                                    <span class="fw-semibold small text-muted">Bitácora</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-sm p-1" id="btnBitacoraAmpliar" title="Ampliar bitácora" aria-label="Ampliar bitácora"><i class="fa-solid fa-expand"></i></button>
                            </div>
                            <div id="rastreoBitacoraContenido" class="small flex-grow-1 overflow-auto mb-2" style="min-height: 72px;">
                                <!-- Mensajes con avatar por JS -->
                            </div>
                            <div class="d-flex gap-2 align-items-center rastreo-bitacora-input-wrap">
                                <input type="text" class="form-control form-control-sm" id="rastreoBitacoraInput" placeholder="Escribir mensaje..." maxlength="500">
                                <button type="button" class="btn btn-sm btn-primary flex-shrink-0" id="rastreoBitacoraEnviar" title="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
                            </div>
                        </div>
                        <div class="rastreo-seccion-dictamen p-3 d-flex flex-column">
                            <div class="rastreo-dictamen-minimizado d-flex align-items-center justify-content-center flex-grow-1" style="min-height: 72px;">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnDictamenAmpliar" title="Ampliar dictamen" aria-label="Ampliar dictamen"><i class="fa-solid fa-expand me-1"></i>Ampliar dictamen</button>
                            </div>
                            <div class="rastreo-dictamen-form d-none" id="rastreoDictamenFormCompleto">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 rastreo-bitacora-titulo">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-file-lines text-primary"></i>
                                        <span class="fw-semibold small text-muted">Dictamen</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label for="rastreoDictamenCombo" class="form-label small fw-semibold text-muted mb-1">Tipo de dictamen <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="rastreoDictamenCombo" required aria-required="true">
                                        <option value="">Seleccione...</option>
                                        <option value="ilocalizable">ILOCALIZABLE</option>
                                        <option value="localizable">LOCALIZABLE</option>
                                        <option value="dual_zonificacion">DUAL || ZONIFICACIÓN</option>
                                        <option value="falta_intensidad_gestion">FALTA INTENSIDAD DE GESTION</option>
                                    </select>
                                </div>
                                <div class="mb-2" id="rastreoDictamenBloqueComentarios">
                                    <label for="rastreoDictamenDescripcion" class="form-label small fw-semibold text-muted mb-1">Descripción <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-sm" id="rastreoDictamenDescripcion" rows="3" placeholder="Escriba la descripción..." required aria-required="true" maxlength="4000"></textarea>
                                </div>
                                <div class="mb-2" id="rastreoDictamenBloqueDomicilios">
                                    <span class="form-label small fw-semibold text-muted mb-0 d-block">Domicilios de visita</span>
                                    <div id="rastreoDictamenDomiciliosWrap" class="rastreo-domicilios-wrap"></div>
                                    <div class="mt-1 text-end"><button type="button" class="btn btn-sm rounded-circle p-1 rastreo-btn-add-domicilio" id="btnAddDomicilioPanel" title="Añadir domicilio" aria-label="Añadir domicilio"><i class="fa-solid fa-plus"></i></button></div>
                                </div>
                                <div class="mb-2" id="rastreoDictamenBloqueEvidencia">
                                    <span class="form-label small fw-semibold text-muted d-block mb-1">Evidencia</span>
                                    <div class="evidencia-dinamica-wrap" id="rastreoDictamenEvidenciasWrap">
                                        <div class="evidencia-slot evidencia-slot-add" id="rastreoDictamenEvidenciaAdd" role="button" tabindex="0" title="Añadir foto">
                                            <i class="fa-solid fa-plus"></i>
                                            <span class="evidencia-slot-label">Añadir</span>
                                        </div>
                                        <div class="evidencia-fotos-list" id="rastreoDictamenEvidenciasFotos"></div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap mt-2">
                                    <button type="button" class="btn btn-sm btn-dictamen-enviar" id="btnDictamenEnviarGestor" title="Enviar al gestor"><i class="fa-solid fa-paper-plane me-1"></i>Enviar al gestor</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Columna centro (IA, evidencias, gestiones) oculta; se mantiene en DOM para referencias JS -->
                <div class="d-none" id="rastreoColCentro" aria-hidden="true">
                    <div id="rastreoAnalizarIAContenido" class="small"></div>
                    <div class="rastreo-seccion-evidencias rastreo-col-evidencias rastreo-centro-card" id="rastreoEvidenciasWrap"><div class="row g-2" id="rastreoEvidenciasSlots"></div></div>
                    <div class="rastreo-seccion-gestiones rastreo-col-gestiones rastreo-centro-card" id="rastreoGestionesWrap"><div id="rastreoGestionesContenido" class="small overflow-auto flex-grow-1"><span class="text-muted">Contactación y dictamen (se cargan por crédito).</span></div></div>
                </div>
            </div>
            <div class="modal-footer py-2 border-top d-flex flex-wrap gap-2 justify-content-end align-items-center">
                <?php if (!$panel_admin_solo_consulta_credito): ?>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAsignarRastreo" onclick="mostrarAsignarOpciones()" title="Asignar este ticket">
                    <i class="fa-solid fa-user-plus me-1"></i>Asignar...
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lectura IA: relectura de Resumir ubicaciones o Resumen gestiones (sin volver a llamar a la IA) -->
<div class="modal fade" id="modalLecturaIA" tabindex="-1" aria-labelledby="modalLecturaIALabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2 border-bottom d-flex align-items-center">
                <h6 class="modal-title mb-0" id="modalLecturaIALabel"><i class="fa-solid fa-book-open me-2"></i>Lectura de IA</h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-4" id="modalLecturaIABody" style="line-height: 1.7; max-height: 70vh; overflow-y: auto;">
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Datos verificados: evidencia cruda (pagos, GPS, gestiones) para contrastar con la IA -->
<div class="modal fade" id="modalEvidenciaVerificacion" tabindex="-1" aria-labelledby="modalEvidenciaVerificacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2 border-bottom d-flex align-items-center bg-success bg-opacity-10">
                <h6 class="modal-title mb-0" id="modalEvidenciaVerificacionLabel">
                    <i class="fa-solid fa-circle-check me-2 text-success"></i>Datos verificados – Evidencia real del sistema
                </h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-4" id="modalEvidenciaVerificacionBody" style="line-height: 1.6; max-height: 70vh; overflow-y: auto;">
                <p class="text-muted mb-0">Cargando...</p>
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Predicción IA: reporte completo de cómo localizar al acreditado -->
<div class="modal fade" id="modalPrediccionIA" tabindex="-1" aria-labelledby="modalPrediccionIALabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header modal-header-analitica-ia py-3 border-bottom d-flex align-items-center">
                <h5 class="modal-title mb-0" id="modalPrediccionIALabel">
                    <i class="fa-solid fa-chart-line me-2"></i>Analítica – Resumen
                </h5>
                <button type="button" class="btn-close btn-close-white btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-4" id="modalPrediccionIABody" style="line-height: 1.6;">
                <p class="text-muted mb-0">Se calcularán las métricas del crédito (ubicación, pagos, gestión) por reglas determinísticas. No se usa IA en este análisis.</p>
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Asignar a: lista de personas del departamento Sabueso -->
<div class="modal fade" id="modalAsignarA" tabindex="-1" aria-labelledby="modalAsignarALabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm">
            <div class="modal-header py-2 border-bottom d-flex align-items-center">
                <h6 class="modal-title text-primary mb-0" id="modalAsignarALabel">
                    <i class="fa-solid fa-user-plus me-2"></i>Asignar a...
                </h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3" id="modalAsignarABody">
                <!-- Se llena por JS con personas del departamento Sabueso -->
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bitácora ampliada: ver mensajes y escribir en grande -->
<div class="modal fade" id="modalBitacoraAmpliada" tabindex="-1" aria-labelledby="modalBitacoraAmpliadaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-bitacora-ampliada">
        <div class="modal-content h-100">
            <div class="modal-header py-2 d-flex align-items-center">
                <h6 class="modal-title mb-0"><i class="fa-solid fa-comments me-2"></i>Bitácora – Vista ampliada</h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3 d-flex flex-column flex-grow-1 min-h-0">
                <div id="rastreoBitacoraAmpliadaContenido" class="bitacora-ampliada-mensajes overflow-auto mb-3">
                    <!-- Se copia el contenido de la bitácora al abrir -->
                </div>
                <div class="d-flex gap-2 align-items-center flex-shrink-0 border-top pt-2">
                    <input type="text" class="form-control form-control-lg" id="rastreoBitacoraAmpliadaInput" placeholder="Escribir mensaje..." maxlength="500">
                    <button type="button" class="btn btn-primary flex-shrink-0" id="rastreoBitacoraAmpliadaEnviar" title="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dictamen ampliada: formulario en grande -->
<div class="modal fade" id="modalDictamenAmpliada" tabindex="-1" aria-labelledby="modalDictamenAmpliadaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dictamen-ampliada modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2 d-flex align-items-center">
                <h6 class="modal-title mb-0"><i class="fa-solid fa-file-lines me-2"></i>Dictamen – Vista ampliada</h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="rastreo-dictamen-form-ampliada">
                    <div class="mb-3">
                        <label for="rastreoDictamenAmpliadaCombo" class="form-label fw-semibold text-muted">Tipo de dictamen <span class="text-danger">*</span></label>
                        <select class="form-select" id="rastreoDictamenAmpliadaCombo" required aria-required="true">
                            <option value="">Seleccione...</option>
                            <option value="ilocalizable">ILOCALIZABLE</option>
                            <option value="localizable">LOCALIZABLE</option>
                            <option value="dual_zonificacion">DUAL || ZONIFICACIÓN</option>
                            <option value="falta_intensidad_gestion">FALTA INTENSIDAD DE GESTION</option>
                        </select>
                    </div>
                    <div class="mb-3" id="rastreoDictamenAmpliadaBloqueComentarios">
                        <label for="rastreoDictamenAmpliadaDescripcion" class="form-label fw-semibold text-muted">Comentarios <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rastreoDictamenAmpliadaDescripcion" rows="3" placeholder="Escriba sus comentarios para el gestor y no incluya en este apartado ubicaciones..." required aria-required="true" maxlength="4000"></textarea>
                    </div>
                    <div class="mb-3" id="rastreoDictamenAmpliadaBloqueDomicilios">
                        <span class="form-label fw-semibold text-muted mb-0 d-block">Domicilios de visita</span>
                        <div id="rastreoDictamenAmpliadaDomiciliosWrap" class="rastreo-domicilios-wrap"></div>
                        <div class="mt-1 text-end"><button type="button" class="btn btn-sm rounded-circle p-1 rastreo-btn-add-domicilio" id="btnAddDomicilioAmpliada" title="Añadir domicilio" aria-label="Añadir domicilio"><i class="fa-solid fa-plus"></i></button></div>
                    </div>
                    <div class="mb-3" id="rastreoDictamenAmpliadaBloqueEvidencia">
                        <span class="form-label fw-semibold text-muted d-block mb-2">Evidencia</span>
                        <div class="evidencia-dinamica-wrap" id="rastreoDictamenAmpliadaEvidenciasWrap">
                            <div class="evidencia-slot evidencia-slot-add" id="rastreoDictamenAmpliadaEvidenciaAdd" role="button" tabindex="0" title="Añadir foto">
                                <i class="fa-solid fa-plus"></i>
                                <span class="evidencia-slot-label">Añadir</span>
                            </div>
                            <div class="evidencia-fotos-list" id="rastreoDictamenAmpliadaEvidenciasFotos"></div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap mt-3">
                        <button type="button" class="btn btn-dictamen-enviar" id="btnDictamenAmpliadaEnviarGestor" title="Enviar al gestor"><i class="fa-solid fa-paper-plane me-1"></i>Enviar al gestor</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal mapa grande (90% pantalla): se abre al hacer clic en el mapa pequeño -->
<div class="modal fade" id="modalMapaGrande" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered h-90">
        <div class="modal-content h-100">
            <div class="modal-header py-2 d-flex align-items-center">
                <h6 class="modal-title mb-0"><i class="fa-solid fa-map-location-dot me-2"></i>Mapa – Ubicaciones</h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-2 d-flex flex-column flex-grow-1 min-h-0">
                <div id="rastreoMapaGrandeContenedor"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal mapa Direcciones alternas grande: azul = maxi app, naranja = gestores (últimos 6) -->
<div class="modal fade" id="modalMapaAlternasGrande" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered h-90">
        <div class="modal-content h-100">
            <div class="modal-header py-2 d-flex align-items-center">
                <h6 class="modal-title mb-0"><i class="fa-solid fa-map-location-dot me-2"></i>Mapa – Direcciones alternas</h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-2 d-flex flex-column flex-grow-1 min-h-0">
                <div id="rastreoMapaAlternasGrandeContenedor"></div>
            </div>
        </div>
    </div>
</div>

<!-- Input oculto para subir evidencias -->
<input type="file" id="inputEvidenciaRastreo" accept="image/*" style="display: none;">
<input type="file" id="inputEvidenciaDictamen" accept="image/*" multiple style="display: none;">

<!-- Modal ver evidencia dictamen (lightbox): solo imagen + cerrar -->
<div class="modal fade" id="modalVerEvidenciaDictamen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered p-0 border-0" style="max-width: min(92vw, 700px);">
        <div class="modal-content border-0 shadow-lg bg-transparent">
            <button type="button" class="btn btn-link position-absolute top-0 end-0 text-white bg-dark bg-opacity-50 rounded-circle p-2 m-2 z-1" style="width: 36px; height: 36px; line-height: 1;" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fa-solid fa-times"></i></button>
            <img id="modalVerEvidenciaDictamenImg" src="" alt="Evidencia" class="img-fluid rounded" style="max-height: 85vh; width: auto; display: block;">
        </div>
    </div>
</div>

<!-- Modal Detalle del dictamen (Panel Admin: clic en ojito) -->
<div class="modal fade" id="modalDetalleDictamen" tabindex="-1" aria-labelledby="modalDetalleDictamenLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-glass">
            <div class="modal-header py-2 d-flex align-items-center">
                <h5 class="modal-title mb-0" id="modalDetalleDictamenLabel"><i class="fa-solid fa-file-lines me-2"></i>Detalle del dictamen</h5>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-12 col-md-5 bg-light bg-opacity-50 p-3 border-end">
                        <div class="dictamen-detalle-imagen-principal mb-2 rounded overflow-hidden bg-dark bg-opacity-10" style="min-height: 200px;">
                            <img id="modalDetalleDictamenImgPrincipal" src="" alt="Evidencia" class="img-fluid w-100" style="object-fit: contain; max-height: 280px;">
                        </div>
                        <div class="d-flex flex-wrap gap-2 dictamen-detalle-miniaturas" id="modalDetalleDictamenMiniaturas"></div>
                    </div>
                    <div class="col-12 col-md-7 p-4">
                        <div class="alert alert-info py-2 mb-3 d-flex align-items-center gap-2" id="modalDetalleDictamenNota12h" role="note">
                            <i class="fa-solid fa-clock text-info"></i>
                            <span>Vas a tener 12 horas para visitar al cliente</span>
                        </div>
                        <div class="mb-3"><span class="text-muted small">Tipo</span><div id="modalDetalleDictamenTipo" class="fw-semibold"></div></div>
                        <div class="mb-3"><span class="text-muted small">Descripción</span><div id="modalDetalleDictamenDescripcion" class="text-break"></div></div>
                        <div class="mb-3" id="modalDetalleDictamenDomiciliosWrap" style="display: none;">
                            <span class="text-muted small">Domicilios de visita</span>
                            <div id="modalDetalleDictamenDomicilios" class="mt-1 d-flex flex-column gap-2"></div>
                        </div>
                        <div class="mb-2"><span class="text-muted small">Enviado</span><div id="modalDetalleDictamenEnviado" class="small"></div></div>
                        <div><span class="text-muted small">Visto por gestor</span><div id="modalDetalleDictamenVisto" class="small"></div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal evidencia: ver imagen + Eliminar / Cerrar, o cargar si está vacío -->
<div class="modal fade" id="modalEvidenciaRastreo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h6 class="modal-title mb-0"><i class="fa-solid fa-image me-2"></i>Evidencia</h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center" id="modalEvidenciaRastreoBody">
                <!-- Vista previa de imagen o zona para arrastrar/soltar -->
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-danger" id="modalEvidenciaEliminar" style="display: none;"><i class="fa-solid fa-trash me-1"></i>Eliminar</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modales analítica determinística (Ubicaciones, Pagos, Cumplimiento Gestor) -->
<div class="modal fade" id="modalAnaliticaSpatial" tabindex="-1" aria-labelledby="modalAnaliticaSpatialLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h6 class="modal-title" id="modalAnaliticaSpatialLabel">Analítica: Ubicaciones</h6>
                <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-analytics-force data-analytics-modal="modalAnaliticaSpatial" data-analytics-type="spatial" data-analytics-title="Analítica: Ubicaciones" title="Forzar actualización (omitir caché)">Forzar actualización</button>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalAnaliticaSpatialBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalAnaliticaPayments" tabindex="-1" aria-labelledby="modalAnaliticaPaymentsLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h6 class="modal-title" id="modalAnaliticaPaymentsLabel">Analítica: Gestiones / Pagos</h6>
                <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-analytics-force data-analytics-modal="modalAnaliticaPayments" data-analytics-type="payments" data-analytics-title="Analítica: Gestiones / Pagos" title="Forzar actualización (omitir caché)">Forzar actualización</button>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalAnaliticaPaymentsBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalAnaliticaCompliance" tabindex="-1" aria-labelledby="modalAnaliticaComplianceLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h6 class="modal-title" id="modalAnaliticaComplianceLabel">Analítica: Cumplimiento Gestor</h6>
                <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-analytics-force data-analytics-modal="modalAnaliticaCompliance" data-analytics-type="compliance" data-analytics-title="Analítica: Cumplimiento Gestor" title="Forzar actualización (omitir caché)">Forzar actualización</button>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body overflow-auto" id="modalAnaliticaComplianceBody" style="max-height: 70vh;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Scrim entre modales: se inyecta por JS cuando se abre un modal hijo sobre el de rastreo -->
<script src="/assets/js/analytics-modals.js"></script>
<script>window.PANEL_ADMIN_MODULO_URLS=<?= isset($panel_admin_modulo_urls_json) && $panel_admin_modulo_urls_json !== '' ? $panel_admin_modulo_urls_json : '{}'; ?>;</script>
<script>
window.etiquetaTipoDictamenSabueso = function(tipo) {
    var t = (tipo || '').toString().toLowerCase().trim();
    var m = { ilocalizable: 'ILOCALIZABLE', localizable: 'LOCALIZABLE', dual_zonificacion: 'DUAL || ZONIFICACIÓN', falta_intensidad_gestion: 'FALTA INTENSIDAD DE GESTION',
        localizado: 'Localizado', no_localizado: 'No localizado', promesa_pago: 'Promesa de pago', otro: 'Otro' };
    return m[t] || (tipo && String(tipo).trim() ? String(tipo).trim() : '—');
};
window.esTipoDictamenIlocalizable = function(tipo) {
    return (tipo || '').toString().toLowerCase().trim() === 'ilocalizable';
};
window.actualizarDictamenCamposPorTipo = function() {
    var v = ($('#rastreoDictamenCombo').val() || '').toString();
    var esIloc = typeof window.esTipoDictamenIlocalizable === 'function' && window.esTipoDictamenIlocalizable(v);
    var sel = '#rastreoDictamenBloqueComentarios, #rastreoDictamenBloqueDomicilios, #rastreoDictamenBloqueEvidencia, #rastreoDictamenAmpliadaBloqueComentarios, #rastreoDictamenAmpliadaBloqueDomicilios, #rastreoDictamenAmpliadaBloqueEvidencia';
    if (esIloc) {
        $(sel).addClass('d-none');
        $('#rastreoDictamenDescripcion, #rastreoDictamenAmpliadaDescripcion').prop('required', false).removeAttr('required');
    } else {
        $(sel).removeClass('d-none');
        $('#rastreoDictamenDescripcion, #rastreoDictamenAmpliadaDescripcion').prop('required', true).attr('aria-required', 'true');
    }
};
</script>
<script>
(function() {
    var SCRIM_Z = 1060;
    var CHILD_MODALS = ['modalAsignarA', 'modalAnaliticaSpatial', 'modalAnaliticaPayments', 'modalAnaliticaCompliance', 'modalLecturaIA', 'modalEvidenciaVerificacion', 'modalPrediccionIA', 'modalMapaGrande', 'modalMapaAlternasGrande', 'modalEvidenciaRastreo', 'modalBitacoraAmpliada', 'modalDictamenAmpliada', 'modalDetalleDictamen', 'modalDictamenSistema'];
    var scrimEl = null;
    var parentModal = null;
    function getOrCreateScrim() {
        if (scrimEl && scrimEl.parentNode) return scrimEl;
        scrimEl = document.createElement('div');
        scrimEl.id = 'scrim-entre-modal-padre-hijo';
        scrimEl.className = 'modal-scrim scrim-entre-modales';
        scrimEl.setAttribute('aria-hidden', 'true');
        scrimEl.setAttribute('data-role', 'scrim-padre-hijo');
        scrimEl.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:' + SCRIM_Z + ';pointer-events:auto;';
        return scrimEl;
    }
    function cleanupScrim() {
        if (scrimEl && scrimEl.parentNode) scrimEl.parentNode.removeChild(scrimEl);
        parentModal = document.getElementById('modalRastreoCredito');
        if (parentModal) parentModal.classList.remove('modal-below-scrim');
        CHILD_MODALS.forEach(function(id) {
            var m = document.getElementById(id);
            if (m) m.classList.remove('modal-nested-open');
        });
    }
    function onChildModalShown(ev) {
        var childModal = ev.target;
        parentModal = document.getElementById('modalRastreoCredito');
        if (!parentModal || !parentModal.classList.contains('show')) return;
        // Modal padre queda detrás; al abrir un hijo encima se pone el scrim entre padre e hijo. El hijo debe quedar POR ENCIMA del scrim.
        var isResumenAnalitica = (childModal.id === 'modalPrediccionIA' && childModal.classList.contains('modal-analitica-ia'));

        parentModal.classList.add('modal-below-scrim');

        // Primero: poner el modal hijo POR ENCIMA del scrim (z-index mayor que SCRIM_Z) ANTES de insertar el scrim
        var childZ = isResumenAnalitica ? '1090' : '1070';
        childModal.style.setProperty('z-index', childZ, 'important');
        var dialogEl = childModal.querySelector('.modal-dialog');
        if (dialogEl) dialogEl.style.setProperty('z-index', childZ, 'important');

        // Mover el modal hijo a body para que sea hermano del scrim y no quede detrás por estar dentro del padre
        if (childModal.parentNode !== document.body) {
            document.body.appendChild(childModal);
        }

        var el = getOrCreateScrim();
        if (el.parentNode) {
            el.parentNode.removeChild(el);
        }
        // Scrim con z-index 1060 (SCRIM_Z); el hijo ya tiene 1070/1090, queda por encima
        document.body.insertBefore(el, childModal);

        // Bootstrap añade .modal-backdrop: TODOS deben quedar DETRÁS del modal hijo (z-index menor que childZ)
        requestAnimationFrame(function() {
            var backdrops = document.querySelectorAll('.modal-backdrop');
            var zDebajoHijo = '1040';
            for (var b = 0; b < backdrops.length; b++) {
                backdrops[b].style.setProperty('z-index', zDebajoHijo, 'important');
                if (isResumenAnalitica && b === backdrops.length - 1) {
                    backdrops[b].style.setProperty('pointer-events', 'none', 'important');
                }
            }
        });

        childModal.classList.add('modal-nested-open');
        document.body.style.overflow = 'hidden';
    }
    function onChildModalHidden(ev) {
        var modal = ev.target;
        modal.classList.remove('modal-nested-open');
        modal.style.removeProperty('z-index');
        var dialog = modal.querySelector('.modal-dialog');
        if (dialog) dialog.style.removeProperty('z-index');
        var backdrops = document.querySelectorAll('.modal-backdrop');
        for (var b = 0; b < backdrops.length; b++) {
            backdrops[b].style.removeProperty('pointer-events');
            backdrops[b].style.removeProperty('z-index');
        }
        var anyChildOpen = CHILD_MODALS.some(function(id) {
            var m = document.getElementById(id);
            return m && m.classList.contains('show');
        });
        if (!anyChildOpen) {
            if (parentModal) parentModal.classList.remove('modal-below-scrim');
            if (scrimEl && scrimEl.parentNode) scrimEl.parentNode.removeChild(scrimEl);
            document.body.style.overflow = '';
            // Evitar scrim/backdrop colgado: si no queda ningún modal hijo, quitar backdrops sobrantes
            var openModals = document.querySelectorAll('.modal.show');
            if (openModals.length <= 1) {
                document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
            }
        }
    }
    function onParentModalHidden() {
        cleanupScrim();
        document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
        document.body.style.overflow = '';
    }
    function bindModals() {
        parentModal = document.getElementById('modalRastreoCredito');
        if (parentModal) {
            parentModal.addEventListener('hidden.bs.modal', onParentModalHidden);
        }
        CHILD_MODALS.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('shown.bs.modal', onChildModalShown);
                el.addEventListener('hidden.bs.modal', onChildModalHidden);
            }
        });
        // Dictamen sistema: al cerrar, limpiar scrim/backdrop colgado (tras prórroga o refresh)
        var dictamenSistemaEl = document.getElementById('modalDictamenSistema');
        if (dictamenSistemaEl) {
            dictamenSistemaEl.addEventListener('hidden.bs.modal', function() {
                setTimeout(function() {
                    var open = document.querySelectorAll('.modal.show');
                    if (open.length === 0) {
                        document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    } else if (document.querySelectorAll('.modal-backdrop').length > open.length) {
                        var backs = document.querySelectorAll('.modal-backdrop');
                        while (backs.length > open.length && backs.length > 0) {
                            backs[backs.length - 1].remove();
                            backs = document.querySelectorAll('.modal-backdrop');
                        }
                    }
                }, 200);
            });
        }
        var verEvidenciaEl = document.getElementById('modalVerEvidenciaDictamen');
        if (verEvidenciaEl) {
            verEvidenciaEl.addEventListener('shown.bs.modal', function() {
                verEvidenciaEl.style.setProperty('z-index', '1080', 'important');
                var d = verEvidenciaEl.querySelector('.modal-dialog');
                if (d) d.style.setProperty('z-index', '1080', 'important');
                if (verEvidenciaEl.parentNode !== document.body) document.body.appendChild(verEvidenciaEl);
                var backdrops = document.querySelectorAll('.modal-backdrop');
                for (var b = 0; b < backdrops.length; b++) backdrops[b].style.setProperty('z-index', '1075', 'important');
            });
            verEvidenciaEl.addEventListener('hidden.bs.modal', function() {
                verEvidenciaEl.style.removeProperty('z-index');
                var d = verEvidenciaEl.querySelector('.modal-dialog');
                if (d) d.style.removeProperty('z-index');
                var backdrops = document.querySelectorAll('.modal-backdrop');
                for (var b = 0; b < backdrops.length; b++) backdrops[b].style.removeProperty('z-index');
                $('#modalVerEvidenciaDictamenImg').attr('src', '');
                setTimeout(function() {
                    var anyChildOpen = CHILD_MODALS.some(function(id) {
                        var m = document.getElementById(id);
                        return m && m.classList.contains('show');
                    });
                    var openModals = document.querySelectorAll('.modal.show');
                    var backdropsNow = document.querySelectorAll('.modal-backdrop');
                    if (!anyChildOpen) {
                        if (parentModal) parentModal.classList.remove('modal-below-scrim');
                        var scrim = document.getElementById('scrim-entre-modal-padre-hijo');
                        if (scrim) scrim.remove();
                    } else {
                        // Si queda un modal hijo abierto (p.ej. Dictamen ampliada), el backdrop debe quedar detrás.
                        for (var k = 0; k < backdropsNow.length; k++) {
                            backdropsNow[k].style.setProperty('z-index', '1040', 'important');
                        }
                    }
                    if (openModals.length === 0) {
                        backdropsNow.forEach(function(b) { b.remove(); });
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                    } else if (backdropsNow.length > openModals.length) {
                        var n = backdropsNow.length - openModals.length;
                        for (var i = 0; i < n; i++) {
                            var list = document.querySelectorAll('.modal-backdrop');
                            if (list.length === 0) break;
                            list[list.length - 1].remove();
                        }
                    }
                }, 150);
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindModals);
    } else {
        bindModals();
    }
})();
(function runWhenJQuery() {
    if (typeof window.$ === 'undefined') {
        setTimeout(runWhenJQuery, 50);
        return;
    }
    $(document).ready(function() {
        var apiBase = (function(){ var p = window.location.pathname || ''; var i = p.indexOf('/sabueso'); return i !== -1 ? p.substring(0, i) : ''; })();
        // Oculta el estado vacío y el loader hasta que la tabla termine de dibujar (clase y Swal se quitan en onSuccess/onError de getTicketsPanelAdmin).
        if (!window.PANEL_ADMIN_SOLO_CONSULTA_CREDITO) {
        (function ocultarEstadoVacioPrimeraCarga() {
            document.body.classList.add('panel-admin-primer-cargando');
        })();
        }

        // —— Filtros Panel Admin (window.panelAdminFiltros lo consume getTicketsPanelAdmin en Sabueso.php) ——
        window.panelAdminFiltros = window.panelAdminFiltros || {};
        function syncPanelAdminFiltrosFromUI() {
            window.panelAdminFiltros.categoria_gestion = 'sabueso';
            var asignado = parseInt($('#filtroAsignado').val(), 10);
            if (isNaN(asignado)) asignado = 0;
            window.panelAdminFiltros.asignado = asignado;
            window.panelAdminFiltros.dictamen_enviado = ($('#filtroDictamenEnviado').val() || '').trim();
            window.panelAdminFiltros.ds_estado = ($('#filtroDsEstado').val() || '').trim();
            window.panelAdminFiltros.dictamen_visto = ($('#filtroDictamenVisto').val() || '').trim();
        }
        function aplicarFiltrosPanelAdminAlCambiar() {
            syncPanelAdminFiltrosFromUI();
            if (typeof getTicketsPanelAdmin === 'function') getTicketsPanelAdmin();
        }
        function limpiarFiltrosPanelAdmin() {
            $('#filtroAsignado').val('0');
            $('#filtroDictamenEnviado').val('');
            $('#filtroDsEstado').val('');
            $('#filtroDictamenVisto').val('');
            window.panelAdminFiltros = { categoria_gestion: 'sabueso' };
            if (typeof getTicketsPanelAdmin === 'function') getTicketsPanelAdmin();
        }
        if ($('#panelAdminFiltrosWrap').length && typeof http !== 'undefined') {
            http.request({
                endpoint: '/sabueso/getPersonasSabueso',
                metodo: 'POST',
                onSuccess: function(resp) {
                    var list = resp.datos || [];
                    var $sel = $('#filtroAsignado');
                    $sel.find('option:not([value="0"]):not([value="-1"])').remove();
                    list.forEach(function(p) {
                        if (p.id) $sel.append($('<option></option>').attr('value', p.id).text(p.nombre_completo || p.id));
                    });
                }
            });
            $('#panelAdminFiltrosWrap').on('change', '.panel-admin-filtro-select', function () {
                aplicarFiltrosPanelAdminAlCambiar();
            });
            $('#btnLimpiarFiltrosPanel').on('click', limpiarFiltrosPanelAdmin);
        }

        // —— Rastreo / consulta por ID crédito: modal rastreo con secciones ocultas ——
        function abrirModalConsultaCreditoPaso1() {
            $('#consultaCreditoPaso1Error').addClass('d-none').text('');
            $('#inputConsultaIdCredito').val('');
            var el = document.getElementById('modalConsultaCreditoPaso1');
            if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(el).show();
            else if (typeof $ !== 'undefined') $('#modalConsultaCreditoPaso1').modal('show');
        }
        function ejecutarConsultaCreditoIr() {
            var raw = ($('#inputConsultaIdCredito').val() || '').toString().trim();
            var id = parseInt(raw, 10);
            if (!raw || isNaN(id) || id < 1) {
                $('#consultaCreditoPaso1Error').removeClass('d-none').text('Escriba un ID de crédito numérico válido.');
                return;
            }
            $('#consultaCreditoPaso1Error').addClass('d-none');
            var paso1 = document.getElementById('modalConsultaCreditoPaso1');
            if (paso1 && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var m1 = bootstrap.Modal.getInstance(paso1) || bootstrap.Modal.getOrCreateInstance(paso1);
                if (m1) m1.hide();
            } else if (typeof $ !== 'undefined') $('#modalConsultaCreditoPaso1').modal('hide');

            var $modalRastreo = $('#modalRastreoCredito');
            $modalRastreo.addClass('consulta-sin-ticket');
            window._consultaCreditoSolo = true;
            window.ticketIdRastreoActual = null;
            $('#rastreoIdTicketActual').val('').attr('data-id-ticket', '');
            $modalRastreo.attr('data-id-ticket', '');

            var $ghost = $('<button type="button" class="btn-rastreo d-none" style="display:none"></button>')
                .attr('data-id-credito', id)
                .attr('data-id-ticket', '')
                .attr('data-asignado', '')
                .attr('data-creador-nombre', '')
                .attr('data-fecha-creacion', '');
            $('body').append($ghost);
            if (typeof abrirRastreo === 'function') {
                try { abrirRastreo($ghost[0]); } catch (e) { console.warn(e); }
            } else {
                http.request({
                    endpoint: '/sabueso/getDatosCredito',
                    metodo: 'POST',
                    data: JSON.stringify({ id_credito: id, omitir_fad: true }),
                    contentType: 'application/json',
                    processData: false,
                    onSuccess: function(resp) {
                        if (!resp.success || !resp.datos) {
                            abrirModalConsultaCreditoPaso1();
                            $('#consultaCreditoPaso1Error').removeClass('d-none').text(resp.mensaje || 'No se encontró el crédito.');
                            return;
                        }
                        var d = resp.datos;
                        var idCred = d.id_credito || id;
                        var nombre = (d.Nombre_cliente || d.nombre_completo || '—').toString().replace(/</g, '&lt;');
                        var tel = ((d.telefono_cliente != null && String(d.telefono_cliente).trim() !== '') ? String(d.telefono_cliente).trim() : '—').toString().replace(/</g, '&lt;');
                        var dom = (d.Domicilio_Completo || '—').toString().replace(/</g, '&lt;');
                        var htmlTop = '<div><span class="text-muted small d-block">ID crédito</span><div class="fw-semibold">' + idCred + '</div></div>' +
                            '<div><span class="text-muted small d-block">Nombre completo</span><div class="fw-semibold">' + nombre + '</div></div>' +
                            '<div><span class="text-muted small d-block">Teléfono cliente</span><div class="fw-semibold">' + tel + '</div></div>' +
                            '<div><span class="text-muted small d-block">Dirección megareporte</span><div class="fw-semibold small">' + dom + '</div></div>';
                        $('#rastreoTopLeft').html(htmlTop);
                        if (typeof sabuesoAppendInformacionIngresos === 'function') {
                            var escFad = function(s) {
                                var x = (s + '').split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;');
                                return x.split(String.fromCharCode(34)).join('&quot;');
                            };
                            sabuesoAppendInformacionIngresos(document.getElementById('rastreoTopLeft'), d, escFad);
                        }
                        window.idCreditoRastreoActual = idCred;
                        if (typeof $ !== 'undefined' && $.fn.modal) $modalRastreo.modal('show');
                    },
                    onError: function() {
                        abrirModalConsultaCreditoPaso1();
                        $('#consultaCreditoPaso1Error').removeClass('d-none').text('Error de conexión.');
                    }
                });
            }
            setTimeout(function() { $ghost.remove(); }, 2000);
        }
        window.ejecutarConsultaCreditoIr = ejecutarConsultaCreditoIr;
        $('#btnAbrirConsultaCredito').on('click', abrirModalConsultaCreditoPaso1);
        $('#btnConsultaCreditoIr').on('click', ejecutarConsultaCreditoIr);
        $('#inputConsultaIdCredito').on('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); ejecutarConsultaCreditoIr(); }
        });
        (function abrirRastreoSiVieneDesdeEstadoCuenta() {
            try {
                var p = new URLSearchParams(window.location.search);
                var raw = p.get('abrir_id_credito');
                var idc = raw ? parseInt(raw, 10) : NaN;
                if (!idc || idc < 1 || !document.getElementById('inputConsultaIdCredito')) {
                    return;
                }
                document.getElementById('inputConsultaIdCredito').value = String(idc);
                setTimeout(function() {
                    if (typeof ejecutarConsultaCreditoIr === 'function') {
                        ejecutarConsultaCreditoIr();
                    }
                }, 350);
            } catch (e) { /* noop */ }
        })();
        $('#modalRastreoCredito').on('hidden.bs.modal', function() {
            if (window._consultaCreditoSolo) {
                $(this).removeClass('consulta-sin-ticket');
                window._consultaCreditoSolo = false;
            }
        });

        var urlSubirEvidencia = (apiBase || '') + '/sabueso/subirEvidenciaTicket';
        var evidenciasEliminadas = [];

        $('#modalRastreoCredito').on('shown.bs.modal', function() {
            var id = typeof idCreditoRastreoActual !== 'undefined' ? idCreditoRastreoActual : null;
            var btn = $('#btnResumenAnaliticaIA');
            if (id) { btn.show(); } else { btn.hide(); }
        }).on('hidden.bs.modal', function() { $('#btnResumenAnaliticaIA').hide(); });

        $('#btnBitacoraAmpliar').on('click', function() {
            var $origen = $('#rastreoBitacoraContenido');
            var $destino = $('#rastreoBitacoraAmpliadaContenido');
            if ($origen.length && $destino.length) { $destino.html($origen.html()); }
            $('#rastreoBitacoraAmpliadaInput').val($('#rastreoBitacoraInput').val() || '');
            $('#modalBitacoraAmpliada').modal('show');
        });
        $('#modalBitacoraAmpliada').on('shown.bs.modal', function() {
            var $origen = $('#rastreoBitacoraContenido');
            var $destino = $('#rastreoBitacoraAmpliadaContenido');
            if ($origen.length && $destino.length) { $destino.html($origen.html()); }
        });

        function crearBloqueDomicilio() {
            var html = '<div class="rastreo-domicilio-block">' +
                '<div class="d-flex flex-column gap-1" style="grid-column:1;">' +
                '<input type="text" class="form-control form-control-sm rastreo-domicilio-desc" placeholder="Descripción del domicilio">' +
                '<input type="url" class="form-control form-control-sm rastreo-domicilio-link" placeholder="Link Google Maps">' +
                '</div>' +
                '<button type="button" class="btn btn-sm btn-outline-danger rastreo-domicilio-quitar" title="Quitar" aria-label="Quitar"><i class="fa-solid fa-times"></i></button>' +
                '</div>';
            return $(html);
        }
        function sincronizarDomiciliosAmpliadaAPanel() {
            var $wrapA = $('#rastreoDictamenAmpliadaDomiciliosWrap');
            var $wrapP = $('#rastreoDictamenDomiciliosWrap');
            $wrapP.empty();
            $wrapA.find('.rastreo-domicilio-block').each(function() {
                var $b = $(this);
                var $n = crearBloqueDomicilio();
                $n.find('.rastreo-domicilio-desc').val($b.find('.rastreo-domicilio-desc').val());
                $n.find('.rastreo-domicilio-link').val($b.find('.rastreo-domicilio-link').val());
                $wrapP.append($n);
            });
        }
        function sincronizarDomiciliosPanelAAmpliada() {
            var $wrapP = $('#rastreoDictamenDomiciliosWrap');
            var $wrapA = $('#rastreoDictamenAmpliadaDomiciliosWrap');
            $wrapA.empty();
            $wrapP.find('.rastreo-domicilio-block').each(function() {
                var $b = $(this);
                var $n = crearBloqueDomicilio();
                $n.find('.rastreo-domicilio-desc').val($b.find('.rastreo-domicilio-desc').val());
                $n.find('.rastreo-domicilio-link').val($b.find('.rastreo-domicilio-link').val());
                $wrapA.append($n);
            });
        }
        function addDomicilioBlock(target) {
            var $wrap = target === 'ampliada' ? $('#rastreoDictamenAmpliadaDomiciliosWrap') : $('#rastreoDictamenDomiciliosWrap');
            var $b = crearBloqueDomicilio();
            $b.find('.rastreo-domicilio-quitar').on('click', function() { $b.remove(); $(document).trigger('dictamen-evidencia-cambio'); });
            $wrap.append($b);
            $(document).trigger('dictamen-evidencia-cambio');
        }
        $(document).on('click', '#btnAddDomicilioPanel', function() { if (!$('.rastreo-seccion-dictamen').hasClass('dictamen-solo-lectura')) addDomicilioBlock('panel'); });
        $(document).on('click', '#btnAddDomicilioAmpliada', function() { if (!$('.rastreo-dictamen-form-ampliada').hasClass('dictamen-solo-lectura')) addDomicilioBlock('ampliada'); });
        $(document).on('click', '.rastreo-domicilio-quitar', function() { $(this).closest('.rastreo-domicilio-block').remove(); $(document).trigger('dictamen-evidencia-cambio'); });

        window.parsearDomiciliosEnDictamen = function(descripcion) {
            if (!descripcion || typeof descripcion !== 'string') return { base: '', domicilios: [] };
            var idx = descripcion.indexOf('Podrás encontrar al usuario en ');
            if (idx === -1) return { base: descripcion.trim(), domicilios: [] };
            var base = descripcion.substring(0, idx).replace(/\.\s*$/, '').trim();
            var domStr = descripcion.substring(idx + 31).trim();
            var domicilios = [];
            domStr.split(/\s*;\s*/).forEach(function(bloq) {
                bloq = bloq.trim();
                if (!bloq) return;
                var urlMatch = bloq.match(/\s+(https?:\/\/\S+)$/);
                var link = urlMatch ? urlMatch[1] : '';
                var desc = urlMatch ? bloq.substring(0, bloq.length - urlMatch[0].length).trim() : bloq;
                domicilios.push({ desc: desc, link: link });
            });
            return { base: base, domicilios: domicilios };
        };
        window.rellenarDomiciliosDictamen = function(descripcion) {
            var parsed = typeof parsearDomiciliosEnDictamen === 'function' ? parsearDomiciliosEnDictamen(descripcion) : { base: descripcion || '', domicilios: [] };
            $('#rastreoDictamenDescripcion, #rastreoDictamenAmpliadaDescripcion').val(parsed.base);
            $('#rastreoDictamenDomiciliosWrap, #rastreoDictamenAmpliadaDomiciliosWrap').empty();
            (parsed.domicilios || []).forEach(function(dom) {
                var $b = crearBloqueDomicilio();
                $b.find('.rastreo-domicilio-desc').val(dom.desc);
                $b.find('.rastreo-domicilio-link').val(dom.link);
                $('#rastreoDictamenDomiciliosWrap').append($b);
            });
        };

        $('#btnDictamenAmpliar').on('click', function() {
            $('#rastreoDictamenAmpliadaCombo').val($('#rastreoDictamenCombo').val() || '');
            $('#rastreoDictamenAmpliadaDescripcion').val($('#rastreoDictamenDescripcion').val() || '');
            sincronizarDomiciliosPanelAAmpliada();
            $('#modalDictamenAmpliada').modal('show');
        });
        $('#modalDictamenAmpliada').on('shown.bs.modal', function() {
            $('#rastreoDictamenAmpliadaCombo').val($('#rastreoDictamenCombo').val() || '');
            $('#rastreoDictamenAmpliadaDescripcion').val($('#rastreoDictamenDescripcion').val() || '');
            sincronizarDomiciliosPanelAAmpliada();
            if (typeof window.actualizarDictamenCamposPorTipo === 'function') window.actualizarDictamenCamposPorTipo();
            var idTicket = (typeof ticketIdRastreoActual !== 'undefined' && ticketIdRastreoActual) ? ticketIdRastreoActual : (parseInt($('#rastreoIdTicketActual').val() || $('#modalRastreoCredito').attr('data-id-ticket') || '', 10) || null);
            if (idTicket && typeof rellenarEvidenciasDictamen === 'function') rellenarEvidenciasDictamen(idTicket);
        });
        $('#modalDictamenAmpliada').on('hidden.bs.modal', function() {
            $('#rastreoDictamenCombo').val($('#rastreoDictamenAmpliadaCombo').val() || '');
            $('#rastreoDictamenDescripcion').val($('#rastreoDictamenAmpliadaDescripcion').val() || '');
            sincronizarDomiciliosAmpliadaAPanel();
        });

        (function evidenciaDictamenDinamica() {
            var inputEl = document.getElementById('inputEvidenciaDictamen');
            var evidenciaTarget = 'panel';
            function getList() { return evidenciaTarget === 'ampliada' ? $('#rastreoDictamenAmpliadaEvidenciasFotos') : $('#rastreoDictamenEvidenciasFotos'); }
            $('#rastreoDictamenEvidenciaAdd').on('click', function() { evidenciaTarget = 'panel'; if ($('.rastreo-seccion-dictamen').hasClass('dictamen-solo-lectura')) return; if (inputEl) inputEl.click(); });
            $('#rastreoDictamenAmpliadaEvidenciaAdd').on('click', function() { evidenciaTarget = 'ampliada'; if ($('.rastreo-dictamen-form-ampliada').hasClass('dictamen-solo-lectura')) return; if (inputEl) inputEl.click(); });
            $(inputEl).on('change', function() {
                var files = this.files;
                var $list = getList();
                if (!files || !files.length) return;
                for (var i = 0; i < files.length; i++) {
                    var f = files[i];
                    if (!f.type.match(/^image\//)) continue;
                    var url = URL.createObjectURL(f);
                    var $item = $('<div class="evidencia-foto-item evidencia-loading" title="Clic para ver en grande"><span class="evidencia-foto-cargando">Cargando…</span><span class="evidencia-foto-ver"><i class="fa-solid fa-magnifying-glass-plus"></i></span><img src="" alt="" decoding="async"><button type="button" class="evidencia-foto-quitar" aria-label="Quitar"><i class="fa-solid fa-times"></i></button></div>');
                    $item.find('img').attr('src', url);
                    $item.find('img').on('load', function() { $item.removeClass('evidencia-loading'); });
                    $item.find('img').on('error', function() { $item.removeClass('evidencia-loading'); });
                    $item.data('url', url);
                    $item.data('file', f);
                    $item.on('click', function(e) {
                        if ($(e.target).closest('.evidencia-foto-quitar').length) return;
                        var src = $item.find('img').attr('src');
                        if (src) {
                            $('#modalVerEvidenciaDictamenImg').attr('src', src);
                            $('#modalVerEvidenciaDictamen').modal('show');
                        }
                    });
                    $item.find('.evidencia-foto-quitar').on('click', function(e) {
                        e.stopPropagation();
                        var u = $item.data('url');
                        if (u) try { URL.revokeObjectURL(u); } catch(err) {}
                        $item.remove();
                        $(document).trigger('dictamen-evidencia-cambio');
                    });
                    $list.append($item);
                }
                this.value = '';
                $(document).trigger('dictamen-evidencia-cambio');
            });
            $('#modalVerEvidenciaDictamen').on('hidden.bs.modal', function() {
                $('#modalVerEvidenciaDictamenImg').attr('src', '');
            });
        })();

        function rellenarEvidenciasDictamen(idTicket) {
            if (!idTicket || typeof http === 'undefined') return;
            evidenciasEliminadas = [];
            $('#rastreoDictamenEvidenciasFotos, #rastreoDictamenAmpliadaEvidenciasFotos').empty();
            http.request({
                endpoint: '/sabueso/getEvidenciasTicket',
                metodo: 'POST',
                data: JSON.stringify({ id_ticket: idTicket, tipo_origen: 'dictamen_sabueso' }),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                onSuccess: function(r) {
                    var list = (r.success && r.datos) ? r.datos : [];
                    list.forEach(function(e) {
                        var url = (apiBase || '') + (e.url || ('/sabueso/verEvidencia?id=' + (e.id || '')));
                        var idEv = e.id;
                        function crearItem() {
                            var $it = $('<div class="evidencia-foto-item evidencia-loading" title="Clic para ver en grande"><span class="evidencia-foto-cargando">Cargando…</span><span class="evidencia-foto-ver"><i class="fa-solid fa-magnifying-glass-plus"></i></span><img src="" alt="" decoding="async"><button type="button" class="evidencia-foto-quitar" aria-label="Quitar"><i class="fa-solid fa-times"></i></button></div>');
                            $it.find('img').attr('src', url);
                            $it.find('img').on('load', function() { $it.removeClass('evidencia-loading'); });
                            $it.find('img').on('error', function() { $it.removeClass('evidencia-loading'); });
                            if (idEv) $it.data('id-evidencia', idEv);
                            $it.on('click', function(ev) {
                                if ($(ev.target).closest('.evidencia-foto-quitar').length) return;
                                var s = $it.find('img').attr('src');
                                if (s) { $('#modalVerEvidenciaDictamenImg').attr('src', s); $('#modalVerEvidenciaDictamen').modal('show'); }
                            });
                            $it.find('.evidencia-foto-quitar').on('click', function(ev) {
                                ev.stopPropagation();
                                var idE = $it.data('id-evidencia');
                                if (idE) evidenciasEliminadas.push(idE);
                                $it.remove();
                                $(document).trigger('dictamen-evidencia-cambio');
                            });
                            return $it;
                        }
                        $('#rastreoDictamenEvidenciasFotos').append(crearItem());
                        $('#rastreoDictamenAmpliadaEvidenciasFotos').append(crearItem());
                    });
                }
            });
        }

        function guardarDictamenBorradorUI(silent, onCompletado) {
            if ($('#modalDictamenAmpliada').hasClass('show')) {
                sincronizarDomiciliosAmpliadaAPanel();
                $('#rastreoDictamenCombo').val($('#rastreoDictamenAmpliadaCombo').val());
                $('#rastreoDictamenDescripcion').val($('#rastreoDictamenAmpliadaDescripcion').val());
            }
            var idTicket = parseInt($('#rastreoIdTicketActual').val() || $('#rastreoIdTicketActual').attr('data-id-ticket') || $('#modalRastreoCredito').attr('data-id-ticket') || '', 10) || (typeof window.ticketIdRastreoActual !== 'undefined' ? window.ticketIdRastreoActual : null);
            if (!idTicket && typeof ticketIdRastreoActual !== 'undefined') idTicket = ticketIdRastreoActual;
            if (!idTicket || isNaN(idTicket)) {
                if (!silent && typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'No hay ticket', text: 'No se identificó el ticket. Cierre el modal de rastreo y ábralo de nuevo desde la tabla.' });
                if (onCompletado) onCompletado('No se identificó el ticket.');
                return;
            }
            if (typeof http === 'undefined') {
                if (onCompletado) onCompletado('Error de configuración.');
                return;
            }
            var tipo = $('#rastreoDictamenCombo').val();
            var esIloc = typeof window.esTipoDictamenIlocalizable === 'function' && window.esTipoDictamenIlocalizable(tipo);
            var descBase = ($('#rastreoDictamenDescripcion').val() || '').trim();
            var domiciliosParts = [];
            $('#rastreoDictamenDomiciliosWrap .rastreo-domicilio-block').each(function() {
                var descD = ($(this).find('.rastreo-domicilio-desc').val() || '').trim();
                var linkD = ($(this).find('.rastreo-domicilio-link').val() || '').trim();
                if (descD || linkD) domiciliosParts.push(descD + (linkD ? ' ' + linkD : ''));
            });
            var desc = '';
            if (!esIloc) {
                desc = descBase;
                if (domiciliosParts.length > 0) desc += (descBase ? '. ' : '') + 'Podrás encontrar al usuario en ' + domiciliosParts.join('; ');
            }
            if (!tipo || (!esIloc && !desc)) {
                if (!silent && typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Faltan datos', text: esIloc ? 'Seleccione el tipo de dictamen.' : 'Seleccione tipo y complete la descripción o al menos un domicilio.' });
                if (onCompletado) onCompletado('Faltan tipo o descripción.');
                return;
            }
            var archivosPendientes = [];
            var vistos = {};
            if (!esIloc) {
            $('#rastreoDictamenEvidenciasFotos .evidencia-foto-item').each(function() { var f = $(this).data('file'); if (f) { var k = f.name + '_' + f.size; if (!vistos[k]) { vistos[k] = true; archivosPendientes.push(f); } } });
            $('#rastreoDictamenAmpliadaEvidenciasFotos .evidencia-foto-item').each(function() { var f = $(this).data('file'); if (f) { var k = f.name + '_' + f.size; if (!vistos[k]) { vistos[k] = true; archivosPendientes.push(f); } } });
            }
            var eliminadasPendientes = evidenciasEliminadas.slice();
            evidenciasEliminadas = [];
            function eliminarSiguiente(idx) {
                if (idx >= eliminadasPendientes.length) {
                    subirSiguiente(0);
                    return;
                }
                http.request({
                    endpoint: '/sabueso/eliminarEvidenciaTicket',
                    metodo: 'POST',
                    data: JSON.stringify({ id_evidencia: eliminadasPendientes[idx], id_ticket: idTicket }),
                    contentType: 'application/json',
                    processData: false,
                    showLoader: false,
                    onSuccess: function() { eliminarSiguiente(idx + 1); },
                    onError: function() { if (onCompletado) onCompletado('No se pudo eliminar una evidencia.'); else eliminarSiguiente(idx + 1); }
                });
            }
            function subirSiguiente(indice, intentos) {
                if (indice >= archivosPendientes.length) {
                    enviarBorrador();
                    return;
                }
                intentos = intentos || 0;
                var f = archivosPendientes[indice];
                var fd = new FormData();
                fd.append('id_ticket', idTicket);
                fd.append('evidencia', f);
                $.ajax({
                    url: urlSubirEvidencia,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(r) {
                        if (r.success) {
                            $('.evidencia-foto-item').each(function() {
                                var fl = $(this).data('file');
                                if (fl && fl.name === f.name && fl.size === f.size) {
                                    $(this).removeData('file');
                                    if (r.datos && r.datos.id) $(this).data('id-evidencia', r.datos.id);
                                }
                            });
                            subirSiguiente(indice + 1, 0);
                        } else if (intentos < 1) {
                            // Reintento automático (1 vez) ante error del servidor
                            setTimeout(function() { subirSiguiente(indice, intentos + 1); }, 600);
                        } else {
                            if (!silent && typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error al subir evidencia', text: (r && r.mensaje) || 'No se pudo subir la imagen.' });
                            if (onCompletado) onCompletado((r && r.mensaje) || 'No se pudo subir la imagen.');
                        }
                    },
                    error: function(xhr) {
                        if (intentos < 1) {
                            // Reintento automático (1 vez) ante error de red/timeout
                            setTimeout(function() { subirSiguiente(indice, intentos + 1); }, 600);
                            return;
                        }
                        var msg = 'No se pudo subir la evidencia.';
                        try {
                            var j = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                            if (j && j.mensaje) msg = j.mensaje;
                        } catch (e) {}
                        if (!silent && typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error al subir evidencia', text: msg });
                        if (onCompletado) onCompletado(msg);
                    }
                });
            }
            function enviarBorrador() {
            http.request({
                endpoint: '/sabueso/guardarDictamenBorrador',
                metodo: 'POST',
                data: JSON.stringify({ id_ticket: idTicket, tipo: tipo, descripcion: desc }),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                onSuccess: function(r) {
                    if (r.success) {
                        if (!silent && typeof Swal !== 'undefined') {
                            var esActualizacion = r.mensaje && (r.mensaje + '').toLowerCase().indexOf('actualizado') !== -1;
                            Swal.fire({
                                icon: 'success',
                                title: esActualizacion ? 'Dictamen actualizado' : 'Guardado',
                                text: esActualizacion ? 'La información del dictamen se actualizó correctamente.' : 'El borrador se guardó correctamente.',
                                timer: 2500,
                                showConfirmButton: true
                            }).then(function() {
                                cargarDictamenRastreo();
                                if (typeof getTicketsPanelAdmin === 'function') getTicketsPanelAdmin();
                                if (typeof rellenarEvidenciasDictamen === 'function') rellenarEvidenciasDictamen(idTicket);
                            });
                        } else {
                            // Autoguardado silencioso: no recargar ni refrescar para no interrumpir la escritura ni mostrar "Procesando su petición"
                        }
                        if (onCompletado) onCompletado(null);
                    } else { var msg = r.mensaje || 'No se pudo guardar.'; if (r.error) msg += ' Detalle: ' + r.error; if (!silent && typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: msg }); if (onCompletado) onCompletado(msg); }
                },
                onError: function(e) { if (!silent && typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo guardar.' }); if (onCompletado) onCompletado((e && e.mensaje) || 'No se pudo guardar.'); }
            });
            }
            eliminarSiguiente(0);
        }
        function enviarDictamenGestorUI() {
            // 1️⃣ Resolver idTicket antes de cualquier otra acción
            var idTicket = parseInt($('#rastreoIdTicketActual').val() || $('#rastreoIdTicketActual').attr('data-id-ticket') || $('#modalRastreoCredito').attr('data-id-ticket') || '', 10) || (typeof window.ticketIdRastreoActual !== 'undefined' ? window.ticketIdRastreoActual : null);
            if (!idTicket && typeof ticketIdRastreoActual !== 'undefined') idTicket = ticketIdRastreoActual;
            if (!idTicket || isNaN(idTicket)) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'No hay ticket', text: 'No se identificó el ticket. Cierre el modal de rastreo y ábralo de nuevo desde la tabla.' });
                return;
            }
            if (typeof http === 'undefined') return;
            sincronizarDomiciliosAmpliadaAPanel();
            $('#rastreoDictamenCombo').val($('#rastreoDictamenAmpliadaCombo').val());
            $('#rastreoDictamenDescripcion').val($('#rastreoDictamenAmpliadaDescripcion').val());
            var tipo = $('#rastreoDictamenCombo').val();
            var esIlocEnviar = typeof window.esTipoDictamenIlocalizable === 'function' && window.esTipoDictamenIlocalizable(tipo);
            var descBase = ($('#rastreoDictamenDescripcion').val() || '').trim();
            var domiciliosParts = [];
            $('#rastreoDictamenDomiciliosWrap .rastreo-domicilio-block').each(function() {
                var descD = ($(this).find('.rastreo-domicilio-desc').val() || '').trim();
                var linkD = ($(this).find('.rastreo-domicilio-link').val() || '').trim();
                if (descD || linkD) domiciliosParts.push(descD + (linkD ? ' ' + linkD : ''));
            });
            var desc = '';
            if (!esIlocEnviar) {
                desc = descBase;
                if (domiciliosParts.length > 0) desc += (descBase ? '. ' : '') + 'Podrás encontrar al usuario en ' + domiciliosParts.join('; ');
            }
            if (!tipo || (!esIlocEnviar && !desc)) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Faltan datos', text: esIlocEnviar ? 'Seleccione el tipo de dictamen.' : 'Seleccione tipo y complete la descripción o al menos un domicilio.' }); return; }

            // 2️⃣ Cancelar el autoguardado pendiente para evitar dos guardados simultáneos
            if (dictamenAutoguardadoTimer) { clearTimeout(dictamenAutoguardadoTimer); dictamenAutoguardadoTimer = null; }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Se está enviando el dictamen',
                    text: 'Por favor espere. Se guardarán las fotos y la información antes de enviar al gestor.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function() { if (Swal.showLoading) Swal.showLoading(); }
                });
            }
            // 3️⃣ Flush completo (fotos pendientes + borrador), con reintentos ante fallos de subida
            guardarDictamenBorradorUI(true, function(err) {
                if (err) {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                        Swal.fire({ icon: 'error', title: 'Error al guardar', text: err });
                    }
                    return;
                }
                http.request({
                    endpoint: '/sabueso/enviarDictamenGestor',
                    metodo: 'POST',
                    data: JSON.stringify({ id_ticket: idTicket }),
                    contentType: 'application/json',
                    processData: false,
                    showLoader: false,
                    onSuccess: function(r) {
                        if (typeof Swal !== 'undefined') Swal.close();
                        if (r.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'success', title: 'Dictamen enviado', text: 'El dictamen fue enviado al gestor correctamente.', timer: 2500, showConfirmButton: true }).then(function() {
                                    $('#modalDictamenAmpliada').modal('hide');
                                    if (typeof getTicketsPanelAdmin === 'function') getTicketsPanelAdmin();
                                });
                            } else {
                                $('#modalDictamenAmpliada').modal('hide');
                                if (typeof getTicketsPanelAdmin === 'function') getTicketsPanelAdmin();
                            }
                        } else { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || 'No se pudo enviar.' }); }
                    },
                    onError: function(e) {
                        if (typeof Swal !== 'undefined') { Swal.close(); Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo enviar.' }); }
                    }
                });
            });
        }
        var dictamenAutoguardadoTimer = null;
        function programarAutoguardadoDictamen() {
            if (dictamenAutoguardadoTimer) clearTimeout(dictamenAutoguardadoTimer);
            dictamenAutoguardadoTimer = setTimeout(function() { dictamenAutoguardadoTimer = null; guardarDictamenBorradorUI(true); }, 800);
        }
        $('#rastreoDictamenCombo, #rastreoDictamenAmpliadaCombo').on('change', function() {
            if ($(this).attr('id') === 'rastreoDictamenAmpliadaCombo') $('#rastreoDictamenCombo').val($(this).val());
            else $('#rastreoDictamenAmpliadaCombo').val($(this).val());
            var nv = ($('#rastreoDictamenCombo').val() || '').toString();
            if (typeof window.esTipoDictamenIlocalizable === 'function' && window.esTipoDictamenIlocalizable(nv)) {
                $('#rastreoDictamenDescripcion, #rastreoDictamenAmpliadaDescripcion').val('');
                $('#rastreoDictamenDomiciliosWrap, #rastreoDictamenAmpliadaDomiciliosWrap').empty();
            }
            if (typeof window.actualizarDictamenCamposPorTipo === 'function') window.actualizarDictamenCamposPorTipo();
            programarAutoguardadoDictamen();
        });
        $('#rastreoDictamenDescripcion, #rastreoDictamenAmpliadaDescripcion').on('input', function() {
            if ($(this).attr('id') === 'rastreoDictamenAmpliadaDescripcion') $('#rastreoDictamenDescripcion').val($(this).val());
            else $('#rastreoDictamenAmpliadaDescripcion').val($(this).val());
            programarAutoguardadoDictamen();
            habilitarBotonEnviarDictamen();
        });
        $(document).on('dictamen-evidencia-cambio', programarAutoguardadoDictamen);
        function habilitarBotonEnviarDictamen() {
            if ($('.rastreo-seccion-dictamen').hasClass('dictamen-solo-lectura')) return;
            $('#btnDictamenEnviarGestor').prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i>Enviar al gestor');
            $('#btnDictamenAmpliadaEnviarGestor').prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i>Enviar al gestor');
        }
        $(document).on('dictamen-evidencia-cambio', habilitarBotonEnviarDictamen);

        $('#btnDictamenEnviarGestor').on('click', function() { enviarDictamenGestorUI(); });
        $('#btnDictamenAmpliadaEnviarGestor').on('click', function() {
            sincronizarDomiciliosAmpliadaAPanel();
            $('#rastreoDictamenCombo').val($('#rastreoDictamenAmpliadaCombo').val());
            $('#rastreoDictamenDescripcion').val($('#rastreoDictamenAmpliadaDescripcion').val());
            enviarDictamenGestorUI();
        });
        $('#tablaTicketsPanel').on('draw.dt', function() {
            var tabla;
            try { tabla = $(this).DataTable(); } catch(ex) { return; }
            tabla.rows().every(function() {
                var d = this.data();
                var node = this.node();
                if (!d || !node) return;
                if (d._dictamen_estado === 'enviado_al_gestor') {
                    $(node).addClass('fila-dictamen-enviado').attr('data-id-ticket', d._id_ticket || '');
                }
            });
            $('#tablaTicketsPanel [data-bs-toggle="tooltip"]').tooltip();
        });
        $(document).on('click', '#tablaTicketsPanel .btn-dictamen-ojito, #tablaTicketsPanel .fa-eye, #tablaTicketsPanel .fa-eye-slash, #tablaTicketsPanel .dictamen-countdown', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).closest('[data-id-ticket]').attr('data-id-ticket') || $(this).closest('tr').attr('data-id-ticket');
            if (id && window.abrirModalDetalleDictamen) window.abrirModalDetalleDictamen(parseInt(id, 10));
        });
        window.abrirModalDetalleDictamen = function(idTicket) {
            if (!idTicket || typeof http === 'undefined') return;
            $('#modalDetalleDictamen .dictamen-detalle-imagen-principal').html('<img id="modalDetalleDictamenImgPrincipal" src="" alt="Evidencia" class="img-fluid w-100" style="object-fit: contain; max-height: 280px;">');
            $('#modalDetalleDictamenMiniaturas').empty();
            $('#modalDetalleDictamenTipo, #modalDetalleDictamenDescripcion, #modalDetalleDictamenEnviado, #modalDetalleDictamenVisto').text('');
            $('#modalDetalleDictamenDomiciliosWrap').hide();
            $('#modalDetalleDictamenDomicilios').empty();
            $('#modalDetalleDictamen').modal('show');
            http.request({
                endpoint: '/sabueso/getDictamenDetalle',
                metodo: 'POST',
                data: JSON.stringify({ id_ticket: idTicket }),
                contentType: 'application/json',
                processData: false,
                onSuccess: function(r) {
                    if (!r.success || !r.datos) {
                        $('#modalDetalleDictamenTipo').text(r.mensaje || 'No se pudo cargar.');
                        return;
                    }
                    var d = r.datos;
                    var dm = d.dictamen || {};
                    $('#modalDetalleDictamenTipo').text(typeof window.etiquetaTipoDictamenSabueso === 'function' ? window.etiquetaTipoDictamenSabueso(dm.tipo) : (dm.tipo || '—'));
                    var descRaw = (dm.descripcion_base !== undefined ? dm.descripcion_base : dm.descripcion) || '';
                    var descMostrar = (descRaw + '').trim() ? descRaw : ((typeof window.esTipoDictamenIlocalizable === 'function' && window.esTipoDictamenIlocalizable(dm.tipo)) ? '— (sin comentarios; dictamen ILOCALIZABLE)' : '—');
                    $('#modalDetalleDictamenDescripcion').html(window.linkifyDescripcionDictamen ? window.linkifyDescripcionDictamen(descMostrar) : $('<div>').text(descMostrar).html());
                    var domicilios = d.domicilios || [];
                    if (domicilios.length > 0) {
                        var $domWrap = $('#modalDetalleDictamenDomicilios');
                        $domWrap.empty();
                        domicilios.forEach(function(dom) {
                            var desc = (dom.desc || '').trim();
                            var link = (dom.link || '').trim();
                            var $card = $('<div class="d-flex align-items-center gap-2 p-2 rounded border bg-light bg-opacity-50"></div>');
                            if (desc) $card.append($('<span class="flex-grow-1 text-break"></span>').text(desc));
                            if (link) {
                                var safe = link.replace(/"/g, '&quot;');
                                $card.append($('<a href="' + safe + '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary text-nowrap"><i class="fa-brands fa-google me-1"></i>Ver en Maps</a>'));
                            }
                            $domWrap.append($card);
                        });
                        $('#modalDetalleDictamenDomiciliosWrap').show();
                    } else {
                        $('#modalDetalleDictamenDomiciliosWrap').hide();
                    }
                    $('#modalDetalleDictamenEnviado').text(dm.fecha_actualizacion ? (new Date(dm.fecha_actualizacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })) : '—');
                    var fechaEnvio = dm.fecha_actualizacion ? new Date(dm.fecha_actualizacion).getTime() : 0;
                    var pasaron12h = fechaEnvio > 0 && (Date.now() - fechaEnvio) > (12 * 60 * 60 * 1000);
                    var nota12h = $('#modalDetalleDictamenNota12h span');
                    if (nota12h.length) nota12h.text(pasaron12h ? 'Ya transcurrieron sus 12 horas para visitar al cliente' : 'Vas a tener 12 horas para visitar al cliente');
                    var vistoStr = dm.fecha_visto_gestor ? (new Date(dm.fecha_visto_gestor).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })) : '';
                    if (vistoStr && (dm.visto_gestor_nombre || '').trim()) vistoStr = 'Por ' + (dm.visto_gestor_nombre || '').trim() + ' el ' + vistoStr;
                    $('#modalDetalleDictamenVisto').text(vistoStr || 'No visto');

                    // IMPORTANTE: d.evidencias contiene SOLO las evidencias del ticket específico (idTicket)
                    // El backend filtra por WHERE id_ticket = :id_ticket, así que no hay riesgo de mezclar evidencias de otros tickets
                    var evidencias = d.evidencias || [];
                    var $containerImg = $('#modalDetalleDictamen .dictamen-detalle-imagen-principal');
                    var $min = $('#modalDetalleDictamenMiniaturas');

                    // Limpiar contenido previo
                    $min.empty();

                    // Si no hay evidencias, mostrar mensaje y salir
                    if (!evidencias || evidencias.length === 0) {
                        $containerImg.html('<div class="d-flex align-items-center justify-content-center h-100 text-muted" style="min-height:200px;"><i class="fa-solid fa-image me-2"></i>Sin evidencias</div>');
                        return;
                    }

                    // Usar apiBase para subruta (ej. /sparta___SPARTA_SECRET_REDACTED__/public/)
                    var url0 = (apiBase || '') + (evidencias[0].url || ('/sabueso/verEvidencia?id=' + (evidencias[0].id || '')));

                    // Restaurar img en el contenedor por si antes se mostró "Sin evidencias"
                    $containerImg.html('<img id="modalDetalleDictamenImgPrincipal" src="" alt="Evidencia" class="img-fluid w-100" style="object-fit: contain; max-height: 280px; cursor: pointer;">');
                    var $imgPrincipal = $('#modalDetalleDictamenImgPrincipal');
                    $imgPrincipal.attr('src', url0).on('click', function() {
                        if (url0 && $('#modalVerEvidenciaDictamen').length) {
                            $('#modalVerEvidenciaDictamenImg').attr('src', url0);
                            $('#modalVerEvidenciaDictamen').modal('show');
                        }
                    });

                    // Crear miniaturas (solo desde índice 1 para no repetir la imagen principal)
                    var idsVistos = {};
                    evidencias.forEach(function(ev, idx) {
                        if (idx === 0) return;
                        var idEv = ev.id || (ev.url || '') + idx;
                        if (idsVistos[idEv]) return;
                        idsVistos[idEv] = true;
                        var url = (apiBase || '') + (ev.url || ('/sabueso/verEvidencia?id=' + (ev.id || '')));
                        if (!url) return;

                        var $thumb = $('<div class="rounded overflow-hidden border" style="width: 60px; height: 60px; cursor: pointer;"><img src="' + url.replace(/"/g, '&quot;') + '" alt="" class="img-fluid w-100 h-100" style="object-fit: cover;"></div>');
                        $thumb.on('click', function() {
                            $imgPrincipal.attr('src', url);
                            if ($('#modalVerEvidenciaDictamen').length) {
                                $('#modalVerEvidenciaDictamenImg').attr('src', url);
                                $('#modalVerEvidenciaDictamen').modal('show');
                            }
                        });
                        $min.append($thumb);
                    });
                    http.request({ endpoint: '/sabueso/marcarDictamenVisto', metodo: 'POST', data: JSON.stringify({ id_ticket: idTicket }), contentType: 'application/json', processData: false });
                    getTicketsPanelAdmin();
                },
                onError: function() { $('#modalDetalleDictamenTipo').text('Error al cargar.'); }
            });
        };

        $('#modalPrediccionIA').on('shown.bs.modal', function() {
            var isDark = document.body && document.body.classList.contains('dark-mode');
            if (isDark) {
                $('#modalPrediccionIABody').addClass('analitica-ia-resumen-dark');
            } else {
                $('#modalPrediccionIABody').removeClass('analitica-ia-resumen-dark');
            }
        }).on('hidden.bs.modal', function() {
            $('#modalPrediccionIABody').removeClass('analitica-ia-resumen-dark');
        });
        $('#btnResumenAnaliticaIA').on('click', function() {
            var id = typeof idCreditoRastreoActual !== 'undefined' ? idCreditoRastreoActual : null;
            if (!id) return;
            var $body = $('#modalPrediccionIABody');
            var $label = $('#modalPrediccionIALabel');
            $label.html('<i class="fa-solid fa-chart-line me-2"></i>Resumen');
            $body.html('<p class="text-muted mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Cargando resumen...</p>');
            $('#modalPrediccionIA').addClass('modal-analitica-ia').modal('show');
            http.request({
                endpoint: '/sabueso/resumenAnaliticaHTML?id_credito=' + id,
                metodo: 'GET',
                timeout: 30000,
                showLoader: false,
                onSuccess: function(r) {
                    if (!r.success) {
                        $body.html('<p class="text-danger mb-0">' + ((r.mensaje || 'Error') + '').replace(/</g, '&lt;') + '</p>');
                        return;
                    }
                    $body.html(r.html || '');
                    var isDark = document.body && document.body.classList.contains('dark-mode');
                    if (isDark) { $body.addClass('analitica-ia-resumen-dark'); }
                },
                onError: function(e) {
                    var msg = (typeof e === 'string' ? e : (e && e.mensaje)) || 'No se pudo cargar el resumen.';
                    $body.html('<p class="text-danger mb-0">' + String(msg).replace(/</g, '&lt;') + '</p>');
                }
            });
        });
    });
})();
</script>
