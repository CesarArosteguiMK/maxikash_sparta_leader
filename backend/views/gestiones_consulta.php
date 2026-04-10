<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Histórico de gestiones en los créditos <i id="gestionesCampoEaster" aria-hidden="true"></i></h4>
            <p class="text-muted small">Busca por ID de crédito</p>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card">

        <!-- Filtros -->
        <div class="row justify-content-between m-4">

            <div class="col-8">
                <label class="form-label">Filtro</label>
                <div class="input-group input-group-merge">

                    <div class="form-check form-check-inline me-3">
                        <input class="form-check-input" type="radio" name="modoBusqueda" id="modoID" value="id"
                            <?= (!isset($_POST['modoBusqueda']) || $_POST['modoBusqueda'] === 'id') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="modoID">ID de crédito</label>
                    </div>

                    <div class="form-check form-check-inline" style="display:none">
                        <input class="form-check-input" type="radio" name="modoBusqueda" id="modoNombre" value="nombre"
                            <?= (isset($_POST['modoBusqueda']) && $_POST['modoBusqueda'] === 'nombre') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="modoNombre">Nombre del cliente</label>
                    </div>

                </div>
            </div>

            <div class="col-4 d-flex align-items-end justify-content-end">
                <button id="btnResetFiltros" class="btn btn-outline-secondary me-2" type="button">Limpiar</button>
            </div>
        </div>

        <!-- FORMULARIO -->
        <div class="card-body">
            <form method="POST" id="formBusqueda">

                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6" id="divID">
                        <label for="idCredito" class="form-label">ID de crédito</label>
                        <div class="input-group input-group-merge">
                            <input type="number" class="form-control" id="idCredito" name="idCredito"
                                   value="<?= $_POST['idCredito'] ?? '' ?>"
                                   placeholder="Ej.: 12345">
                            <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6" id="divNombre" style="display: none;">
                        <label for="nombre" class="form-label">Nombre del Cliente</label>
                        <div class="input-group input-group-merge">
                            <input type="text" class="form-control" name="nombre" id="nombre"
                                   value="<?= $_POST['nombre'] ?? '' ?>"
                                   placeholder="Nombre completo o parcial">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                        </div>
                    </div>
                    <input type="hidden" name="fechaCorte" id="fechaCorte" value="<?= $fecha_actual_iso ?>">
                    <div class="col-12">
                        <button type="submit" class="btn btn-outline-primary w-100" id="btnBuscar">Buscar</button>
                    </div>
                </div>

            </form>
        </div>



    </div>
</div>
<?= $script ?? '' ?>

<style>
.gestiones-easter-icon{color:#94a3b8;font-size:0.9rem;cursor:pointer;opacity:0.85;transition:color .2s, transform .2s}
.gestiones-easter-icon:hover{color:#22c55e;transform:scale(1.1)}
.gestiones-easter-wrap{position:fixed;inset:0;z-index:1048;pointer-events:none;overflow:hidden}
.gestiones-easter-dot{position:absolute;width:14px;height:14px;border-radius:50%;pointer-events:none;left:50%;top:50%;box-shadow:0 0 12px 2px currentColor, 0 0 24px currentColor}
@keyframes gestionesEasterBurst{0%{opacity:1;transform:translate(-50%,-50%) scale(1)}100%{opacity:0;transform:translate(calc(-50% + var(--gx)), calc(-50% + var(--gy))) scale(0.2)}}
.gestiones-easter-toast{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);z-index:1050;background:linear-gradient(135deg,#166534 0%,#22c55e 50%,#4ade80 100%);color:#fff;padding:20px 36px;border-radius:16px;font-size:1.15rem;font-weight:700;box-shadow:0 12px 40px rgba(34,197,94,0.5), 0 0 0 2px rgba(255,255,255,0.3);border:none;opacity:0;animation:gestionesEasterIn .4s cubic-bezier(0.34,1.56,0.64,1) forwards;pointer-events:none;text-align:center;display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:center}
.gestiones-easter-toast .gestiones-easter-wrench{font-size:1.8rem;display:inline-block;animation:gestionesEasterSpin .6s linear infinite}
@keyframes gestionesEasterSpin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
@keyframes gestionesEasterIn{0%{opacity:0;transform:translate(-50%,-50%) scale(0.5)}100%{opacity:1;transform:translate(-50%,-50%) scale(1)}}
@keyframes gestionesEasterOut{0%{opacity:1;transform:translate(-50%,-50%) scale(1)}100%{opacity:0;transform:translate(-50%,-50%) scale(0.9)}}
</style>
<script>
(function(){
    var el=document.getElementById('gestionesCampoEaster');
    if(!el)return;
    var timer=null;
    function showToast(){
        var wrap=document.createElement('div');
        wrap.className='gestiones-easter-wrap';
        var colors=['#22c55e','#4ade80','#fbbf24','#f59e0b','#166534'];
        for(var i=0;i<24;i++){
            var angle=(i/24)*Math.PI*2+Math.random()*0.5;
            var dist=80+Math.random()*120;
            var gx=Math.cos(angle)*dist+'px';
            var gy=Math.sin(angle)*dist+'px';
            var dot=document.createElement('div');
            dot.className='gestiones-easter-dot';
            dot.style.cssText='background:'+colors[i%colors.length]+';color:'+colors[i%colors.length]+';animation:gestionesEasterBurst '+(0.9+Math.random()*0.4)+'s ease-out forwards;--gx:'+gx+';--gy:'+gy+';';
            wrap.appendChild(dot);
        }
        document.body.appendChild(wrap);
        var t=document.createElement('div');
        t.className='gestiones-easter-toast';
        t.innerHTML='<span class="gestiones-easter-wrench">\uD83D\uDD27</span> \u00A1Herramientas listas!';
        document.body.appendChild(t);
        var animDuration=2800;
        try{
            var a=new Audio('/assets/audio/tool_ready.mp3');
            a.volume=0.6;
            a.play().catch(function(){var b=new Audio('/assets/audio/ring2.mp3');b.volume=0.6;b.play().catch(function(){});});
            setTimeout(function(){a.pause();a.currentTime=0;},animDuration);
        }catch(e){}
        setTimeout(function(){
            t.style.animation='gestionesEasterOut .35s ease forwards';
            setTimeout(function(){
                if(t.parentNode)t.parentNode.removeChild(t);
                if(wrap.parentNode)wrap.parentNode.removeChild(wrap);
            },350);
        },animDuration);
    }
    function start(){timer=setTimeout(showToast,700);}
    function cancel(){if(timer){clearTimeout(timer);timer=null}}
    el.addEventListener('mousedown',start);
    el.addEventListener('mouseup',cancel);
    el.addEventListener('mouseleave',cancel);
    el.addEventListener('touchstart',function(e){e.preventDefault();start();});
    el.addEventListener('touchend',function(e){e.preventDefault();cancel();});
    el.addEventListener('touchcancel',cancel);
})();
</script>


