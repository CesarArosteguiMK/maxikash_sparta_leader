<svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" aria-hidden="true">
    <defs>
        <linearGradient id="pp-icon-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#00B4DB"/>
            <stop offset="100%" style="stop-color:#6E48AA"/>
        </linearGradient>
    </defs>
</svg>

<style>
.pp-icon-svg { height: 150px; width: auto; max-width: 100%; stroke: url(#pp-icon-gradient); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
</style>

<div class="card">
    <div>
        <div class="card">
            <div class="row g-0 align-items-center">

                <!-- Texto -->
                <div class="col-12 col-md-8">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper($_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?> <i class="fa-solid fa-calendar-week ms-2 text-primary" aria-hidden="true"></i></h5>
                        <p class="mb-6">
                            Créditos con <strong>primer vencimiento el lunes de cierre</strong>: revisa la distribución por bucket de nacimiento, el avance al corte actual (current + recuperados vs pendientes) y la efectividad por territorial, zonal y gestor.
                        </p>
                    </div>
                </div>

                <!-- Imagen -->
                <div class="col-12 col-md-4">
                    <div class="card-body ps-md-2 pe-5 text-end">
                        <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                             class="img-fluid scaleX-n1-rtl"
                             alt="View Badge User">
                    </div>
                </div>

                <div class="row gy-6 mb-6">
                    <div class="col-lg-6">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Primeros pagos semana actual</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">Créditos cuyo primer vencimiento cayó el lunes pasado. Visualiza la matriz de efectividad, distribución de nacimiento y corte, y el seguimiento por jerarquía.</p>
                                    </div>
                                    <div class="mb-0">
                                        <a href="/reporteria/VencimientosLunes" class="btn btn-primary">
                                            <i class="fa fa-eye me-1"></i>Visualizar
                                        </a>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <svg class="pp-icon-svg scaleX-n1-rtl" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" aria-hidden="true">
                                        <rect x="8" y="6" width="48" height="44" rx="4" ry="4"/>
                                        <path d="M8 18h48M20 6v8M44 6v8"/>
                                        <path d="M22 32l6 6 14-14"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Primeros pagos siguiente semana</h5>
                                        <p class="text-body app-academy-sm-60 app-academy-xl-100">Vista prevista para créditos con primer vencimiento en el próximo lunes de cierre. En desarrollo.</p>
                                    </div>
                                    <div class="mb-0">
                                        <button type="button" class="btn btn-sm btn-secondary" disabled title="En construcción">
                                            <i class="fa fa-hourglass-half me-1"></i>Próximamente
                                        </button>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <svg class="pp-icon-svg scaleX-n1-rtl" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" aria-hidden="true">
                                        <rect x="6" y="8" width="44" height="40" rx="4" ry="4"/>
                                        <path d="M6 22h44M22 8v10M38 8v10"/>
                                        <path d="M52 36l6 6-6 6M58 42H42"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
