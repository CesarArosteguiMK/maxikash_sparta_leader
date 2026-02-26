<div class="card">
    <div >
        <div class="card">
            <div class="row g-0 align-items-center"> <!-- quitar gutters y centrar vertical -->

                <!-- Texto -->
                <div class="col-12 col-md-8">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= $_SESSION['usuario_nombre']; ?> <i class="fa-solid fa-phone ms-2 reporteria-call-easter-icon" id="reporteriaCallEaster" aria-hidden="true"></i></h5>
                        <p class="mb-6">
                            Obtén tus reportes según el día de consulta y descubre toda la información actualizada del Mega Reporte.¡Todo al alcance de un clic!
                        </p>

                    </div>
                </div>

                <!-- Imagen -->
                <div class="col-12 col-md-4">
                    <div class="card-body ps-md-2 pe-5 text-end"> <!-- padding solo izquierda, alineada a la derecha -->
                        <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                             class="img-fluid scaleX-n1-rtl"
                             alt="View Badge User">
                    </div>
                </div>

                <div class="row gy-6 mb-6">
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Disponible para descarga diaria</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">El Último corte es: <strong id="ultimo-corte-display">Cargando...</strong></p>
                                    </div>
                                    <form id="form-descarga" method="GET" action="/Reporteria/ProcesarDescargarCorte">
                                        <input type="hidden" name="columna" id="input-columna" value="">
                                        <button type="submit" id="btn-ultimo-corte" class="btn btn-primary">
                                            Descargar Último Corte
                                        </button>
                                    </form>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png?ga=GA1.1.191732613.1764875703" alt="boy illustration">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Disponible para descarga solo lunes</h5>
                                        <p class="text-body app-academy-sm-60 app-academy-xl-100">Seguimiento a clientes que han cumplido con su compromiso de pago.</p>
                                    </div>
                                    <div class="mb-0"><button class="btn btn-sm btn-primary">CLIENTES PAGO CORRIENTE</button></div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053333.png?ga=GA1.1.191732613.1764875703" alt="girl illustration">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Disponible para descarga de Jueves a Sabado</h5>
                                        <p class="text-body app-academy-sm-60 app-academy-xl-100">Clientes próximos a iniciar su historial de pagos con su primer abono.</p>
                                    </div>
                                    <div class="mb-0"><button class="btn btn-sm btn-primary">PRIMEROS PAGOS</button></div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053244.png?ga=GA1.1.191732613.1764875703" alt="girl illustration">
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
/* Easter egg Resumen Call Center: triple clic en icono teléfono → ring + "Llamada en curso" */
.reporteria-call-easter-icon { color: #94a3b8; font-size: 0.9rem; cursor: pointer; transition: transform 0.2s, color 0.2s; opacity: 0.85; }
.reporteria-call-easter-icon:hover { color: #696cff; transform: scale(1.15); opacity: 1; }
body.dark-mode .reporteria-call-easter-icon { color: #64748b; }
body.dark-mode .reporteria-call-easter-icon:hover { color: #818cf8; }
.reporteria-call-toast { position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 1060; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%); color: #fff; padding: 20px 36px; border-radius: 16px; font-size: 1.1rem; font-weight: 600; box-shadow: 0 16px 48px rgba(37, 99, 235, 0.35); border: 2px solid rgba(255,255,255,0.25); opacity: 0; animation: reporteriaCallToastIn 0.35s ease forwards; pointer-events: none; text-align: center; }
.reporteria-call-toast .reporteria-call-toast-emoji { font-size: 2.25rem; display: inline-block; margin-bottom: 6px; animation: reporteriaCallRing 0.5s ease-in-out infinite; }
@keyframes reporteriaCallRing { 0%, 100% { transform: rotate(-6deg) translateX(0); } 20% { transform: rotate(6deg) translateX(-3px); } 40% { transform: rotate(-6deg) translateX(3px); } 60% { transform: rotate(6deg) translateX(-2px); } 80% { transform: rotate(-6deg) translateX(2px); } }
@keyframes reporteriaCallToastIn { 0% { opacity: 0; transform: translate(-50%, -50%) scale(0.85); } 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); } }
@keyframes reporteriaCallToastOut { 0% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 100% { opacity: 0; transform: translate(-50%, -50%) scale(0.95); } }
</style>

<script>
(function() {
    var el = document.getElementById('reporteriaCallEaster');
    if (!el) return;
    var count = 0, t = null;
    el.addEventListener('click', function() {
        count++;
        if (count === 3) {
            count = 0;
            if (t) clearTimeout(t);
            var toast = document.createElement('div');
            toast.className = 'reporteria-call-toast';
            toast.innerHTML = '<span class="reporteria-call-toast-emoji">📞</span>Llamada en curso';
            document.body.appendChild(toast);
            var audio = new Audio('/assets/audio/ring.mp3');
            audio.volume = 0.35;
            audio.play().catch(function(){});
            setTimeout(function() {
                toast.style.animation = 'reporteriaCallToastOut 0.4s ease forwards';
                setTimeout(function() { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 400);
            }, 6000);
            return;
        }
        if (t) clearTimeout(t);
        t = setTimeout(function() { count = 0; }, 600);
    });
})();
</script>

<script>
    // Cargar el último corte disponible al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/Reporteria/getUltimoCorte', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'ultimo_corte' })
        })
        .then(resp => resp.json())
        .then(data => {
            const nombreColumna = data?.datos?.columna || "";
            const displayElement = document.getElementById('ultimo-corte-display');
            
            if (nombreColumna) {
                displayElement.textContent = nombreColumna;
                displayElement.style.color = '#696cff';
            } else {
                displayElement.textContent = 'No disponible';
                displayElement.style.color = '#a1acb8';
            }
        })
        .catch(err => {
            console.error('Error al cargar último corte:', err);
            document.getElementById('ultimo-corte-display').textContent = 'Error al cargar';
        });
    });
</script>
