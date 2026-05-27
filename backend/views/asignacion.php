<div id="asg-landing" class="cc-call-center-page reporteria-landing-root">
  <div class="card">
    <svg xmlns="http://www.w3.org/2000/svg" class="asg-svg-defs" aria-hidden="true" focusable="false">
      <defs>
        <linearGradient id="asg-icon-gradient" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
          <stop offset="0" stop-color="#12c2e9"></stop>
          <stop offset="0.5" stop-color="#2f80ed"></stop>
          <stop offset="1" stop-color="#7b61ff"></stop>
        </linearGradient>
      </defs>
    </svg>
    <div class="card">
      <div class="card">
        <div class="row g-0 align-items-center overflow-visible cc-hero-row cc-hero-row--con-mascota cc-hero-block">
          <div class="col-12 col-md-8 cc-hero-text">
            <div class="card-body">
              <h5 class="card-title text-primary mb-3">
                HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?>
                <i class="fa-solid fa-user-check ms-2 text-primary" aria-hidden="true"></i>
              </h5>
              <p class="mb-6 mb-md-0">
                Consulta direcciones por cr&eacute;dito y tableros de asignaci&oacute;n con el mismo estilo operativo del m&oacute;dulo de anal&iacute;tica.
              </p>
            </div>
          </div>

          <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end cc-hero-mascot-col">
            <img src="/assets/img/illustrations/comparativas-mascota.png"
                 class="cc-hero-mascot-floating img-fluid"
                 width="400"
                 height="400"
                 alt="Asignacion">
          </div>

          <div class="row gy-6 mb-6 g-3 g-lg-4 justify-content-start">
            <div class="col-12 col-md-6 col-lg-4">
              <div class="card shadow-none bg-label-primary h-100">
                <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                  <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                    <div class="card-title">
                      <h5 class="text-primary mb-2">Direcciones por cr&eacute;dito</h5>
                      <p class="text-body w-sm-80 app-academy-xl-100">Busca por ID de cr&eacute;dito, agrega nuevas direcciones y arrastra la direcci&oacute;n correcta al primer lugar para marcarla como principal.</p>
                    </div>
                    <div class="mb-0 mt-3">
                      <a href="/analitica/asignacionDirecciones" class="btn btn-primary w-100">
                        <i class="fa-solid fa-location-dot me-1"></i>Gestionar direcciones
                      </a>
                    </div>
                  </div>
                  <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end align-items-start align-self-start h-px-150 mb-4 mb-sm-0 flex-shrink-0 asg-icon-slot">
                    <span class="scaleX-n1-rtl asg-icon-frame" aria-hidden="true">
                      <svg class="asg-icon-svg" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" focusable="false">
                        <path d="M10 18l14-6 16 6 14-6v34l-14 6-16-6-14 6z"></path>
                        <path d="M24 12v34"></path>
                        <path d="M40 18v34"></path>
                        <path d="M32 9c-6.1 0-11 4.9-11 11 0 8.8 11 20 11 20s11-11.2 11-20c0-6.1-4.9-11-11-11z"></path>
                        <circle cx="32" cy="20" r="3"></circle>
                      </svg>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
              <div class="card shadow-none bg-label-secondary h-100 opacity-75">
                <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                  <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                    <div class="card-title">
                      <h5 class="text-muted mb-2">Tableros de asignaci&oacute;n</h5>
                      <p class="text-body w-sm-80 app-academy-xl-100">Consulta los tableros de proyecci&oacute;n en tres ventanas o dos ventanas para revisar continuidad, cambios y asignaciones.</p>
                    </div>
                    <div class="mb-0 mt-3 d-grid gap-2">
                      <button type="button" class="btn btn-secondary w-100" disabled aria-disabled="true">
                        <i class="fa-solid fa-table-columns me-1"></i>Tablero proyecci&oacute;n
                      </button>
                      <button type="button" class="btn btn-outline-secondary w-100" disabled aria-disabled="true">
                        <i class="fa-solid fa-table-list me-1"></i>Dos ventanas
                      </button>
                      <span class="text-muted small text-center">Funci&oacute;n en preparaci&oacute;n</span>
                    </div>
                  </div>
                  <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end align-items-start align-self-start h-px-150 mb-4 mb-sm-0 flex-shrink-0 asg-icon-slot">
                    <span class="scaleX-n1-rtl asg-icon-frame asg-icon-frame-muted" aria-hidden="true">
                      <svg class="asg-icon-svg" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" focusable="false">
                        <rect x="12" y="14" width="40" height="36" rx="6"></rect>
                        <path d="M20 10v10"></path>
                        <path d="M44 10v10"></path>
                        <path d="M12 26h40"></path>
                        <path d="M25 34v10"></path>
                        <path d="M39 34v10"></path>
                      </svg>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .asg-svg-defs {
    position: absolute;
    width: 0;
    height: 0;
    overflow: hidden;
  }

  #asg-landing .asg-icon-slot {
    width: 100% !important;
    height: 7.5rem;
    flex: 0 0 7.5rem;
    align-self: stretch !important;
    justify-content: center !important;
    margin-bottom: 1rem !important;
  }

  #asg-landing .asg-icon-frame {
    width: 7.5rem;
    height: 7.5rem;
    min-width: 7.5rem;
    min-height: 7.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: visible;
  }

  #asg-landing .asg-icon-svg {
    width: 100%;
    height: 100%;
    display: block;
    fill: none;
    stroke: url(#asg-icon-gradient);
    stroke-width: 2.6;
    stroke-linecap: round;
    stroke-linejoin: round;
  }

  #asg-landing .asg-icon-frame-muted {
    opacity: .5;
  }
</style>
