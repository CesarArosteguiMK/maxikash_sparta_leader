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
    /* FONDO GRIS: contenedor padre = gris azulado (Slate 100). Tarjetas blancas resaltan encima. */
    #modalRastreoCredito .modal-content { height: 100%; display: flex; flex-direction: column; border-radius: 16px; overflow: visible; background-color: #F1F5F9 !important; border: none !important; }
    #modalRastreoCredito .modal-body { flex: 1; overflow-x: hidden; overflow-y: auto; -webkit-overflow-scrolling: touch; background-color: #F1F5F9 !important; padding: 1rem; -webkit-font-smoothing: antialiased; display: flex; flex-direction: column; min-height: 0; }
    #modalRastreoCredito .modal-body .small { line-height: 1.5; letter-spacing: 0.01em; color: #374151; }
    /* Encabezado y pie BLANCOS para limpieza visual */
    #modalRastreoCredito .modal-header { background-color: #FFFFFF !important; border-bottom: 1px solid #E5E7EB !important; }
    #modalRastreoCredito .modal-footer { background-color: #FFFFFF !important; border-top: 1px solid #E5E7EB !important; }
    /* Encabezado en dos columnas: izquierda = datos cliente + ticket; derecha = quién levantó / cuando / asignado */
    #modalRastreoCredito .rastreo-header-grid { display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: start; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(0,0,0,0.06); }
    #modalRastreoCredito .rastreo-header-left { display: flex; flex-direction: column; gap: 0.35rem; min-width: 0; }
    #modalRastreoCredito .rastreo-header-left .rastreo-datos-row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0 1rem; }
    /* Ticket levantado: estilo alerta informativa */
    #modalRastreoCredito .rastreo-tickets-cell { margin-top: 0.35rem; background: #eff6ff; border-radius: 10px; padding: 0.75rem 1rem; border: none; border-left: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(59, 130, 246, 0.08); }
    #modalRastreoCredito .rastreo-tickets-cell .fw-semibold.small.text-muted { color: #1e40af !important; font-weight: 600; }
    #modalRastreoCredito .rastreo-tickets-cell .credito-modal-list { color: #1e3a8a; }
    #modalRastreoCredito .rastreo-header-right { display: flex; flex-direction: column; gap: 0.5rem; padding-left: 1rem; border-left: 1px solid rgba(0,0,0,0.08); min-width: 180px; }
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
    #modalRastreoCredito .bitacora-btn-delete { flex-shrink: 0; opacity: 1; }
    #modalRastreoCredito .bitacora-msg-mine .bitacora-btn-delete { color: rgba(255,255,255,0.95) !important; }
    /* Scrim entre modales: padre abajo, scrim en medio, hijo encima */
    #modalRastreoCredito.modal-below-scrim { z-index: 1045 !important; }
    .modal-scrim { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1060; pointer-events: auto; }
    .modal.modal-nested-open { z-index: 1070 !important; }
    /* Input chat: cápsula redondeada */
    #modalRastreoCredito #rastreoBitacoraInput { border-radius: 24px; border: 1px solid #e2e8f0; padding: 0.5rem 1rem; font-size: 0.9rem; }
    #modalRastreoCredito #rastreoBitacoraInput:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }
    #modalRastreoCredito #rastreoBitacoraEnviar { border-radius: 50%; width: 38px; height: 38px; min-width: 38px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    #modalRastreoCredito .rastreo-bitacora-input-wrap,
    #modalRastreoCredito .rastreo-dictamen-input-wrap { padding: 0.25rem 0; margin-top: auto; flex-shrink: 0; }
    #modalRastreoCredito #rastreoDictamenInput { border-radius: 24px; border: 1px solid #e2e8f0; padding: 0.5rem 1rem; font-size: 0.9rem; }
    #modalRastreoCredito #rastreoDictamenInput:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }
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
    #tablaTicketsPanel th:nth-child(6) { min-width: 10rem; white-space: nowrap; }
    #tablaTicketsPanel .d-flex.flex-wrap.gap-1 { flex-wrap: wrap; gap: 0.35rem !important; }
    #tablaTicketsPanel .d-flex.flex-wrap.gap-1 .btn { flex-shrink: 0; }
    @media (max-width: 768px) {
        #tablaTicketsPanel .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    }
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
    #modalEvidenciaRastreo .evidencia-comentario-texto {
        word-break: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
        white-space: pre-wrap;
    }
    #modalEvidenciaRastreo #evidenciaComentarioModal {
        color: #ffffff !important;
        background-color: #4a5568 !important;
        border-color: #718096 !important;
        resize: none;
        max-height: 120px;
    }
    #modalEvidenciaRastreo #evidenciaComentarioModal::placeholder {
        color: #a0aec0 !important;
    }
    #modalEvidenciaRastreo .evidencia-comentario-counter {
        font-size: 0.75rem;
        color: #94a3b8 !important;
        margin-top: 0.25rem;
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
        #modalRastreoCredito #rastreoDictamenEnviar,
        #modalRastreoCredito .rastreo-bitacora-input-wrap .btn,
        #modalRastreoCredito .rastreo-dictamen-input-wrap .btn { min-width: 44px; min-height: 44px; width: 44px; height: 44px; }
        #modalRastreoCredito #rastreoBitacoraInput,
        #modalRastreoCredito #rastreoDictamenInput { min-height: 44px; padding: 0.5rem 1rem; }
        #modalRastreoCredito .btn-analizar-ia,
        #modalRastreoCredito #btnResumirUbicacionesIA,
        #modalRastreoCredito .modal-footer .btn { min-height: 44px; padding: 0.5rem 1rem; }
        #modalRastreoCredito .evidencia-slot { min-height: 80px; }
        #modalRastreoCredito .gestion-row .gestion-label { min-width: 4rem; }
        .card-header, .card { padding-left: 0.5rem; padding-right: 0.5rem; }
    }
</style>
<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">
            <i class="fa-solid fa-list me-2"></i>Panel Admin – Todos los tickets
        </h5>
    </div>
    <div class="card-datatable table-responsive">
        <table id="tablaTicketsPanel" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>Folio / Tipo</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Crédito</th>
                    <th>Fechas</th>
                    <th>Quién levantó</th>
                    <th>Asignado a</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Iniciar rastreo: datos de la persona/crédito del ticket (casi pantalla completa) -->
<div class="modal fade" id="modalRastreoCredito" tabindex="-1" aria-labelledby="modalRastreoCreditoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm">
            <div class="modal-header py-2 border-bottom d-flex align-items-center">
                <h6 class="modal-title text-primary mb-0" id="modalRastreoCreditoLabel">
                    <i class="fa-solid fa-magnifying-glass-plus me-2"></i>Iniciar rastreo – Datos del crédito
                </h6>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
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
                            <div class="d-flex align-items-center gap-2 mb-2 rastreo-bitacora-titulo">
                                <i class="fa-solid fa-comments text-primary"></i>
                                <span class="fw-semibold small text-muted">Bitácora</span>
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
                            <div class="d-flex align-items-center gap-2 mb-2 rastreo-bitacora-titulo">
                                <i class="fa-solid fa-file-lines text-primary"></i>
                                <span class="fw-semibold small text-muted">Dictamen</span>
                            </div>
                            <div id="rastreoDictamenContenido" class="small flex-grow-1 overflow-auto mb-2" style="min-height: 72px;">
                                <!-- Mensajes dictamen por JS -->
                            </div>
                            <div class="d-flex gap-2 align-items-center rastreo-dictamen-input-wrap">
                                <input type="text" class="form-control form-control-sm" id="rastreoDictamenInput" placeholder="Escribir dictamen..." maxlength="500">
                                <button type="button" class="btn btn-sm btn-primary flex-shrink-0" id="rastreoDictamenEnviar" title="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
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
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAsignarRastreo" onclick="mostrarAsignarOpciones()" title="Asignar este ticket">
                    <i class="fa-solid fa-user-plus me-1"></i>Asignar...
                </button>
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
<script>
(function() {
    var SCRIM_Z = 1060;
    var CHILD_MODALS = ['modalAsignarA', 'modalAnaliticaSpatial', 'modalAnaliticaPayments', 'modalAnaliticaCompliance', 'modalLecturaIA', 'modalEvidenciaVerificacion', 'modalPrediccionIA', 'modalMapaGrande', 'modalMapaAlternasGrande', 'modalEvidenciaRastreo'];
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
        }
    }
    function onParentModalHidden() {
        cleanupScrim();
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
        $('#modalRastreoCredito').on('shown.bs.modal', function() {
            var id = typeof idCreditoRastreoActual !== 'undefined' ? idCreditoRastreoActual : null;
            var btn = $('#btnResumenAnaliticaIA');
            if (id) { btn.show(); } else { btn.hide(); }
        }).on('hidden.bs.modal', function() { $('#btnResumenAnaliticaIA').hide(); });

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
