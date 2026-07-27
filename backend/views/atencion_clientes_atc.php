<?php
$atcAbrirSolicitud = ($atc_pestana_inicial ?? 'retenciones') === 'solicitud';
$solicitud_en_modal = true;
?>
<style>
.atc-unificado { --atc-azul:#1d4ed8; --atc-marino:#173d78; }
.atc-unificado__hero {
    background:linear-gradient(130deg,var(--atc-marino),var(--atc-azul) 68%,#38bdf8);
    border-radius:1rem; color:#fff; padding:1.45rem 1.7rem; margin-bottom:1.15rem;
    box-shadow:0 12px 28px rgba(30,64,175,.18);
}
.atc-unificado__hero h3 { color:#fff; font-weight:800; margin:0 0 .35rem; }
.atc-unificado__hero p { margin:0; opacity:.86; max-width:720px; }
.atc-unificado .atc-embedded-retenciones > .container-fluid { padding:0 !important; }
.atc-unificado .atc-embedded-retenciones .ac-header-gradient { display:none !important; }
.atc-modal-solicitud .modal-content { border:0; border-radius:1rem; overflow:hidden; }
.atc-modal-solicitud .modal-header { background:linear-gradient(130deg,var(--atc-marino),var(--atc-azul)); color:#fff; border:0; }
.atc-modal-solicitud .btn-close { filter:invert(1); }
.atc-modal-solicitud .modal-body { background:#f8fafc; padding:1.25rem; }
.atc-modal-solicitud .sa-atc { padding:0 !important; }
.atc-modal-solicitud .sa-hero,
.atc-modal-solicitud .col-xl-7 { display:none !important; }
.atc-modal-solicitud .col-xl-5 { width:100%; flex:0 0 100%; }
.atc-modal-solicitud .sa-panel { box-shadow:none; }
@media (max-width:575px) {
    .atc-unificado__hero { padding:1.15rem; }
    .atc-modal-solicitud .modal-body { padding:.75rem; }
}
</style>

<div class="atc-unificado container-fluid py-4">
    <section class="atc-unificado__hero d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="text-uppercase small fw-bold opacity-75 mb-1">Motos adjudicadas</div>
            <h3><i class="fa-solid fa-headset me-2"></i>ATC - Atencion a Clientes</h3>
            <p>Consulta y dicta retenciones desde una sola bandeja.</p>
        </div>
        <button type="button" class="btn btn-light fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#atc-modal-solicitud">
            <i class="fa-solid fa-plus me-1"></i>Nueva solicitud
        </button>
    </section>

    <section class="atc-embedded-retenciones">
        <?php require __DIR__ . '/atencion_clientes_consulta.php'; ?>
    </section>
</div>

<div class="modal fade atc-modal-solicitud" id="atc-modal-solicitud" tabindex="-1" aria-labelledby="atc-modal-solicitud-titulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="atc-modal-solicitud-titulo"><i class="fa-solid fa-file-circle-plus me-2"></i>Nueva solicitud de adjudicacion</h5>
                    <small class="opacity-75">Busca el credito y registra los datos de entrega.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <?php require __DIR__ . '/solicitud_adjudicacion_atc.php'; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('atc-modal-solicitud');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    <?php if ($atcAbrirSolicitud): ?>
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    <?php endif; ?>
});
</script>
