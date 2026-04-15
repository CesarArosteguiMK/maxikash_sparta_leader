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
</style>


<!-- ── Main grid ──────────────────────────────────────────────────────────── -->
<div class="row g-6">

    <!-- ════════════════════════════════ LEFT COLUMN ════════════════════════ -->
    <div class="col-12 col-lg-8">

        <!-- Course title & meta ─────────────────────────────────────────── -->
        <div class="card mb-4">
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
        <div class="card mb-4">
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
                        $videoSrc = '/onboarding/video';
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
                 onerror="this.src='https://media.vogue.mx/photos/5dfb443c38e2b300084b20b7/2:3/w_2560%2Cc_limit/GettyImages-818664.jpg'" />
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