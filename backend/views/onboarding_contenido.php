<?php
/**
 * Onboarding — Vista principal
 * Basada en: https://demos.themeselection.com/sneat-bootstrap-html-admin-template/html/vertical-menu-template/app-academy-course-details.html
 */
?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="/assets/vendor/css/pages/app-academy-details.css" />
<link rel="stylesheet" href="/assets/vendor/css/pages/app-academy.css" />

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
.onboarding-welcome-icon { width: 2.7rem; height: 2.7rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .7rem; background: var(--bs-primary); color: #fff; box-shadow: 0 .35rem .8rem rgba(var(--bs-primary-rgb), .25); }
.onboarding-welcome-copy { max-width: 620px; }
.onboarding-welcome-copy p { line-height: 1.55; }
.onboarding-route-card { height: 100%; border: 1px solid var(--bs-border-color); border-radius: .6rem; background: var(--bs-body-bg); transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
.onboarding-route-card.is-current { border-color: rgba(var(--bs-primary-rgb), .45); box-shadow: 0 .25rem .8rem rgba(var(--bs-primary-rgb), .08); }
.onboarding-route-card:hover { transform: translateY(-2px); border-color: rgba(var(--bs-primary-rgb), .5); }
.onboarding-route-icon { width: 2.35rem; height: 2.35rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .55rem; background: rgba(var(--bs-primary-rgb), .1); color: var(--bs-primary); }
.onboarding-route-card.is-pending { opacity: .72; }
.onboarding-route-card .btn { white-space: nowrap; }
.onboarding-hero-video { border: 1px solid rgba(var(--bs-primary-rgb), .25); border-radius: .75rem; overflow: hidden; background: var(--bs-body-bg); box-shadow: 0 .35rem 1rem rgba(67, 89, 113, .12); }
.onboarding-hero-video .course-video-wrapper { border-radius: 0; }
.onboarding-hero-video .video-progress-track { background: rgba(var(--bs-primary-rgb), .12); }
.onboarding-instructions { border: 1px solid rgba(var(--bs-primary-rgb), .18); border-top: 3px solid var(--bs-primary); background: linear-gradient(100deg, rgba(var(--bs-primary-rgb), .05), var(--bs-body-bg) 55%); }
.onboarding-instruction-step { height: 100%; padding: .65rem; border-radius: .55rem; }
.onboarding-instruction-step .step-icon { width: 2rem; height: 2rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(var(--bs-primary-rgb), .1); color: var(--bs-primary); flex: 0 0 auto; }
.onboarding-instruction-step.is-progress .step-icon { background: rgba(var(--bs-success-rgb), .13); color: var(--bs-success); }
.onboarding-instruction-step.is-quiz .step-icon { background: rgba(var(--bs-warning-rgb), .17); color: #b76e00; }
.onboarding-module-studio { border: 1px solid rgba(var(--bs-primary-rgb), .22); overflow: hidden; }
.onboarding-module-player { min-height: 330px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; border-radius: .65rem; background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .11), rgba(var(--bs-primary-rgb), .025)); border: 1px dashed rgba(var(--bs-primary-rgb), .45); }
.onboarding-module-player .player-icon { width: 4rem; height: 4rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 1rem; background: var(--bs-primary); color: #fff; font-size: 1.5rem; box-shadow: 0 .45rem 1rem rgba(var(--bs-primary-rgb), .22); }
.onboarding-module-list { max-height: 382px; overflow-y: auto; }
.onboarding-module-item { display: flex; align-items: center; gap: .7rem; padding: .75rem; border: 1px solid var(--bs-border-color); border-radius: .55rem; background: var(--bs-body-bg); transition: border-color .2s ease, background .2s ease, transform .2s ease; }
.onboarding-module-item[type="button"] { color: inherit; cursor: pointer; }
.onboarding-module-item[type="button"]:hover, .onboarding-module-item[type="button"].is-selected { border-color: rgba(var(--bs-primary-rgb), .48); background: rgba(var(--bs-primary-rgb), .07); transform: translateX(2px); }
.onboarding-module-item + .onboarding-module-item { margin-top: .55rem; }
.onboarding-module-item.is-ready { border-color: rgba(var(--bs-primary-rgb), .45); background: rgba(var(--bs-primary-rgb), .06); }
.onboarding-module-item .module-number { width: 2rem; height: 2rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .5rem; background: rgba(var(--bs-primary-rgb), .1); color: var(--bs-primary); font-size: .8rem; font-weight: 700; flex: 0 0 auto; }
.onboarding-roadmap { border: 1px solid rgba(var(--bs-success-rgb), .25); }
.onboarding-roadmap-stage { height: 100%; border: 1px solid var(--bs-border-color); border-radius: .65rem; padding: 1rem; background: var(--bs-body-bg); }
.onboarding-roadmap-stage .stage-label { display: inline-block; padding: .25rem .55rem; border-radius: 999px; background: rgba(var(--bs-primary-rgb), .1); color: var(--bs-primary); font-size: .7rem; font-weight: 700; text-transform: uppercase; }
.onboarding-roadmap-stage.is-first .stage-label { background: rgba(var(--bs-success-rgb), .13); color: var(--bs-success); }
.onboarding-roadmap-stage.is-adaptation .stage-label { background: rgba(var(--bs-primary-rgb), .13); color: var(--bs-primary); }
.onboarding-roadmap-stage.is-autonomy .stage-label { background: rgba(105, 108, 255, .14); color: #696cff; }
.onboarding-culture-card { height: 100%; border: 1px solid var(--bs-border-color); border-top: 3px solid var(--bs-primary); }
.onboarding-culture-card.is-vision { border-top-color: var(--bs-success); }
.onboarding-value { height: 100%; padding: 1rem; border-radius: .6rem; background: rgba(var(--bs-primary-rgb), .045); border: 1px solid rgba(var(--bs-primary-rgb), .13); }
.onboarding-identity-badge { display: inline-flex; align-items: center; gap: .35rem; padding: .42rem .82rem; border: 1px solid rgba(67, 89, 113, .28); border-radius: 999px; background: #eef6ed; color: #435971; font-size: .72rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; box-shadow: 0 .15rem .35rem rgba(67, 89, 113, .1); }
.onboarding-identity-badge i { color: #71dd37; }
@media (max-width: 575.98px) { .onboarding-hero .card-body { padding: 1.25rem !important; } }
</style>


<!-- ── Main grid ──────────────────────────────────────────────────────────── -->
<!-- Portal: primera capa del prototipo, adaptada al sistema visual de Sparta. -->
<section class="card onboarding-hero mb-4">
    <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="onboarding-kicker mb-2"><i class="fa-solid fa-graduation-cap me-1"></i>Portal de onboarding</div>
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
                    <div id="onboardingVideoMount"></div>
                </div>
            </div>
        </div>
        <div class="onboarding-instructions rounded p-3 p-lg-4 mt-4">
            <div class="d-flex align-items-center gap-2 mb-3"><span class="onboarding-route-icon"><i class="fa-solid fa-list-check"></i></span><h5 class="mb-0 text-primary">Instrucciones para completar tu Inducci&oacute;n</h5></div>
            <div class="row g-3">
                <div class="col-lg-4"><div class="onboarding-instruction-step d-flex gap-3"><span class="step-icon"><i class="fa-solid fa-play"></i></span><div><div class="fw-semibold mb-1 text-primary">Paso 1</div><small class="text-muted">Visualiza el video institucional superior y los 9 m&oacute;dulos tem&aacute;ticos situados justo abajo.</small></div></div></div>
                <div class="col-lg-4"><div class="onboarding-instruction-step is-progress d-flex gap-3"><span class="step-icon"><i class="fa-solid fa-circle-check"></i></span><div><div class="fw-semibold mb-1 text-success">Paso 2</div><small class="text-muted">Al concluir cada video, m&aacute;rcalo como completado para actualizar tu barra de progreso en tiempo real.</small></div></div></div>
                <div class="col-lg-4"><div class="onboarding-instruction-step is-quiz d-flex gap-3"><span class="step-icon"><i class="fa-solid fa-award"></i></span><div><div class="fw-semibold mb-1 text-warning">Paso 3</div><small class="text-muted">Responde de forma correcta el Quiz final para desbloquear y descargar tu Diploma Digital corporativo.</small></div></div></div>
            </div>
        </div>
    </div>
</section>

<!-- Preparado para los videos por modulo: se activara al cargar el catalogo real. -->
<section class="card onboarding-module-studio mb-4">
    <div class="card-header bg-transparent border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div><h5 class="mb-1"><i class="fa-solid fa-film text-primary me-2"></i>Contenido del Onboarding</h5><small class="text-muted">Selecciona un m&oacute;dulo para ver su video de inducci&oacute;n.</small></div>
        <span class="badge bg-label-primary">9 m&oacute;dulos</span>
    </div>
    <div class="card-body p-3 p-lg-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="onboarding-module-player p-0 overflow-hidden" id="onboardingModulePlayer">
                    <div class="w-100 px-3 py-2 border-bottom bg-label-primary small fw-semibold text-primary" id="onboardingModuleTitle"><i class="fa-solid fa-circle-play me-1"></i>Selecciona un m&oacute;dulo</div>
                    <video class="w-100 bg-black" id="onboardingModuleVideo" controls playsinline preload="metadata" style="display:none; aspect-ratio:16 / 9;"></video>
                    <div class="p-4 p-lg-5" id="onboardingModuleEmpty"><span class="player-icon mb-3"><i class="fa-solid fa-video"></i></span><h5 class="mb-2">Selecciona un m&oacute;dulo para iniciar</h5><p class="text-muted mb-0 px-4">Elige uno de los videos de la lista para reproducirlo aqu&iacute;.</p></div>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted"><span><i class="fa-solid fa-circle-info text-primary me-1"></i>Selecciona los videos en el orden recomendado.</span><span class="badge bg-label-success"><i class="fa-solid fa-circle-play me-1"></i>Videos disponibles</span></div>
            </div>
            <div class="col-lg-4">
                <div class="onboarding-module-list pe-1">
                    <button type="button" class="onboarding-module-item is-ready w-100 text-start" data-module="legacyapp" data-title="LegacyApp"><span class="module-number">1</span><span class="flex-grow-1"><span class="fw-semibold d-block">LegacyApp</span><small class="text-muted">Video disponible</small></span><span class="badge bg-label-success">Ver</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="asistencia" data-title="Asistencia"><span class="module-number">2</span><span class="flex-grow-1"><span class="fw-semibold d-block">Asistencia</span><small class="text-muted">Video disponible</small></span><span class="badge bg-label-success">Ver</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="nomina" data-title="Pago de N&oacute;mina"><span class="module-number">3</span><span class="flex-grow-1"><span class="fw-semibold d-block">Pago de N&oacute;mina</span><small class="text-muted">Video disponible</small></span><span class="badge bg-label-success">Ver</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="bonos" data-title="Bonos e Incentivos"><span class="module-number">4</span><span class="flex-grow-1"><span class="fw-semibold d-block">Bonos e Incentivos</span><small class="text-muted">Video disponible</small></span><span class="badge bg-label-success">Ver</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="recibos_nomina" data-title="Recibos de N&oacute;mina"><span class="module-number">5</span><span class="flex-grow-1"><span class="fw-semibold d-block">Recibos de N&oacute;mina</span><small class="text-muted">Video disponible</small></span><span class="badge bg-label-success">Ver</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="cambio_cuenta" data-title="Cambio de Cuenta"><span class="module-number">6</span><span class="flex-grow-1"><span class="fw-semibold d-block">Cambio de Cuenta</span><small class="text-muted">Video disponible</small></span><span class="badge bg-label-success">Ver</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="incapacidades" data-title="Incidencias M&eacute;dicas"><span class="module-number">7</span><span class="flex-grow-1"><span class="fw-semibold d-block">Incidencias M&eacute;dicas</span><small class="text-muted">Video disponible</small></span><span class="badge bg-label-success">Ver</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="cultura" data-next-module="cultura_corporativa" data-next-title="Cultura corporativa" data-title="Cultura y Valores"><span class="module-number">8</span><span class="flex-grow-1"><span class="fw-semibold d-block">Cultura y Valores</span><small class="text-muted">2 videos disponibles</small></span><span class="badge bg-label-success">Ver</span></button>
                    <button type="button" class="onboarding-module-item w-100 text-start" data-module="cierre_induccion" data-title="Cierre de Inducci&oacute;n"><span class="module-number">9</span><span class="flex-grow-1"><span class="fw-semibold d-block">Cierre de Inducci&oacute;n</span><small class="text-muted">Video disponible</small></span><span class="badge bg-label-success">Ver</span></button>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="card onboarding-roadmap mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start mb-3"><div><h5 class="mb-1"><i class="fa-solid fa-route text-success me-2"></i>Tu Ruta de Integraci&oacute;n de 30 D&iacute;as</h5><p class="text-muted small mb-0">Sigue esta planeaci&oacute;n paso a paso para asegurar un inicio espectacular en la compa&ntilde;&iacute;a.</p></div><span class="badge bg-label-success">Roadmap</span></div>
        <div class="row g-3">
            <div class="col-lg-4"><div class="onboarding-roadmap-stage is-first"><span class="stage-label">D&iacute;a 1: Configuraci&oacute;n</span><ul class="small mb-0 mt-3 ps-3"><li>Completa el portal de Onboarding.</li><li>Configura cuentas y herramientas internas.</li><li>Presentaci&oacute;n oficial con tu l&iacute;der directo.</li></ul></div></div>
            <div class="col-lg-4"><div class="onboarding-roadmap-stage is-adaptation"><span class="stage-label">Semana 1: Adaptaci&oacute;n</span><ul class="small mb-0 mt-3 ps-3"><li>Sesiones de acompa&ntilde;amiento con el equipo.</li><li>Comprensi&oacute;n de objetivos y m&eacute;tricas.</li><li>Primera sesi&oacute;n de preguntas con tu supervisor.</li></ul></div></div>
            <div class="col-lg-4"><div class="onboarding-roadmap-stage is-autonomy"><span class="stage-label">Mes 1: Autonom&iacute;a</span><ul class="small mb-0 mt-3 ps-3"><li>Ejecuci&oacute;n aut&oacute;noma de funciones diarias.</li><li>Retroalimentaci&oacute;n de los primeros 30 d&iacute;as.</li><li>Alineaci&oacute;n de objetivos del siguiente trimestre.</li></ul></div></div>
        </div>
    </div>
</section>

<section class="mb-4">
    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="card onboarding-culture-card"><div class="card-body"><span class="onboarding-route-icon mb-3"><i class="fa-solid fa-bullseye"></i></span><h5 class="text-primary">Misi&oacute;n</h5><p class="mb-0">Impulsar el crecimiento financiero de nuestros clientes mediante soluciones innovadoras, accesibles y eficientes, respaldadas por tecnolog&iacute;a, cercan&iacute;a y un equipo comprometido.</p></div></div></div>
        <div class="col-lg-6"><div class="card onboarding-culture-card is-vision"><div class="card-body"><span class="onboarding-route-icon mb-3"><i class="fa-solid fa-eye"></i></span><h5 class="text-success">Visi&oacute;n</h5><p class="mb-0">Ser una fintech referente en Latinoam&eacute;rica, reconocida por transformar la experiencia financiera a trav&eacute;s de innovaci&oacute;n, expansi&oacute;n estrat&eacute;gica y excelencia operativa.</p></div></div></div>
    </div>
    <div class="card onboarding-culture-card"><div class="card-body p-4"><div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><div><h5 class="mb-1"><i class="fa-solid fa-heart text-danger me-2"></i>Nuestros Valores</h5><small class="text-muted">Los principios fundamentales alineados a nuestra cultura corporativa.</small></div><span class="onboarding-identity-badge"><i class="fa-solid fa-fingerprint"></i>Identidad</span></div><div class="row g-3"><div class="col-lg-4"><div class="onboarding-value"><h6 class="text-primary"><i class="fa-solid fa-lightbulb me-1"></i>Innovaci&oacute;n</h6><small>Buscamos soluciones tecnol&oacute;gicas ingeniosas para mejorar las finanzas de nuestros clientes y hacer m&aacute;s eficientes nuestros procesos.</small></div></div><div class="col-lg-4"><div class="onboarding-value"><h6 class="text-primary"><i class="fa-solid fa-people-group me-1"></i>Cercan&iacute;a</h6><small>Creemos en el factor humano y en una comunicaci&oacute;n directa, transparente y emp&aacute;tica con colaboradores y usuarios.</small></div></div><div class="col-lg-4"><div class="onboarding-value"><h6 class="text-primary"><i class="fa-solid fa-scale-balanced me-1"></i>Disciplina</h6><small>El orden, compromiso diario y apego a nuestras pol&iacute;ticas sostienen nuestro crecimiento profesional.</small></div></div></div></div></div>
</section>

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

        <!-- Video player ─────────────────────────────────────────────────── -->
        <div id="onboarding-video-card" class="d-none" aria-hidden="true">
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
    if (!video || !empty || !title || !modules.length) return;

    modules.forEach(function (item) {
        item.addEventListener('click', function () {
            modules.forEach(function (moduleItem) { moduleItem.classList.remove('is-selected'); });
            item.classList.add('is-selected');
            title.innerHTML = '<i class="fa-solid fa-circle-play me-1"></i>' + item.dataset.title;
            empty.style.display = 'none';
            video.style.display = 'block';
            video.dataset.nextModule = item.dataset.nextModule || '';
            video.dataset.nextTitle = item.dataset.nextTitle || '';
            video.src = '/onboarding/video?modulo=' + encodeURIComponent(item.dataset.module);
            video.load();
            video.play().catch(function () { /* El navegador puede requerir otro clic para iniciar. */ });
        });
    });

    video.addEventListener('ended', function () {
        if (!video.dataset.nextModule) return;
        title.innerHTML = '<i class="fa-solid fa-circle-play me-1"></i>' + video.dataset.nextTitle;
        video.src = '/onboarding/video?modulo=' + encodeURIComponent(video.dataset.nextModule);
        video.dataset.nextModule = '';
        video.dataset.nextTitle = '';
        video.load();
        video.play().catch(function () { /* El navegador puede requerir otro clic para continuar. */ });
    });
})();
</script>

<script>
(function () {
    var tarjetaVideo = document.getElementById('onboarding-video-card');
    var destino = document.getElementById('onboardingVideoMount');
    if (!tarjetaVideo || !destino) return;

    var cuerpoVideo = tarjetaVideo.querySelector('.card-body');
    if (!cuerpoVideo) return;
    destino.appendChild(cuerpoVideo);
})();
</script>
