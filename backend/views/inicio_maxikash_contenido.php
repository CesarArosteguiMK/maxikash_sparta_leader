<?php
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$puestoUsuario = $_SESSION['nombre_puesto'] ?? 'Usuario';
$nombreCorto = trim(preg_replace('/\s+/', ' ', $nombreUsuario));
if (strpos($nombreCorto, ' ') !== false) {
    $partes = explode(' ', $nombreCorto);
    $nombreCorto = $partes[0] . ' ' . end($partes);
}
$accesosRapidos = $accesosRapidos ?? [];
$mostrarBotonAnalytics = $mostrarBotonAnalytics ?? false;
$itemsAnalytics = $itemsAnalytics ?? [];
$logoUrl = '/assets/img/Logotipo-Maxikash-Outline.webp';

$mensajesPorPuesto = [
    'Administrador de Proyectos' => 'Responsable de planificar, coordinar y supervisar la ejecución de los proyectos estratégicos de la organización.',
    'Desarrollador Junior'       => 'Perfil orientado al desarrollo y soporte de funcionalidades bajo supervisión técnica.',
    'Desarrollador Senior'      => 'Responsable del diseño técnico, arquitectura y supervisión de soluciones dentro del sistema. Lidera decisiones técnicas, revisa código, optimiza procesos y garantiza la calidad, seguridad y escalabilidad de las aplicaciones desarrolladas.',
    'Call Center'               => 'Gestiona la operación de atención al cliente. Supervisa llamadas, seguimiento y desempeño del equipo en tiempo real.',
    'Capital Humano'            => 'Gestiona el talento y los procesos laborales. Controla asistencias, nómina y administración del personal desde un panel unificado.',
];
// Normalizar nombre de puesto (BD puede tener "Desarrollador Jr", "Call center", etc.)
$aliasPuesto = [
    'Desarrollador Jr' => 'Desarrollador Junior',
    'Desarrollador Sr' => 'Desarrollador Senior',
    'Call center'      => 'Call Center',
    'call center'      => 'Call Center',
    'Capital humano'  => 'Capital Humano',
    'capital humano'  => 'Capital Humano',
    'Administrador'   => 'Administrador de Proyectos',
];
$puestoNormalizado = trim((string) $puestoUsuario);
$puestoParaBusqueda = $aliasPuesto[$puestoNormalizado] ?? $puestoNormalizado;
$heroDesc = $mensajesPorPuesto[$puestoParaBusqueda] ?? 'Tienes acceso al portal. Usa los accesos rápidos o el menú lateral para navegar.';
?>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
.inicio-mkx { --blue:#1A52A8; --yellow:#C8D62B; --r:14px; }
.inicio-mkx .page{padding:0 0 2rem;}
.inicio-mkx .hero{background:linear-gradient(128deg,#0d2757 0%,var(--blue) 50%,#1c67c2 100%);border-radius:var(--r);padding:36px 38px;margin-bottom:22px;position:relative;overflow:hidden;display:grid;grid-template-columns:1fr auto;gap:24px;align-items:center;box-shadow:0 8px 32px rgba(26,82,168,.22);}
.inicio-mkx .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 65% 90% at 90% -20%,rgba(200,214,43,.1) 0%,transparent 55%);pointer-events:none;z-index:0;}
.inicio-mkx .hero::after{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(to bottom,var(--yellow),#8fa010);border-radius:var(--r) 0 0 var(--r);z-index:0;}
.inicio-mkx .hero-logo-bg{position:absolute;right:20px;top:50%;transform:translateY(-50%);width:200px;height:200px;pointer-events:none;z-index:0;display:flex;align-items:center;justify-content:center;}
.inicio-mkx .hero-logo-bg svg{width:100%;height:100%;}
body.dark-mode .inicio-mkx .hero-logo-bg svg{opacity:1;}
body.dark-mode .inicio-mkx .hero::after{background:linear-gradient(to bottom,#c8d62b,#8fa010) !important;opacity:1;}
.inicio-mkx .hero-content{position:relative;z-index:1;}
.inicio-mkx .hero-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(200,214,43,.14);border:1px solid rgba(200,214,43,.28);border-radius:20px;padding:4px 12px;font-size:10.5px;font-weight:600;color:var(--yellow);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;}
.inicio-mkx .hero-badge .badge-dot{width:6px;height:6px;border-radius:50%;background:var(--yellow);animation:hero-badge-blink 2s ease-in-out infinite;box-shadow:0 0 8px var(--yellow);}
@keyframes hero-badge-blink{0%,100%{opacity:1;box-shadow:0 0 8px var(--yellow);}50%{opacity:0.7;box-shadow:0 0 4px var(--yellow);}}
body.dark-mode .inicio-mkx .hero-badge .badge-dot{box-shadow:0 0 10px var(--yellow);}
.inicio-mkx .hero-title{font-family:'Outfit',sans-serif;font-size:28px;font-weight:700;color:#fff;line-height:1.2;margin-bottom:8px;letter-spacing:-0.02em;}
.inicio-mkx .hero-desc{font-size:13.5px;color:rgba(255,255,255,.55);max-width:560px;line-height:1.65;}
.inicio-mkx .hero-datetime{position:relative;z-index:1;display:flex;align-items:center;gap:14px;padding:14px 18px;background:rgba(255,255,255,0.12);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.2);border-radius:14px;cursor:pointer;transition:all .2s;}
.inicio-mkx .hero-datetime:hover{background:rgba(255,255,255,0.2);transform:scale(1.02);}
.inicio-mkx .hero-datetime-icon{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,rgba(200,214,43,0.5),rgba(200,214,43,0.25));display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;color:#C8D62B;animation:hero-clock-pulse 1.5s ease-in-out infinite;box-shadow:0 2px 12px rgba(0,0,0,0.12),inset 0 1px 0 rgba(255,255,255,0.25),0 0 0 0 rgba(200,214,43,0);border:1px solid rgba(200,214,43,0.5);}
.inicio-mkx .hero-datetime-icon i{color:#C8D62B;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.15));}
.inicio-mkx .hero-datetime-time{font-weight:700;font-size:1.35rem;line-height:1.2;color:#fff;}
.inicio-mkx .hero-datetime-date{font-size:0.8rem;opacity:0.95;margin-top:2px;color:rgba(255,255,255,0.9);}
@keyframes hero-clock-pulse{0%,100%{transform:scale(1);box-shadow:0 2px 12px rgba(0,0,0,0.12),0 0 0 0 rgba(200,214,43,0.4);}50%{transform:scale(1.08);box-shadow:0 4px 16px rgba(0,0,0,0.18),0 0 20px 4px rgba(200,214,43,0.5);}}

/* Panel reloj desplegable – Liquid Glass */
.clock-panel-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:9998;display:none;backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);}
.clock-panel-overlay.open{display:block;animation:overlayIn .25s ease;}
@keyframes overlayIn{from{opacity:0;}to{opacity:1;}}
.clock-panel{
  position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
  width:340px;max-width:calc(100vw - 32px);
  background:rgba(255,255,255,0.88);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border-radius:24px;
  border:1px solid rgba(255,255,255,0.5);
  box-shadow:0 25px 80px rgba(0,0,0,.2),0 0 0 1px rgba(0,0,0,0.05),inset 0 1px 0 rgba(255,255,255,0.6);
  z-index:9999;display:none;overflow:hidden;
}
.clock-panel.open{display:block;animation:panelIn .35s cubic-bezier(0.34,1.56,0.64,1);}
@keyframes panelIn{from{opacity:0;transform:translate(-50%,-50%) scale(.85);}to{opacity:1;transform:translate(-50%,-50%) scale(1);}}
.cp-header{
  background:linear-gradient(135deg,rgba(26,82,168,0.95) 0%,rgba(37,99,196,0.95) 100%);
  padding:24px 20px;text-align:center;color:#fff;
  backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
}
.cp-time{font-family:'Outfit',sans-serif;font-size:52px;font-weight:700;letter-spacing:2px;line-height:1;text-shadow:0 2px 10px rgba(0,0,0,0.15);}
.cp-date{font-size:15px;opacity:.95;font-weight:500;margin-top:6px;}
.cp-weather{
  display:flex;align-items:center;gap:14px;padding:16px 20px;
  background:rgba(224,242,254,0.6);
  border-bottom:1px solid rgba(0,0,0,0.06);
}
.cp-weather-ico{font-size:36px;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1));}
.cp-weather-info{flex:1;}
.cp-weather-label{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;font-weight:600;}
.cp-weather-temp{font-size:22px;font-weight:700;color:#1e293b;}
.cp-calendar{padding:16px 20px;border-bottom:1px solid rgba(0,0,0,0.06);background:rgba(255,255,255,0.3);}
.cp-cal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
.cp-cal-month{font-size:15px;font-weight:600;color:#1e293b;}
.cp-cal-nav{display:flex;gap:6px;}
.cp-cal-nav button{
  width:28px;height:28px;border:1px solid rgba(0,0,0,0.1);border-radius:8px;
  background:rgba(255,255,255,0.7);cursor:pointer;font-size:12px;color:#64748b;
  display:flex;align-items:center;justify-content:center;transition:all .2s;
}
.cp-cal-nav button:hover{background:#1A52A8;color:#fff;border-color:#1A52A8;transform:scale(1.05);}
.cp-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;text-align:center;}
.cp-cal-dn{font-size:10px;font-weight:600;color:#94a3b8;padding:4px 0;}
.cp-cal-d{font-size:12px;padding:7px 0;border-radius:8px;color:#64748b;transition:all .15s;position:relative;}
.cp-cal-d.empty{visibility:hidden;}
.cp-cal-d.today{background:linear-gradient(135deg,#1A52A8,#2563eb);color:#fff;font-weight:700;box-shadow:0 2px 8px rgba(26,82,168,0.35);}
.cp-event-dot{position:absolute;bottom:2px;left:50%;transform:translateX(-50%);width:5px;height:5px;background:#f59e0b;border-radius:50%;box-shadow:0 0 4px rgba(245,158,11,0.5);}
.cp-cal-d.today .cp-event-dot{background:#fff;box-shadow:0 0 4px rgba(255,255,255,0.5);}
.cp-quote{
  padding:16px 20px;
  background:linear-gradient(135deg,rgba(255,251,235,0.8),rgba(254,243,199,0.6));
  border-left:4px solid #f59e0b;
}
.cp-quote-label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:#d97706;margin-bottom:8px;display:flex;align-items:center;gap:6px;}
.cp-quote-text{font-size:14px;font-style:italic;color:#78350f;line-height:1.55;}
.cp-quote-author{font-size:12px;color:#6b7280;font-weight:500;margin-top:6px;}

/* Dark mode para panel – Liquid Glass */
body.dark-mode .clock-panel-overlay{background:rgba(0,0,0,.5);}
body.dark-mode .clock-panel{
  background:rgba(30,41,59,0.85);
  border-color:rgba(255,255,255,0.1);
  box-shadow:0 25px 80px rgba(0,0,0,.5),0 0 0 1px rgba(255,255,255,0.05),inset 0 1px 0 rgba(255,255,255,0.1);
}
body.dark-mode .cp-header{background:linear-gradient(135deg,rgba(30,58,138,0.95) 0%,rgba(29,78,216,0.95) 100%);}
body.dark-mode .cp-weather{background:rgba(30,58,95,0.5);border-color:rgba(255,255,255,0.06);}
body.dark-mode .cp-weather-temp{color:#fff;}
body.dark-mode .cp-weather-label{color:#94a3b8;}
body.dark-mode .cp-calendar{border-color:rgba(255,255,255,0.06);background:rgba(0,0,0,0.15);}
body.dark-mode .cp-cal-month{color:#fff;}
body.dark-mode .cp-cal-nav button{background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.15);color:#94a3b8;}
body.dark-mode .cp-cal-nav button:hover{background:#3b82f6;color:#fff;border-color:#3b82f6;}
body.dark-mode .cp-cal-d{color:#94a3b8;}
body.dark-mode .cp-cal-d.today{background:linear-gradient(135deg,#3b82f6,#2563eb);box-shadow:0 2px 10px rgba(59,130,246,0.4);}
body.dark-mode .cp-event-dot{background:#fbbf24;box-shadow:0 0 6px rgba(251,191,36,0.6);}
body.dark-mode .cp-quote{background:linear-gradient(135deg,rgba(66,32,6,0.7),rgba(120,53,15,0.5));border-left-color:#f59e0b;}
body.dark-mode .clock-panel .cp-quote-label{color:#d97706 !important;}
body.dark-mode .clock-panel .cp-quote-text{color:#fef3c7 !important;}
body.dark-mode .clock-panel .cp-quote-author{color:#9ca3af !important;}
body.dark-mode .inicio-mkx .hero-datetime-icon{background:linear-gradient(135deg,rgba(200,214,43,0.35),rgba(200,214,43,0.12));border-color:rgba(200,214,43,0.4);box-shadow:0 2px 14px rgba(0,0,0,0.35),inset 0 1px 0 rgba(255,255,255,0.08);}
body.dark-mode .inicio-mkx .hero-datetime-icon i{color:#c8d62b;filter:drop-shadow(0 1px 3px rgba(0,0,0,0.3));}

/* Panel reloj – Responsive móvil */
@media (max-width: 575.98px) {
  .clock-panel{
    width:calc(100vw - 24px);
    max-width:340px;
    border-radius:20px;
  }
  .cp-header{padding:20px 16px;}
  .cp-time{font-size:42px;letter-spacing:1px;}
  .cp-date{font-size:13px;}
  .cp-weather{padding:14px 16px;gap:12px;}
  .cp-weather-ico{font-size:30px;}
  .cp-weather-temp{font-size:20px;}
  .cp-calendar{padding:14px 16px;}
  .cp-cal-month{font-size:14px;}
  .cp-cal-nav button{width:26px;height:26px;font-size:11px;}
  .cp-cal-d{font-size:11px;padding:6px 0;}
  .cp-event-dot{width:4px;height:4px;}
  .cp-quote{padding:14px 16px;}
  .cp-quote-text{font-size:13px;}
  .cp-quote-author{font-size:11px;}
}

@media (max-width: 400px) {
  .clock-panel{
    width:calc(100vw - 16px);
    border-radius:18px;
  }
  .cp-header{padding:18px 14px;}
  .cp-time{font-size:36px;}
  .cp-date{font-size:12px;}
  .cp-weather{padding:12px 14px;}
  .cp-weather-ico{font-size:26px;}
  .cp-weather-temp{font-size:18px;}
  .cp-weather-label{font-size:10px;}
  .cp-calendar{padding:12px 14px;}
  .cp-cal-grid{gap:2px;}
  .cp-cal-d{font-size:10px;padding:5px 0;border-radius:6px;}
  .cp-cal-dn{font-size:9px;}
  .cp-quote{padding:12px 14px;}
  .cp-quote-label{font-size:8px;}
  .cp-quote-text{font-size:12px;line-height:1.5;}
}
.inicio-mkx .sec-hd{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.inicio-mkx .sec-txt{font-family:'Outfit',sans-serif;font-size:14px;font-weight:700;color:inherit;}
.inicio-mkx .sec-line{flex:1;height:1px;background:var(--bs-border-color, #e2e8f0);}
.inicio-mkx .grid-cards{display:flex;flex-wrap:wrap;justify-content:center;gap:13px;}
.inicio-mkx .qcard{width:188px;min-width:188px;max-width:188px;background:var(--bs-body-bg, #fff);border:1px solid var(--bs-border-color, #e2e8f0);border-radius:var(--r);padding:22px 17px;cursor:pointer;position:relative;overflow:hidden;transition:all .22s;text-decoration:none;color:inherit;display:block;}
.inicio-mkx .qcard:hover{transform:translateY(-5px);box-shadow:0 14px 36px rgba(26,82,168,.12);}
.inicio-mkx .qcard::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--blue),var(--yellow));transform:scaleX(0);transform-origin:left;transition:transform .28s ease;border-radius:0 0 var(--r) var(--r);z-index:1;}
.inicio-mkx .qcard:hover::after{transform:scaleX(1);}
body.dark-mode .inicio-mkx .qcard::after{background:linear-gradient(90deg,#3b82f6,#22c55e);}
body.dark-mode .inicio-mkx .qcard:hover::after{transform:scaleX(1);}
.inicio-mkx .qico{width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:12px;background:var(--bs-secondary-bg, #f1f5f9);}
.inicio-mkx .qico i{color:var(--bs-body-color, #697a8d) !important;}
.inicio-mkx .qt{font-size:13.5px;font-weight:700;margin-bottom:4px;}
.inicio-mkx .qd{font-size:12px;opacity:.85;}
.inicio-mkx .qarr{position:absolute;top:15px;right:13px;width:21px;height:21px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:11px;opacity:0;transition:all .2s;}
.inicio-mkx .qcard:hover .qarr{opacity:1;background:var(--blue);color:#fff;}
/* Hero responsive para celular */
@media (max-width: 991.98px) {
  .inicio-mkx .hero {
    grid-template-columns: 1fr;
    padding: 24px 20px;
    gap: 20px;
  }
  .inicio-mkx .hero-logo-bg {
    width: 120px;
    height: 120px;
    right: 16px;
    top: 50%;
    opacity: 0.85;
  }
  .inicio-mkx .hero-logo-bg svg {
    width: 100%;
    height: 100%;
  }
  .inicio-mkx .hero-title {
    font-size: 1.5rem;
  }
  .inicio-mkx .hero-desc {
    font-size: 12.5px;
  }
  .inicio-mkx .hero-datetime {
    flex-wrap: wrap;
    padding: 12px 14px;
  }
  .inicio-mkx .hero-datetime-icon {
    width: 40px;
    height: 40px;
    font-size: 18px;
  }
  .inicio-mkx .hero-datetime-time {
    font-size: 1.15rem;
  }
}
@media (max-width: 575.98px) {
  .inicio-mkx .hero {
    padding: 20px 16px;
  }
  .inicio-mkx .hero-title {
    font-size: 1.35rem;
  }
  .inicio-mkx .hero-badge {
    font-size: 9.5px;
    padding: 3px 10px;
  }
}

/* Botón flotante Gráficas/Análisis (solo usuario 878) */
.btn-toggle-analytics {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9998;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #1A52A8 0%, #1e40af 100%);
  color: #fff;
  box-shadow: 0 4px 16px rgba(26, 82, 168, 0.45);
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  transition: all 0.25s ease;
  font-size: 10px;
  font-weight: 600;
}
.btn-toggle-analytics i {
  font-size: 20px;
}
.btn-toggle-analytics:hover {
  transform: scale(1.08);
  box-shadow: 0 6px 24px rgba(26, 82, 168, 0.55);
}
.btn-toggle-analytics:active {
  transform: scale(0.96);
}
body.dark-mode .btn-toggle-analytics {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  box-shadow: 0 4px 16px rgba(37, 99, 235, 0.4);
}
body.dark-mode .btn-toggle-analytics:hover {
  box-shadow: 0 6px 24px rgba(37, 99, 235, 0.5);
}

/* Panel de gráficas/análisis (mismo estilo que accesos) */
.inicio-panel-analytics .sec-txt { color: inherit; }
.inicio-vista-rapida .card { border-radius: var(--r, 14px); overflow: hidden; }
.inicio-iframe-monitor { display: block; background: #fff; }
body.dark-mode .inicio-iframe-monitor { background: #1e293b; }
</style>

<div class="inicio-mkx">
  <div class="page">
    <div class="hero">
      <div class="hero-logo-bg" aria-hidden="true">
        <svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill="rgba(255,255,255,0.28)" fill-rule="evenodd" d="M100 28 L172 100 L100 172 L28 100 Z M100 52 L148 100 L100 148 L52 100 Z"/>
          <path fill="rgba(255,255,255,0.14)" stroke="rgba(255,255,255,0.38)" stroke-width="2.5" stroke-linejoin="round" d="M100 52 L148 100 L100 148 L52 100 Z"/>
        </svg>
      </div>
      <div class="hero-content">
        <div class="hero-badge"><span class="badge-dot"></span><span id="heroBadge"><?= htmlspecialchars($puestoUsuario) ?></span></div>
        <h1 class="hero-title">¡Hola, <?= htmlspecialchars($nombreCorto) ?>! 👋</h1>
        <p class="hero-desc"><?= htmlspecialchars($heroDesc) ?></p>
      </div>
      <div class="hero-datetime" id="btnOpenClockPanel" title="Ver más información">
        <span class="hero-datetime-icon"><i class="fa-solid fa-clock"></i></span>
        <div>
          <div class="hero-datetime-time" id="inicioTime">--:--</div>
          <div class="hero-datetime-date" id="inicioDate">--</div>
        </div>
      </div>
    </div>

    <!-- Overlay y Panel del reloj -->
    <div class="clock-panel-overlay" id="clockOverlay"></div>
    <div class="clock-panel" id="clockPanel">
      <div class="cp-header">
        <div class="cp-time" id="cpTime">--:--:--</div>
        <div class="cp-date" id="cpDate">Cargando...</div>
      </div>
      <div class="cp-weather">
        <div class="cp-weather-ico" id="cpWxIco">⛅</div>
        <div class="cp-weather-info">
          <div class="cp-weather-label">Clima estimado</div>
          <div class="cp-weather-temp" id="cpWxTemp">23°C</div>
        </div>
      </div>
      <div class="cp-calendar">
        <div class="cp-cal-header">
          <span class="cp-cal-month" id="cpCalMonth">Febrero 2026</span>
          <div class="cp-cal-nav">
            <button type="button" id="cpCalPrev">◀</button>
            <button type="button" id="cpCalNext">▶</button>
          </div>
        </div>
        <div class="cp-cal-grid" id="cpCalGrid"></div>
      </div>
      <div class="cp-quote">
        <div class="cp-quote-label" style="color: #d97706 !important;">💡 Frase del día</div>
        <div class="cp-quote-text" id="cpQuoteText">"El éxito es la suma de pequeños esfuerzos repetidos día tras día."</div>
        <div class="cp-quote-author" id="cpQuoteAuthor" style="color: #9ca3af !important;">— Robert Collier</div>
      </div>
    </div>

    <div id="inicio-accesos-wrap">
      <div class="sec-hd"><span class="sec-txt">Accesos rápidos</span><div class="sec-line"></div></div>
      <div class="grid-cards" id="cardsGrid">
        <?php foreach ($accesosRapidos as $acc): ?>
          <a class="qcard" href="<?= htmlspecialchars($acc['url']) ?>">
            <div class="qico"><i class="<?= htmlspecialchars($acc['icon']) ?>"></i></div>
            <div class="qt"><?= htmlspecialchars($acc['label']) ?></div>
            <div class="qd">Ir a <?= htmlspecialchars($acc['label']) ?></div>
            <div class="qarr">→</div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($mostrarBotonAnalytics): ?>
    <div id="inicio-panel-analytics" class="inicio-panel-analytics" style="display: none;">
      <div class="sec-hd"><span class="sec-txt">Gráficas, análisis y monitoreo</span><div class="sec-line"></div></div>
      <p class="inicio-analytics-intro text-muted small mb-3">Vista rápida de indicadores. Abre cada enlace debajo para ver el reporte completo.</p>

      <div class="row g-3">
          <div class="col-12 col-lg-4">
            <div class="card h-100">
              <div class="card-header py-2 d-flex align-items-center justify-content-between">
                <span class="small fw-semibold">KPI Total</span>
                <a href="/indicadores/kpiTotal" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-2" title="Abrir en pantalla completa">Abrir</a>
              </div>
              <div class="card-body p-0 overflow-hidden" style="height: 280px;">
                <iframe src="/indicadores/kpiTotal" title="KPI Total" class="inicio-iframe-monitor" style="width:100%;height:100%;border:none;"></iframe>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="card h-100">
              <div class="card-header py-2 d-flex align-items-center justify-content-between">
                <span class="small fw-semibold">Gestión 1-7</span>
                <a href="/indicadores/gestiones1A7" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-2" title="Abrir en pantalla completa">Abrir</a>
              </div>
              <div class="card-body p-0 overflow-hidden" style="height: 280px;">
                <iframe src="/indicadores/gestiones1A7" title="Gestión 1-7" class="inicio-iframe-monitor" style="width:100%;height:100%;border:none;"></iframe>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="card h-100">
              <div class="card-header py-2 d-flex align-items-center justify-content-between">
                <span class="small fw-semibold">Eficiencia 1-7</span>
                <a href="/indicadores/eficiencia1A7" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-2" title="Abrir en pantalla completa">Abrir</a>
              </div>
              <div class="card-body p-0 overflow-hidden" style="height: 280px;">
                <iframe src="/indicadores/eficiencia1A7" title="Eficiencia 1-7" class="inicio-iframe-monitor" style="width:100%;height:100%;border:none;"></iframe>
              </div>
            </div>
          </div>
        </div>

      <div class="sec-hd mt-4"><span class="sec-txt">Todos los accesos: reportes, análisis y monitoreo</span><div class="sec-line"></div></div>
      <div class="grid-cards" id="analyticsGrid">
        <?php foreach ($itemsAnalytics as $item): ?>
          <a class="qcard" href="<?= htmlspecialchars($item['url']) ?>">
            <div class="qico"><i class="<?= htmlspecialchars($item['icon']) ?>"></i></div>
            <div class="qt"><?= htmlspecialchars($item['label']) ?></div>
            <div class="qd">Ir a <?= htmlspecialchars($item['label']) ?></div>
            <div class="qarr">→</div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="button" id="btnToggleAnalytics" class="btn-toggle-analytics" title="Ver gráficas y análisis" aria-label="Alternar gráficas y análisis">
      <i class="fa-solid fa-chart-line" id="btnToggleAnalyticsIcon"></i>
      <span id="btnToggleAnalyticsText">Gráficas</span>
    </button>
    <?php endif; ?>

  </div>
</div>

<script>
(function(){
  var pad = function(n){ return (n < 10 ? '0' : '') + n; };
  
  // Obtener fecha/hora en zona horaria de CDMX
  function getCDMXDate(){
    return new Date(new Date().toLocaleString('en-US', { timeZone: 'America/Mexico_City' }));
  }
  
  // Actualizar hora del hero (CDMX)
  function updateInicioDateTime(){
    var elTime = document.getElementById('inicioTime');
    var elDate = document.getElementById('inicioDate');
    if (!elTime || !elDate) return;
    var n = getCDMXDate();
    var h = n.getHours(), m = n.getMinutes();
    elTime.textContent = pad(h) + ':' + pad(m);
    var d = n.getDate(), mes = n.getMonth(), dia = n.getDay();
    var meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    var dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    elDate.textContent = dias[dia] + ' ' + d + ' ' + meses[mes];
  }
  updateInicioDateTime();
  setInterval(updateInicioDateTime, 1000);

  // Panel reloj
  var btnOpen = document.getElementById('btnOpenClockPanel');
  var clockPanel = document.getElementById('clockPanel');
  var clockOverlay = document.getElementById('clockOverlay');
  var cdmxNow = getCDMXDate();
  var cpCy = cdmxNow.getFullYear();
  var cpCm = cdmxNow.getMonth();

  // Frases cargadas desde archivo (fallback incluido)
  var QUOTES = [];
  var todayQuote = null;

  // Cargar frases desde el archivo JSON
  fetch('/backend/config/frases_motivacionales.json')
    .then(function(r){ return r.json(); })
    .then(function(data){
      if(data && data.frases && data.frases.length > 0){
        QUOTES = data.frases.map(function(f, idx){ return { id: idx, t: f.texto, a: f.autor }; });
        todayQuote = selectTodayQuote();
      }
    })
    .catch(function(){
      // Fallback si no carga el archivo
      QUOTES = [
        { id: 0, t:'El éxito es la suma de pequeños esfuerzos repetidos día tras día.', a:'Robert Collier' },
        { id: 1, t:'La actitud es la pequeña cosa que hace una gran diferencia.', a:'Winston Churchill' },
        { id: 2, t:'Siempre parece imposible hasta que se hace.', a:'Nelson Mandela' }
      ];
      todayQuote = selectTodayQuote();
    });

  // Sistema de frases sin repetir por 5 días
  function getTodayKey(){
    var n = getCDMXDate();
    return n.getFullYear() + '-' + (n.getMonth()+1) + '-' + n.getDate();
  }

  function getUsedQuotes(){
    try {
      var stored = localStorage.getItem('usedQuotes');
      return stored ? JSON.parse(stored) : {};
    } catch(e){ return {}; }
  }

  function saveUsedQuotes(used){
    try {
      localStorage.setItem('usedQuotes', JSON.stringify(used));
    } catch(e){}
  }

  function selectTodayQuote(){
    var todayKey = getTodayKey();
    var used = getUsedQuotes();
    
    // Si ya hay una frase guardada para hoy, usarla
    if(used[todayKey] && used[todayKey].quote){
      var savedQuote = QUOTES.find(function(q){ return q.id === used[todayKey].quote.id; });
      if(savedQuote) return savedQuote;
    }
    
    // Limpiar frases usadas con más de 5 días
    var now = getCDMXDate();
    var fiveDaysAgo = new Date(now);
    fiveDaysAgo.setDate(fiveDaysAgo.getDate() - 5);
    
    var recentlyUsedIds = [];
    Object.keys(used).forEach(function(dateKey){
      var parts = dateKey.split('-');
      var usedDate = new Date(parts[0], parts[1]-1, parts[2]);
      if(usedDate >= fiveDaysAgo && dateKey !== todayKey){
        if(used[dateKey].quote && typeof used[dateKey].quote.id !== 'undefined'){
          recentlyUsedIds.push(used[dateKey].quote.id);
        }
      } else if(usedDate < fiveDaysAgo){
        // Eliminar registros antiguos (más de 5 días)
        delete used[dateKey];
      }
    });
    
    // Filtrar frases disponibles (no usadas en últimos 5 días)
    var availableQuotes = QUOTES.filter(function(q){
      return recentlyUsedIds.indexOf(q.id) === -1;
    });
    
    // Si no hay frases disponibles, usar todas
    if(availableQuotes.length === 0){
      availableQuotes = QUOTES;
    }
    
    // Seleccionar una frase random
    var randomIndex = Math.floor(Math.random() * availableQuotes.length);
    var selectedQuote = availableQuotes[randomIndex];
    
    // Guardar la frase de hoy
    used[todayKey] = { quote: { id: selectedQuote.id, t: selectedQuote.t, a: selectedQuote.a } };
    saveUsedQuotes(used);
    
    return selectedQuote;
  }

  // Días festivos/eventos de México (mes, día) - los puntos en el calendario
  var EVENTOS_MX = {
    '1-1': 'Año Nuevo',
    '2-5': 'Día de la Constitución',
    '3-21': 'Natalicio de Benito Juárez',
    '5-1': 'Día del Trabajo',
    '5-10': 'Día de las Madres',
    '9-16': 'Día de la Independencia',
    '11-2': 'Día de Muertos',
    '11-20': 'Revolución Mexicana',
    '12-25': 'Navidad',
    '12-31': 'Fin de Año'
  };

  function openPanel(){
    clockPanel.classList.add('open');
    clockOverlay.classList.add('open');
    updateClockPanel();
    renderCpCal();
    loadCpQuote();
    loadCpWeather();
  }
  function closePanel(){
    clockPanel.classList.remove('open');
    clockOverlay.classList.remove('open');
  }

  btnOpen.addEventListener('click', function(e){
    e.stopPropagation();
    openPanel();
  });
  clockOverlay.addEventListener('click', closePanel);
  clockPanel.addEventListener('click', function(e){ e.stopPropagation(); });

  // Actualizar hora del panel (CDMX)
  function updateClockPanel(){
    var n = getCDMXDate();
    document.getElementById('cpTime').textContent = pad(n.getHours()) + ':' + pad(n.getMinutes()) + ':' + pad(n.getSeconds());
    var dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    var meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    document.getElementById('cpDate').textContent = dias[n.getDay()] + ', ' + n.getDate() + ' de ' + meses[n.getMonth()] + ' ' + n.getFullYear();
  }

  // Calendario con eventos (puntos)
  function renderCpCal(){
    var MN = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    document.getElementById('cpCalMonth').textContent = MN[cpCm] + ' ' + cpCy;
    var fd = new Date(cpCy, cpCm, 1).getDay();
    var tot = new Date(cpCy, cpCm+1, 0).getDate();
    var now = getCDMXDate();
    var td = (now.getMonth() === cpCm && now.getFullYear() === cpCy) ? now.getDate() : -1;
    var dns = ['D','L','M','X','J','V','S'];
    var h = dns.map(function(d){ return '<div class="cp-cal-dn">' + d + '</div>'; }).join('');
    for (var i = 0; i < fd; i++) h += '<div class="cp-cal-d empty"></div>';
    for (var d = 1; d <= tot; d++){
      var c = 'cp-cal-d';
      if (d === td) c += ' today';
      var eventKey = (cpCm + 1) + '-' + d;
      var hasEvent = EVENTOS_MX[eventKey];
      var eventDot = hasEvent ? '<span class="cp-event-dot" title="' + hasEvent + '"></span>' : '';
      h += '<div class="' + c + '">' + d + eventDot + '</div>';
    }
    document.getElementById('cpCalGrid').innerHTML = h;
  }

  function cpChMonth(dir){
    cpCm += dir;
    if (cpCm < 0){ cpCm = 11; cpCy--; }
    if (cpCm > 11){ cpCm = 0; cpCy++; }
    renderCpCal();
  }

  document.getElementById('cpCalPrev').addEventListener('click', function(){ cpChMonth(-1); });
  document.getElementById('cpCalNext').addEventListener('click', function(){ cpChMonth(1); });

  // Frase del día (random, sin repetir por 5 días)
  function loadCpQuote(){
    // Si no hay frase seleccionada aún, intentar seleccionar
    if(!todayQuote && QUOTES.length > 0){
      todayQuote = selectTodayQuote();
    }
    
    if(todayQuote){
      document.getElementById('cpQuoteText').textContent = '"' + todayQuote.t + '"';
      document.getElementById('cpQuoteAuthor').textContent = '— ' + todayQuote.a;
    }
    
    // Aplicar colores según modo (fix para dark mode)
    applyQuoteColors();
  }
  
  function applyQuoteColors(){
    var isDark = document.body.classList.contains('dark-mode');
    var labelEl = document.querySelector('.cp-quote-label');
    var authorEl = document.getElementById('cpQuoteAuthor');
    
    if(labelEl){
      labelEl.style.color = '#d97706'; // Amarillo medio (igual en ambos modos)
    }
    if(authorEl){
      authorEl.style.color = isDark ? '#9ca3af' : '#6b7280'; // Gris
    }
  }

  // Clima con caché (4 horas)
  var weatherCache = { temp: null, code: null, time: 0 };
  var WEATHER_CACHE_MS = 4 * 60 * 60 * 1000; // 4 horas

  function loadCpWeather(){
    var elTemp = document.getElementById('cpWxTemp');
    var elIco = document.getElementById('cpWxIco');
    var elLabel = document.querySelector('.cp-weather-label');
    
    // Verificar caché en localStorage
    try {
      var cached = localStorage.getItem('weatherCache');
      if(cached){
        var c = JSON.parse(cached);
        if(c.time && (Date.now() - c.time) < WEATHER_CACHE_MS){
          // Usar caché
          elTemp.textContent = c.temp + '°C';
          elIco.textContent = getWeatherEmoji(c.code);
          if(elLabel) elLabel.textContent = 'Clima CDMX';
          return;
        }
      }
    } catch(e){}
    
    // Si ya está cargando o ya tiene datos recientes en memoria
    if(weatherCache.temp && (Date.now() - weatherCache.time) < WEATHER_CACHE_MS){
      elTemp.textContent = weatherCache.temp + '°C';
      elIco.textContent = getWeatherEmoji(weatherCache.code);
      if(elLabel) elLabel.textContent = 'Clima CDMX';
      return;
    }
    
    // Mostrar cargando
    elTemp.textContent = 'Cargando...';
    elIco.textContent = '⏳';
    
    // Cargar desde servidor
    fetch('/clima/cdmx')
      .then(function(response){ return response.json(); })
      .then(function(data){
        if(data && data.success){
          weatherCache = { temp: data.temperature, code: data.weather_code, time: Date.now() };
          // Guardar en localStorage
          try { localStorage.setItem('weatherCache', JSON.stringify(weatherCache)); } catch(e){}
          elTemp.textContent = data.temperature + '°C';
          elIco.textContent = getWeatherEmoji(data.weather_code);
          if(elLabel) elLabel.textContent = 'Clima CDMX';
        } else {
          throw new Error(data.error || 'Error desconocido');
        }
      })
      .catch(function(err){
        console.log('Error clima:', err);
        elTemp.textContent = 'No disponible';
        elIco.textContent = '❓';
      });
  }

  // Pre-cargar clima al inicio (en segundo plano)
  setTimeout(loadCpWeather, 500);

  // Convertir código WMO a emoji
  function getWeatherEmoji(code){
    if(code === 0) return '☀️';
    if(code === 1 || code === 2) return '⛅';
    if(code === 3) return '☁️';
    if(code >= 45 && code <= 48) return '🌫️';
    if(code >= 51 && code <= 57) return '🌧️';
    if(code >= 61 && code <= 67) return '🌧️';
    if(code >= 71 && code <= 77) return '❄️';
    if(code >= 80 && code <= 82) return '🌦️';
    if(code >= 85 && code <= 86) return '🌨️';
    if(code >= 95 && code <= 99) return '⛈️';
    return '🌡️';
  }

  // Actualizar reloj del panel cada segundo si está abierto
  setInterval(function(){
    if(clockPanel.classList.contains('open')) updateClockPanel();
  }, 1000);

  // Toggle Accesos rápidos ↔ Gráficas/Análisis (usuarios 1 y 878)
  var btnToggle = document.getElementById('btnToggleAnalytics');
  var wrapAccesos = document.getElementById('inicio-accesos-wrap');
  var panelAnalytics = document.getElementById('inicio-panel-analytics');
  var iconToggle = document.getElementById('btnToggleAnalyticsIcon');
  var textToggle = document.getElementById('btnToggleAnalyticsText');
  if (btnToggle && wrapAccesos && panelAnalytics) {
    var showingAnalytics = false;
    btnToggle.addEventListener('click', function(){
      showingAnalytics = !showingAnalytics;
      if (showingAnalytics) {
        wrapAccesos.style.display = 'none';
        panelAnalytics.style.display = 'block';
        if (iconToggle) { iconToggle.className = 'fa-solid fa-th-large'; }
        if (textToggle) { textToggle.textContent = 'Accesos'; }
        btnToggle.title = 'Ver accesos rápidos';
      } else {
        wrapAccesos.style.display = '';
        panelAnalytics.style.display = 'none';
        if (iconToggle) { iconToggle.className = 'fa-solid fa-chart-line'; }
        if (textToggle) { textToggle.textContent = 'Gráficas'; }
        btnToggle.title = 'Ver gráficas y análisis';
      }
    });
  }
})();
</script>
