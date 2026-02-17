<?php
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$puestoUsuario = $_SESSION['nombre_puesto'] ?? 'Usuario';
$modulosUsuario = $_SESSION['modulos'] ?? [];
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
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Maxikash | Inicio</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --blue:     #1A52A8;
  --blue-mid: #2563C4;
  --yellow:   #C8D62B;
  --white:    #ffffff;
  --bg:       #eef1f7;
  --surface:  #ffffff;
  --border:   #e2e8f0;
  --text:     #1e293b;
  --muted:    #64748b;
  --soft:     #94a3b8;
  --sw:       244px;
  --r:        14px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg);
  min-height:100vh;
  display:flex;
  color:var(--text);
}
body::before{
  content:'';position:fixed;inset:0;
  background-image:radial-gradient(circle,#c8d6e128 1px,transparent 1px);
  background-size:24px 24px;pointer-events:none;z-index:0;
}

.sidebar{
  width:var(--sw);background:var(--white);
  border-right:1px solid var(--border);
  position:fixed;top:0;left:0;bottom:0;z-index:50;
  display:flex;flex-direction:column;padding:24px 0 20px;
}
.sb-logo{
  padding:0 18px 22px;border-bottom:1px solid var(--border);
  margin-bottom:12px;display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;
}
.sb-logo img{height:56px;width:auto;object-fit:contain;}
.sb-section{padding:0 10px;flex:1;overflow-y:auto;}
.sb-label{
  font-size:10px;font-weight:600;text-transform:uppercase;
  letter-spacing:1.3px;color:var(--soft);padding:14px 8px 5px;
}
.sb-item{
  display:flex;align-items:center;gap:10px;padding:9px 10px;
  border-radius:9px;cursor:pointer;color:var(--muted);
  font-size:13.5px;font-weight:500;transition:all .18s;position:relative;
  text-decoration:none;border:none;background:none;width:100%;text-align:left;font-family:inherit;
}
.sb-item:hover{background:#f1f5f9;color:var(--blue);}
.sb-item.active{
  background:linear-gradient(120deg,#e8efff,#edf2ff);
  color:var(--blue);font-weight:600;
}
.sb-item.active::after{
  content:'';position:absolute;left:-10px;top:25%;bottom:25%;
  width:3px;background:var(--blue);border-radius:0 3px 3px 0;
}
.sb-icon{font-size:15px;width:22px;text-align:center;}
.sb-footer{padding:14px 10px 0;border-top:1px solid var(--border);position:relative;}
.sb-user-wrap{position:relative;}
.sb-user{
  display:flex;align-items:center;gap:9px;padding:9px 10px;
  border-radius:9px;cursor:pointer;transition:background .18s;
}
.sb-user:hover{background:#f1f5f9;}
.sb-user-dropdown{
  position:absolute;left:0;right:0;bottom:100%;margin-bottom:6px;
  background:var(--surface);border:1px solid var(--border);border-radius:var(--r);
  box-shadow:0 8px 24px rgba(0,0,0,.12);min-width:200px;padding:8px 10px;
  display:none;z-index:60;
}
.sb-user-dropdown.open{display:block;}
.sb-user-dropdown a,.sb-user-dropdown button{
  display:flex;align-items:center;gap:10px;width:100%;padding:9px 10px;
  border:none;background:none;cursor:pointer;font-size:13.5px;font-weight:500;
  color:var(--muted);font-family:'DM Sans',sans-serif;text-align:left;
  transition:all .18s;text-decoration:none;border-radius:9px;box-sizing:border-box;
}
.sb-user-dropdown a:hover,.sb-user-dropdown button:hover{
  background:#f1f5f9;color:var(--blue);
}
.sb-user-dropdown .ud-ico{font-size:15px;width:22px;text-align:center;flex-shrink:0;}
.sb-user-dropdown .ud-divider{height:1px;background:var(--border);margin:6px 0;}
.sb-avatar{
  width:32px;height:32px;border-radius:50%;
  background:linear-gradient(135deg,var(--blue),#5b95f9);
  display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:700;color:#fff;flex-shrink:0;
}
.sb-uname{font-size:12.5px;font-weight:600;color:var(--text);}
.sb-urole{font-size:11px;color:var(--soft);}
.sb-online{width:7px;height:7px;border-radius:50%;background:#22c55e;flex-shrink:0;box-shadow:0 0 0 2px #fff;}

.main{
  margin-left:var(--sw);flex:1;display:flex;
  flex-direction:column;min-height:100vh;position:relative;z-index:1;
}
.topbar{
  height:60px;background:rgba(255,255,255,.88);backdrop-filter:blur(12px);
  border-bottom:1px solid var(--border);display:flex;align-items:center;
  justify-content:space-between;padding:0 32px;position:sticky;top:0;z-index:40;
}
.tb-left{font-size:13px;color:var(--muted);}
.tb-left strong{color:var(--text);}
.tb-right{display:flex;align-items:center;gap:10px;}
.tb-chip{
  background:#f1f5f9;border:1px solid var(--border);border-radius:8px;
  padding:5px 12px;font-size:12.5px;font-weight:500;color:var(--muted);
}
.tb-notif{
  width:34px;height:34px;border-radius:9px;border:1px solid var(--border);
  background:#fff;cursor:pointer;display:flex;align-items:center;
  justify-content:center;font-size:15px;position:relative;transition:all .2s;
}
.tb-notif:hover{background:#f1f5f9;}
.tb-dot{position:absolute;top:6px;right:7px;width:6px;height:6px;background:#ef4444;border-radius:50%;border:1.5px solid #fff;}

.page{padding:26px 34px 56px;}

.role-sw{
  display:inline-flex;align-items:center;gap:6px;
  background:#fff;border:1px solid var(--border);border-radius:10px;
  padding:7px 14px;margin-bottom:22px;font-size:12px;color:var(--muted);
  box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.r-btn{
  padding:4px 10px;border:1px solid var(--border);background:#f8fafc;
  border-radius:6px;font-size:11.5px;font-weight:600;cursor:pointer;
  color:var(--muted);font-family:'DM Sans',sans-serif;transition:all .18s;
}
.r-btn:hover,.r-btn.active{background:var(--blue);color:#fff;border-color:var(--blue);}

.hero{
  background:linear-gradient(128deg,#0d2757 0%,var(--blue) 50%,#1c67c2 100%);
  border-radius:var(--r);padding:36px 38px;margin-bottom:22px;
  position:relative;overflow:hidden;
  display:grid;grid-template-columns:1fr auto;gap:20px;align-items:center;
  animation:up .55s ease both;
  box-shadow:0 8px 32px rgba(26,82,168,.22);
}
.hero::before{
  content:'';position:absolute;inset:0;
  background:
    radial-gradient(ellipse 65% 90% at 90% -20%,rgba(200,214,43,.1) 0%,transparent 55%),
    radial-gradient(ellipse 35% 55% at -5% 110%,rgba(255,255,255,.04) 0%,transparent 50%);
  pointer-events:none;
}
.hero::after{
  content:'';position:absolute;left:0;top:0;bottom:0;width:4px;
  background:linear-gradient(to bottom,var(--yellow),#8fa010);
  border-radius:var(--r) 0 0 var(--r);
}
.hero-wm{
  position:absolute;right:34px;top:50%;transform:translateY(-50%);
  opacity:.07;pointer-events:none;
}
.hero-badge{
  display:inline-flex;align-items:center;gap:5px;
  background:rgba(200,214,43,.14);border:1px solid rgba(200,214,43,.28);
  border-radius:20px;padding:3px 10px;font-size:10.5px;font-weight:600;
  color:var(--yellow);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;
}
.badge-dot{width:5px;height:5px;border-radius:50%;background:var(--yellow);animation:blink 2s infinite;}
.hero-title{
  font-family:'Syne',sans-serif;font-size:28px;font-weight:800;
  color:#fff;line-height:1.15;margin-bottom:8px;
}
.hero-desc{font-size:13.5px;color:rgba(255,255,255,.55);max-width:380px;line-height:1.65;}

.hstats{display:flex;flex-direction:column;gap:9px;min-width:185px;}
.hst{
  background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);
  border-radius:10px;padding:11px 14px;display:flex;align-items:center;
  gap:10px;backdrop-filter:blur(4px);transition:background .2s;
}
.hst:hover{background:rgba(255,255,255,.13);}
.hst-ico{
  width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.1);
  display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;
}
.hst-val{
  font-family:'Syne',sans-serif;font-size:15px;font-weight:800;
  color:#fff;line-height:1;white-space:nowrap;
}
.hst-lbl{font-size:10.5px;color:rgba(255,255,255,.45);margin-top:2px;}

.sec-hd{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.sec-txt{
  font-family:'Syne',sans-serif;font-size:14px;font-weight:700;
  color:var(--text);white-space:nowrap;
}
.sec-line{flex:1;height:1px;background:var(--border);}

.grid-cards{
  display:flex;flex-wrap:wrap;justify-content:center;
  gap:13px;margin-bottom:22px;
}
.qcard{
  width:188px;min-width:188px;max-width:188px;
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--r);padding:22px 17px 17px;cursor:pointer;
  position:relative;overflow:hidden;transition:all .22s cubic-bezier(.4,0,.2,1);
  animation:up .5s ease both;opacity:0;animation-fill-mode:both;
  text-decoration:none;color:inherit;display:block;
}
.qcard:hover{
  transform:translateY(-5px);
  box-shadow:0 14px 36px rgba(26,82,168,.12);
  border-color:rgba(26,82,168,.18);
}
.qcard::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:2.5px;
  background:linear-gradient(90deg,var(--blue),var(--yellow));
  transform:scaleX(0);transform-origin:left;
  transition:transform .28s ease;border-radius:0 0 var(--r) var(--r);
}
.qcard:hover::after{transform:scaleX(1);}
.qico{
  width:44px;height:44px;border-radius:11px;display:flex;
  align-items:center;justify-content:center;font-size:20px;margin-bottom:12px;
}
.qt{font-size:13.5px;font-weight:700;color:var(--text);margin-bottom:4px;}
.qd{font-size:12px;color:var(--soft);line-height:1.45;}
.qarr{
  position:absolute;top:15px;right:13px;width:21px;height:21px;border-radius:6px;
  background:#f1f5f9;display:flex;align-items:center;justify-content:center;
  font-size:11px;color:var(--soft);opacity:0;transition:all .2s;
}
.qcard:hover .qarr{opacity:1;background:var(--blue);color:#fff;}
.bg-blue  {background:#eff4ff;} .bg-green {background:#f0fdf4;}
.bg-orange{background:#fff7ed;} .bg-purple{background:#faf5ff;}
.bg-red   {background:#fef2f2;} .bg-teal  {background:#f0fdfa;}
.bg-yellow{background:#fefce8;}

.bot{display:grid;grid-template-columns:1fr 292px;gap:16px;}

.panel{
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--r);padding:22px;
  animation:up .5s .35s ease both;opacity:0;animation-fill-mode:both;
}
.p-title{
  font-family:'Syne',sans-serif;font-size:14px;font-weight:700;
  color:var(--text);margin-bottom:18px;display:flex;align-items:center;gap:8px;
}
.live{
  display:inline-flex;align-items:center;gap:4px;
  background:#fef2f2;border:1px solid #fecaca;border-radius:20px;
  padding:2px 8px;font-size:10px;font-weight:700;color:#ef4444;
  text-transform:uppercase;letter-spacing:.8px;
}
.live-d{width:5px;height:5px;border-radius:50%;background:#ef4444;animation:blink 1.5s infinite;}

.ni{
  display:flex;gap:13px;padding:13px 0;border-bottom:1px solid #f1f5f9;
  cursor:pointer;transition:all .18s;border-radius:8px;
}
.ni:last-child{border-bottom:none;padding-bottom:0;}
.ni:hover{background:#f8fafc;padding-left:6px;padding-right:6px;margin:0 -6px;}
.ni-num{
  font-family:'Syne',sans-serif;font-size:20px;font-weight:800;
  color:#e2e8f0;line-height:1;min-width:26px;padding-top:2px;
}
.ni-cat{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;}
.ni-ttl{font-size:13.5px;font-weight:600;color:var(--text);margin-bottom:3px;line-height:1.4;}
.ni-meta{font-size:11.5px;color:var(--soft);}

.rcol{display:flex;flex-direction:column;gap:13px;}

.icard{
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--r);padding:18px;
  animation:up .5s .4s ease both;opacity:0;animation-fill-mode:both;
}
.clock-block{
  text-align:center;padding-bottom:14px;
  border-bottom:1px solid #f1f5f9;margin-bottom:14px;
}
.clock-time{
  font-family:'Syne',sans-serif;font-size:40px;font-weight:800;
  color:var(--blue);letter-spacing:-1.5px;line-height:1;margin-bottom:3px;
}
.clock-date{font-size:12px;color:var(--soft);}

.info-row{
  display:flex;align-items:center;justify-content:space-between;
  border-radius:9px;padding:10px 13px;margin-bottom:9px;
}
.ir-blue{background:linear-gradient(120deg,#eff4ff,#edf2ff);}
.ir-yellow{background:#fefce8;border:1px solid #fef08a;}
.ir-green{background:#f0fdf4;border:1px solid #bbf7d0;}
.ir-label{font-size:12px;color:var(--muted);}
.ir-val{
  font-family:'Syne',sans-serif;font-size:13px;font-weight:700;
}
.ir-val.blue{color:var(--blue);}
.ir-val.amber{color:#854d0e;}
.ir-val.green{color:#166534;}
.ir-ico{font-size:18px;}

.cal-card{
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--r);padding:18px;
  animation:up .5s .45s ease both;opacity:0;animation-fill-mode:both;
}
.cal-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:11px;}
.cal-month{font-family:'Syne',sans-serif;font-size:13px;font-weight:700;color:var(--text);}
.cal-nav{
  background:none;border:none;cursor:pointer;color:var(--soft);
  font-size:14px;padding:2px 6px;border-radius:5px;transition:all .18s;font-family:inherit;
}
.cal-nav:hover{background:#f1f5f9;color:var(--blue);}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:1px;text-align:center;}
.cal-dn{font-size:10px;font-weight:700;color:var(--soft);padding:3px 0;}
.cal-d{
  font-size:12px;padding:5px 2px;border-radius:6px;cursor:pointer;
  color:var(--muted);transition:all .15s;position:relative;
}
.cal-d:hover:not(.empty){background:#f1f5f9;color:var(--blue);}
.cal-d.today{background:var(--blue);color:#fff;font-weight:700;box-shadow:0 2px 8px rgba(26,82,168,.3);}
.cal-d.ev::after{
  content:'';position:absolute;bottom:2px;left:50%;transform:translateX(-50%);
  width:4px;height:4px;background:var(--yellow);border-radius:50%;
}
.cal-d.today.ev::after{background:rgba(255,255,255,.7);}
.cal-d.empty{color:transparent;cursor:default;pointer-events:none;}

.quote-card{
  background:linear-gradient(135deg,#0d2757,var(--blue));
  border-radius:var(--r);padding:17px 19px;position:relative;overflow:hidden;
  animation:up .5s .5s ease both;opacity:0;animation-fill-mode:both;
}
.quote-card::before{
  content:'"';position:absolute;top:-15px;left:10px;
  font-size:96px;font-family:'Syne',sans-serif;font-weight:800;
  color:rgba(255,255,255,.05);line-height:1;pointer-events:none;
}
.q-label{
  font-size:10px;font-weight:700;text-transform:uppercase;
  letter-spacing:1.2px;color:var(--yellow);margin-bottom:7px;
}
.q-text{font-size:13px;color:rgba(255,255,255,.82);line-height:1.6;font-style:italic;margin-bottom:7px;}
.q-author{font-size:11px;color:rgba(255,255,255,.38);}

@keyframes up{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}
@keyframes blink{0%,100%{opacity:1;}50%{opacity:.3;}}

/* Modo oscuro */
body.dark-mode{--bg:#0f172a;--surface:#1e293b;--border:#334155;--text:#f1f5f9;--muted:#94a3b8;--soft:#64748b;}
body.dark-mode .sidebar{background:var(--surface);border-color:var(--border);}
body.dark-mode .sb-item:hover{background:#334155;color:#93c5fd;}
body.dark-mode .sb-item.active{background:linear-gradient(120deg,#1e3a5f,#2563eb33);color:#93c5fd;}
body.dark-mode .sb-user:hover{background:#334155;}
body.dark-mode .sb-user-dropdown{background:var(--surface);border-color:var(--border);box-shadow:0 8px 24px rgba(0,0,0,.4);}
body.dark-mode .sb-user-dropdown a:hover,body.dark-mode .sb-user-dropdown button:hover{background:#334155;color:#93c5fd;}
body.dark-mode .topbar{background:rgba(30,41,59,.9);border-color:var(--border);}
body.dark-mode .hero{background:linear-gradient(128deg,#0f172a 0%,#1e3a5f 50%,#1e40af 100%);}
body.dark-mode .qcard{background:var(--surface);border-color:var(--border);}
body.dark-mode .qcard:hover{border-color:#475569;}
body.dark-mode .panel,body.dark-mode .icard,body.dark-mode .cal-card,body.dark-mode .quote-card{background:var(--surface);border-color:var(--border);}
body.dark-mode .role-sw{background:var(--surface);border-color:var(--border);}
body.dark-mode .r-btn{background:#334155;color:var(--muted);border-color:var(--border);}
body.dark-mode .r-btn:hover,body.dark-mode .r-btn.active{background:var(--blue);color:#fff;}
</style>
</head>
<body>

<aside class="sidebar">
  <a class="sb-logo" href="/inicio">
    <img src="/assets/img/Logotipo-Maxikash-Outline.webp" alt="Maxikash" />
  </a>
  <div class="sb-section" id="sbMenu"></div>
  <div class="sb-footer">
    <div class="sb-user-wrap">
      <div class="sb-user" id="sbUserBtn" title="Opciones de cuenta">
        <div class="sb-avatar" id="sbInit"><?= htmlspecialchars($iniciales) ?></div>
        <div style="flex:1;min-width:0">
          <div class="sb-uname" id="sbName"><?= htmlspecialchars($nombreUsuario) ?></div>
          <div class="sb-urole" id="sbRole"><?= htmlspecialchars($puestoUsuario) ?></div>
        </div>
        <div class="sb-online"></div>
      </div>
      <div class="sb-user-dropdown" id="sbUserDropdown">
        <a href="/perfil" class="ud-item"><span class="ud-ico">⚙️</span><span>Ajustes <small style="color:var(--soft);font-size:11px;font-weight:400;">(tu perfil)</small></span></a>
        <div class="ud-divider"></div>
        <button type="button" id="btnDarkMode" class="ud-item"><span class="ud-ico" id="darkModeIco">🌙</span><span class="ud-label">Modo oscuro</span></button>
        <div class="ud-divider"></div>
        <a href="/login/cerrarSesion" class="ud-item"><span class="ud-ico">🚪</span><span>Cerrar sesión</span></a>
      </div>
    </div>
  </div>
</aside>

<div class="main">
  <header class="topbar">
    <div class="tb-left">Portal Maxikash &nbsp;·&nbsp; <strong id="tbName"><?= htmlspecialchars($nombreCorto) ?></strong></div>
    <div class="tb-right">
      <span class="tb-chip" id="tbDate"></span>
      <a href="/login/cerrarSesion" class="tb-notif" title="Cerrar sesión">🚪</a>
    </div>
  </header>

  <div class="page">
    <div class="role-sw">
      <span>Vista demo:</span>
      <button type="button" class="r-btn active" data-role="admin">Admin</button>
      <button type="button" class="r-btn" data-role="callcenter">Call Center</button>
      <button type="button" class="r-btn" data-role="sabueso">Sabueso</button>
      <button type="button" class="r-btn" data-role="rrhh">RRHH</button>
    </div>

    <div class="hero">
      <svg class="hero-wm" width="170" height="170" viewBox="0 0 40 40" fill="none">
        <path d="M20 2L38 20L20 38L2 20Z" fill="none" stroke="#fff" stroke-width="2.5"/>
        <path d="M20 10L30 20L20 30L10 20Z" fill="#fff"/>
        <path d="M20 14L26 20L20 26L14 20Z" fill="#1A52A8"/>
      </svg>
      <div>
        <div class="hero-badge"><span class="badge-dot"></span><span id="heroBadge"><?= htmlspecialchars($puestoUsuario) ?></span></div>
        <h1 class="hero-title" id="heroTitle">¡Hola, <?= htmlspecialchars($nombreCorto) ?>! 👋</h1>
        <p class="hero-desc" id="heroDesc">Tienes acceso al portal. Usa los accesos rápidos o el menú lateral para navegar.</p>
      </div>
      <div class="hstats" id="hstats"></div>
    </div>

    <div class="sec-hd"><span class="sec-txt">Accesos rápidos</span><div class="sec-line"></div></div>
    <div class="grid-cards" id="cardsGrid"></div>

    <div class="bot">
      <div class="panel">
        <div class="p-title">
          📢 Avisos internos
          <span class="live"><span class="live-d"></span>Nuevo</span>
        </div>
        <div class="ni">
          <div class="ni-num">01</div>
          <div>
            <div class="ni-cat" style="color:#1A52A8;">📌 Comunicado</div>
            <div class="ni-ttl">Actualización de políticas de crédito — vigente desde el 18 de febrero</div>
            <div class="ni-meta">Dirección General · Hace 2 horas</div>
          </div>
        </div>
        <div class="ni">
          <div class="ni-num">02</div>
          <div>
            <div class="ni-cat" style="color:#16a34a;">🎉 Recursos Humanos</div>
            <div class="ni-ttl">Celebración de cumpleaños del mes — Viernes 21 en sala de juntas, 13:00 hrs</div>
            <div class="ni-meta">RRHH · Hace 5 horas</div>
          </div>
        </div>
        <div class="ni">
          <div class="ni-num">03</div>
          <div>
            <div class="ni-cat" style="color:#d97706;">⚠️ Sistemas</div>
            <div class="ni-ttl">Mantenimiento programado del sistema — Sábado 22 de 00:00 a 04:00 hrs</div>
            <div class="ni-meta">TI · Ayer</div>
          </div>
        </div>
      </div>

      <div class="rcol">
        <div class="icard">
          <div class="clock-block">
            <div class="clock-time" id="cTime">--:--:--</div>
            <div class="clock-date" id="cDate">cargando...</div>
          </div>
          <div class="info-row ir-blue">
            <div><div class="ir-label">Turno activo</div><div class="ir-val blue" id="shiftTxt">--</div></div>
            <span id="shiftIco" style="font-size:20px">⏰</span>
          </div>
          <div class="info-row ir-yellow">
            <div><div class="ir-label">Próxima quincena</div><div class="ir-val amber" id="payTxt">-- días</div></div>
            <span class="ir-ico">💵</span>
          </div>
          <div class="info-row ir-green" style="margin-bottom:0">
            <div>
              <div class="ir-label">Clima estimado</div>
              <div class="ir-val green" id="wxTxt">-- °C</div>
            </div>
            <span class="ir-ico" id="wxIco">🌤️</span>
          </div>
        </div>

        <div class="cal-card">
          <div class="cal-hd">
            <span class="cal-month" id="calM">—</span>
            <div>
              <button type="button" class="cal-nav" id="calPrev">‹</button>
              <button type="button" class="cal-nav" id="calNext">›</button>
            </div>
          </div>
          <div class="cal-grid" id="calG"></div>
        </div>

        <div class="quote-card">
          <div class="q-label">💡 Frase del día</div>
          <div class="q-text" id="qText">"El éxito es la suma de pequeños esfuerzos repetidos día tras día."</div>
          <div class="q-author" id="qAuth">— Robert Collier</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var urls = <?= json_encode($urls) ?>;
  var userName = <?= json_encode($nombreCorto) ?>;
  var userFull = <?= json_encode($nombreUsuario) ?>;
  var userRole = <?= json_encode($puestoUsuario) ?>;
  var userInit = <?= json_encode($iniciales) ?>;

  var HERO_DESC = {
    admin:     'Gestiona créditos, personal, reportería y configuración desde un solo lugar. Usa los accesos rápidos o el menú lateral.',
    callcenter:'Tu centro de gestión de llamadas y seguimiento de clientes está listo para ti.',
    sabueso:   'Gestiona cobranza y seguimiento de clientes desde Sabueso. Todo lo que necesitas a un clic.',
    rrhh:      'Administra personal, asistencias y nómina desde Capital Humano.'
  };
  var ROLES = {
    admin:     { name: userName, full: userFull, init: userInit, role: userRole, heroDesc: HERO_DESC.admin, menu: ['inicio','creditos','campo','rrhh','reporteria','condonaciones','sabueso','despachos','configuracion'], cards: ['creditos','campo','rrhh','reporteria','condonaciones','sabueso','despachos','configuracion'] },
    callcenter:{ name: userName, full: userFull, init: userInit, role: 'Agente Call Center', heroDesc: HERO_DESC.callcenter, menu: ['inicio','sabueso'], cards: ['sabueso'] },
    sabueso:   { name: userName, full: userFull, init: userInit, role: 'Gestor de Cobranza', heroDesc: HERO_DESC.sabueso, menu: ['inicio','sabueso','configuracion'], cards: ['sabueso','configuracion'] },
    rrhh:      { name: userName, full: userFull, init: userInit, role: 'Recursos Humanos', heroDesc: HERO_DESC.rrhh, menu: ['inicio','rrhh','configuracion'], cards: ['rrhh','configuracion'] }
  };

  var MENU = {
    inicio:{ ico:'🏠', l:'Inicio' },
    creditos:{ ico:'💰', l:'Créditos' },
    campo:{ ico:'🏕️', l:'Gestiones Campo' },
    rrhh:{ ico:'👥', l:'Capital Humano' },
    reporteria:{ ico:'📊', l:'Reportería' },
    condonaciones:{ ico:'💸', l:'Condonaciones' },
    sabueso:{ ico:'🔍', l:'Sabueso' },
    despachos:{ ico:'🚚', l:'Despachos' },
    configuracion:{ ico:'⚙️', l:'Configuración' }
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

  var QUOTES = [
    { t:'El éxito es la suma de pequeños esfuerzos repetidos día tras día.', a:'Robert Collier' },
    { t:'La actitud es la pequeña cosa que hace una gran diferencia.', a:'Winston Churchill' },
    { t:'No cuentes los días, haz que los días cuenten.', a:'Muhammad Ali' },
    { t:'El único modo de hacer un gran trabajo es amar lo que haces.', a:'Steve Jobs' },
    { t:'Cada día es una nueva oportunidad para crecer y mejorar.', a:'Proverbio' },
    { t:'La disciplina es el puente entre las metas y los logros.', a:'Jim Rohn' }
  ];

  var WX = [ { t:'23°C', i:'⛅' }, { t:'27°C', i:'☀️' }, { t:'19°C', i:'☁️' }, { t:'25°C', i:'🌤️' }, { t:'21°C', i:'🌥️' } ];

  function setRole(key){
    var r = ROLES[key] || ROLES.admin;
    document.getElementById('heroBadge').textContent = r.role;
    document.getElementById('heroTitle').textContent = '¡Hola, ' + r.name + '! 👋';
    document.getElementById('heroDesc').textContent = r.heroDesc || HERO_DESC.admin;
    document.getElementById('tbName').textContent = r.name;
    document.getElementById('sbInit').textContent = r.init;
    document.getElementById('sbName').textContent = r.full;
    document.getElementById('sbRole').textContent = r.role;

    var menuHtml = '<div class="sb-label">Menú</div>';
    r.menu.forEach(function(k, i){
      var u = urls[k] || '#';
      var item = MENU[k];
      if (!item) return;
      var active = (i === 0) ? ' active' : '';
      menuHtml += '<a class="sb-item' + active + '" href="' + u + '"><span class="sb-icon">' + item.ico + '</span>' + item.l + '</a>';
    });
    document.getElementById('sbMenu').innerHTML = menuHtml;

    var g = document.getElementById('cardsGrid');
    g.innerHTML = r.cards.map(function(k, i){
      var c = CARDS[k];
      if (!c) return '';
      return '<a class="qcard" href="' + (c.url || '#') + '" style="animation-delay:' + (.07*i+.08) + 's">' +
        '<div class="qico ' + c.bg + '">' + c.ico + '</div>' +
        '<div class="qt">' + c.t + '</div>' +
        '<div class="qd">' + c.d + '</div>' +
        '<div class="qarr">→</div></a>';
    }).join('');
    renderHStats();
  }

  function pad(n){ return (n < 10 ? '0' : '') + n; }
  function shift(h){
    if (h >= 6 && h < 14) return { l: 'Matutino  06:00 – 14:00', i: '🌅' };
    if (h >= 14 && h < 22) return { l: 'Vespertino 14:00 – 22:00', i: '🌆' };
    return { l: 'Nocturno  22:00 – 06:00', i: '🌙' };
  }
  function payDays(){
    var n = new Date(), d = n.getDate(), m = n.getMonth(), y = n.getFullYear();
    var nx = d < 15 ? new Date(y, m, 15) : (d < 28 ? new Date(y, m, 28) : new Date(y, m+1, 15));
    var diff = Math.ceil((nx - n) / 864e5);
    return diff <= 0 ? 1 : diff;
  }
  function tick(){
    var n = new Date(), h = n.getHours(), mn = n.getMinutes(), s = n.getSeconds();
    document.getElementById('cTime').textContent = pad(h) + ':' + pad(mn) + ':' + pad(s);
    var dns = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    var mns = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    document.getElementById('cDate').textContent = dns[n.getDay()] + ', ' + n.getDate() + ' de ' + mns[n.getMonth()] + ' ' + n.getFullYear();
    document.getElementById('tbDate').textContent = dns[n.getDay()] + ' ' + n.getDate() + ' ' + mns[n.getMonth()];
    var sh = shift(h);
    document.getElementById('shiftTxt').textContent = sh.l;
    document.getElementById('shiftIco').textContent = sh.i;
    document.getElementById('payTxt').textContent = payDays() + ' día' + (payDays() === 1 ? '' : 's');
  }
  function renderHStats(){
    var n = new Date(), h = n.getHours();
    var sh = shift(h);
    var mns = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    var dns = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    var pd = payDays();
    document.getElementById('hstats').innerHTML =
      '<div class="hst"><div class="hst-ico">🕐</div><div><div class="hst-val" id="hTime">' + pad(h) + ':' + pad(n.getMinutes()) + '</div><div class="hst-lbl">' + dns[n.getDay()] + ' ' + n.getDate() + ' ' + mns[n.getMonth()] + '</div></div></div>' +
      '<div class="hst"><div class="hst-ico">' + sh.i + '</div><div><div class="hst-val" style="font-size:13px">' + sh.l.split(' ')[0] + '</div><div class="hst-lbl">Turno en curso</div></div></div>' +
      '<div class="hst"><div class="hst-ico">💵</div><div><div class="hst-val">' + pd + ' día' + (pd === 1 ? '' : 's') + '</div><div class="hst-lbl">Para la quincena</div></div></div>';
  }
  function loadWeather(){
    var w = WX[new Date().getDay() % WX.length];
    document.getElementById('wxTxt').textContent = w.t;
    document.getElementById('wxIco').textContent = w.i;
  }
  var cy = new Date().getFullYear(), cm = new Date().getMonth();
  var EV = [3,8,12,16,21,25];
  function renderCal(){
    var MN = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    document.getElementById('calM').textContent = MN[cm] + ' ' + cy;
    var fd = new Date(cy, cm, 1).getDay();
    var tot = new Date(cy, cm+1, 0).getDate();
    var now = new Date();
    var td = (now.getMonth() === cm && now.getFullYear() === cy) ? now.getDate() : -1;
    var dns = ['D','L','M','X','J','V','S'];
    var h = dns.map(function(d){ return '<div class="cal-dn">' + d + '</div>'; }).join('');
    for (var i = 0; i < fd; i++) h += '<div class="cal-d empty">0</div>';
    for (var d = 1; d <= tot; d++){
      var c = 'cal-d';
      if (d === td) c += ' today';
      if (EV.indexOf(d) !== -1) c += ' ev';
      h += '<div class="' + c + '">' + d + '</div>';
    }
    document.getElementById('calG').innerHTML = h;
  }
  function chMonth(dir){
    cm += dir;
    if (cm < 0){ cm = 11; cy--; }
    if (cm > 11){ cm = 0; cy++; }
    renderCal();
  }
  function loadQuote(){
    var q = QUOTES[new Date().getDate() % QUOTES.length];
    document.getElementById('qText').textContent = '"' + q.t + '"';
    document.getElementById('qAuth').textContent = '— ' + q.a;
  }

  setRole('admin');
  tick(); renderCal(); loadWeather(); loadQuote();
  setInterval(tick, 1000);

  document.querySelectorAll('.r-btn').forEach(function(btn){
    btn.addEventListener('click', function(){ document.querySelectorAll('.r-btn').forEach(function(b){ b.classList.remove('active'); }); this.classList.add('active'); setRole(this.getAttribute('data-role')); });
  });
  document.getElementById('calPrev').addEventListener('click', function(){ chMonth(-1); });
  document.getElementById('calNext').addEventListener('click', function(){ chMonth(1); });

  /* Dropdown usuario (izquierda inferior) */
  var sbUserBtn = document.getElementById('sbUserBtn');
  var sbUserDropdown = document.getElementById('sbUserDropdown');
  sbUserBtn.addEventListener('click', function(e){ e.stopPropagation(); sbUserDropdown.classList.toggle('open'); });
  document.addEventListener('click', function(){ sbUserDropdown.classList.remove('open'); });
  sbUserDropdown.addEventListener('click', function(e){ e.stopPropagation(); });

  /* Modo oscuro: misma clave que el resto del portal (View.php) para que quede integrado */
  var darkStored = localStorage.getItem('darkMode') === 'enabled';
  if (darkStored) document.body.classList.add('dark-mode');
  var btnDark = document.getElementById('btnDarkMode');
  function updateDarkLabel(){
    var isDark = document.body.classList.contains('dark-mode');
    btnDark.innerHTML = '<span class="ud-ico" id="darkModeIco">' + (isDark ? '☀️' : '🌙') + '</span><span class="ud-label">' + (isDark ? 'Modo claro' : 'Modo oscuro') + '</span>';
  }
  updateDarkLabel();
  btnDark.addEventListener('click', function(){
    document.body.classList.toggle('dark-mode');
    var isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
    updateDarkLabel();
  });
})();
</script>
</body>
</html>
