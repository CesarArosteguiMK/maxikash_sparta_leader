<div id="asg-landing" class="cc-call-center-page reporteria-landing-root">
<div class="card">
    <div class="card">
        <div class="card">
            <div class="row g-0 align-items-center overflow-visible cc-hero-row cc-hero-row--con-mascota cc-hero-block">

                <div class="col-12 col-md-8 cc-hero-text">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?> <i class="fa-solid fa-user-check ms-2 text-primary" aria-hidden="true"></i></h5>
                        <p class="mb-6 mb-md-0">
                            Consulta el <strong>tablero de Asignación</strong> con el mismo formato que Comparativas; las columnas muestran métricas propias del proceso de asignación.
                        </p>
                    </div>
                </div>

                <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end cc-hero-mascot-col">
                    <img src="/assets/img/illustrations/comparativas-mascota.png"
                         class="cc-hero-mascot-floating img-fluid"
                         width="400"
                         height="400"
                         alt="Asignación — ilustración">
                </div>

                <div class="row gy-6 mb-6 g-3 g-lg-4 justify-content-start">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Tablero Proyección</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">Tres ventanas: <strong>semana pasada</strong>, <strong>actual</strong> y <strong>próxima</strong> (martes a lunes). En cada una: <strong>External ID</strong>, <strong>Nombre del gestor</strong> y <strong>Puesto</strong>, más la <strong>proyección de cambios</strong>.</p>
                                    </div>
                                    <div class="mb-0 mt-3">
                                        <a href="/analitica/asignacionTablero" class="btn btn-primary w-100">
                                            <i class="fa-solid fa-table-columns me-1"></i>Ver tablero
                                        </a>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png?ga=GA1.1.191732613.1764875703" alt="Asignación illustration">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Tablero dos ventanas</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">Solo <strong>dos</strong> columnas (martes a lunes): las mismas que en el tablero de tres son <strong>semana pasada</strong> y <strong>semana actual</strong> (sin la columna «próxima» ni cambio proyectado). El bucket de la semana pasada sale del histórico de Segundómetro.</p>
                                    </div>
                                    <div class="mb-0 mt-3">
                                        <a href="/analitica/asignacionTableroDos" class="btn btn-primary w-100">
                                            <i class="fa-solid fa-table-columns me-1"></i>Ver tablero
                                        </a>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png?ga=GA1.1.191732613.1764875703" alt="Asignación — dos ventanas">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</div><!-- /#asg-landing -->
