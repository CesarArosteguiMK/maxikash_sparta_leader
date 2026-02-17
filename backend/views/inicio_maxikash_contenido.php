<?php
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$puestoUsuario = $_SESSION['nombre_puesto'] ?? 'Usuario';
$iniciales = preg_match_all('/\b\w/u', $nombreUsuario, $m) ? mb_substr(implode('', array_map(function($x){ return mb_substr($x,0,1); }, $m[0])), 0, 2) : 'U';
$iniciales = strtoupper($iniciales);
$nombreCorto = trim(preg_replace('/\s+/', ' ', $nombreUsuario));
if (strpos($nombreCorto, ' ') !== false) {
    $partes = explode(' ', $nombreCorto);
    $nombreCorto = $partes[0] . ' ' . end($partes);
}
$urls = [
    'inicio' => '/inicio',
    'creditos' => '/estadocuenta/consulta',
    'campo' => '/gestiones/seguimiento',
    'rrhh' => '/caphum/gestion',
    'reporteria' => '/reporteria/resumencallcenter',
    'condonaciones' => '/condonaciones/historial',
    'sabueso' => '/sabueso/ticket',
    'despachos' => '/Despachos/AsignacionCreditosDespacho',
    'configuracion' => '/departamentos/consulta',
];
?>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
.inicio-mkx { --blue:#1A52A8; --yellow:#C8D62B; --r:14px; }
.inicio-mkx .page{padding:0 0 2rem;}
.inicio-mkx .role-sw{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;margin-bottom:22px;font-size:12px;color:#64748b;border-radius:10px;}
.inicio-mkx .r-btn{padding:4px 10px;border:1px solid #e2e8f0;background:#f8fafc;border-radius:6px;font-size:11.5px;font-weight:600;cursor:pointer;color:#64748b;font-family:'DM Sans',sans-serif;transition:all .18s;}
.inicio-mkx .r-btn:hover,.inicio-mkx .r-btn.active{background:var(--blue);color:#fff;border-color:var(--blue);}
.inicio-mkx .hero{background:linear-gradient(128deg,#0d2757 0%,var(--blue) 50%,#1c67c2 100%);border-radius:var(--r);padding:36px 38px;margin-bottom:22px;position:relative;overflow:hidden;display:grid;grid-template-columns:1fr;gap:20px;align-items:center;box-shadow:0 8px 32px rgba(26,82,168,.22);}
.inicio-mkx .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 65% 90% at 90% -20%,rgba(200,214,43,.1) 0%,transparent 55%);pointer-events:none;}
.inicio-mkx .hero::after{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(to bottom,var(--yellow),#8fa010);border-radius:var(--r) 0 0 var(--r);}
.inicio-mkx .hero-badge{display:inline-flex;align-items:center;gap:5px;background:rgba(200,214,43,.14);border:1px solid rgba(200,214,43,.28);border-radius:20px;padding:3px 10px;font-size:10.5px;font-weight:600;color:var(--yellow);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;}
.inicio-mkx .hero-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:#fff;line-height:1.15;margin-bottom:8px;}
.inicio-mkx .hero-desc{font-size:13.5px;color:rgba(255,255,255,.55);max-width:380px;line-height:1.65;}
.inicio-mkx .sec-hd{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.inicio-mkx .sec-txt{font-family:'Syne',sans-serif;font-size:14px;font-weight:700;color:inherit;}
.inicio-mkx .sec-line{flex:1;height:1px;background:var(--bs-border-color, #e2e8f0);}
.inicio-mkx .grid-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(188px,1fr));gap:13px;}
.inicio-mkx .qcard{background:var(--bs-body-bg, #fff);border:1px solid var(--bs-border-color, #e2e8f0);border-radius:var(--r);padding:22px 17px;cursor:pointer;position:relative;overflow:hidden;transition:all .22s;text-decoration:none;color:inherit;display:block;}
.inicio-mkx .qcard:hover{transform:translateY(-5px);box-shadow:0 14px 36px rgba(26,82,168,.12);}
.inicio-mkx .qcard::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2.5px;background:linear-gradient(90deg,var(--blue),var(--yellow));transform:scaleX(0);transform-origin:left;transition:transform .28s ease;border-radius:0 0 var(--r) var(--r);}
.inicio-mkx .qcard:hover::after{transform:scaleX(1);}
.inicio-mkx .qico{width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:12px;}
.inicio-mkx .qt{font-size:13.5px;font-weight:700;margin-bottom:4px;}
.inicio-mkx .qd{font-size:12px;opacity:.85;}
.inicio-mkx .qarr{position:absolute;top:15px;right:13px;width:21px;height:21px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:11px;opacity:0;transition:all .2s;}
.inicio-mkx .qcard:hover .qarr{opacity:1;background:var(--blue);color:#fff;}
.inicio-mkx .bg-blue{background:#eff4ff;} .inicio-mkx .bg-green{background:#f0fdf4;} .inicio-mkx .bg-orange{background:#fff7ed;} .inicio-mkx .bg-purple{background:#faf5ff;}
.inicio-mkx .bg-red{background:#fef2f2;} .inicio-mkx .bg-teal{background:#f0fdfa;} .inicio-mkx .bg-yellow{background:#fefce8;}
</style>

<div class="inicio-mkx">
  <div class="page">
    <div class="role-sw">
      <span>Vista demo:</span>
      <button type="button" class="r-btn active" data-role="admin">Admin</button>
      <button type="button" class="r-btn" data-role="callcenter">Call Center</button>
      <button type="button" class="r-btn" data-role="sabueso">Sabueso</button>
      <button type="button" class="r-btn" data-role="rrhh">RRHH</button>
    </div>

    <div class="hero">
      <div>
        <div class="hero-badge"><span id="heroBadge"><?= htmlspecialchars($puestoUsuario) ?></span></div>
        <h1 class="hero-title" id="heroTitle">¡Hola, <?= htmlspecialchars($nombreCorto) ?>! 👋</h1>
        <p class="hero-desc" id="heroDesc">Tienes acceso al portal. Usa los accesos rápidos o el menú lateral para navegar.</p>
      </div>
    </div>

    <div class="sec-hd"><span class="sec-txt">Accesos rápidos</span><div class="sec-line"></div></div>
    <div class="grid-cards" id="cardsGrid"></div>
  </div>
</div>

<script>
(function(){
  var urls = <?= json_encode($urls) ?>;
  var userName = <?= json_encode($nombreCorto) ?>;
  var userFull = <?= json_encode($nombreUsuario) ?>;
  var userRole = <?= json_encode($puestoUsuario) ?>;
  var userInit = <?= json_encode($iniciales) ?>;
  var HERO_DESC = { admin:'Gestiona créditos, personal, reportería y configuración desde un solo lugar. Usa los accesos rápidos o el menú lateral.', callcenter:'Tu centro de gestión de llamadas y seguimiento de clientes está listo para ti.', sabueso:'Gestiona cobranza y seguimiento de clientes desde Sabueso. Todo lo que necesitas a un clic.', rrhh:'Administra personal, asistencias y nómina desde Capital Humano.' };
  var ROLES = {
    admin:     { name: userName, full: userFull, init: userInit, role: userRole, heroDesc: HERO_DESC.admin, cards: ['creditos','campo','rrhh','reporteria','condonaciones','sabueso','despachos','configuracion'] },
    callcenter:{ name: userName, full: userFull, init: userInit, role: 'Agente Call Center', heroDesc: HERO_DESC.callcenter, cards: ['sabueso'] },
    sabueso:   { name: userName, full: userFull, init: userInit, role: 'Gestor de Cobranza', heroDesc: HERO_DESC.sabueso, cards: ['sabueso','configuracion'] },
    rrhh:      { name: userName, full: userFull, init: userInit, role: 'Recursos Humanos', heroDesc: HERO_DESC.rrhh, cards: ['rrhh','configuracion'] }
  };
  var CARDS = {
    creditos:     { ico:'💰', bg:'bg-blue',   t:'Créditos',         d:'Estados de cuenta y documentación', url: urls.creditos },
    campo:        { ico:'🏕️', bg:'bg-green',  t:'Gestiones Campo',  d:'Supervisión en terreno', url: urls.campo },
    rrhh:         { ico:'👥', bg:'bg-purple', t:'Capital Humano',   d:'Personal y nómina', url: urls.rrhh },
    reporteria:   { ico:'📊', bg:'bg-orange', t:'Reportería',       d:'Informes y estadísticas', url: urls.reporteria },
    condonaciones:{ ico:'💸', bg:'bg-red',    t:'Condonaciones',    d:'Gestión de excepciones', url: urls.condonaciones },
    sabueso:      { ico:'🔍', bg:'bg-teal',   t:'Sabueso',         d:'Cobranza y seguimiento', url: urls.sabueso },
    despachos:    { ico:'🚚', bg:'bg-yellow', t:'Despachos',       d:'Asignación de créditos', url: urls.despachos },
    configuracion:{ ico:'⚙️', bg:'bg-blue',   t:'Configuración',   d:'Parámetros del sistema', url: urls.configuracion }
  };
  function setRole(key){
    var r = ROLES[key] || ROLES.admin;
    var badge = document.getElementById('heroBadge');
    var title = document.getElementById('heroTitle');
    var desc = document.getElementById('heroDesc');
    var g = document.getElementById('cardsGrid');
    if (badge) badge.textContent = r.role;
    if (title) title.textContent = '¡Hola, ' + r.name + '! 👋';
    if (desc) desc.textContent = r.heroDesc || HERO_DESC.admin;
    if (g) g.innerHTML = (r.cards || []).map(function(k, i){
      var c = CARDS[k];
      if (!c) return '';
      return '<a class="qcard" href="' + (c.url || '#') + '"><div class="qico ' + c.bg + '">' + c.ico + '</div><div class="qt">' + c.t + '</div><div class="qd">' + c.d + '</div><div class="qarr">→</div></a>';
    }).join('');
  }
  setRole('admin');
  document.querySelectorAll('.inicio-mkx .r-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.inicio-mkx .r-btn').forEach(function(b){ b.classList.remove('active'); });
      this.classList.add('active');
      setRole(this.getAttribute('data-role'));
    });
  });
})();
</script>
