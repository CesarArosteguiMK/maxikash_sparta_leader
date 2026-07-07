<style>
    .avmigrado-shell {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .avmigrado-panel {
        width: min(100%, 48rem);
        border: 1px solid #dbe4ef;
        background: #fff;
        border-radius: .5rem;
        padding: 1.25rem;
    }
    .avmigrado-icon {
        width: 3rem;
        height: 3rem;
        border-radius: .5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff7ed;
        color: #c2410c;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .avmigrado-flow {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: .5rem;
        padding: .85rem;
    }
    .avmigrado-step {
        display: flex;
        align-items: center;
        gap: .65rem;
        color: #334155;
        font-size: .88rem;
        font-weight: 700;
    }
    .avmigrado-step i {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #dbeafe;
        color: #1d4ed8;
        flex-shrink: 0;
    }
    @media (max-width: 575.98px) {
        .avmigrado-panel {
            padding: 1rem;
        }
        .avmigrado-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="avmigrado-shell">
    <section class="avmigrado-panel shadow-sm">
        <div class="d-flex align-items-start gap-3 mb-3">
            <span class="avmigrado-icon">
                <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
            </span>
            <div>
                <div class="text-muted small fw-bold text-uppercase">Almacen Virtual</div>
                <h4 class="mb-1">Evidencias y Codigo vive en MotoTrack</h4>
                <div class="text-muted">
                    La captura de evidencias y el codigo de validacion se operan desde la app movil.
                    En Sparta este paso queda deshabilitado y se conserva solo como respaldo tecnico.
                </div>
            </div>
        </div>

        <div class="avmigrado-flow mb-3">
            <div class="avmigrado-step mb-2">
                <i class="fa-solid fa-camera" aria-hidden="true"></i>
                Almacenista captura evidencias desde MotoTrack.
            </div>
            <div class="avmigrado-step mb-2">
                <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                Recepcion de Almacen revisa lo recibido y valida el codigo de ingreso.
            </div>
            <div class="avmigrado-step">
                <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
                La unidad pasa a revision mecanica administrativa.
            </div>
        </div>

        <div class="avmigrado-actions d-flex gap-2 flex-wrap justify-content-end">
            <a href="/MotosAdjudicadas/almacenVirtual" class="btn btn-label-secondary">
                <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Almacen Virtual
            </a>
            <a href="/MotosAdjudicadas/recepcionAlmacen" class="btn btn-success">
                <i class="fa-solid fa-clipboard-check me-1" aria-hidden="true"></i>Ir a recepcion
            </a>
        </div>
    </section>
</div>
