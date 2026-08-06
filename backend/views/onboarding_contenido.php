<?php
/**
 * Onboarding — Vista principal
 * Basada en: https://demos.themeselection.com/sneat-bootstrap-html-admin-template/html/vertical-menu-template/app-academy-course-details.html
 */
$onboardingProgressUser = (string) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 'guest');
$onboardingQuizSession = session_id() ?: $onboardingProgressUser;
$onboardingSpecializedQuestions = [
    ['¿Cuál es el primer paso obligatorio al presentarse en el domicilio del cliente?', ['Ingresar de forma autónoma a la propiedad privada sin aguardar respuesta.', 'Exigir el pago total inmediato con voz fuerte para ejercer presión.', 'Identificarse con credencial corporativa vigente y explicar el motivo de la visita de forma educada.']],
    ['Si el deudor legítimo no se encuentra, pero un familiar directo atiende la puerta, ¿cómo procede?', ['Dejar un citatorio confidencial cerrado dirigido al titular, sin revelar montos ni datos sensibles a terceros.', 'Detallar el estado de cuenta y las consecuencias legales al familiar para que lo presione.', 'Retirarse del lugar de inmediato sin dejar ningún tipo de notificación física.']],
    ['¿Qué acción debe tomar si el cliente realiza un abono en efectivo en campo?', ['Emitir un recibo foliado oficial al instante y subir la evidencia a la plataforma de inmediato.', 'Aceptar transferencias bancarias directas a la cuenta de ahorros personal del gestor.', 'Guardar el dinero en la billetera y reportarlo hasta el cierre de operaciones del fin de semana.']],
    ['Ante una actitud hostil o agresión verbal física inminente en el domicilio, ¿cuál es el protocolo?', ['Discutir firmemente con el cliente hasta lograr que firme una promesa de pago.', 'Solicitar el auxilio inmediato de otros gestores de campo de zonas aledañas para confrontación.', 'Retirarse inmediatamente resguardando su integridad física y levantar un reporte de incidencia en el sistema.']],
    ['¿Cuál es la herramienta tecnológica obligatoria para trazar la ruta de visitas diarias?', ['Elegir las colonias de forma aleatoria basándose en la experiencia personal diaria.', 'El sistema de geolocalización y mapas integrado en el dispositivo corporativo.', 'Utilizar mapas impresos obsoletos ajenos al control de la plataforma central.']],
    ['Si el domicilio asignado en el sistema resulta ser un terreno baldío o dirección inexistente, usted debe:', ['Registrar la verificación domiciliaria fallida adjuntando evidencia fotográfica de las nomenclaturas.', 'Omitir la cuenta y continuar con la siguiente visita sin dejar registro del error.', 'Inventar datos de contacto de vecinos simulando haber realizado la validación física.']],
    ['¿Qué norma legal prohíbe revelar los datos de deudas a los vecinos del cliente?', ['El Código de Comercio en su sección de correspondencias estándar.', 'La Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP).', 'La Ley General de Instituciones de Crédito Bancario General.']],
    ['¿Cuál es el objetivo principal del Gestor Domiciliario en Maxikash?', ['Embargar bienes de manera autónoma durante la primera visita técnica.', 'Clausurar locales comerciales de los acreditados con firmas simuladas.', 'Recuperar la cartera vencida mediante la conciliación, ofreciendo planes aprobados por el sistema.']],
    ['¿Qué se entiende por un \'Convenio de Pago\' válido en campo?', ['Un acuerdo verbal informal donde el deudor promete pagar en una fecha imprecisa.', 'Aquel estructurado por el sistema central, firmado por el cliente y cargado en la aplicación.', 'Un documento genérico en blanco firmado únicamente por el deudor externo.']],
    ['¿En qué horarios está autorizado realizar visitas de cobranza según las normativas vigentes?', ['Únicamente en los horarios permitidos por la regulación legal aplicable (7:00 AM a 10:00 PM).', 'A cualquier hora de la madrugada siempre y cuando se localice al titular en casa.', 'Exclusivamente en días festivos y fines de semana después de la medianoche.']],
];
?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="/assets/vendor/css/pages/app-academy-details.css" />
<link rel="stylesheet" href="/assets/vendor/css/pages/app-academy.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

<style>
/* ── Onboarding overrides & extras ──────────────────────────── */
.course-video-wrapper {
    position: relative;
    background: #000;
    border-radius: .5rem;
    overflow: hidden;
    aspect-ratio: 16 / 9;
}
.course-video-wrapper video,
.course-video-wrapper iframe {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
}
.course-video-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,.45);
    cursor: pointer;
    transition: background .2s;
}
.course-video-overlay:hover { background: rgba(0,0,0,.3); }
.course-video-overlay .play-btn {
    width: 64px; height: 64px;
    background: rgba(255,255,255,.15);
    backdrop-filter: blur(4px);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid rgba(255,255,255,.6);
    transition: transform .2s, background .2s;
}
.course-video-overlay:hover .play-btn { transform: scale(1.1); background: rgba(255,255,255,.25); }
.play-btn i { color: #fff; font-size: 1.5rem; margin-left: 4px; }

.course-badge { font-size: .7rem; }

.stat-item { display: flex; flex-direction: column; }
.stat-label { font-size: .75rem; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: .04em; }
.stat-value { font-size: .95rem; font-weight: 600; }

.instructor-card .instructor-avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; }

/* Course content accordion */
.course-content-sidebar .accordion-button { font-weight: 600; font-size: .92rem; }
.course-content-sidebar .accordion-button:not(.collapsed) { background: transparent; box-shadow: none; }
.course-content-sidebar .lesson-row { display: flex; align-items: center; gap: .75rem; padding: .5rem 0; border-bottom: 1px solid var(--bs-border-color-translucent); transition: background .25s; }
.course-content-sidebar .lesson-row:last-child { border-bottom: none; }
.course-content-sidebar .lesson-row.active-lesson { background: rgba(var(--bs-primary-rgb), .06); border-radius: .35rem; }
.course-content-sidebar .lesson-check { flex-shrink: 0; width: 18px; height: 18px; pointer-events: none; }
.course-content-sidebar .lesson-title { flex: 1; font-size: .875rem; transition: color .25s; }
.course-content-sidebar .lesson-title.done { text-decoration: line-through; color: var(--bs-secondary-color); }
.course-content-sidebar .lesson-duration { font-size: .8rem; color: var(--bs-secondary-color); white-space: nowrap; }

/* Locked video progress track */
.video-progress-track { width: 100%; height: 5px; background: rgba(255,255,255,.2); border-radius: 3px; overflow: hidden; }
.video-progress-fill  { height: 100%; background: var(--bs-primary); border-radius: 3px; width: 0%; transition: width .4s linear; }

/* Checkpoint pulse when unlocked */
@keyframes checkPulse { 0%{transform:scale(1)} 40%{transform:scale(1.35)} 100%{transform:scale(1)} }
.lesson-check.just-checked { animation: checkPulse .4s ease; }

.stick-top { position: sticky; top: 80px; }

/* Breadcrumb page header */
.onboarding-header { margin-bottom: 1.5rem; }

/* Primera migracion visual del portal: usa variables y componentes de Sparta. */
.onboarding-hero { position: relative; overflow: hidden; border: 1px solid rgba(var(--bs-primary-rgb), .28); border-left: 4px solid var(--bs-primary); background: linear-gradient(120deg, rgba(var(--bs-primary-rgb), .17), rgba(var(--bs-primary-rgb), .035) 65%, var(--bs-body-bg)); }
.onboarding-hero::after { content: ''; position: absolute; width: 220px; height: 220px; right: -70px; top: -110px; border-radius: 50%; background: rgba(var(--bs-primary-rgb), .08); }
.onboarding-hero > * { position: relative; z-index: 1; }
.onboarding-kicker { font-size: .72rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--bs-primary); }
.onboarding-hero-title { max-width: 760px; }
.onboarding-welcome-icon { width: 2.7rem; height: 2.7rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .7rem; background: linear-gradient(135deg, #0d6efd, #084298); color: #c7db3e; box-shadow: 0 .35rem .8rem rgba(13, 110, 253, .25); }
.onboarding-welcome-copy { max-width: 620px; }
.onboarding-welcome-copy p { line-height: 1.55; text-align: justify; }
.onboarding-route-card { height: 100%; border: 1px solid var(--bs-border-color); border-radius: .6rem; background: var(--bs-body-bg); transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
.onboarding-route-card.is-current { border-color: rgba(var(--bs-primary-rgb), .45); box-shadow: 0 .25rem .8rem rgba(var(--bs-primary-rgb), .08); }
.onboarding-route-card:hover { transform: translateY(-2px); border-color: rgba(var(--bs-primary-rgb), .5); }
.onboarding-route-icon { width: 2.35rem; height: 2.35rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .55rem; background: linear-gradient(135deg, #0d6efd, #084298); color: #c7db3e; box-shadow: 0 .28rem .7rem rgba(13, 110, 253, .24); }
.onboarding-route-icon i { color: #c7db3e !important; }
.onboarding-section-heading > .onboarding-route-icon, .onboarding-section-title-icon { flex: 0 0 auto; }
.onboarding-section-heading h5, .onboarding-section-title h5 { color: #344563 !important; }
.onboarding-route-card.is-pending { opacity: .72; }
.onboarding-route-card .btn { white-space: nowrap; }
.onboarding-hero-video { border: 1px solid rgba(var(--bs-primary-rgb), .25); border-radius: .75rem; overflow: hidden; background: var(--bs-body-bg); box-shadow: 0 .35rem 1rem rgba(67, 89, 113, .12); }
.onboarding-hero-video .course-video-wrapper { border-radius: 0; }
.onboarding-hero-video .video-progress-track { background: rgba(var(--bs-primary-rgb), .12); }
.onboarding-welcome-hint { animation: onboardingWelcomeHint 1.7s ease-in-out infinite; }
@keyframes onboardingWelcomeHint { 50% { transform: translateY(-2px); box-shadow: 0 .35rem .8rem rgba(var(--bs-primary-rgb), .23); } }
.onboarding-instructions { border: 1px solid rgba(var(--bs-primary-rgb), .18); border-top: 3px solid var(--bs-primary); background: linear-gradient(100deg, rgba(var(--bs-primary-rgb), .05), var(--bs-body-bg) 55%); box-shadow: none !important; }
.onboarding-instruction-step { position: relative; height: 100%; padding: 1rem; border: 1px solid var(--bs-border-color); border-left: 3px solid var(--bs-primary); border-radius: .65rem; background: var(--bs-body-bg); box-shadow: 0 .2rem .55rem rgba(67, 89, 113, .06); }
.onboarding-instruction-step .step-icon { width: 2rem; height: 2rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(var(--bs-primary-rgb), .1); color: var(--bs-primary); flex: 0 0 auto; }
.onboarding-instruction-step.is-progress { border-left-color: var(--bs-success); }
.onboarding-instruction-step.is-progress .step-icon { background: rgba(var(--bs-success-rgb), .13); color: var(--bs-success); }
.onboarding-instruction-step.is-quiz { border-left-color: #b76e00; }
.onboarding-instruction-step.is-quiz .step-icon { background: rgba(var(--bs-warning-rgb), .17); color: #b76e00; }
.onboarding-module-studio { border: 1px solid rgba(var(--bs-primary-rgb), .22); overflow: hidden; }
.onboarding-module-player { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; border-radius: .65rem; background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .11), rgba(var(--bs-primary-rgb), .025)); border: 1px dashed rgba(var(--bs-primary-rgb), .45); }
.onboarding-module-player .player-icon { width: 4rem; height: 4rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 1rem; background: var(--bs-primary); color: #fff; font-size: 1.5rem; box-shadow: 0 .45rem 1rem rgba(var(--bs-primary-rgb), .22); }
.onboarding-module-player #onboardingModuleEmpty { width: 100%; aspect-ratio: 16 / 9; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.onboarding-module-list { height: 382px; max-height: 382px; overflow-y: auto; scroll-snap-type: y proximity; }
.onboarding-module-list .onboarding-module-item { scroll-snap-align: start; }
.onboarding-module-item { display: flex; align-items: center; gap: .7rem; padding: .75rem; border: 1px solid var(--bs-border-color); border-radius: .55rem; background: var(--bs-body-bg); transition: border-color .2s ease, background .2s ease, transform .2s ease; }
.onboarding-module-item[type="button"] { color: inherit; cursor: pointer; }
.onboarding-module-item[type="button"]:hover, .onboarding-module-item[type="button"].is-selected { border-color: rgba(var(--bs-primary-rgb), .48); background: rgba(var(--bs-primary-rgb), .07); transform: translateX(2px); }
.onboarding-module-item + .onboarding-module-item { margin-top: .55rem; }
.onboarding-module-item.is-ready { border-color: rgba(var(--bs-primary-rgb), .45); background: rgba(var(--bs-primary-rgb), .06); }
.onboarding-module-item.is-suggested { border-color: rgba(var(--bs-info-rgb), .72); background: rgba(var(--bs-info-rgb), .07); box-shadow: 0 .2rem .65rem rgba(var(--bs-info-rgb), .14); }
.onboarding-module-item.is-suggested:hover { border-color: var(--bs-info); box-shadow: 0 .35rem .85rem rgba(var(--bs-info-rgb), .22); }
.onboarding-module-item .module-icon, .onboarding-module-item .module-status { width: 2rem; height: 2rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .5rem; flex: 0 0 auto; }
.onboarding-module-item .module-icon { background: linear-gradient(135deg, #e6f6bd, #c7db3e); color: #084298; font-size: 1rem; box-shadow: 0 .2rem .5rem rgba(199,219,62,.22); }
.onboarding-module-item .module-status { width:1.75rem; height:1.75rem; padding:0; border-radius:50%; border:1px solid var(--bs-border-color); background:#fff; color:var(--bs-secondary); font-size:0; }
.onboarding-module-item .module-status i { font-size:.82rem; }
.onboarding-module-item .module-status.is-complete { border-color:rgba(var(--bs-success-rgb),.32); background:rgba(var(--bs-success-rgb),.13); color:var(--bs-success); }
.onboarding-module-guidance { font-size:.72rem; font-weight:700; }
.onboarding-roadmap { position: relative; overflow: hidden; border: 1px solid rgba(var(--bs-primary-rgb), .18); border-top: 3px solid var(--bs-primary); background: linear-gradient(100deg, rgba(var(--bs-primary-rgb), .05), var(--bs-body-bg) 55%); }
.onboarding-roadmap-stage { position: relative; height: 100%; border: 1px solid var(--bs-border-color); border-left: 3px solid var(--bs-primary); border-radius: .65rem; padding: 1rem; background: var(--bs-body-bg); box-shadow: 0 .2rem .55rem rgba(67, 89, 113, .06); }
.onboarding-roadmap-stage.is-first { border-left-color: var(--bs-success); }
.onboarding-roadmap-stage.is-autonomy { border-left-color: #696cff; }
.onboarding-roadmap-stage.is-first { border-left-color: #b76e00; }
.onboarding-roadmap-stage.is-adaptation { border-left-color: #2f7d32; }
.onboarding-roadmap-stage.is-autonomy { border-left-color: #6f42c1; }
.onboarding-roadmap-stage .stage-icon, .onboarding-value .value-icon { width: 2rem; height: 2rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .55rem; margin-right: .45rem; }
.onboarding-roadmap-stage.is-first .stage-icon, .onboarding-value.is-yellow .value-icon { color:#b76e00; background:linear-gradient(135deg, rgba(255,193,7,.26), rgba(255,193,7,.08)); }
.onboarding-roadmap-stage.is-adaptation .stage-icon, .onboarding-value.is-green .value-icon { color:#2f7d32; background:linear-gradient(135deg, rgba(40,167,69,.25), rgba(40,167,69,.07)); }
.onboarding-roadmap-stage.is-autonomy .stage-icon, .onboarding-value.is-purple .value-icon { color:#6f42c1; background:linear-gradient(135deg, rgba(111,66,193,.25), rgba(111,66,193,.07)); }
.onboarding-roadmap-stage.is-first .stage-label, .onboarding-value.is-yellow h6 { color:#b76e00; }
.onboarding-roadmap-stage.is-adaptation .stage-label, .onboarding-value.is-green h6 { color:#2f7d32; }
.onboarding-roadmap-stage.is-autonomy .stage-label, .onboarding-value.is-purple h6 { color:#6f42c1; }
.onboarding-roadmap-stage .stage-label { background: transparent !important; padding: 0 !important; border-radius: 0 !important; }
.onboarding-glossary-item { border-left-color:#168d92; }
.onboarding-glossary-item h6::before { font-family:"Font Awesome 6 Free"; font-weight:900; content:"\f02d"; margin-right:.45rem; }
#soporte .row > div:nth-child(1) .onboarding-contact-card { border-left:3px solid #168d92; } #soporte .row > div:nth-child(1) .onboarding-contact-icon, #soporte .row > div:nth-child(1) .onboarding-contact-area { color:#168d92; }
#soporte .row > div:nth-child(2) .onboarding-contact-card { border-left:3px solid #e67e22; } #soporte .row > div:nth-child(2) .onboarding-contact-icon, #soporte .row > div:nth-child(2) .onboarding-contact-area { color:#e67e22; }
#soporte .row > div:nth-child(3) .onboarding-contact-card { border-left:3px solid #d63384; } #soporte .row > div:nth-child(3) .onboarding-contact-icon, #soporte .row > div:nth-child(3) .onboarding-contact-area { color:#d63384; }
#soporte .row > div:nth-child(4) .onboarding-contact-card { border-left:3px solid #dc3545; } #soporte .row > div:nth-child(4) .onboarding-contact-icon, #soporte .row > div:nth-child(4) .onboarding-contact-area { color:#dc3545; }
#soporte .row > div:nth-child(5) .onboarding-contact-card { border-left:3px solid #7952b3; } #soporte .row > div:nth-child(5) .onboarding-contact-icon, #soporte .row > div:nth-child(5) .onboarding-contact-area { color:#7952b3; }
#feedback-sec { border-top-color:var(--bs-success); box-shadow:0 .25rem .8rem rgba(var(--bs-success-rgb), .1); }
.onboarding-evaluation-badge { background:rgba(111,66,193,.14); color:#6f42c1; }
#evaluacion > .card-body > .onboarding-quiz-question, #evaluacion-especializada .onboarding-quiz-question.text-center { border:0; border-left:0; background:transparent; padding:0; text-align:left !important; }
.onboarding-quiz-pair > div > .onboarding-info-section { height:100%; }
#feedback-sec .onboarding-feedback-rating label { border-color:var(--bs-success); color:var(--bs-success); } #feedback-sec .onboarding-feedback-rating input:checked + label { background:var(--bs-success); border-color:var(--bs-success); color:#fff; }
.onboarding-quiz-question { border-left: 4px solid #6f42c1; box-shadow: -.32rem 0 .8rem rgba(111,66,193,.34), 0 .2rem .55rem rgba(67,89,113,.08); }
.onboarding-corporate-carousel-controls { display:flex; justify-content:space-between; gap:.75rem; margin-top:1.25rem; }
.onboarding-diploma { border:1px solid rgba(183,110,0,.5); border-radius:1rem; background:linear-gradient(135deg, #07101f, #0b1220); color:#fff; text-align:center; padding:2rem; }.onboarding-diploma .diploma-kicker,.onboarding-diploma .diploma-action { color:#c7db3e; }.onboarding-diploma .diploma-name { font-size:1.5rem; font-weight:800; letter-spacing:.04em; border-bottom:1px solid rgba(255,255,255,.16); padding-bottom:.6rem; }
.onboarding-specialized-progress { display:flex; align-items:center; justify-content:space-between; gap:.25rem; margin:0 0 1.5rem; }.onboarding-specialized-progress .progress-step { position:relative; z-index:1; display:grid; place-items:center; width:2rem; height:2rem; border-radius:50%; background:#20c7d2; color:#fff; font-size:.75rem; font-weight:700; }.onboarding-specialized-progress .progress-step.is-answered { background:var(--bs-success); }.onboarding-specialized-progress .progress-step.is-wrong { background:var(--bs-danger); }.onboarding-specialized-progress::before { content:''; position:absolute; left:2rem; right:2rem; height:1px; background:var(--bs-border-color); }.onboarding-specialized-progress { position:sticky; top:-1rem; z-index:2; padding:.8rem 0; background:var(--bs-body-bg); }.onboarding-specialized-error { display:none; }.onboarding-specialized-error.is-visible { display:block; }
.onboarding-document-card.is-yellow { border-left-color:#b76e00 !important; }.onboarding-document-card.is-green { border-left-color:#2f7d32 !important; }.onboarding-document-card.is-purple { border-left-color:#6f42c1 !important; }
.onboarding-document-card.is-yellow .onboarding-document-icon { color:#b76e00; background:linear-gradient(135deg, rgba(255,193,7,.26), rgba(255,193,7,.08)); }.onboarding-document-card.is-green .onboarding-document-icon { color:#2f7d32; background:linear-gradient(135deg, rgba(40,167,69,.25), rgba(40,167,69,.07)); }.onboarding-document-card.is-purple .onboarding-document-icon { color:#6f42c1; background:linear-gradient(135deg, rgba(111,66,193,.25), rgba(111,66,193,.07)); }
.onboarding-document-card .onboarding-document-action { font-size:.78rem; }
.onboarding-roadmap-stage ul { line-height: 1.55; }
.onboarding-roadmap-stage .stage-label { display: inline-block; padding: .25rem .55rem; border-radius: 999px; background: rgba(var(--bs-primary-rgb), .1); color: var(--bs-primary); font-size: .7rem; font-weight: 700; text-transform: uppercase; }
.onboarding-roadmap-stage.is-first .stage-label { background: rgba(var(--bs-success-rgb), .13); color: var(--bs-success); }
.onboarding-roadmap-stage.is-adaptation .stage-label { background: rgba(var(--bs-primary-rgb), .13); color: var(--bs-primary); }
.onboarding-roadmap-stage.is-autonomy .stage-label { background: rgba(105, 108, 255, .14); color: #696cff; }
.onboarding-culture-card { height: 100%; border: 1px solid var(--bs-border-color); border-top: 3px solid var(--bs-primary); background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .045), var(--bs-body-bg) 62%); box-shadow: 0 .2rem .55rem rgba(67, 89, 113, .06); transition: transform .2s ease, box-shadow .2s ease; }
.onboarding-culture-card.is-vision { border-top-color: var(--bs-success); }
.onboarding-culture-card:not(.is-values):hover { transform: translateY(-2px); box-shadow: 0 .45rem 1rem rgba(67, 89, 113, .12); }
.onboarding-culture-card .card-body { padding: 1.25rem; }
.onboarding-value { height: 100%; padding: 1rem; border: 1px solid var(--bs-border-color); border-left: 3px solid var(--bs-primary); border-radius: .65rem; background: var(--bs-body-bg); box-shadow: 0 .2rem .55rem rgba(67, 89, 113, .06); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
.onboarding-value:hover { transform: translateY(-2px); border-color: rgba(var(--bs-primary-rgb), .38); box-shadow: 0 .45rem .95rem rgba(67, 89, 113, .12); }
.onboarding-identity-badge { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .55rem; border: 0; border-radius: 999px; background: rgba(var(--bs-warning-rgb), .17); color: #b76e00; font-size: .7rem; font-weight: 700; text-transform: none; }
.onboarding-identity-badge i { color: inherit; }
.onboarding-info-section { border: 1px solid rgba(var(--bs-primary-rgb), .18); border-top: 3px solid var(--bs-primary); background: linear-gradient(100deg, rgba(var(--bs-primary-rgb), .05), var(--bs-body-bg) 55%); }
.onboarding-section-heading { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.25rem; }
.onboarding-section-heading h5 { margin: 0; }
.onboarding-glossary-item, .onboarding-contact-card, .onboarding-document-card { height: 100%; border: 1px solid var(--bs-border-color); border-radius: .65rem; background: var(--bs-body-bg); box-shadow: 0 .2rem .55rem rgba(67, 89, 113, .06); }
.onboarding-glossary-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
.onboarding-glossary-toolbar .onboarding-section-heading { margin-bottom: 0; }
.onboarding-glossary-search { width: min(100%, 22rem); }
.onboarding-glossary-item { padding: 1rem; border-left: 3px solid var(--bs-primary); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
.onboarding-glossary-item:hover { transform: translateY(-2px); box-shadow: 0 .45rem .95rem rgba(67, 89, 113, .14); }
.onboarding-glossary-item.is-highlighted { border-color: var(--bs-success); box-shadow: 0 .55rem 1.15rem rgba(var(--bs-success-rgb), .22); }
.onboarding-glossary-item h6, .onboarding-glossary-item h6 i { color: #168d92; }
.onboarding-contact-card, .onboarding-document-card { padding: 1rem; transition: transform .2s ease, box-shadow .2s ease; }
.onboarding-document-card { border-left: 3px solid var(--bs-primary); background: var(--bs-body-bg); }
.onboarding-contact-card:hover, .onboarding-document-card:hover { transform: translateY(-2px); box-shadow: 0 .45rem 1rem rgba(67, 89, 113, .12); }
.onboarding-contact-icon, .onboarding-document-icon { width: 2.5rem; height: 2.5rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .65rem; background: rgba(var(--bs-primary-rgb), .1); color: var(--bs-primary); flex: 0 0 auto; }
.onboarding-contact-card { padding: .7rem .8rem; align-items: center; }
.onboarding-contact-area { color: var(--bs-primary); font-size: .72rem; font-weight: 700; letter-spacing: .035em; text-transform: uppercase; }
.onboarding-contact-card .contact-name { white-space: nowrap; }
.onboarding-contact-card .contact-action { white-space: nowrap; }
.onboarding-contact-phone { color: #2f7d32; font-size: .75rem; font-weight: 600; white-space: nowrap; }
.onboarding-faq .accordion-item { border: 1px solid var(--bs-border-color); border-left: 3px solid var(--bs-primary); border-radius: .65rem !important; overflow: hidden; background: var(--bs-body-bg); box-shadow: 0 .2rem .55rem rgba(67, 89, 113, .06); }
.onboarding-faq .accordion-item + .accordion-item { margin-top: .65rem; }
.onboarding-faq .accordion-button { box-shadow: none; font-weight: 600; }
.onboarding-faq .accordion-button:not(.collapsed) { color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), .06); }
.onboarding-feedback-rating input { position: absolute; opacity: 0; pointer-events: none; }
.onboarding-feedback-rating label { min-width: 2.6rem; text-align: center; }
.onboarding-feedback-rating input:checked + label { background: var(--bs-primary); border-color: var(--bs-primary); color: #fff; }
.onboarding-quiz-question { scroll-margin-top: 1rem; border: 1px solid var(--bs-border-color); border-left: 3px solid var(--bs-primary); border-radius: .65rem; padding: 1rem; background: var(--bs-body-bg); }
.onboarding-quiz-question + .onboarding-quiz-question { margin-top: 1rem; }
.onboarding-quiz-question.is-missing { border-color: var(--bs-danger); box-shadow: 0 0 0 .2rem rgba(var(--bs-danger-rgb), .12); }
.onboarding-quiz-option { display: block; height: 100%; padding: .85rem 1rem; border: 1px solid var(--bs-border-color); border-radius: .55rem; cursor: pointer; transition: border-color .2s ease, background .2s ease; }
.onboarding-quiz-option:has(input:checked) { border-color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), .06); }
.onboarding-quiz-option input { margin-right: .55rem; }
.onboarding-quiz-success { border: 1px solid rgba(var(--bs-success-rgb), .3); border-top: 3px solid var(--bs-success); background: rgba(var(--bs-success-rgb), .06); }
.onboarding-module-count { background: #2f7d32; color: #fff; }
.onboarding-evaluation-badge { background: rgba(var(--bs-primary-rgb), .13); color: var(--bs-primary); }
.onboarding-quiz-modal .modal-content { overflow: hidden; border: 0; background: #fff !important; color: var(--bs-body-color); }
.onboarding-quiz-modal .modal-header { padding: 1.15rem 1.35rem; border: 0; background: linear-gradient(110deg, #003a9c, #004fd3) !important; color: #fff !important; }
.onboarding-quiz-modal .modal-title { display: flex; align-items: center; gap: .75rem; color: #fff !important; font-weight: 700; }
.onboarding-quiz-modal .modal-title-icon { width: 2.5rem; height: 2.5rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .6rem; background: rgba(199, 219, 62, .15); color: #c7db3e; box-shadow: inset 0 0 0 1px rgba(199, 219, 62, .18); }
.onboarding-quiz-modal .modal-title small { display: block; margin-top: .15rem; color: rgba(255, 255, 255, .68); font-size: .76rem; font-weight: 400; }
.onboarding-quiz-modal .btn-close { filter: invert(1) grayscale(1) brightness(3); opacity: .85; }
.onboarding-quiz-modal .modal-body { background: #fff !important; color: var(--bs-body-color); }
.onboarding-quiz-modal .modal-body .text-muted { color: var(--bs-secondary-color) !important; }
.onboarding-quiz-modal .modal-body .onboarding-quiz-question { background: var(--bs-body-bg); border-color: var(--bs-border-color); }
.onboarding-quiz-modal .modal-body .onboarding-quiz-option { background: #fff; border-color: var(--bs-border-color); color: var(--bs-body-color); }
.onboarding-quiz-modal .modal-body .form-select, .onboarding-quiz-modal .modal-body .form-control { background: #fff; border-color: var(--bs-border-color); color: var(--bs-body-color); }
.onboarding-quiz-modal .modal-body .form-select option { background: #fff; color: var(--bs-body-color); }
.onboarding-quiz-modal .onboarding-specialized-progress { background: #fff; }
.onboarding-specialized-modal-top { margin-top: 1.25rem; margin-bottom: 1.25rem; }
.onboarding-specialized-progress .progress-step { cursor: pointer; transition: transform .18s ease, box-shadow .18s ease; }
.onboarding-specialized-progress .progress-step:hover { transform: translateY(-2px) scale(1.06); box-shadow: 0 .25rem .6rem rgba(var(--bs-primary-rgb), .25); }
.onboarding-diploma-preview-modal .modal-dialog { width: min(94vw, 740px); max-width: none; margin: 1rem auto; }
.onboarding-diploma-preview-modal .modal-content { background: #07101f; border: 0; }
.onboarding-diploma-preview-modal .modal-header { background: #004fd3; color: #fff; border: 0; }
.onboarding-diploma-preview-modal .btn-close { filter: invert(1) grayscale(1) brightness(3); }
.onboarding-diploma-preview { position: relative; width: min(100%, 540px); margin: 0 auto; padding: clamp(2rem, 5vw, 3rem) clamp(2rem, 6vw, 4rem); border: .3rem solid #004fd3; box-shadow: inset .65rem 0 #c3d64c, inset -.65rem 0 #c3d64c; background: #fff; color: #004fd3; text-align: center; }
.onboarding-diploma-preview::before, .onboarding-diploma-preview::after { content: ''; position: absolute; left: 0; right: 0; height: .9rem; background: #004fd3; }
.onboarding-diploma-preview::before { top: 0; }
.onboarding-diploma-preview::after { bottom: 0; }
.onboarding-diploma-preview > * { position: relative; z-index: 1; }
.onboarding-diploma-preview .preview-brand { display: flex; justify-content: center; }
.onboarding-diploma-preview .preview-brand img { width: min(11rem, 42vw); height: auto; object-fit: contain; }
.onboarding-diploma-preview .preview-title { margin: 2rem 0 1.25rem; color: #004fd3; font-family: Georgia, serif; font-size: clamp(2.25rem, 7vw, 3.75rem); font-weight: 500; letter-spacing: .04em; line-height: 1; }
.onboarding-diploma-preview .preview-name { display: inline-block; max-width: 100%; margin: 1.2rem auto 1.4rem; padding: 0 .75rem .55rem; border-bottom: 3px solid #c3d64c; color: #004fd3; font-family: Georgia, serif; font-size: clamp(1.65rem, 4.5vw, 2.45rem); line-height: 1.15; overflow-wrap: anywhere; }
.onboarding-diploma-preview .preview-accent { display: flex; align-items: center; justify-content: center; gap: .5rem; color: #8aa000; font-size: .72rem; font-weight: 800; letter-spacing: .12em; line-height: 1.35; }
.onboarding-diploma-preview .preview-honor-mark { display: inline-grid; place-items: center; width: 1.6rem; height: 1.6rem; margin: 0 .75rem; border: 1px solid #c3d64c; border-radius: 50%; background: #004fd3; color: #c3d64c; }
.onboarding-diploma-preview #onboardingDiplomaPreviewGrant { color: #55708d !important; }
.onboarding-diploma-preview #onboardingDiplomaPreviewText { color: #38516b !important; }
.onboarding-diploma-preview #onboardingDiplomaPreviewIssuer,
.onboarding-diploma-preview #onboardingDiplomaPreviewValidation { color: #61758d !important; }
@media (max-width: 575.98px) { .onboarding-diploma-preview { padding: 1.75rem 1.5rem; } .onboarding-diploma-preview .preview-honor-mark { width: 1.35rem; height: 1.35rem; margin: 0; } }
.onboarding-specialized-meta { border-bottom: 1px solid var(--bs-border-color); }
.onboarding-footer { border-top: 1px solid rgba(var(--bs-primary-rgb), .18); color: var(--bs-secondary-color); }
.onboarding-footer hr { width: 16rem; max-width: 100%; margin: 1.25rem auto; border-color: rgba(var(--bs-primary-rgb), .2); opacity: 1; }
.onboarding-footer .footer-project { color: #8f9e18; font-size: .78rem; font-weight: 700; letter-spacing: .08em; }
.onboarding-progress-fab { position: fixed; z-index: 1030; right: 1.25rem; bottom: 1.25rem; display: flex; align-items: center; justify-content: center; width: 4.35rem; height: 4.35rem; padding: .28rem; border: 0; border-radius: 50%; color: var(--bs-danger); background: conic-gradient(var(--bs-danger) 0deg, rgba(var(--bs-danger-rgb), .15) 0deg); box-shadow: 0 .35rem 1rem rgba(67, 89, 113, .24); cursor: pointer; transition: transform .2s ease, box-shadow .2s ease; }
.onboarding-instructions h5, .onboarding-module-studio .card-header h5, .onboarding-roadmap h5, .onboarding-section-heading h5, .onboarding-culture-card h5 { color: #344563 !important; }
.onboarding-instructions .onboarding-instruction-step { border-left-width: 3px; box-shadow: -.14rem 0 .45rem rgba(67, 89, 113, .08), 0 .2rem .55rem rgba(67, 89, 113, .06); }
.onboarding-instructions .col-lg-4:nth-child(1) .onboarding-instruction-step { border-left-color: #b76e00; }
.onboarding-instructions .col-lg-4:nth-child(1) .step-icon { color: #b76e00; background: linear-gradient(135deg, rgba(255, 193, 7, .26), rgba(255, 193, 7, .08)); }
.onboarding-instructions .col-lg-4:nth-child(1) .fw-semibold { color: #b76e00 !important; }
.onboarding-instructions .col-lg-4:nth-child(2) .onboarding-instruction-step { border-left-color: #2f7d32; }
.onboarding-instructions .col-lg-4:nth-child(2) .step-icon { color: #2f7d32; background: linear-gradient(135deg, rgba(40, 167, 69, .25), rgba(40, 167, 69, .07)); }
.onboarding-instructions .col-lg-4:nth-child(2) .fw-semibold { color: #2f7d32 !important; }
.onboarding-instructions .col-lg-4:nth-child(3) .onboarding-instruction-step { border-left-color: #6f42c1; }
.onboarding-instructions .col-lg-4:nth-child(3) .step-icon { color: #6f42c1; background: linear-gradient(135deg, rgba(111, 66, 193, .25), rgba(111, 66, 193, .07)); }
.onboarding-instructions .col-lg-4:nth-child(3) .fw-semibold { color: #6f42c1 !important; }
.onboarding-instructions:not(.is-welcome-complete) .col-lg-4:nth-child(2) .onboarding-instruction-step, .onboarding-instructions:not(.is-welcome-complete) .col-lg-4:nth-child(3) .onboarding-instruction-step { visibility: hidden; opacity: 0; }
.onboarding-instructions.is-intro-animation .col-lg-4:nth-child(2) .onboarding-instruction-step, .onboarding-instructions.is-intro-animation .col-lg-4:nth-child(3) .onboarding-instruction-step { visibility: visible; animation: onboardingInstructionReveal 1.8s ease both; }
.onboarding-instructions.is-intro-animation .col-lg-4:nth-child(2) .onboarding-instruction-step { animation-delay: 2s; }
.onboarding-instructions.is-intro-animation .col-lg-4:nth-child(3) .onboarding-instruction-step { animation-delay: 4s; }
@keyframes onboardingInstructionReveal { from { opacity: 0; transform: translateX(-2.25rem); } to { opacity: 1; transform: translateX(0); } }
.onboarding-roadmap-stage.is-first .stage-label { color: #b76e00 !important; }
.onboarding-value.is-yellow { border-left-color: #b76e00; box-shadow: -.18rem 0 .55rem rgba(183,110,0,.22), 0 .2rem .55rem rgba(67,89,113,.06); }
.onboarding-value.is-green { border-left-color: #2f7d32; box-shadow: -.18rem 0 .55rem rgba(47,125,50,.22), 0 .2rem .55rem rgba(67,89,113,.06); }
.onboarding-value.is-purple { border-left-color: #6f42c1; box-shadow: -.18rem 0 .55rem rgba(111,66,193,.22), 0 .2rem .55rem rgba(67,89,113,.06); }
.onboarding-value.is-yellow:hover { box-shadow: -.22rem 0 .7rem rgba(183,110,0,.32), 0 .45rem .95rem rgba(183,110,0,.16); }
.onboarding-value.is-green:hover { box-shadow: -.22rem 0 .7rem rgba(47,125,50,.32), 0 .45rem .95rem rgba(47,125,50,.16); }
.onboarding-value.is-purple:hover { box-shadow: -.22rem 0 .7rem rgba(111,66,193,.32), 0 .45rem .95rem rgba(111,66,193,.16); }
.onboarding-progress-fab:hover { transform: translateY(-2px); box-shadow: 0 .55rem 1.2rem rgba(67, 89, 113, .28); }
.onboarding-progress-fab span { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; border-radius: 50%; background: var(--bs-body-bg); font-size: .8rem; font-weight: 700; }
.onboarding-course-celebration { position: fixed; z-index: 1085; inset: 0; display: grid; place-items: center; pointer-events: none; overflow: hidden; }
.onboarding-course-celebration-message { position: relative; z-index: 2; max-width: min(90vw, 34rem); padding: 1.25rem 1.75rem; border: 1px solid rgba(195,214,76,.7); border-radius: 1rem; background: linear-gradient(135deg, #003a9c, #004fd3); color: #fff; font-size: clamp(1.1rem, 2.3vw, 1.45rem); font-weight: 700; text-align: center; box-shadow: 0 1rem 2.5rem rgba(0,79,211,.35); animation: onboardingCelebrationMessage 4.5s ease both; }
.onboarding-course-celebration-message i { color: #c3d64c; }
.onboarding-firework-particle { position: absolute; left: 50%; top: 50%; width: .55rem; height: .55rem; border-radius: 50%; background: var(--particle-color); box-shadow: 0 0 .8rem var(--particle-color); animation: onboardingFirework 1.6s cubic-bezier(.15,.65,.35,1) forwards; }
@keyframes onboardingFirework { from { opacity: 1; transform: translate(-50%, -50%) rotate(var(--particle-angle)) translateY(0) scale(1); } to { opacity: 0; transform: translate(-50%, -50%) rotate(var(--particle-angle)) translateY(var(--particle-distance)) scale(.2); } }
@keyframes onboardingCelebrationMessage { 0%,100% { opacity: 0; transform: translateY(1rem) scale(.94); } 12%,84% { opacity: 1; transform: translateY(0) scale(1); } }
@media (prefers-reduced-motion: reduce) { .onboarding-firework-particle, .onboarding-course-celebration-message { animation-duration: .01ms; } }
@media (max-width: 575.98px) { .onboarding-progress-fab { right: .85rem; bottom: .85rem; width: 3.8rem; height: 3.8rem; } }
@media (min-width: 992px) { .onboarding-roadmap .row > div:not(:last-child) .onboarding-roadmap-stage::after { content: ''; position: absolute; z-index: 1; top: 1.65rem; right: -1.55rem; width: 1.35rem; height: 2px; background: rgba(var(--bs-primary-rgb), .35); } }
@media (max-width: 575.98px) { .onboarding-hero .card-body { padding: 1.25rem !important; } .onboarding-quiz-modal .modal-body { padding: 1rem; } .onboarding-quiz-modal .modal-header { padding: 1rem; } }
</style>


<!-- ── Main grid ──────────────────────────────────────────────────────────── -->
<!-- Portal: primera capa del prototipo, adaptada al sistema visual de Sparta. -->
<section class="card onboarding-hero mb-4">
    <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="d-flex align-items-center gap-3 mb-3"><span class="onboarding-welcome-icon"><i class="fa-solid fa-handshake"></i></span><h3 class="mb-0 onboarding-hero-title">&iexcl;Hola, bienvenid@ a Maxikash!</h3></div>
                <div class="onboarding-welcome-copy">
                    <p class="mb-3">Hoy comienza una nueva etapa profesional y nos emociona que formes parte de este gran equipo. En Maxikash creemos en las personas con iniciativa, compromiso y ganas de superarse, que buscan aportar nuevas ideas, asumir retos y crecer junto con la compa&ntilde;&iacute;a.</p>
                    <p class="mb-3">Por ello, este kit fue creado para servirte como gu&iacute;a durante tu proceso de integraci&oacute;n, brindarte informaci&oacute;n relevante y ayudarte a conocer mejor nuestra cultura, valores y forma de trabajo.</p>
                    <p class="fw-semibold text-primary mb-0">&iexcl;Te invitamos a revisar los m&oacute;dulos interactivos aqu&iacute; abajo!</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="onboarding-hero-video">
                    <div class="px-3 py-2 border-bottom small fw-semibold text-primary bg-label-primary"><i class="fa-solid fa-circle-play me-1"></i>Video institucional de bienvenida</div>
                    <div class="card-body p-0">
                        <div class="course-video-wrapper" id="videoWrapper">
                            <!-- Overlay inicial -->
                            <div class="course-video-overlay" id="videoOverlay" onclick="onbTogglePlay()">
                                <div class="play-btn" id="overlayPlayBtn">
                                    <i class="fa-solid fa-play"></i>
                                </div>
                            </div>
                            <?php
                                // ── Video: public/uploads/YTDown…1080p.mp4 (raíz) o public/uploads/onboarding/*.mp4
                                // ── El controlador lo sirve en: /onboarding/video
                                $videoSrc = '/onboarding/video?modulo=bienvenida';
                            ?>
                            <video id="courseVideo"
                                   style="width:100%;height:100%;display:block;background:#000;"
                                   preload="metadata"
                                   playsinline
                                   controlsList="nodownload nofullscreen noremoteplayback"
                                   disablePictureInPicture>
                                <source src="<?= $videoSrc ?>" type="video/mp4" />
                                Tu navegador no soporta video HTML5.
                            </video>
                        </div>

                        <!-- Barra de progreso bloqueada (solo lectura) -->
                        <div class="px-4 pt-3 pb-1">
                            <div class="video-progress-track">
                                <div class="video-progress-fill" id="videoProgressFill"></div>
                            </div>
                        </div>

                        <!-- Controles personalizados -->
                        <div class="d-flex align-items-center justify-content-between px-4 py-2 border-top" style="font-size:.875rem;">
                            <div class="d-flex align-items-center gap-3">
                                <button class="btn btn-sm btn-icon btn-label-primary" id="btnPlay" title="Play / Pause" onclick="onbTogglePlay()">
                                    <i class="fa-solid fa-play"></i>
                                </button>
                                <span class="text-muted" id="timeDisplay">00:00 / 00:00</span>
                                <span class="badge bg-label-warning ms-1" id="lockBadge" title="No puedes adelantar el video">
                                    <i class="fa-solid fa-lock fa-xs me-1"></i>Bloqueado
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-volume-high text-muted"></i>
                                <input type="range" class="form-range" style="width:80px;" id="volumeSlider" min="0" max="1" step="0.05" value="1" />
                                <button class="btn btn-sm btn-icon btn-label-secondary" id="btnFullscreen" title="Pantalla completa">
                                    <i class="fa-solid fa-expand"></i>
                                </button>
                            </div>
                        </div>
                        <div class="px-4 pb-3 d-none" id="onboardingWelcomeHint"><button type="button" class="btn btn-sm btn-primary onboarding-welcome-hint"><i class="fa-solid fa-circle-play me-1"></i>Click aqu&iacute; para reproducir tu video de bienvenida</button></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="onboarding-instructions rounded p-3 p-lg-4 mt-4" data-onboarding-instructions data-onboarding-user="<?= htmlspecialchars($onboardingProgressUser, ENT_QUOTES, 'UTF-8') ?>">
            <div class="onboarding-section-heading mb-3"><span class="onboarding-route-icon"><i class="fa-solid fa-list-check"></i></span><div><h5>Instrucciones para completar tu Inducci&oacute;n</h5></div></div>
            <div class="row g-3">
                <div class="col-lg-4"><div class="onboarding-instruction-step d-flex gap-3"><span class="step-icon"><i class="fa-solid fa-play"></i></span><div><div class="fw-semibold mb-1 text-primary">Paso 1</div><small class="text-muted">Visualiza el video institucional superior y los 9 m&oacute;dulos tem&aacute;ticos situados justo abajo.</small></div></div></div>
                <div class="col-lg-4"><div class="onboarding-instruction-step is-progress d-flex gap-3"><span class="step-icon"><i class="fa-solid fa-circle-check"></i></span><div><div class="fw-semibold mb-1 text-success">Paso 2</div><small class="text-muted">Al concluir cada video, m&aacute;rcalo como completado para actualizar tu barra de progreso en tiempo real.</small></div></div></div>
                <div class="col-lg-4"><div class="onboarding-instruction-step is-quiz d-flex gap-3"><span class="step-icon"><i class="fa-solid fa-award"></i></span><div><div class="fw-semibold mb-1 text-warning">Paso 3</div><small class="text-muted">Responde de forma correcta el Quiz final para desbloquear y descargar tu Diploma Digital corporativo.</small></div></div></div>
            </div>
        </div>
    </div>
</section>

<!-- Preparado para los videos por modulo: se activara al cargar el catalogo real. -->
<section class="card onboarding-module-studio mb-4" data-onboarding-user="<?= htmlspecialchars($onboardingProgressUser, ENT_QUOTES, 'UTF-8') ?>">
    <div class="card-header bg-transparent border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="onboarding-section-heading mb-0"><span class="onboarding-route-icon"><i class="fa-solid fa-film"></i></span><div><h5>Contenido del Curso de Inducción</h5><small class="text-muted">Selecciona un m&oacute;dulo para ver su video de inducci&oacute;n.</small></div></div>
        <span class="badge onboarding-module-count" id="onboardingModuleCount"></span>
    </div>
    <div class="card-body p-3 p-lg-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="onboarding-module-player p-0 overflow-hidden" id="onboardingModulePlayer">
                    <div class="w-100 px-3 py-2 border-bottom bg-label-primary small fw-semibold text-primary" id="onboardingModuleTitle"><i class="fa-solid fa-circle-play me-1"></i>Selecciona un m&oacute;dulo</div>
                    <video class="w-100 bg-black" id="onboardingModuleVideo" controls playsinline preload="metadata" style="display:none; aspect-ratio:16 / 9;"></video>
                    <div class="p-4 p-lg-5" id="onboardingModuleEmpty"><span class="player-icon mb-3"><i class="fa-solid fa-video"></i></span><h5 class="mb-2">Selecciona un m&oacute;dulo para iniciar</h5><p class="text-muted mb-0 px-4">Elige uno de los videos de la lista para reproducirlo aqu&iacute;.</p></div>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted"><span class="badge bg-label-warning onboarding-module-guidance"><i class="fa-solid fa-lock fa-xs me-1"></i>Selecciona los videos en el orden recomendado.</span><span class="badge bg-label-info text-info"><i class="fa-solid fa-circle-play me-1"></i>Videos disponibles</span></div>
            </div>
            <div class="col-lg-4">
                <div class="onboarding-module-list pe-1">
                    <button type="button" class="onboarding-module-item is-ready w-100 text-start" data-module="legacyapp" data-title="LegacyApp"><span class="module-icon" aria-hidden="true"><i class="bi bi-play-fill"></i></span><span class="flex-grow-1"><span class="fw-semibold d-block">1. LegacyApp</span></span><span class="module-status" aria-label="Video pendiente" title="Video pendiente">Pendiente</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="asistencia" data-title="Asistencia"><span class="module-icon" aria-hidden="true"><i class="bi bi-phone-fill"></i></span><span class="flex-grow-1"><span class="fw-semibold d-block">2. Asistencia</span></span><span class="module-status" aria-label="Video pendiente" title="Video pendiente">Pendiente</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="nomina" data-title="Pago de N&oacute;mina"><span class="module-icon" aria-hidden="true"><i class="bi bi-cash-stack"></i></span><span class="flex-grow-1"><span class="fw-semibold d-block">3. Pago de N&oacute;mina</span></span><span class="module-status" aria-label="Video pendiente" title="Video pendiente">Pendiente</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="bonos" data-title="Bonos e Incentivos"><span class="module-icon" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></span><span class="flex-grow-1"><span class="fw-semibold d-block">4. Bonos e Incentivos</span></span><span class="module-status" aria-label="Video pendiente" title="Video pendiente">Pendiente</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="recibos_nomina" data-title="Recibos de N&oacute;mina"><span class="module-icon" aria-hidden="true"><i class="bi bi-receipt"></i></span><span class="flex-grow-1"><span class="fw-semibold d-block">5. Recibos de N&oacute;mina</span></span><span class="module-status" aria-label="Video pendiente" title="Video pendiente">Pendiente</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="cambio_cuenta" data-title="Cambio de Cuenta"><span class="module-icon" aria-hidden="true"><i class="bi bi-arrow-left-right"></i></span><span class="flex-grow-1"><span class="fw-semibold d-block">6. Cambio de Cuenta</span></span><span class="module-status" aria-label="Video pendiente" title="Video pendiente">Pendiente</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="incapacidades" data-title="Incidencias M&eacute;dicas"><span class="module-icon" aria-hidden="true"><i class="bi bi-heart-pulse-fill"></i></span><span class="flex-grow-1"><span class="fw-semibold d-block">7. Incidencias M&eacute;dicas</span></span><span class="module-status" aria-label="Video pendiente" title="Video pendiente">Pendiente</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="valores" data-title="Nuestros Valores"><span class="module-icon" aria-hidden="true"><i class="bi bi-award-fill"></i></span><span class="flex-grow-1"><span class="fw-semibold d-block">8. Nuestros Valores</span></span><span class="module-status" aria-label="Video pendiente" title="Video pendiente">Pendiente</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="cultura" data-title="Nuestra Cultura"><span class="module-icon" aria-hidden="true"><i class="bi bi-balloon-fill"></i></span><span class="flex-grow-1"><span class="fw-semibold d-block">9. Nuestra Cultura</span></span><span class="module-status" aria-label="Video pendiente" title="Video pendiente">Pendiente</span></button>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="card onboarding-roadmap mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start mb-3"><div class="onboarding-section-heading mb-0"><span class="onboarding-route-icon"><i class="fa-solid fa-route"></i></span><div><h5>Tu Ruta de Integraci&oacute;n de 30 D&iacute;as</h5><small class="text-muted">Sigue esta planeaci&oacute;n paso a paso para asegurar un inicio espectacular en la compa&ntilde;&iacute;a.</small></div></div><span class="badge bg-label-success">Roadmap</span></div>
        <div class="row g-3">
            <div class="col-lg-4"><div class="onboarding-roadmap-stage is-first"><div class="d-flex align-items-center"><span class="stage-icon"><i class="fa-solid fa-gear"></i></span><span class="stage-label">D&iacute;a 1: Configuraci&oacute;n</span></div><ul class="small mb-0 mt-3 ps-3"><li>Completa el Curso de Inducción.</li><li>Configura cuentas y herramientas internas.</li><li>Presentaci&oacute;n oficial con tu l&iacute;der directo.</li></ul></div></div>
            <div class="col-lg-4"><div class="onboarding-roadmap-stage is-adaptation"><div class="d-flex align-items-center"><span class="stage-icon"><i class="fa-solid fa-people-group"></i></span><span class="stage-label">Semana 1: Adaptaci&oacute;n</span></div><ul class="small mb-0 mt-3 ps-3"><li>Sesiones de acompa&ntilde;amiento con el equipo.</li><li>Comprensi&oacute;n de objetivos y m&eacute;tricas.</li><li>Primera sesi&oacute;n de preguntas con tu supervisor.</li></ul></div></div>
            <div class="col-lg-4"><div class="onboarding-roadmap-stage is-autonomy"><div class="d-flex align-items-center"><span class="stage-icon"><i class="fa-solid fa-rocket"></i></span><span class="stage-label">Mes 1: Autonom&iacute;a</span></div><ul class="small mb-0 mt-3 ps-3"><li>Ejecuci&oacute;n aut&oacute;noma de funciones diarias.</li><li>Retroalimentaci&oacute;n de los primeros 30 d&iacute;as.</li><li>Alineaci&oacute;n de objetivos del siguiente trimestre.</li></ul></div></div>
        </div>
    </div>
</section>

<section class="mb-4">
    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="card onboarding-culture-card"><div class="card-body"><div class="onboarding-section-heading mb-3"><span class="onboarding-route-icon"><i class="fa-solid fa-bullseye"></i></span><div><h5>Misi&oacute;n</h5></div></div><p class="mb-0">Impulsar el crecimiento financiero de nuestros clientes mediante soluciones innovadoras, accesibles y eficientes, respaldadas por tecnolog&iacute;a, cercan&iacute;a y un equipo comprometido.</p></div></div></div>
        <div class="col-lg-6"><div class="card onboarding-culture-card is-vision"><div class="card-body"><div class="onboarding-section-heading mb-3"><span class="onboarding-route-icon"><i class="fa-solid fa-eye"></i></span><div><h5>Visi&oacute;n</h5></div></div><p class="mb-0">Ser una fintech referente en Latinoam&eacute;rica, reconocida por transformar la experiencia financiera a trav&eacute;s de innovaci&oacute;n, expansi&oacute;n estrat&eacute;gica y excelencia operativa.</p></div></div></div>
    </div>
    <div class="card onboarding-culture-card is-values"><div class="card-body p-4"><div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3"><div class="d-flex align-items-center gap-2"><span class="onboarding-route-icon"><i class="fa-solid fa-heart"></i></span><div><h5 class="mb-1">Nuestros Valores</h5><small class="text-muted">Los principios fundamentales alineados a nuestra cultura corporativa.</small></div></div><span class="onboarding-identity-badge">Identidad</span></div><div class="row g-3"><div class="col-lg-4"><div class="onboarding-value is-yellow"><h6><span class="value-icon"><i class="fa-solid fa-lightbulb"></i></span>Innovaci&oacute;n</h6><small>Buscamos soluciones tecnol&oacute;gicas ingeniosas para mejorar las finanzas de nuestros clientes y hacer m&aacute;s eficientes nuestros procesos.</small></div></div><div class="col-lg-4"><div class="onboarding-value is-green"><h6><span class="value-icon"><i class="fa-solid fa-people-group"></i></span>Cercan&iacute;a</h6><small>Creemos en el factor humano y en una comunicaci&oacute;n directa, transparente y emp&aacute;tica con colaboradores y usuarios.</small></div></div><div class="col-lg-4"><div class="onboarding-value is-purple"><h6><span class="value-icon"><i class="fa-solid fa-scale-balanced"></i></span>Disciplina</h6><small>El orden, compromiso diario y apego a nuestras pol&iacute;ticas sostienen nuestro crecimiento profesional.</small></div></div></div></div></div>
</section>

<section class="card onboarding-info-section mb-4" id="glosario-sec">
    <div class="card-body p-4">
        <div class="onboarding-glossary-toolbar"><div class="onboarding-section-heading"><span class="onboarding-route-icon"><i class="fa-solid fa-book"></i></span><div><h5>Glosario de T&eacute;rminos Maxikash</h5><small class="text-muted">Busca y domina el lenguaje operativo, legal y financiero que usamos en el d&iacute;a a d&iacute;a.</small></div></div></div>
        <div class="row g-3" id="onboardingGlossaryList">
            <div class="col-md-6 col-xl-4" data-glossary-item data-glossary-key="lfpdppp"><div class="onboarding-glossary-item"><h6>LFPDPPP</h6><small>Ley Federal de Protecci&oacute;n de Datos Personales en Posesi&oacute;n de los Particulares.</small></div></div>
            <div class="col-md-6 col-xl-4" data-glossary-item data-glossary-key="derechos arco"><div class="onboarding-glossary-item"><h6>Derechos ARCO</h6><small>Derechos de acceso, rectificaci&oacute;n, cancelaci&oacute;n y oposici&oacute;n respecto al manejo de datos personales.</small></div></div>
            <div class="col-md-6 col-xl-4" data-glossary-item data-glossary-key="clabe interbancaria"><div class="onboarding-glossary-item"><h6>CLABE Interbancaria</h6><small>Clave Bancaria Estandarizada de 18 d&iacute;gitos utilizada para dispersar pagos de n&oacute;mina.</small></div></div>
            <div class="col-md-6 col-xl-4" data-glossary-item data-glossary-key="imss"><div class="onboarding-glossary-item"><h6>IMSS</h6><small>Instituto Mexicano del Seguro Social.</small></div></div>
            <div class="col-md-6 col-xl-4" data-glossary-item data-glossary-key="fintech"><div class="onboarding-glossary-item"><h6>Fintech</h6><small>Empresa que utiliza tecnolog&iacute;a para ofrecer o mejorar servicios financieros.</small></div></div>
            <div class="col-md-6 col-xl-4" data-glossary-item data-glossary-key="timbrado de nómina"><div class="onboarding-glossary-item"><h6>Timbrado de N&oacute;mina</h6><small>Proceso fiscal mediante el cual se valida y certifica un recibo de n&oacute;mina.</small></div></div>
        </div>
    </div>
</section>

<section class="card onboarding-info-section mb-4" id="soporte">
    <div class="card-body p-4">
        <div class="onboarding-section-heading"><span class="onboarding-route-icon"><i class="fa-solid fa-address-card"></i></span><div><h5>Directorio de Atenci&oacute;n y Soporte</h5><small class="text-muted">&iquest;Tienes dudas o necesitas reportar una incidencia? Comun&iacute;cate con el &aacute;rea correspondiente.</small></div></div>
        <div class="row g-3">
            <div class="col-md-6 col-xl"><div class="onboarding-contact-card d-flex gap-2"><span class="onboarding-contact-icon"><i class="fa-solid fa-people-group"></i></span><div class="flex-grow-1 min-w-0"><span class="onboarding-contact-area d-block mb-1">RRHH Corporativo</span><strong class="contact-name d-block small">Nathaly / Elvira</strong><span class="onboarding-contact-phone d-block"><i class="fa-brands fa-whatsapp me-1"></i>+52 56 1816 7733</span></div></div></div>
            <div class="col-md-6 col-xl"><div class="onboarding-contact-card d-flex gap-2"><span class="onboarding-contact-icon"><i class="fa-solid fa-headset"></i></span><div class="flex-grow-1 min-w-0"><span class="onboarding-contact-area d-block mb-1">RRHH Cobranza</span><strong class="contact-name d-block small">Owen Ruiz</strong><span class="onboarding-contact-phone d-block"><i class="fa-brands fa-whatsapp me-1"></i>+52 56 5770 7341</span></div></div></div>
            <div class="col-md-6 col-xl"><div class="onboarding-contact-card d-flex gap-2"><span class="onboarding-contact-icon"><i class="fa-solid fa-user-tie"></i></span><div class="flex-grow-1 min-w-0"><span class="onboarding-contact-area d-block mb-1">Subdirecci&oacute;n RRHH</span><strong class="contact-name d-block small">Jorge del Angel</strong><span class="onboarding-contact-phone d-block"><i class="fa-brands fa-whatsapp me-1"></i>+52 55 3419 5498</span></div></div></div>
            <div class="col-md-6 col-xl"><div class="onboarding-contact-card d-flex gap-2"><span class="onboarding-contact-icon"><i class="fa-solid fa-laptop-medical"></i></span><div class="flex-grow-1 min-w-0"><span class="onboarding-contact-area d-block mb-1">N&oacute;minas</span><strong class="contact-name d-block small">Allan / Christian</strong><span class="onboarding-contact-phone d-block"><i class="fa-brands fa-whatsapp me-1"></i>+52 55 4065 3300</span></div></div></div>
            <div class="col-md-6 col-xl"><div class="onboarding-contact-card d-flex gap-2"><span class="onboarding-contact-icon"><i class="fa-solid fa-scale-balanced"></i></span><div class="flex-grow-1 min-w-0"><span class="onboarding-contact-area d-block mb-1">&Aacute;rea Legal</span><strong class="contact-name d-block small">Jur&iacute;dico</strong></div><a class="btn btn-sm btn-label-primary contact-action" href="mailto:juridico@maxikash.mx"><i class="fa-solid fa-envelope me-1"></i>Correo</a></div></div>
        </div>
    </div>
</section>

<section class="card onboarding-info-section mb-4" id="documentos">
    <div class="card-body p-4">
        <div class="onboarding-section-heading"><span class="onboarding-route-icon"><i class="fa-solid fa-file-lines"></i></span><div><h5>Documentaci&oacute;n de Soporte</h5><small class="text-muted">Descarga las gu&iacute;as oficiales para resolver dudas administrativas en cualquier momento.</small></div></div>
        <div class="row g-3">
            <div class="col-md-6 col-xl-4"><div class="onboarding-document-card is-yellow"><div class="d-flex gap-3 mb-3"><span class="onboarding-document-icon"><i class="fa-solid fa-mobile-screen-button"></i></span><div><h6 class="mb-1">Manual de Registro en App</h6><small class="text-muted">PDF explicativo paso a paso (Gu&iacute;a LegacyApp)</small></div></div><button type="button" class="btn btn-primary onboarding-document-action" data-bs-toggle="modal" data-bs-target="#onboardingDocumentModal" data-document-title="Manual de Registro en App">Ver documento</button></div></div>
            <div class="col-md-6 col-xl-4"><div class="onboarding-document-card is-green"><div class="d-flex gap-3 mb-3"><span class="onboarding-document-icon"><i class="fa-solid fa-calendar-days"></i></span><div><h6 class="mb-1">Calendario de Pagos 2026</h6><small class="text-muted">Fechas oficiales de dispersi&oacute;n</small></div></div><button type="button" class="btn btn-primary onboarding-document-action" data-bs-toggle="modal" data-bs-target="#onboardingDocumentModal" data-document-title="Calendario de Pagos 2026">Ver documento</button></div></div>
            <div class="col-md-6 col-xl-4"><div class="onboarding-document-card is-purple"><div class="d-flex gap-3 mb-3"><span class="onboarding-document-icon"><i class="fa-solid fa-file-signature"></i></span><div><h6 class="mb-1">Formato Cambio de Cuenta</h6><small class="text-muted">Formato para actualizaci&oacute;n de datos bancarios</small></div></div><button type="button" class="btn btn-primary onboarding-document-action" data-bs-toggle="modal" data-bs-target="#onboardingDocumentModal" data-document-title="Formato Cambio de Cuenta">Ver documento</button></div></div>
        </div>
    </div>
</section>

<section class="card onboarding-info-section mb-4" id="faq">
    <div class="card-body p-4">
        <div class="onboarding-section-heading"><span class="onboarding-route-icon"><i class="fa-solid fa-circle-question"></i></span><div><h5>Preguntas Frecuentes</h5><small class="text-muted">Encuentra respuestas a las dudas m&aacute;s comunes durante tu proceso de integraci&oacute;n.</small></div></div>
        <div class="accordion onboarding-faq" id="onboardingFaqAccordion">
            <div class="accordion-item"><h2 class="accordion-header" id="onboardingFaqHeadingOne"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#onboardingFaqOne" aria-expanded="false" aria-controls="onboardingFaqOne">&iquest;C&oacute;mo se protegen mis Datos Personales? (LFPDPPP)</button></h2><div id="onboardingFaqOne" class="accordion-collapse collapse" aria-labelledby="onboardingFaqHeadingOne" data-bs-parent="#onboardingFaqAccordion"><div class="accordion-body"><p class="fw-semibold">Aviso de Privacidad Simplificado - Colaboradores</p><p>Amigo Efectivo S.A. de C.V. (Maxikash M&eacute;xico), con domicilio oficial para o&iacute;r y recibir notificaciones, es el responsable del tratamiento de los datos personales y sensibles que recopila con motivo de tu proceso de inducci&oacute;n y relaci&oacute;n laboral.</p><p>Tus datos son tratados bajo estrictas medidas de seguridad f&iacute;sicas y digitales conforme a la Ley Federal de Protecci&oacute;n de Datos Personales en Posesi&oacute;n de los Particulares (LFPDPPP) con las siguientes finalidades principales:</p><ul><li>Validaci&oacute;n de identidad, documentos escolares y trayectoria profesional.</li><li>Alta ante el Instituto Mexicano del Seguro Social (IMSS) e INFONAVIT.</li><li>Gesti&oacute;n de pagos de n&oacute;mina, bonos, dispersi&oacute;n de fondos y apertura de cuentas de n&oacute;mina.</li></ul><p class="mb-0">Podr&aacute;s ejercer tus derechos de Acceso, Rectificaci&oacute;n, Cancelaci&oacute;n u Oposici&oacute;n (Derechos ARCO) enviando un correo al &aacute;rea regulada correspondiente: <a href="mailto:juridico@maxikash.mx">juridico@maxikash.mx</a>.</p></div></div></div>
            <div class="accordion-item"><h2 class="accordion-header" id="onboardingFaqHeadingTwo"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#onboardingFaqTwo" aria-expanded="false" aria-controls="onboardingFaqTwo">&iquest;Qu&eacute; justificantes m&eacute;dicos se aceptan para n&oacute;mina?</button></h2><div id="onboardingFaqTwo" class="accordion-collapse collapse" aria-labelledby="onboardingFaqHeadingTwo" data-bs-parent="#onboardingFaqAccordion"><div class="accordion-body">&Uacute;nicamente se validan justificantes oficiales emitidos por el IMSS. No se aceptan recetas o comprobantes de m&eacute;dicos privados o farmacias de conveniencia.</div></div></div>
            <div class="accordion-item"><h2 class="accordion-header" id="onboardingFaqHeadingThree"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#onboardingFaqThree" aria-expanded="false" aria-controls="onboardingFaqThree">&iquest;C&oacute;mo reporto una incapacidad m&eacute;dica?</button></h2><div id="onboardingFaqThree" class="accordion-collapse collapse" aria-labelledby="onboardingFaqHeadingThree" data-bs-parent="#onboardingFaqAccordion"><div class="accordion-body">Informa inmediatamente a tu supervisor. Despu&eacute;s, haz llegar tu comprobante del IMSS a la extensi&oacute;n de Recursos Humanos que corresponda a tu &aacute;rea mediante el directorio de arriba.</div></div></div>
            <div class="accordion-item"><h2 class="accordion-header" id="onboardingFaqHeadingFour"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#onboardingFaqFour" aria-expanded="false" aria-controls="onboardingFaqFour">&iquest;D&oacute;nde descargo mis recibos de n&oacute;mina?</button></h2><div id="onboardingFaqFour" class="accordion-collapse collapse" aria-labelledby="onboardingFaqHeadingFour" data-bs-parent="#onboardingFaqAccordion"><div class="accordion-body">Ser&aacute;n enviados autom&aacute;ticamente a tu direcci&oacute;n de correo electr&oacute;nico personal registrada. Revisa constantemente tu bandeja de entrada y la carpeta de SPAM.</div></div></div>
        </div>
    </div>
</section>

<div class="modal fade" id="onboardingDocumentModal" tabindex="-1" aria-labelledby="onboardingDocumentModalTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="onboardingDocumentModalTitle"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body"></div></div></div></div>

<section class="card onboarding-info-section mb-4" id="evaluacion" data-onboarding-quiz-session="<?= htmlspecialchars($onboardingQuizSession, ENT_QUOTES, 'UTF-8') ?>">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center gap-2"><div class="onboarding-section-heading mb-0"><span class="onboarding-route-icon"><i class="fa-solid fa-graduation-cap"></i></span><div><h5>Quiz de Inducci&oacute;n Corporativo</h5></div></div><span class="badge onboarding-evaluation-badge">Evaluaci&oacute;n</span></div><p class="text-muted text-center small my-4">Eval&uacute;a lo aprendido en los videos y desbloquea tu Diploma Digital de Inducci&oacute;n de forma inmediata.</p><div class="text-center"><button type="button" class="btn btn-primary" id="onboardingCorporateQuizLaunch" data-bs-toggle="modal" data-bs-target="#onboardingCorporateQuizModal"><i class="fa-solid fa-right-to-bracket me-1"></i>Iniciar quiz corporativo</button></div>
        <form id="onboardingQuizForm" novalidate>
            <fieldset class="onboarding-quiz-question" data-quiz-question><legend class="fs-6 fw-semibold">1. &iquest;Qu&eacute; comprobante m&eacute;dico es legalmente v&aacute;lido y aceptado para justificar una incapacidad en Maxikash?</legend><div class="row g-3"><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ1" value="receta" />Receta m&eacute;dica de una farmacia privada de conveniencia</label></div><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ1" value="imss" />Incapacidad oficial emitida directamente por el IMSS</label></div></div></fieldset>
            <fieldset class="onboarding-quiz-question" data-quiz-question><legend class="fs-6 fw-semibold">2. &iquest;A qu&eacute; &aacute;rea se debe dirigir la solicitud formal en caso de requerir un Cambio de Cuenta de N&oacute;mina?</legend><div class="row g-3"><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ2" value="nominas" />&Aacute;rea de N&oacute;minas, adjuntando el formato firmado y estado de cuenta</label></div><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ2" value="soporte" />&Uacute;nicamente al departamento de Soporte T&eacute;cnico</label></div></div></fieldset>
            <fieldset class="onboarding-quiz-question" data-quiz-question><legend class="fs-6 fw-semibold">3. &iquest;C&oacute;mo ejerce un colaborador de Maxikash sus Derechos ARCO para la protecci&oacute;n de sus Datos Personales?</legend><div class="row g-3"><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ3" value="juridico" />Enviando una solicitud formal al correo del departamento Jur&iacute;dico</label></div><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ3" value="nominas" />Por medio de una llamada telef&oacute;nica informal al &aacute;rea de N&oacute;minas</label></div></div></fieldset>
            <fieldset class="onboarding-quiz-question" data-quiz-question><legend class="fs-6 fw-semibold">4. &iquest;Cu&aacute;les son los tres valores fundamentales que gu&iacute;an a Maxikash?</legend><div class="row g-3"><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ4" value="responsabilidad" />Responsabilidad, Calidad y Liderazgo</label></div><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ4" value="valores" />Innovaci&oacute;n, Cercan&iacute;a y Disciplina</label></div></div></fieldset>
            <fieldset class="onboarding-quiz-question" data-quiz-question><legend class="fs-6 fw-semibold">5. &iquest;C&oacute;mo se distribuyen tus recibos de n&oacute;mina digitales?</legend><div class="row g-3"><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ5" value="correo" />Se env&iacute;an autom&aacute;ticamente al correo personal registrado del colaborador</label></div><div class="col-md-6"><label class="onboarding-quiz-option"><input type="radio" name="onboardingQuizQ5" value="impresos" />Se entregan impresos &uacute;nicamente los d&iacute;as 15 de cada mes</label></div></div></fieldset>
            <button type="submit" class="btn btn-primary mt-4" id="onboardingQuizSubmit"><i class="fa-solid fa-paper-plane me-1"></i>Enviar y evaluar respuestas</button>
        </form>
        <div class="onboarding-quiz-success rounded p-4 text-center d-none" id="onboardingQuizSuccess"><span class="onboarding-route-icon mb-3"><i class="fa-solid fa-circle-check"></i></span><h5>&iexcl;Felicidades!</h5><p>Has acreditado exitosamente la inducci&oacute;n corporativa.</p><div class="onboarding-diploma mt-4"><div class="fs-2 mb-3"><i class="fa-solid fa-award diploma-kicker"></i></div><h3 class="mb-2">DIPLOMA DE INDUCCI&Oacute;N</h3><div class="diploma-kicker fw-bold small mb-4">OTORGADO CON HONORES POR MAXIKASH M&Eacute;XICO</div><p class="text-white-50">Se otorga con orgullo el presente reconocimiento a:</p><div class="diploma-name mb-4"><?= htmlspecialchars($onboardingCertificateName ?? 'Colaborador Maxikash', ENT_QUOTES, 'UTF-8') ?></div><p class="text-white-50 mb-4">Por haber acreditado exitosamente la inducci&oacute;n corporativa sobre pol&iacute;ticas de asistencia, n&oacute;mina, cultura y normativas vigentes.</p><button type="button" class="btn btn-outline-light diploma-action" disabled><i class="fa-solid fa-print me-1"></i>Imprimir o guardar diploma</button></div></div>
    </div>
</section>

<section class="card onboarding-info-section mb-4" id="evaluacion-especializada">
    <div class="card-body p-4"><div class="d-flex justify-content-between align-items-center gap-2"><div class="onboarding-section-heading mb-0"><span class="onboarding-route-icon"><i class="fa-solid fa-briefcase"></i></span><div><h5>Quiz por Puestos (Espec&iacute;fico)</h5></div></div><span class="badge bg-label-warning">Especializado</span></div><p class="text-muted text-center small my-4">Secci&oacute;n asignada para las evaluaciones espec&iacute;ficas seg&uacute;n el puesto comercial, operativo o administrativo.</p><div class="text-center"><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#onboardingSpecializedQuizModal"><i class="fa-solid fa-right-to-bracket me-1"></i>Iniciar quiz de puestos</button></div></div>
</section>

<section class="card onboarding-info-section mb-4" id="feedback-sec">
    <div class="card-body p-4"><div class="onboarding-section-heading"><span class="onboarding-route-icon"><i class="fa-solid fa-comments"></i></span><div><h5>Buz&oacute;n de Retroalimentaci&oacute;n</h5><small class="text-muted">Queremos ofrecerte la mejor experiencia. Califica este portal de inducci&oacute;n y d&eacute;janos tus comentarios.</small></div></div><form id="onboardingFeedbackForm"><div class="mb-4"><label class="form-label fw-semibold">&iquest;C&oacute;mo calificar&iacute;as la claridad de la informaci&oacute;n y la usabilidad de este portal?</label><div class="d-flex gap-2 onboarding-feedback-rating"><input type="radio" id="feedbackRating1" name="feedbackRating" value="1" /><label class="btn btn-outline-primary" for="feedbackRating1">1</label><input type="radio" id="feedbackRating2" name="feedbackRating" value="2" /><label class="btn btn-outline-primary" for="feedbackRating2">2</label><input type="radio" id="feedbackRating3" name="feedbackRating" value="3" /><label class="btn btn-outline-primary" for="feedbackRating3">3</label><input type="radio" id="feedbackRating4" name="feedbackRating" value="4" /><label class="btn btn-outline-primary" for="feedbackRating4">4</label><input type="radio" id="feedbackRating5" name="feedbackRating" value="5" /><label class="btn btn-outline-primary" for="feedbackRating5">5</label></div></div><div class="mb-3"><label class="form-label fw-semibold" for="onboardingFeedbackComment">&iquest;Qu&eacute; sugerencia tienes para mejorar el proceso de bienvenida en Maxikash?</label><textarea class="form-control" id="onboardingFeedbackComment" rows="4" placeholder="Escribe aqu&iacute; tus comentarios, sugerencias o dudas..."></textarea></div><button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>Enviar feedback</button><div class="alert alert-success mt-3 mb-0 d-none" id="onboardingFeedbackSuccess">Muchas gracias! Recibimos tu retroalimentaci&oacute;n correctamente.</div></form></div>
</section>

<div class="modal fade onboarding-quiz-modal" id="onboardingSpecializedQuizModal" tabindex="-1" aria-labelledby="onboardingSpecializedQuizModalTitle" aria-hidden="true" data-onboarding-specialized-session="<?= htmlspecialchars($onboardingQuizSession, ENT_QUOTES, 'UTF-8') ?>" data-assigned-name="<?= htmlspecialchars($onboardingCertificateName ?? 'Colaborador Maxikash', ENT_QUOTES, 'UTF-8') ?>" data-assigned-position="<?= htmlspecialchars($onboardingAssignedPosition ?? '', ENT_QUOTES, 'UTF-8') ?>" data-specialized-quiz-type="<?= htmlspecialchars($onboardingSpecializedQuizType ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="modal-dialog modal-xl modal-dialog-scrollable onboarding-specialized-modal-top">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="onboardingSpecializedQuizModalTitle"><span class="modal-title-icon"><i class="fa-solid fa-briefcase"></i></span><span>Sistema de Evaluaci&oacute;n T&eacute;cnica Avanzada<br><small class="fw-normal">Test de <?= htmlspecialchars($onboardingCertificateName ?? 'Colaborador Maxikash', ENT_QUOTES, 'UTF-8') ?></small></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3 p-lg-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 pb-3 mb-4 onboarding-specialized-meta"><span class="onboarding-kicker">Evaluaci&oacute;n de puesto</span></div>
                <div class="onboarding-specialized-progress d-none" id="onboardingSpecializedProgress" aria-label="Avance de preguntas"><?php for ($progressStep = 1; $progressStep <= 10; $progressStep++): ?><span class="progress-step" data-specialized-progress="<?= $progressStep ?>"><?= $progressStep ?></span><?php endfor; ?></div>
                <div class="alert alert-warning onboarding-specialized-error" id="onboardingSpecializedError">Revisa tus respuestas: necesitas una calificación mínima de 8.</div>
                <form id="onboardingSpecializedQuizForm" novalidate>
                    <div class="row g-3 mb-4"><div class="col-md-6"><label class="form-label fw-semibold" for="onboardingSpecializedRole">1. Selecciona tu puesto asignado:</label><select class="form-select" id="onboardingSpecializedRole" required><option value="">Selecciona un puesto</option><option value="gestor_domiciliaria">Gestor de Cobranza Domiciliaria</option><option value="gestor_telefonica">Gestor de Cobranza Telef&oacute;nica</option><option value="supervisor_cobranza">Supervisor de Cobranza</option><option value="gerente_cobranza">Gerente de Cobranza</option><option value="asesor_credito">Asesor de Cr&eacute;dito Individual</option><option value="analista_credito">Analista de Cr&eacute;dito y Riesgos</option><option value="ventas_fintech">Ejecutivo de Ventas Fintech</option><option value="rrhh">Auxiliar de Recursos Humanos</option><option value="soporte">Especialista de Soporte T&eacute;cnico</option><option value="atencion">Agente de Atenci&oacute;n a Clientes</option><option value="auditor">Auditor Interno de Procesos</option><option value="capacitacion">Coordinador de Capacitaci&oacute;n</option><option value="bi">Analista de Business Intelligence</option><option value="contador">Contador General Fiscal</option><option value="facturacion">Ejecutivo de Facturaci&oacute;n</option><option value="qa">Ingeniero de Calidad / QA Tester</option><option value="desarrollador">Desarrollador de Software Jr</option><option value="ux">Dise&ntilde;ador UX / UI</option><option value="marketing">Community Manager / Marketing</option><option value="seo">Especialista en Growth SEO</option><option value="reclutador">Reclutador Operativo e IT</option><option value="abogado">Abogado Corporativo Jr</option><option value="pld">Analista de Cumplimiento / PLD</option><option value="gerente_operaciones">Gerente General de Operaciones</option><option value="supervisor_comercial">Supervisor del &Aacute;rea Comercial</option><option value="auxiliar_contable">Auxiliar Contable y Fiscal</option><option value="recepcionista">Recepcionista / Control de Accesos</option></select></div><div class="col-md-6"><label class="form-label fw-semibold" for="onboardingSpecializedName">2. Introduce tu nombre completo para el diploma:</label><input type="text" class="form-control" id="onboardingSpecializedName" required /></div></div>
                    <?php foreach ($onboardingSpecializedQuestions as $questionIndex => $question): ?>
                        <fieldset class="onboarding-quiz-question" data-specialized-question><legend class="fs-6 fw-semibold mb-3"><?= $questionIndex + 1 ?>. <?= htmlspecialchars($question[0], ENT_QUOTES, 'UTF-8') ?></legend><div class="row g-2"><?php foreach ($question[1] as $optionIndex => $option): ?><div class="col-md-4"><label class="onboarding-quiz-option"><input type="radio" name="onboardingSpecializedQ<?= $questionIndex + 1 ?>" value="<?= $optionIndex ?>" /><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></label></div><?php endforeach; ?></div></fieldset>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary mt-4"><i class="fa-solid fa-paper-plane me-1"></i>Finalizar y validar competencias</button>
                </form>
                <div class="onboarding-quiz-success rounded p-4 text-center d-none" id="onboardingSpecializedQuizSuccess"><span class="onboarding-route-icon mb-3"><i class="fa-solid fa-circle-check"></i></span><h5>&iexcl;Felicidades!</h5><p>Has acreditado satisfactoriamente la evaluaci&oacute;n especializada.</p><div class="onboarding-diploma mt-4"><div class="fs-2 mb-3"><i class="fa-solid fa-award diploma-kicker"></i></div><h3 class="mb-2">DIPLOMA DE COMPETENCIA T&Eacute;CNICA</h3><div class="diploma-kicker fw-bold small mb-4">OTORGADO CON HONORES POR MAXIKASH M&Eacute;XICO</div><p class="text-white-50">Se otorga con orgullo el presente reconocimiento a:</p><div class="diploma-name mb-4"><?= htmlspecialchars($onboardingCertificateName ?? 'Colaborador Maxikash', ENT_QUOTES, 'UTF-8') ?></div><p class="text-white-50 mb-4">Por haber acreditado exitosamente la evaluación especializada de su puesto.</p><button type="button" class="btn btn-outline-light diploma-action" disabled><i class="fa-solid fa-print me-1"></i>Imprimir o guardar diploma</button></div></div>
            </div>
        </div>
    </div>
</div>

<footer class="onboarding-footer text-center py-4 mt-2">
    <div class="fw-semibold">Amigo Efectivo - Maxikash M&eacute;xico</div>
    <div class="small mt-1">&copy; Todos los derechos reservados 2026</div>
</footer>
<button type="button" class="onboarding-progress-fab" id="onboardingProgressFab" aria-label="Progreso del Curso de Inducción: 0 por ciento" title="Progreso del Curso de Inducción"><span id="onboardingProgressFabValue">0%</span></button>
<div class="modal fade onboarding-quiz-modal" id="onboardingCorporateQuizModal" tabindex="-1" aria-labelledby="onboardingCorporateQuizModalTitle" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable onboarding-specialized-modal-top"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="onboardingCorporateQuizModalTitle"><span class="modal-title-icon"><i class="fa-solid fa-graduation-cap"></i></span><span>Quiz de Inducci&oacute;n Corporativo<small>Evaluaci&oacute;n de conocimientos de inducci&oacute;n</small></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body p-3 p-lg-4" id="onboardingCorporateQuizModalBody"></div></div></div></div>

<div class="modal fade onboarding-diploma-preview-modal" id="onboardingDiplomaPreviewModal" tabindex="-1" aria-labelledby="onboardingDiplomaPreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="onboardingDiplomaPreviewTitle"><i class="fa-solid fa-award me-2"></i>Vista previa del diploma</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body p-2 p-md-3">
                <div class="onboarding-diploma-preview">
                    <div class="preview-brand"><img src="/assets/img/Logotipo-Maxikash-Outline.png" alt="Maxikash" class="sidebar-logo"></div>
                    <div class="preview-title" id="onboardingDiplomaPreviewTitleText">CONSTANCIA</div>
                    <div class="preview-accent"><span class="preview-honor-mark">★</span><span id="onboardingDiplomaPreviewAccent">OTORGADO CON HONORES POR MAXIKASH MÉXICO</span><span class="preview-honor-mark">★</span></div>
                    <p class="mt-4 mb-0 text-secondary" id="onboardingDiplomaPreviewGrant">Se otorga con orgullo el presente reconocimiento a:</p>
                    <div class="preview-name"><?= htmlspecialchars($onboardingCertificateName ?? 'Colaborador Maxikash', ENT_QUOTES, 'UTF-8') ?></div>
                    <p class="mx-auto mb-4 text-secondary" id="onboardingDiplomaPreviewText" style="max-width: 30rem;">Por haber acreditado exitosamente la inducción corporativa sobre políticas de asistencia, nómina, cultura y normativas vigentes del ejercicio 2026.</p>
                    <div class="small text-secondary d-flex justify-content-center gap-4 flex-wrap"><span id="onboardingDiplomaPreviewIssuer">Amigo Efectivo S.A. de C.V.<br>Área de Recursos Humanos</span><span id="onboardingDiplomaPreviewValidation">FECHA: <?= date('d/m/Y') ?><br>ID: MK-2026-OK</span></div>
                </div>
                <div class="text-center mt-3"><a class="btn btn-primary" id="onboardingDiplomaPreviewDownload" href="/onboarding/diploma?tipo=corporativo"><i class="fa-solid fa-file-pdf me-1"></i>Descargar diploma PDF</a></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-6 d-none" aria-hidden="true">

    <!-- ════════════════════════════════ LEFT COLUMN ════════════════════════ -->
    <div class="col-12 col-lg-8 d-flex flex-column">

        <!-- Course title & meta ─────────────────────────────────────────── -->
        <div class="card mb-4 d-none" aria-hidden="true">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-label-primary course-badge">Bienvenido!</span>
                    <span class="badge bg-label-success course-badge">Cobranza</span>
                    <span class="badge bg-label-warning course-badge">Principiante</span>
                </div>
                <h4 class="card-title mb-0">Curso de inducción para cobranza Maxikash</h4>
            </div>
        </div>

        <!-- About this course ────────────────────────────────────────────── -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Acerca de este curso</h5>
                <p class="mb-0">
                    Bienvenido a Maxikash! El departamento de cobranza es el área responsable de gestionar la recuperación
                    de los créditos otorgados a nuestros clientes. Su misión es mantener la cartera sana mediante
                    el seguimiento oportuno de pagos, la negociación de acuerdos y el trato digno con cada persona.
                    Es un equipo clave para la operación financiera de la empresa y el primer punto de contacto
                    cuando un cliente necesita apoyo para regularizar su situación.
                </p>
            </div>
        </div>

        <!-- En números — comentado temporalmente, descomentar cuando se requiera
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4"><i class="fa-solid fa-chart-bar me-2 text-primary"></i>En números</h5>
                <div class="row g-4">
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="stat-item">
                            <span class="stat-label">Nivel</span>
                            <span class="stat-value">Todos los niveles</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="stat-item">
                            <span class="stat-label">Estudiantes</span>
                            <span class="stat-value">38,815</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="stat-item">
                            <span class="stat-label">Idioma</span>
                            <span class="stat-value">Español</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="stat-item">
                            <span class="stat-label">Subtítulos</span>
                            <span class="stat-value">Sí</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="stat-item">
                            <span class="stat-label">Lecciones</span>
                            <span class="stat-value">19</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="stat-item">
                            <span class="stat-label">Video</span>
                            <span class="stat-value">1.5 horas</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="stat-item">
                            <span class="stat-label">Acceso</span>
                            <span class="stat-value">De por vida</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="stat-item">
                            <span class="stat-label">Certificado</span>
                            <span class="stat-value">Sí</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        -->

        <!-- Description ──────────────────────────────────────────────────── -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fa-solid fa-align-left me-2 text-primary"></i>Descripción</h5>
                <p class="lh-lg">
                    Este curso de inducción está diseñado para ayudarte a integrarte al área de Cobranza de Maxikash. Aquí conocerás cómo funciona el departamento, sus objetivos y las actividades clave del día a día.
                </p>
                <p class="lh-lg">
                    Aprenderás a manejar los diferentes tipos de cartera, usar nuestras herramientas internas y aplicar buenas prácticas de atención y negociación con clientes. También conocerás las reglas y procesos que guían nuestro trabajo.
                </p>
                <p class="lh-lg">
                    Además, te acercarás a la cultura del equipo: el respeto al cliente, el trabajo en conjunto y el impacto que tiene tu labor en la empresa.
                </p>
                <p class="lh-lg">
                    Al finalizar, tendrás las bases necesarias para comenzar a operar con confianza y desarrollarte dentro del área.
                    
                </p>

               <p> Bienvenido a Cobranza Maxikash — este es tu primer paso! </p> 
            </div>
        </div>

        <!-- Instructor ───────────────────────────────────────────────────── -->

      <div class="card mb-4">
    <div class="card-body instructor-card">
        <h5 class="card-title mb-3"><i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>Instructor</h5>
        <div class="d-flex align-items-center gap-3">
            <img src="/assets/img/misc/user-female.svg"
                 alt="Sandra Yunueth Avendaño Villanueva"
                 class="instructor-avatar"
                 onerror="this.src='https://static.vecteezy.com/system/resources/previews/010/962/888/non_2x/avatar-woman-wearing-glasses-free-vector.jpg'" />
            <div>
                <h6 class="mb-0">Sandra Yunueth Avendaño Villanueva</h6>
                <p class="text-muted small mb-0">Administradora, Cobranza</p>
            </div>
        </div>
    </div>
</div>

    </div><!-- /col left -->

    <!-- ════════════════════════════════ RIGHT COLUMN ═══════════════════════ -->
    <div class="col-12 col-lg-4">
        <div class="stick-top course-content-sidebar">

            <!-- Course content accordion -->
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h6 class="mb-0"><i class="fa-solid fa-list-ul me-2 text-primary"></i>Contenido del curso</h6>
                    <p class="text-muted small mb-0 mt-1">5 secciones · 18 lecciones · 1.5 horas en total</p>
                </div>
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="courseAccordion">

                        <!-- Section 1 — checkpoints sincronizados con el video -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#section1"
                                        aria-expanded="true" aria-controls="section1">
                                    <div class="w-100">
                                        <span class="d-block">Contenido del curso</span>
                                        <small class="text-muted fw-normal" id="sectionProgress">0 / 5</small>
                                    </div>
                                </button>
                            </h2>
                            <div id="section1" class="accordion-collapse collapse show" data-bs-parent="#courseAccordion">
                                <div class="accordion-body pt-2" id="lessonList">
                                    <!--
                                        data-at = segundo en el que se marca el checkpoint.
                                        Ajusta estos valores según la duración real de tu video.
                                        Ejemplo para un video de 10 min (600 s):
                                          Punto 1 →   0 s  (inicio)
                                          Punto 2 → 120 s  (2:00)
                                          Punto 3 → 240 s  (4:00)
                                          Punto 4 → 360 s  (6:00)
                                          Punto 5 → 480 s  (8:00)
                                    -->
                                    <div class="lesson-row" data-checkpoint="0" data-at="0">
                                        <input type="checkbox" class="form-check-input lesson-check" id="chk0" />
                                        <span class="lesson-title" id="lt0">Introducción y bienvenida</span>
                                        <span class="lesson-duration">0:00</span>
                                    </div>
                                    <div class="lesson-row" data-checkpoint="1" data-at="120">
                                        <input type="checkbox" class="form-check-input lesson-check" id="chk1" />
                                        <span class="lesson-title" id="lt1">Cultura y valores del equipo</span>
                                        <span class="lesson-duration">2:00</span>
                                    </div>
                                    <div class="lesson-row" data-checkpoint="2" data-at="240">
                                        <input type="checkbox" class="form-check-input lesson-check" id="chk2" />
                                        <span class="lesson-title" id="lt2">Plataformas y herramientas internas</span>
                                        <span class="lesson-duration">4:00</span>
                                    </div>
                                    <div class="lesson-row" data-checkpoint="3" data-at="360">
                                        <input type="checkbox" class="form-check-input lesson-check" id="chk3" />
                                        <span class="lesson-title" id="lt3">Procesos y flujos de trabajo</span>
                                        <span class="lesson-duration">6:00</span>
                                    </div>
                                    <div class="lesson-row" data-checkpoint="4" data-at="480">
                                        <input type="checkbox" class="form-check-input lesson-check" id="chk4" />
                                        <span class="lesson-title" id="lt4">Cierre y siguientes pasos</span>
                                        <span class="lesson-duration">8:00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /accordion -->
                </div>
            </div><!-- /card course content -->

            <!-- Course progress (dinámico) -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-medium">Tu progreso</span>
                        <span class="small text-muted" id="progressLabel">0 / 5 puntos</span>
                    </div>
                    <div class="progress mb-1" style="height:6px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                             id="progressBar" style="width:0%;"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="text-muted small mb-0" id="progressPct">0% completado</p>
                </div>
            </div>

        </div><!-- /stick-top -->
    </div><!-- /col right -->

</div><!-- /row -->

<script>
(function () {
    if (new URLSearchParams(window.location.search).get('progress_reset') === '1') {
        var resetQuizModal = document.getElementById('onboardingSpecializedQuizModal');
        var resetQuizSession = resetQuizModal ? resetQuizModal.dataset.onboardingSpecializedSession : 'guest';
        try {
            window.localStorage.removeItem('sparta.onboarding.quiz.' + resetQuizSession);
            window.sessionStorage.removeItem('sparta.onboarding.specialized-quiz.' + resetQuizSession);
        } catch (error) {
            // El reset del avance del servidor permanece aplicado aunque el navegador bloquee almacenamiento local.
        }
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    var progressFab = document.getElementById('onboardingProgressFab');
    var progressFabValue = document.getElementById('onboardingProgressFabValue');
    var welcomeHint = document.getElementById('onboardingWelcomeHint');
    var currentProgress = null;
    var instructions = document.querySelector('[data-onboarding-instructions]');
    var instructionsIntroPlayed = false;
    var celebrationTriggered = false;

    function revealInitialInstructions(record, welcomeVideoCompleted) {
        if (!record && instructions) record = { id_usuario: Number(instructions.dataset.onboardingUser || 0), videos: { bienvenida: Boolean(welcomeVideoCompleted) } };
        var welcomeCompleted = Boolean(welcomeVideoCompleted || (record && record.videos && record.videos.bienvenida));
        if (instructionsIntroPlayed || !instructions || !record || Number(record.id_usuario || 0) <= 0 || !welcomeCompleted) return;
        if (!welcomeVideoCompleted) {
            instructions.classList.add('is-welcome-complete');
            return;
        }
        instructionsIntroPlayed = true;
        instructions.classList.remove('is-intro-animation');
        void instructions.offsetWidth;
        window.requestAnimationFrame(function () {
            instructions.classList.add('is-intro-animation');
            window.setTimeout(function () {
                instructions.classList.remove('is-intro-animation');
                instructions.classList.add('is-welcome-complete');
            }, 7000);
        });
    }

    window.onboardingInstructions = {
        revealAfterWelcome: function () { revealInitialInstructions(currentProgress, true); }
    };
    document.addEventListener('onboardingwelcomecomplete', function () { revealInitialInstructions(currentProgress, true); });

    function showCourseCelebration(record) {
        if (celebrationTriggered || !record || !record.finalizado || record.celebracion_mostrada || Number(record.progress && record.progress.percentage) !== 100) return;
        celebrationTriggered = true;
        var celebration = document.createElement('div');
        celebration.className = 'onboarding-course-celebration';
        celebration.innerHTML = '<div class="onboarding-course-celebration-message"><i class="fa-solid fa-trophy me-2"></i>¡Felicidades! Concluiste con éxito tu curso de inducción.</div>';
        var colors = ['#c3d64c', '#004fd3', '#20c7d2', '#f0ad4e', '#6f42c1'];
        var origins = [[22, 30], [50, 22], [78, 32], [34, 70], [68, 72]];
        for (var index = 0; index < 70; index++) {
            var particle = document.createElement('span');
            var origin = origins[index % origins.length];
            particle.className = 'onboarding-firework-particle';
            particle.style.left = origin[0] + '%';
            particle.style.top = origin[1] + '%';
            particle.style.setProperty('--particle-color', colors[index % colors.length]);
            particle.style.setProperty('--particle-angle', (index * 37) + 'deg');
            particle.style.setProperty('--particle-distance', '-' + (80 + (index % 7) * 13) + 'px');
            particle.style.animationDelay = (index % 5) * .11 + 's';
            celebration.appendChild(particle);
        }
        document.body.appendChild(celebration);
        window.setTimeout(function () { celebration.remove(); }, 5000);
        if (window.onboardingProgress) window.onboardingProgress.record('celebration_shown').catch(function () {});
    }

    function applyProgress(record) {
        currentProgress = record || null;
        var percentage = Number(record && record.progress ? record.progress.percentage : 0) || 0;
        if (progressFab) {
            progressFab.style.background = 'conic-gradient(var(--bs-danger) ' + (percentage * 3.6) + 'deg, rgba(var(--bs-danger-rgb), .15) 0deg)';
            progressFab.setAttribute('aria-label', 'Progreso del Curso de Inducción: ' + percentage + ' por ciento');
        }
        if (progressFabValue) progressFabValue.textContent = percentage + '%';
        if (welcomeHint) welcomeHint.classList.toggle('d-none', percentage !== 0);
        revealInitialInstructions(record);
        showCourseCelebration(record);
        document.dispatchEvent(new CustomEvent('onboardingprogresschange', { detail: record }));
    }

    function requestProgress(options) {
        return fetch('/onboarding/progress', options).then(function (response) {
            if (!response.ok) throw new Error('No se pudo actualizar el avance.');
            return response.json();
        }).then(function (response) {
            if (!response.success) throw new Error(response.message || 'No se pudo actualizar el avance.');
            applyProgress(response.data);
            return response.data;
        });
    }

    window.onboardingProgress = {
        get: function () { return currentProgress; },
        load: function () { return requestProgress({ headers: { 'Accept': 'application/json' } }); },
        record: function (action, module, score) {
            return requestProgress({
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ action: action, module: module || '', score: Number(score || 0) })
            });
        }
    };

    if (progressFab) progressFab.addEventListener('click', function () {
        var record = currentProgress || {};
        var target = null;
        if (!record.videos || !record.videos.bienvenida) target = document.querySelector('.onboarding-hero');
        else if (!record.videos.modulos || Number(record.progress && record.progress.completed_modules) < Number(record.progress && record.progress.total_modules)) {
            var moduleItems = document.querySelectorAll('.onboarding-module-item[data-module]');
            for (var index = 0; index < moduleItems.length; index++) {
                if (!record.videos.modulos || !record.videos.modulos[moduleItems[index].dataset.module]) { target = moduleItems[index]; break; }
            }
            if (!target) target = document.querySelector('.onboarding-module-studio');
        }
        else if (!record.evaluaciones || !record.evaluaciones.corporativa) target = document.getElementById('evaluacion');
        else if (!record.evaluaciones || !record.evaluaciones.especializada) target = document.getElementById('evaluacion-especializada');
        else if (!record.feedback) target = document.getElementById('feedback-sec');
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    if (welcomeHint) welcomeHint.addEventListener('click', function () {
        var welcomeVideo = document.getElementById('courseVideo');
        if (welcomeVideo) welcomeVideo.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (window.onbTogglePlay) window.onbTogglePlay();
    });
    window.onboardingProgress.load().catch(function () { applyProgress(null); });
})();
</script>

<script>
(function () {
    'use strict';

    const TOTAL_CHECKPOINTS = 5;
    let maxReached    = 0;
    let videoFinished = false;
    let checkedCount  = 0;
    let startedOnce   = false;

    const video       = document.getElementById('courseVideo');
    const overlay     = document.getElementById('videoOverlay');
    const overlayBtn  = document.getElementById('overlayPlayBtn');
    const btnPlay     = document.getElementById('btnPlay');
    const timeDisplay = document.getElementById('timeDisplay');
    const fillBar     = document.getElementById('videoProgressFill');
    const lockBadge   = document.getElementById('lockBadge');
    const progressBar = document.getElementById('progressBar');
    const progressLbl = document.getElementById('progressLabel');
    const progressPct = document.getElementById('progressPct');
    const sectionProg = document.getElementById('sectionProgress');
    const volSlider   = document.getElementById('volumeSlider');
    const btnFS       = document.getElementById('btnFullscreen');

    /* ── Helpers ──────────────────────────────────────────────────────────── */
    function fmt(s) {
        s = isNaN(s) ? 0 : Math.max(0, s);
        return String(Math.floor(s / 60)).padStart(2,'0') + ':' + String(Math.floor(s % 60)).padStart(2,'0');
    }

    function setPlayIcon(playing) {
        if (btnPlay) btnPlay.innerHTML = '<i class="fa-solid fa-' + (playing ? 'pause' : 'play') + '"></i>';
    }

    function updateSidebarProgress() {
        const pct = Math.round((checkedCount / TOTAL_CHECKPOINTS) * 100);
        if (progressBar) { progressBar.style.width = pct + '%'; progressBar.setAttribute('aria-valuenow', pct); }
        if (progressLbl) progressLbl.textContent = checkedCount + ' / ' + TOTAL_CHECKPOINTS + ' puntos';
        if (progressPct) progressPct.textContent = pct + '% completado';
        if (sectionProg) sectionProg.textContent = checkedCount + ' / ' + TOTAL_CHECKPOINTS;
    }

    function checkPoint(row) {
        const chk = row.querySelector('.lesson-check');
        const lbl = row.querySelector('.lesson-title');
        if (chk && !chk.checked) {
            chk.checked = true;
            chk.classList.add('just-checked');
            setTimeout(() => chk.classList.remove('just-checked'), 500);
            if (lbl) lbl.classList.add('done');
            checkedCount++;
            updateSidebarProgress();
        }
    }

    function highlightActiveLesson(t) {
        const rows = document.querySelectorAll('#lessonList .lesson-row');
        let activeRow = null;
        rows.forEach(r => {
            r.classList.remove('active-lesson');
            if (parseFloat(r.dataset.at) <= t) activeRow = r;
        });
        if (activeRow) activeRow.classList.add('active-lesson');
    }

    function resetAll() {
        maxReached = 0; videoFinished = false; checkedCount = 0;
        document.querySelectorAll('#lessonList .lesson-check').forEach(c => { c.checked = false; });
        document.querySelectorAll('#lessonList .lesson-title').forEach(l => l.classList.remove('done'));
        document.querySelectorAll('#lessonList .lesson-row').forEach(r => r.classList.remove('active-lesson'));
        if (lockBadge) { lockBadge.innerHTML = '<i class="fa-solid fa-lock fa-xs me-1"></i>Bloqueado'; lockBadge.classList.remove('bg-label-success'); lockBadge.classList.add('bg-label-warning'); }
        if (fillBar) fillBar.style.width = '0%';
        updateSidebarProgress();
    }

    /* ── Bloqueo de seek ─────────────────────────────────────────────────── */
    video.addEventListener('seeking', function () {
        if (videoFinished) return;
        if (video.currentTime > maxReached + 0.5) video.currentTime = maxReached;
    });

    /* ── timeupdate: progreso, checkpoints, UI ───────────────────────────── */
    video.addEventListener('timeupdate', function () {
        const t = video.currentTime;
        const d = video.duration || 0;
        if (t > maxReached) maxReached = t;
        if (d > 0 && fillBar) fillBar.style.width = ((t / d) * 100).toFixed(2) + '%';
        if (timeDisplay) timeDisplay.textContent = fmt(t) + ' / ' + fmt(d);
        document.querySelectorAll('#lessonList .lesson-row').forEach(row => {
            if (t >= parseFloat(row.dataset.at)) checkPoint(row);
        });
        highlightActiveLesson(t);
    });

    /* ── Duración disponible ─────────────────────────────────────────────── */
    video.addEventListener('loadedmetadata', function () {
        if (timeDisplay) timeDisplay.textContent = '00:00 / ' + fmt(video.duration);
    });

    /* ── Fin del video ───────────────────────────────────────────────────── */
    video.addEventListener('ended', function () {
        videoFinished = true;
        setPlayIcon(false);
        document.querySelectorAll('#lessonList .lesson-row').forEach(checkPoint);
        if (lockBadge) { lockBadge.innerHTML = '<i class="fa-solid fa-lock-open fa-xs me-1"></i>Completado'; lockBadge.classList.remove('bg-label-warning'); lockBadge.classList.add('bg-label-success'); }
        if (overlay)    overlay.style.display = 'flex';
        if (overlayBtn) overlayBtn.innerHTML  = '<i class="fa-solid fa-rotate-right"></i>';
        if (window.onboardingInstructions) window.onboardingInstructions.revealAfterWelcome();
        document.dispatchEvent(new CustomEvent('onboardingwelcomecomplete'));
        if (window.onboardingProgress) window.onboardingProgress.record('video_complete', 'bienvenida').catch(function () {});
    });

    video.addEventListener('play',  () => setPlayIcon(true));
    video.addEventListener('pause', () => setPlayIcon(false));

    /* ── Toggle play/pause ───────────────────────────────────────────────── */
    function onbTogglePlay() {
        if (video.ended && videoFinished) {
            resetAll();
            startedOnce = false;
            video.currentTime = 0;
        }
        if (video.paused || video.ended) {
            video.play();
            if (!startedOnce) { if (overlay) overlay.style.display = 'none'; startedOnce = true; }
        } else {
            video.pause();
        }
    }
    window.onbTogglePlay = onbTogglePlay;

    /* ── Volumen ─────────────────────────────────────────────────────────── */
    if (volSlider) {
        volSlider.addEventListener('input', function () { video.volume = parseFloat(this.value); });
    }

    /* ── Pantalla completa ───────────────────────────────────────────────── */
    if (btnFS) {
        btnFS.addEventListener('click', function () {
            const wrapper = document.getElementById('videoWrapper');
            if (!document.fullscreenElement) {
                (wrapper.requestFullscreen || wrapper.webkitRequestFullscreen).call(wrapper);
            } else {
                (document.exitFullscreen || document.webkitExitFullscreen).call(document);
            }
        });
    }

    updateSidebarProgress();

})();
</script>

<script>
(function () {
    var video = document.getElementById('onboardingModuleVideo');
    var empty = document.getElementById('onboardingModuleEmpty');
    var title = document.getElementById('onboardingModuleTitle');
    var modules = document.querySelectorAll('.onboarding-module-item[data-module]');
    var player = document.getElementById('onboardingModulePlayer');
    var moduleList = document.querySelector('.onboarding-module-list');
    var moduleCount = document.getElementById('onboardingModuleCount');
    var onboardingRoot = document.querySelector('.onboarding-module-studio[data-onboarding-user]');
    if (!video || !empty || !title || !player || !moduleList || !moduleCount || !onboardingRoot || !modules.length) return;

    var completedModules = {};
    var moduleMaxReached = 0;
    var moduleVideoFinished = false;

    moduleCount.textContent = modules.length + (modules.length === 1 ? ' módulo' : ' módulos');

    function updateModuleStatus(item) {
        var status = item.querySelector('.module-status');
        if (!status) return;
        var completed = Boolean(completedModules[item.dataset.module]);
        status.classList.toggle('is-complete', completed);
        status.setAttribute('aria-label', completed ? 'Video completado' : 'Video pendiente');
        status.setAttribute('title', completed ? 'Video completado' : 'Video pendiente');
        status.innerHTML = completed ? '<i class="bi bi-check-lg" aria-hidden="true"></i>' : '<i class="bi bi-circle" aria-hidden="true"></i>';
    }

    function saveCompletedModule(moduleKey) {
        if (!moduleKey) return;
        if (window.onboardingProgress) {
            window.onboardingProgress.record('video_complete', moduleKey).catch(function () {});
            return;
        }
        completedModules[moduleKey] = new Date().toISOString();
        try {
            window.localStorage.setItem(progressKey, JSON.stringify({ completed: completedModules }));
        } catch (error) {
            // Si el navegador no permite almacenamiento local, el estado solo dura mientras la página permanezca abierta.
        }
        modules.forEach(updateModuleStatus);
    }

    modules.forEach(updateModuleStatus);

    function applyModuleProgress(progress) {
        completedModules = progress && progress.videos && progress.videos.modulos ? progress.videos.modulos : {};
        var suggested = null;
        modules.forEach(function (item) {
            updateModuleStatus(item);
            item.classList.remove('is-suggested');
            if (!suggested && !completedModules[item.dataset.module]) suggested = item;
        });
        if (suggested) suggested.classList.add('is-suggested');
    }

    document.addEventListener('onboardingprogresschange', function (event) {
        applyModuleProgress(event.detail);
    });
    if (window.onboardingProgress && window.onboardingProgress.get()) applyModuleProgress(window.onboardingProgress.get());

    function syncModuleListHeight() {
        var playerHeight = Math.ceil(player.getBoundingClientRect().height);
        if (!playerHeight) return;
        moduleList.style.height = playerHeight + 'px';
        moduleList.style.maxHeight = playerHeight + 'px';
    }

    syncModuleListHeight();
    window.addEventListener('resize', syncModuleListHeight);

    modules.forEach(function (item) {
        item.addEventListener('click', function () {
            modules.forEach(function (moduleItem) { moduleItem.classList.remove('is-selected'); });
            item.classList.add('is-selected');
            title.innerHTML = '<i class="fa-solid fa-circle-play me-1"></i>' + item.dataset.title;
            empty.style.display = 'none';
            video.style.display = 'block';
            video.dataset.nextModule = item.dataset.nextModule || '';
            video.dataset.nextTitle = item.dataset.nextTitle || '';
            video.dataset.progressModule = item.dataset.module || '';
            moduleMaxReached = 0;
            moduleVideoFinished = false;
            video.src = '/onboarding/video?modulo=' + encodeURIComponent(item.dataset.module);
            video.load();
            requestAnimationFrame(syncModuleListHeight);
            video.play().catch(function () { /* El navegador puede requerir otro clic para iniciar. */ });
        });
    });

    video.addEventListener('ended', function () {
        moduleVideoFinished = true;
        if (video.dataset.nextModule) {
            title.innerHTML = '<i class="fa-solid fa-circle-play me-1"></i>' + video.dataset.nextTitle;
            video.src = '/onboarding/video?modulo=' + encodeURIComponent(video.dataset.nextModule);
            video.dataset.nextModule = '';
            video.dataset.nextTitle = '';
            video.load();
            video.play().catch(function () { /* El navegador puede requerir otro clic para continuar. */ });
            return;
        }

        saveCompletedModule(video.dataset.progressModule);
    });

    video.addEventListener('timeupdate', function () {
        if (!moduleVideoFinished && video.currentTime > moduleMaxReached + .75) {
            video.currentTime = moduleMaxReached;
            return;
        }
        if (video.currentTime > moduleMaxReached) moduleMaxReached = video.currentTime;
    });
    video.addEventListener('seeking', function () {
        if (!moduleVideoFinished && video.currentTime > moduleMaxReached + .5) video.currentTime = moduleMaxReached;
    });
})();
</script>

<script>
(function () {
    var glossarySearch = document.getElementById('onboardingGlossarySearch');
    var glossaryItems = document.querySelectorAll('[data-glossary-item]');
    var documentModal = document.getElementById('onboardingDocumentModal');
    var documentModalTitle = document.getElementById('onboardingDocumentModalTitle');

    if (glossarySearch && glossaryItems.length) {
        glossarySearch.addEventListener('input', function () {
            var query = this.value.trim().toLocaleLowerCase();
            glossaryItems.forEach(function (item) {
                item.style.display = !query || item.textContent.toLocaleLowerCase().indexOf(query) !== -1 ? '' : 'none';
                var glossaryCard = item.querySelector('.onboarding-glossary-item');
                if (glossaryCard) glossaryCard.classList.remove('is-highlighted');
            });

            var selectedItem = Array.prototype.find.call(glossaryItems, function (item) {
                return item.dataset.glossaryKey === query;
            });
            if (!selectedItem) return;

            var selectedCard = selectedItem.querySelector('.onboarding-glossary-item');
            if (selectedCard) selectedCard.classList.add('is-highlighted');
            selectedItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }

    if (documentModal && documentModalTitle) {
        documentModal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            documentModalTitle.textContent = trigger && trigger.dataset.documentTitle ? trigger.dataset.documentTitle : '';
        });
    }

    var diplomaPreviewModal = document.getElementById('onboardingDiplomaPreviewModal');
    var diplomaPreviewText = document.getElementById('onboardingDiplomaPreviewText');
    var diplomaPreviewDownload = document.getElementById('onboardingDiplomaPreviewDownload');
    var diplomaPreviewAccent = document.getElementById('onboardingDiplomaPreviewAccent');
    function showDiplomaPreview(type, score) {
        if (!diplomaPreviewModal || !window.bootstrap) return;
        if (diplomaPreviewText) diplomaPreviewText.textContent = type === 'especializado'
            ? 'Por haber acreditado exitosamente la evaluación especializada de su puesto.'
            : 'Por haber acreditado exitosamente la inducción corporativa sobre políticas de asistencia, nómina y cultura.';
        if (diplomaPreviewDownload) diplomaPreviewDownload.href = '/onboarding/diploma?tipo=' + type;
        if (diplomaPreviewAccent) diplomaPreviewAccent.textContent = type !== 'especializado' || Number(score) === 10 ? 'OTORGADO CON HONORES POR MAXIKASH MÉXICO' : 'ACREDITADO POR MAXIKASH MÉXICO';
        window.bootstrap.Modal.getOrCreateInstance(diplomaPreviewModal).show();
    }

    // Vista reutilizable: la estructura se conserva y cada quiz aporta sus textos.
    function showDiplomaPreview(type, score) {
        if (!diplomaPreviewModal || !window.bootstrap) return;
        var specialized = type === 'especializado';
        var previewTitle = document.getElementById('onboardingDiplomaPreviewTitleText');
        var previewGrant = document.getElementById('onboardingDiplomaPreviewGrant');
        var previewIssuer = document.getElementById('onboardingDiplomaPreviewIssuer');
        var previewValidation = document.getElementById('onboardingDiplomaPreviewValidation');
        if (previewTitle) previewTitle.textContent = specialized ? 'CERTIFICADO DE COMPETENCIA TÉCNICA' : 'CONSTANCIA';
        if (diplomaPreviewAccent) diplomaPreviewAccent.textContent = specialized ? 'PUESTO: EVALUACIÓN ESPECIALIZADA' : 'OTORGADO CON HONORES POR MAXIKASH MÉXICO';
        if (previewGrant) previewGrant.textContent = specialized ? 'Otorgado con éxito a la trayectoria de:' : 'Se otorga con orgullo el presente reconocimiento a:';
        if (diplomaPreviewText) diplomaPreviewText.textContent = specialized
            ? 'Por haber completado y aprobado satisfactoriamente los 10 criterios de evaluación específicos exigidos por los comités operativos de Maxikash para el correcto desarrollo de sus funciones estructurales.'
            : 'Por haber acreditado exitosamente la inducción corporativa sobre políticas de asistencia, nómina, cultura y normativas vigentes del ejercicio 2026.';
        if (previewIssuer) previewIssuer.innerHTML = specialized ? 'Amigo Efectivo S.A. de C.V. - Unidad de Evaluación Fintech' : 'Amigo Efectivo S.A. de C.V.<br>Área de Recursos Humanos';
        if (previewValidation) previewValidation.textContent = specialized ? 'ID DE REGISTRO ÚNICO: MK-CERT-2026' : 'FECHA: ' + new Date().toLocaleDateString('es-MX') + ' | ID: MK-2026-OK';
        if (diplomaPreviewDownload) diplomaPreviewDownload.href = '/onboarding/diploma?tipo=' + type;
        window.bootstrap.Modal.getOrCreateInstance(diplomaPreviewModal).show();
    }

    var quizRoot = document.getElementById('evaluacion');
    var specializedSection = document.getElementById('evaluacion-especializada');
    if (quizRoot && specializedSection && quizRoot.parentNode === specializedSection.parentNode) {
        var quizPair = document.createElement('div');
        quizPair.className = 'row g-4 mb-4 onboarding-quiz-pair';
        quizRoot.parentNode.insertBefore(quizPair, quizRoot);
        var corporateColumn = document.createElement('div');
        corporateColumn.className = 'col-lg-6';
        var specializedColumn = document.createElement('div');
        specializedColumn.className = 'col-lg-6';
        quizPair.appendChild(corporateColumn);
        quizPair.appendChild(specializedColumn);
        corporateColumn.appendChild(quizRoot);
        specializedColumn.appendChild(specializedSection);
        quizRoot.classList.remove('mb-4');
        specializedSection.classList.remove('mb-4');
    }
    var quizForm = document.getElementById('onboardingQuizForm');
    var quizSuccess = document.getElementById('onboardingQuizSuccess');
    var corporateQuizModalBody = document.getElementById('onboardingCorporateQuizModalBody');
    if (corporateQuizModalBody && quizForm && quizSuccess) {
        corporateQuizModalBody.appendChild(quizForm);
        corporateQuizModalBody.appendChild(quizSuccess);
    }
    if (quizRoot && quizForm && quizSuccess) {
        var quizStorageKey = 'sparta.onboarding.quiz.' + (quizRoot.dataset.onboardingQuizSession || 'guest');
        var quizQuestions = quizForm.querySelectorAll('[data-quiz-question]');

        function showQuizSuccess() {
            quizForm.classList.add('d-none');
            quizSuccess.classList.remove('d-none');
            var diplomaTitle = quizSuccess.querySelector('.onboarding-diploma h3');
            if (diplomaTitle) diplomaTitle.textContent = 'DIPLOMA';
            var diplomaAction = quizSuccess.querySelector('.diploma-action');
            if (diplomaAction) {
                diplomaAction.disabled = false;
                diplomaAction.onclick = function () { showDiplomaPreview('corporativo'); };
                diplomaAction.innerHTML = '<i class="fa-solid fa-award me-1"></i>Vista previa del diploma';
            }
            var quizLaunch = document.getElementById('onboardingCorporateQuizLaunch');
            if (quizLaunch) {
                quizLaunch.classList.remove('btn-primary');
                quizLaunch.classList.add('btn-info');
                quizLaunch.removeAttribute('data-bs-toggle');
                quizLaunch.removeAttribute('data-bs-target');
                quizLaunch.onclick = function () { showDiplomaPreview('corporativo'); };
                quizLaunch.innerHTML = '<i class="fa-solid fa-award me-1"></i>Vista previa del diploma';
            }
        }

        function applyCorporateProgress(progress) {
            if (progress && progress.evaluaciones && progress.evaluaciones.corporativa) showQuizSuccess();
        }
        document.addEventListener('onboardingprogresschange', function (event) { applyCorporateProgress(event.detail); });
        if (window.onboardingProgress && window.onboardingProgress.get()) applyCorporateProgress(window.onboardingProgress.get());

        try {
            if (window.localStorage.getItem(quizStorageKey)) showQuizSuccess();
        } catch (error) {
            // La simulación permanece disponible durante la sesión aunque el navegador bloquee almacenamiento local.
        }

        var carouselIndex = 0;
        var carouselControls = document.createElement('div');
        carouselControls.className = 'onboarding-corporate-carousel-controls';
        carouselControls.innerHTML = '<button type="button" class="btn btn-label-secondary" data-quiz-prev>Anterior</button><span class="small text-muted align-self-center" data-quiz-count></span><button type="button" class="btn btn-primary" data-quiz-next>Siguiente</button>';
        quizForm.appendChild(carouselControls);
        var previousButton = carouselControls.querySelector('[data-quiz-prev]');
        var nextButton = carouselControls.querySelector('[data-quiz-next]');
        var countLabel = carouselControls.querySelector('[data-quiz-count]');
        var submitButton = quizForm.querySelector('#onboardingQuizSubmit');
        function renderCarousel() { quizQuestions.forEach(function (question, index) { question.classList.toggle('d-none', index !== carouselIndex); }); previousButton.disabled = carouselIndex === 0; nextButton.classList.toggle('d-none', carouselIndex === quizQuestions.length - 1); submitButton.classList.toggle('d-none', carouselIndex !== quizQuestions.length - 1); countLabel.textContent = (carouselIndex + 1) + ' / ' + quizQuestions.length; }
        previousButton.addEventListener('click', function () { carouselIndex = Math.max(0, carouselIndex - 1); renderCarousel(); });
        nextButton.addEventListener('click', function () { carouselIndex = Math.min(quizQuestions.length - 1, carouselIndex + 1); renderCarousel(); });
        renderCarousel();

        quizQuestions.forEach(function (question) {
            question.addEventListener('change', function () {
                if (question.querySelector('input:checked')) question.classList.remove('is-missing');
            });
        });

        quizForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var firstMissing = null;
            quizQuestions.forEach(function (question) {
                var complete = Boolean(question.querySelector('input:checked'));
                question.classList.toggle('is-missing', !complete);
                if (!complete && !firstMissing) firstMissing = question;
            });

            var quizName = document.getElementById('onboardingQuizName');
            if (quizName && !quizName.value.trim()) {
                quizName.closest('.onboarding-quiz-question').classList.add('is-missing');
                if (!firstMissing) firstMissing = quizName.closest('.onboarding-quiz-question');
            }

            if (firstMissing) {
                firstMissing.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var missingControl = firstMissing.querySelector('input');
                if (missingControl) missingControl.focus({ preventScroll: true });
                return;
            }

            var correctAnswers = ['imss', 'nominas', 'juridico', 'valores', 'correo'];
            var isCorrect = Array.prototype.every.call(quizQuestions, function (question, index) { return question.querySelector('input:checked').value === correctAnswers[index]; });
            if (!isCorrect) {
                var quizError = document.getElementById('onboardingCorporateQuizError');
                if (!quizError) { quizError = document.createElement('div'); quizError.id = 'onboardingCorporateQuizError'; quizError.className = 'alert alert-warning mt-3 mb-0'; quizForm.appendChild(quizError); }
                quizError.textContent = 'Aún hay respuestas incorrectas. Revisa tus respuestas antes de evaluar.';
                return;
            }
            if (window.onboardingProgress) {
                window.onboardingProgress.record('corporate_quiz_complete').then(showQuizSuccess).catch(function () {});
                return;
            }

            try {
                window.localStorage.setItem(quizStorageKey, JSON.stringify({ submittedAt: new Date().toISOString() }));
            } catch (error) {
                // Sin persistencia local, se conserva la simulación hasta que el usuario recargue la página.
            }
            showQuizSuccess();
        });
    }

    var specializedModal = document.getElementById('onboardingSpecializedQuizModal');
    var specializedForm = document.getElementById('onboardingSpecializedQuizForm');
    var specializedSuccess = document.getElementById('onboardingSpecializedQuizSuccess');
    if (specializedModal && specializedForm && specializedSuccess) {
        var specializedStorageKey = 'sparta.onboarding.specialized-quiz.' + (specializedModal.dataset.onboardingSpecializedSession || 'guest');
        var specializedQuestions = specializedForm.querySelectorAll('[data-specialized-question]');
        var specializedRole = document.getElementById('onboardingSpecializedRole');
        var specializedName = document.getElementById('onboardingSpecializedName');
        var specializedProgressSteps = document.querySelectorAll('[data-specialized-progress]');
        var specializedError = document.getElementById('onboardingSpecializedError');
        var specializedCorrectAnswers = ['2', '0', '0', '2', '1', '0', '1', '2', '1', '0'];
        var specializedStarted = false;
        var specializedIndex = 0;
        var specializedSetup = specializedRole.closest('.row');
        var assignedName = specializedModal.dataset.assignedName || 'Colaborador Maxikash';
        var assignedPosition = specializedModal.dataset.assignedPosition || '';
        var assignedQuizType = specializedModal.dataset.specializedQuizType || '';
        if (assignedQuizType) specializedRole.value = assignedQuizType;
        if (specializedName) specializedName.value = assignedName;
        specializedSetup.className = 'onboarding-specialized-assignment border rounded p-3 mb-4';
        specializedSetup.innerHTML = '<div class="small text-uppercase fw-bold text-muted mb-3">Evaluación de puesto</div><div class="mb-3"><span class="d-block small text-muted">Colaborador</span><strong data-specialized-assigned-name></strong></div><div><span class="d-block small text-muted">Puesto asignado</span><strong data-specialized-assigned-position></strong></div>';
        specializedSetup.querySelector('[data-specialized-assigned-name]').textContent = assignedName;
        specializedSetup.querySelector('[data-specialized-assigned-position]').textContent = assignedPosition || 'Puesto no asignado';
        var specializedProgress = document.getElementById('onboardingSpecializedProgress');
        var specializedSubmit = specializedForm.querySelector('button[type="submit"]');
        specializedQuestions.forEach(function (question) { question.classList.add('d-none'); });
        specializedSubmit.classList.add('d-none');
        var specializedStart = document.createElement('button');
        specializedStart.type = 'button'; specializedStart.className = 'btn btn-primary'; specializedStart.textContent = 'Comenzar'; specializedStart.disabled = !assignedQuizType;
        specializedSetup.insertAdjacentElement('afterend', specializedStart);
        var specializedControls = document.createElement('div');
        specializedControls.className = 'onboarding-corporate-carousel-controls d-none';
        specializedControls.innerHTML = '<button type="button" class="btn btn-label-secondary" data-specialized-prev>Anterior</button><span class="small text-muted align-self-center" data-specialized-count></span><button type="button" class="btn btn-primary" data-specialized-next>Siguiente</button>';
        specializedForm.appendChild(specializedControls);
        function renderSpecializedCarousel() { specializedQuestions.forEach(function (question, index) { question.classList.toggle('d-none', index !== specializedIndex); }); specializedControls.querySelector('[data-specialized-prev]').disabled = specializedIndex === 0; specializedControls.querySelector('[data-specialized-next]').classList.toggle('d-none', specializedIndex === specializedQuestions.length - 1); specializedSubmit.classList.toggle('d-none', specializedIndex !== specializedQuestions.length - 1); specializedControls.querySelector('[data-specialized-count]').textContent = (specializedIndex + 1) + ' / ' + specializedQuestions.length; }
        specializedStart.addEventListener('click', function () { if (!specializedRole.value) { specializedRole.classList.add('is-invalid'); return; } specializedStarted = true; specializedSetup.classList.add('d-none'); specializedStart.classList.add('d-none'); specializedProgress.classList.remove('d-none'); specializedControls.classList.remove('d-none'); renderSpecializedCarousel(); });
        specializedControls.querySelector('[data-specialized-prev]').addEventListener('click', function () { specializedIndex = Math.max(0, specializedIndex - 1); renderSpecializedCarousel(); });
        specializedControls.querySelector('[data-specialized-next]').addEventListener('click', function () { specializedIndex = Math.min(specializedQuestions.length - 1, specializedIndex + 1); renderSpecializedCarousel(); });
        specializedProgressSteps.forEach(function (step, index) {
            step.setAttribute('role', 'button');
            step.setAttribute('tabindex', '0');
            step.setAttribute('aria-label', 'Ir a la pregunta ' + (index + 1));
            function goToQuestion() {
                if (!specializedStarted) return;
                specializedIndex = index;
                renderSpecializedCarousel();
            }
            step.addEventListener('click', goToQuestion);
            step.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); goToQuestion(); }
            });
        });

        function updateSpecializedProgress() {
            specializedQuestions.forEach(function (question, index) {
                var step = specializedProgressSteps[index];
                if (step) { step.classList.toggle('is-answered', Boolean(question.querySelector('input:checked'))); step.classList.remove('is-wrong'); }
            });
        }

        function getSpecializedState() {
            try { return JSON.parse(window.sessionStorage.getItem(specializedStorageKey) || '{}'); } catch (error) { return {}; }
        }

        function saveSpecializedState(completed, score) {
            var answers = {};
            specializedQuestions.forEach(function (question, index) {
                var selected = question.querySelector('input:checked');
                if (selected) answers['q' + (index + 1)] = selected.value;
            });
            try {
                window.sessionStorage.setItem(specializedStorageKey, JSON.stringify({
                    role: specializedRole.value,
                    name: specializedName.value,
                    answers: answers,
                    completed: Boolean(completed),
                    score: Number(score || 0),
                    updatedAt: new Date().toISOString()
                }));
            } catch (error) {
                // La vista permanece funcional aunque el navegador bloquee el almacenamiento de sesi&oacute;n.
            }
        }

        function showSpecializedSuccess(score) {
            specializedForm.classList.add('d-none');
            specializedSuccess.classList.remove('d-none');
            var diplomaTitle = specializedSuccess.querySelector('.onboarding-diploma h3');
            if (diplomaTitle) diplomaTitle.textContent = 'DIPLOMA';
            var diplomaKicker = specializedSuccess.querySelector('.diploma-kicker');
            if (diplomaKicker) diplomaKicker.textContent = Number(score) === 10 ? 'OTORGADO CON HONORES POR MAXIKASH MÉXICO' : 'ACREDITADO POR MAXIKASH MÉXICO';
            var diplomaAction = specializedSuccess.querySelector('.diploma-action');
            if (diplomaAction) {
                diplomaAction.disabled = false;
                diplomaAction.onclick = function () { showDiplomaPreview('especializado', score); };
                diplomaAction.innerHTML = '<i class="fa-solid fa-award me-1"></i>Vista previa del diploma';
            }
            var specializedLaunch = document.querySelector('[data-bs-target="#onboardingSpecializedQuizModal"]');
            if (specializedLaunch) {
                specializedLaunch.classList.remove('btn-primary');
                specializedLaunch.classList.add('btn-info');
                specializedLaunch.removeAttribute('data-bs-toggle');
                specializedLaunch.removeAttribute('data-bs-target');
                specializedLaunch.onclick = function () { showDiplomaPreview('especializado', score); };
                specializedLaunch.innerHTML = '<i class="fa-solid fa-award me-1"></i>Vista previa del diploma';
            }
        }

        function applySpecializedProgress(progress) {
            if (progress && progress.evaluaciones && progress.evaluaciones.especializada) showSpecializedSuccess(progress.evaluaciones.especializada_score);
        }
        document.addEventListener('onboardingprogresschange', function (event) { applySpecializedProgress(event.detail); });
        if (window.onboardingProgress && window.onboardingProgress.get()) applySpecializedProgress(window.onboardingProgress.get());
        specializedModal.addEventListener('hide.bs.modal', function (event) {
            if (specializedStarted && !specializedForm.classList.contains('d-none') && !window.confirm('Si sales sin terminar el test, no se guardará avance. ¿Deseas salir?')) event.preventDefault();
        });

        function restoreSpecializedState() {
            var state = getSpecializedState();
            if (state.completed) { showSpecializedSuccess(state.score); return; }
            if (assignedQuizType) specializedRole.value = assignedQuizType;
            if (specializedName) specializedName.value = assignedName;
            Object.keys(state.answers || {}).forEach(function (key) {
                var answer = specializedForm.querySelector('input[name="onboardingSpecializedQ' + key.slice(1) + '"][value="' + state.answers[key] + '"]');
                if (answer) answer.checked = true;
            });
        }

        restoreSpecializedState();
        updateSpecializedProgress();
        specializedForm.addEventListener('input', function () { saveSpecializedState(false); });
        specializedForm.addEventListener('change', function () { saveSpecializedState(false); });
        specializedQuestions.forEach(function (question) {
            question.addEventListener('change', function () {
                if (question.querySelector('input:checked')) question.classList.remove('is-missing');
                updateSpecializedProgress();
            });
        });

        specializedForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var firstMissing = null;
            [specializedRole].forEach(function (control) {
                var complete = Boolean(control.value && control.value.trim());
                control.classList.toggle('is-invalid', !complete);
                if (!complete && !firstMissing) firstMissing = control;
            });
            specializedQuestions.forEach(function (question) {
                var complete = Boolean(question.querySelector('input:checked'));
                question.classList.toggle('is-missing', !complete);
                if (!complete && !firstMissing) firstMissing = question;
            });
            if (firstMissing) {
                firstMissing.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var control = firstMissing.matches('input, select') ? firstMissing : firstMissing.querySelector('input');
                if (control) control.focus({ preventScroll: true });
                return;
            }
            var specializedScore = 0;
            specializedQuestions.forEach(function (question, index) {
                var isCorrect = question.querySelector('input:checked').value === specializedCorrectAnswers[index];
                if (isCorrect) specializedScore++;
                if (specializedProgressSteps[index]) specializedProgressSteps[index].classList.toggle('is-wrong', !isCorrect);
            });
            if (specializedScore < 8) {
                if (specializedError) specializedError.classList.add('is-visible');
                return;
            }
            if (specializedError) specializedError.classList.remove('is-visible');
            if (window.onboardingProgress) {
                window.onboardingProgress.record('specialized_quiz_complete', '', specializedScore).then(function () { showSpecializedSuccess(specializedScore); }).catch(function () {});
                return;
            }
            saveSpecializedState(true, specializedScore);
            showSpecializedSuccess(specializedScore);
        });
    }

    var feedbackForm = document.getElementById('onboardingFeedbackForm');
    var feedbackSuccess = document.getElementById('onboardingFeedbackSuccess');
    if (feedbackForm && feedbackSuccess) {
        feedbackForm.addEventListener('submit', function (event) {
            event.preventDefault();
            function showFeedbackSuccess() { feedbackSuccess.classList.remove('d-none'); }
            if (window.onboardingProgress) window.onboardingProgress.record('feedback_sent').then(showFeedbackSuccess).catch(showFeedbackSuccess);
            else showFeedbackSuccess();
        });
        document.addEventListener('onboardingprogresschange', function (event) {
            if (event.detail && event.detail.feedback) feedbackSuccess.classList.remove('d-none');
        });
    }
})();
</script>
