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
$mostrarDiagnosticoAdmin = $mostrarDiagnosticoAdmin ?? false;
$mostrarBotonApiDocOneClick = $mostrarBotonApiDocOneClick ?? false;
$mostrarBotonEstadoServicios = $mostrarBotonEstadoServicios ?? false;
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

/* Easter egg: modo cuco (5 clics rápidos en el reloj) */
.inicio-mkx .hero-datetime{position:relative;}
.inicio-mkx .hero-datetime .cuckoo-bird{position:absolute;left:24px;top:6px;font-size:2.25rem;opacity:0;pointer-events:none;transform:translateY(8px);transition:opacity .2s, transform .2s;z-index:2;}
.inicio-mkx .hero-datetime.cuckoo-mode .cuckoo-bird{opacity:1;transform:translateY(0);}
.inicio-mkx .hero-datetime.cuckoo-mode .cuckoo-bird.cuckoo-peek{animation:hero-cuckoo-peek 0.4s ease-out;}
@keyframes hero-cuckoo-peek{0%{transform:translateY(0);}40%{transform:translateY(-6px);}100%{transform:translateY(0);}}
.inicio-mkx .hero-datetime.cuckoo-mode .hero-datetime-icon{animation:hero-cuckoo-swing 0.5s ease-in-out infinite;}
.inicio-mkx .hero-datetime.cuckoo-mode .hero-datetime-time{font-size:1.1rem;font-style:italic;}
@keyframes hero-cuckoo-swing{0%,100%{transform:rotate(-12deg);}50%{transform:rotate(12deg);}}

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
.cp-cal-d.quincena:not(.today){color:inherit;cursor:pointer;}
.cp-cal-d.quincena:not(.today) .cp-quincena-dot{position:absolute;bottom:2px;left:50%;transform:translateX(-50%);width:4px;height:4px;background:rgba(34,197,94,0.7);border-radius:50%;}
/* Easter egg: lluvia de dinero (doble clic en día quincena) */
.cp-money-rain{position:fixed;inset:0;z-index:10001;pointer-events:none;overflow:hidden;}
.cp-money-rain .cp-money-item{position:absolute;top:-30px;font-size:18px;opacity:0.9;animation:cp-money-fall 4s linear forwards;}
@keyframes cp-money-fall{0%{transform:translateY(0) rotate(0deg);opacity:0.9;}100%{transform:translateY(100vh) rotate(360deg);opacity:0.2;}}
/* Billete desvaneciente al doble clic (estilo condonar) */
.cp-billete-fade{position:fixed;font-size:2rem;animation:cp-billete-fade 1.2s ease-out forwards;pointer-events:none;z-index:10002;}
@keyframes cp-billete-fade{0%{opacity:1;transform:translate(-50%,-50%) scale(1);}100%{opacity:0;transform:translate(-50%,-50%) translateY(-100px) scale(0.5);}}
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
body.dark-mode .cp-cal-d.quincena:not(.today) .cp-quincena-dot{background:rgba(74,222,128,0.75);}
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
/* Easter: mano saludando (long-press en icono mano) */
.inicio-easter-hand-wrap{position:fixed;inset:0;z-index:10050;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.4);backdrop-filter:blur(6px);pointer-events:none;opacity:0;animation:inicioHandOverlayIn .35s ease-out forwards;}
.inicio-easter-hand-wrap .inicio-easter-hand-ico{font-size:clamp(5rem,18vw,10rem);color:#C8D62B;text-shadow:0 4px 20px rgba(0,0,0,0.3);animation:inicioHandWave 0.5s ease-in-out 6 forwards;}
@keyframes inicioHandOverlayIn{0%{opacity:0}100%{opacity:1}}
@keyframes inicioHandWave{0%,100%{transform:rotate(-18deg)}50%{transform:rotate(18deg)}}
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

/* Botones de diagnóstico (solo usuario id 1): Segundómetro y BD alternas */
.inicio-btn-diagnostico-wrap {
  position: fixed;
  bottom: 24px;
  left: 24px;
  z-index: 9997;
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: center;
}
.inicio-btn-diagnostico {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  border: none;
  color: #fff;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  transition: all 0.25s ease;
  font-size: 9px;
  font-weight: 600;
  box-shadow: 0 4px 14px rgba(0,0,0,0.25);
  text-decoration: none;
  line-height: 1.2;
}
.inicio-btn-diagnostico i { font-size: 18px; }
.inicio-btn-diagnostico:hover {
  transform: scale(1.08);
  box-shadow: 0 6px 20px rgba(0,0,0,0.35);
}
.inicio-btn-diagnostico:active { transform: scale(0.96); }
.inicio-btn-diagnostico-segundo {
  background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
}
.inicio-btn-diagnostico-segundo:hover { box-shadow: 0 6px 20px rgba(13, 148, 136, 0.5); }
.inicio-btn-diagnostico-bd {
  background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
}
.inicio-btn-diagnostico-bd:hover { box-shadow: 0 6px 20px rgba(124, 58, 237, 0.5); }
body.dark-mode .inicio-btn-diagnostico-segundo {
  background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
}
body.dark-mode .inicio-btn-diagnostico-bd {
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
}

/* Botón 1-click API documentación (solo usuario 878) */
.inicio-btn-api1click {
  position: fixed;
  bottom: 24px;
  left: 24px;
  z-index: 9996;
  width: 58px;
  height: 58px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
  color: #fff;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1px;
  box-shadow: 0 4px 16px rgba(234, 88, 12, 0.45);
  transition: all 0.25s ease;
  font-size: 9px;
  font-weight: 700;
  line-height: 1.1;
}
.inicio-btn-api1click i { font-size: 18px; }
.inicio-btn-api1click:hover {
  transform: scale(1.08);
  box-shadow: 0 6px 24px rgba(234, 88, 12, 0.55);
}
.inicio-btn-api1click:active { transform: scale(0.96); }
.inicio-btn-api1click.running {
  background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
  box-shadow: 0 6px 24px rgba(13, 148, 136, 0.55);
}
body.dark-mode .inicio-btn-api1click {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
}

/* Botón "Servicios" — estado en vivo de los puertos locales (solo usuario 878) */
.inicio-btn-estado-srv {
  position: fixed;
  bottom: 24px;
  left: 92px; /* a la derecha del botón API */
  z-index: 9996;
  width: 58px;
  height: 58px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
  color: #fff;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1px;
  box-shadow: 0 4px 16px rgba(79, 70, 229, 0.45);
  transition: all 0.25s ease;
  font-size: 9px;
  font-weight: 700;
  line-height: 1.1;
}
.inicio-btn-estado-srv i { font-size: 18px; }
.inicio-btn-estado-srv:hover {
  transform: scale(1.08);
  box-shadow: 0 6px 24px rgba(79, 70, 229, 0.55);
}
.inicio-btn-estado-srv:active { transform: scale(0.96); }
.inicio-btn-estado-srv.alerta {
  background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
  box-shadow: 0 6px 24px rgba(220, 38, 38, 0.55);
}
.estado-srv-panel {
  position: fixed;
  bottom: 95px;
  left: 24px;
  z-index: 9996;
  width: min(620px, calc(100vw - 36px));
  max-height: 70vh;
  display: none;
  background: rgba(15, 23, 42, 0.96);
  color: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 14px;
  box-shadow: 0 12px 30px rgba(2, 6, 23, 0.45);
  overflow: hidden;
}
.estado-srv-panel.open { display: block; }
.estado-srv-hd {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: rgba(30, 41, 59, 0.95);
  border-bottom: 1px solid rgba(148, 163, 184, 0.35);
  flex-wrap: wrap;
}
.estado-srv-title { font-size: 12px; font-weight: 700; flex: 1 1 auto; }
.estado-srv-summary {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(100, 116, 139, 0.35);
}
.estado-srv-summary.ok { background: rgba(22, 163, 74, 0.35); color: #86efac; }
.estado-srv-summary.err { background: rgba(220, 38, 38, 0.35); color: #fecaca; }
.estado-srv-summary.warn { background: rgba(202, 138, 4, 0.35); color: #fde68a; }
.estado-srv-actions { display: flex; align-items: center; gap: 8px; }
.estado-srv-chk {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  color: #cbd5e1;
}
.estado-srv-tbtn {
  padding: 4px 10px;
  border-radius: 6px;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(51, 65, 85, 0.9);
  color: #e2e8f0;
  font-size: 11px;
  cursor: pointer;
}
.estado-srv-tbtn:hover { background: rgba(71, 85, 105, 0.95); }
.estado-srv-help {
  margin: 0;
  padding: 8px 12px;
  font-size: 11px;
  color: #94a3b8;
  background: rgba(15, 23, 42, 0.7);
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}
.estado-srv-grid {
  padding: 8px;
  max-height: calc(70vh - 130px);
  overflow: auto;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.estado-srv-loading { padding: 12px; text-align: center; color: #94a3b8; font-size: 12px; }
.estado-srv-card {
  display: grid;
  grid-template-columns: 12px 1fr auto;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  background: rgba(30, 41, 59, 0.85);
  border: 1px solid rgba(148, 163, 184, 0.18);
}
.estado-srv-card .dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #475569;
  flex-shrink: 0;
}
.estado-srv-card.up   .dot { background: #22c55e; box-shadow: 0 0 6px #22c55e; }
.estado-srv-card.warn .dot { background: #eab308; box-shadow: 0 0 6px #eab308; }
.estado-srv-card.down .dot { background: #ef4444; box-shadow: 0 0 6px #ef4444; }
.estado-srv-name { font-size: 12.5px; font-weight: 700; line-height: 1.2; }
.estado-srv-meta { font-size: 11px; color: #94a3b8; margin-top: 2px; word-break: break-word; }
.estado-srv-meta a { color: #93c5fd; text-decoration: none; }
.estado-srv-meta a:hover { text-decoration: underline; }
.estado-srv-tag {
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
  white-space: nowrap;
}
.estado-srv-card.up   .estado-srv-tag { background: rgba(22, 163, 74, 0.35); color: #86efac; }
.estado-srv-card.warn .estado-srv-tag { background: rgba(202, 138, 4, 0.35); color: #fde68a; }
.estado-srv-card.down .estado-srv-tag { background: rgba(220, 38, 38, 0.35); color: #fecaca; }
.estado-srv-foot {
  font-size: 10.5px;
  color: #94a3b8;
  padding: 6px 12px 10px;
  text-align: right;
  display: flex;
  flex-direction: column;
  gap: 6px;
  align-items: flex-end;
}
.estado-srv-foot-generated { opacity: 0.9; }
.estado-srv-foot-action {
  font-size: 11px;
  font-weight: 600;
  color: #93c5fd;
  text-align: right;
  max-width: 100%;
  word-break: break-word;
}
.estado-srv-foot-action.estado-srv-foot-action--ok { color: #86efac; }
.estado-srv-foot-action.estado-srv-foot-action--err { color: #fecaca; }
.estado-srv-foot-action.estado-srv-foot-action--wait { color: #fde68a; }
.estado-srv-panel.estado-srv-panel--busy {
  outline: 2px solid rgba(147, 197, 253, 0.45);
  outline-offset: 2px;
}
.estado-srv-ops {
  margin-top: 6px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.estado-srv-opbtn {
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(51, 65, 85, 0.9);
  color: #e2e8f0;
  border-radius: 6px;
  padding: 3px 8px;
  font-size: 10.5px;
  cursor: pointer;
  line-height: 1.25;
}
.estado-srv-opbtn:hover { background: rgba(71, 85, 105, 0.95); }
.estado-srv-opbtn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
.estado-srv-opbtn--danger {
  border-color: rgba(239, 68, 68, 0.55);
  background: rgba(127, 29, 29, 0.82);
  color: #fecaca;
}
.estado-srv-opbtn--danger:hover { background: rgba(153, 27, 27, 0.9); }

.api1click-panel {
  position: fixed;
  bottom: 95px;
  left: 24px;
  z-index: 9996;
  width: min(580px, calc(100vw - 36px));
  max-height: 62vh;
  display: none;
  background: rgba(15, 23, 42, 0.96);
  color: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 14px;
  box-shadow: 0 12px 30px rgba(2, 6, 23, 0.45);
  overflow: hidden;
}
.api1click-panel.open { display: block; }
.api1click-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 10px 12px;
  background: rgba(30, 41, 59, 0.95);
  border-bottom: 1px solid rgba(148, 163, 184, 0.35);
}
.api1click-title { font-size: 12px; font-weight: 700; letter-spacing: 0.2px; }
.api1click-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(100, 116, 139, 0.35);
}
.api1click-badge.ok { background: rgba(22, 163, 74, 0.35); color: #86efac; }
.api1click-badge.err { background: rgba(220, 38, 38, 0.35); color: #fecaca; }
.api1click-badge.run { background: rgba(13, 148, 136, 0.35); color: #99f6e4; }
.api1click-body {
  margin: 0;
  padding: 12px;
  max-height: calc(62vh - 168px);
  overflow: auto;
  font-size: 12px;
  line-height: 1.45;
  white-space: pre-wrap;
  word-break: break-word;
}
.api1click-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
  padding: 8px 10px;
  background: rgba(15, 23, 42, 0.98);
  border-bottom: 1px solid rgba(148, 163, 184, 0.25);
  font-size: 11px;
}
.api1click-toolbar select#api1clickLogSelect {
  flex: 1 1 140px;
  min-width: 0;
  max-width: 100%;
  padding: 4px 6px;
  border-radius: 6px;
  border: 1px solid rgba(148, 163, 184, 0.4);
  background: rgba(30, 41, 59, 0.9);
  color: #e2e8f0;
  font-size: 11px;
}
.api1click-tbtn {
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(51, 65, 85, 0.9);
  color: #e2e8f0;
  font-size: 11px;
  cursor: pointer;
  white-space: nowrap;
}
.api1click-tbtn:hover { background: rgba(71, 85, 105, 0.95); }
.api1click-tbtn--danger {
  border-color: rgba(239, 68, 68, 0.55);
  background: rgba(127, 29, 29, 0.82);
  color: #fecaca;
}
.api1click-tbtn--danger:hover { background: rgba(153, 27, 27, 0.9); }
body.dark-mode .api1click-tbtn--danger { color: #fecaca; }
.api1click-chk {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #94a3b8;
  cursor: pointer;
  user-select: none;
}
.api1click-chk input { accent-color: #0d9488; }
.api1click-help {
  width: 100%;
  margin: 0;
  padding: 6px 10px 0;
  font-size: 10px;
  color: #94a3b8;
  line-height: 1.35;
}
.api1doc-diag {
  padding: 8px 10px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
  background: rgba(15, 23, 42, 0.98);
}
.api1doc-diag-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}
.api1doc-diag-hd {
  flex: 1 1 180px;
  font-size: 10px;
  color: #94a3b8;
  line-height: 1.35;
  min-width: 0;
}
.api1doc-diag-body {
  margin: 0;
  padding: 8px;
  max-height: 220px;
  overflow: auto;
  font-size: 10px;
  line-height: 1.4;
  white-space: pre-wrap;
  word-break: break-word;
  background: rgba(2, 6, 23, 0.55);
  border-radius: 8px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #cbd5e1;
}
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
        <h1 class="hero-title">¡Hola, <?= htmlspecialchars($nombreCorto) ?>! <span class="inicio-easter-hand-trigger" title="Mantén pulsado" role="button" tabindex="0" style="cursor:pointer;display:inline-block;user-select:none;">👋</span></h1>
        <p class="hero-desc"><?= htmlspecialchars($heroDesc) ?></p>
      </div>
      <div class="hero-datetime" id="btnOpenClockPanel" title="Ver más información">
        <span class="hero-datetime-icon"><i class="fa-solid fa-clock"></i></span>
        <span class="cuckoo-bird" id="cuckooBird" aria-hidden="true">🐦</span>
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
      <p class="inicio-analytics-intro text-muted small mb-3">Gráficas, análisis y monitoreo. Abre cada enlace debajo para ver el reporte completo.</p>

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

    <?php if (!empty($mostrarDiagnosticoAdmin)): ?>
    <div class="inicio-btn-diagnostico-wrap" aria-label="Diagnósticos de fallos (solo admin)">
      <a href="/inicio/diagnosticoSegundometro" target="_blank" rel="noopener" class="inicio-btn-diagnostico inicio-btn-diagnostico-segundo" title="Diagnóstico: reportes Segundómetro (SSH, path, listar archivos). Analiza dónde puede estar la falla.">
        <i class="fa-solid fa-stopwatch"></i>
        <span>Segundo.</span>
      </a>
      <a href="/inicio/diagnosticoConexiones" target="_blank" rel="noopener" class="inicio-btn-diagnostico inicio-btn-diagnostico-bd" title="Diagnóstico: BD alternas (__SPARTA_SECRET_REDACTED__, SSL, certificados, red). Analiza dónde puede estar la falla.">
        <i class="fa-solid fa-database"></i>
        <span>BD alternas</span>
      </a>
    </div>
    <?php endif; ?>

    <?php if (!empty($mostrarBotonApiDocOneClick)): ?>
    <button
      type="button"
      id="btnApiDocOneClick"
      class="inicio-btn-api1click"
      title="Canal oficial (usuario 878): diagnosticar, instalar dependencias si hace falta e iniciar la API — sin ejecutar BAT a mano en el servidor."
      aria-label="API documentación: diagnóstico e inicio desde la web">
      <i class="fa-solid fa-rocket"></i>
      <span>API</span>
    </button>
    <div class="api1click-panel" id="api1clickPanel" aria-live="polite">
      <div class="api1click-hd">
        <span class="api1click-title">API documentación · flujo oficial 1‑click</span>
        <span class="api1click-badge" id="api1clickBadge">Listo</span>
      </div>
      <p class="api1click-help"><strong>Canal oficial (usuario 878):</strong> diagnóstico, instalación si hace falta y arranque de la API desde aquí — sin ejecutar BAT a mano en el servidor ni depender del PATH. Python/Tesseract dentro de la carpeta de la API los deja soporte/despliegue una vez; el día a día es este botón. Logs temporales del sistema: Lista → Ver → Copiar o Descargar.</p>
      <div class="api1doc-diag">
        <div class="api1doc-diag-row">
          <button type="button" class="api1click-tbtn" id="btnDocVerDiag878" title="Pruebas desde PHP: health, TCP, GET/POST cortos a validar-expediente (sin PDF). Puede tardar hasta ~45 s.">Diagnóstico expediente API</button>
          <span class="api1doc-diag-hd">Ejecuta en el <strong>mismo servidor que PHP</strong> las pruebas de <code>config.ini</code> [doc_verificacion] hacia la API Python (sin SSH). Útil si Capital Humano ve timeouts al verificar documentos.</span>
        </div>
        <pre class="api1doc-diag-body" id="api1docDiagOutput" role="status" aria-live="polite">Pulse «Diagnóstico expediente API» para ver el resultado JSON (puede tardar ~30–45 s en el peor caso).</pre>
      </div>
      <div class="api1click-toolbar">
        <select id="api1clickLogSelect" title="Archivos .log temporales de la API" aria-label="Seleccionar log">
          <option value="">— Lista de logs (pulsa «Lista» o espera al auto-actualizar) —</option>
        </select>
        <button type="button" class="api1click-tbtn" id="api1clickBtnRefreshList" title="Actualizar lista de logs">Lista</button>
        <button type="button" class="api1click-tbtn" id="api1clickBtnView" title="Mostrar contenido aquí abajo">Ver log</button>
        <label class="api1click-chk" title="Si el archivo es muy grande, el servidor envía solo los últimos 512 KiB">
          <input type="checkbox" id="api1clickLogCompleto" /> Completo
        </label>
        <button type="button" class="api1click-tbtn" id="api1clickBtnCopy" title="Copiar texto visible al portapapeles">Copiar</button>
        <button type="button" class="api1click-tbtn" id="api1clickBtnDownload" title="Descargar .log">Descargar</button>
        <button type="button" class="api1click-tbtn api1click-tbtn--danger" id="api1clickBtnClearLogs" title="Borrar todos los .log temporales acumulados de la API">Borrar logs</button>
        <button type="button" class="api1click-tbtn" id="api1clickBtnOlvidar" title="Solo quita el bloqueo en la web; no mata procesos en el servidor">Desbloquear panel</button>
        <button type="button" class="api1click-tbtn api1click-tbtn--danger" id="api1clickBtnParar" title="Corta en el servidor esta ejecución (batch/doctor/pip/python de esta API + puerto 8001)">Parar ejecución</button>
      </div>
      <pre class="api1click-body" id="api1clickOutput">Sin ejecución todavía.</pre>
    </div>
    <?php endif; ?>

    <?php if (!empty($mostrarBotonEstadoServicios)): ?>
    <button
      type="button"
      id="btnEstadoServicios"
      class="inicio-btn-estado-srv"
      title="Estado de todos los agentes y APIs locales (puertos 3001, 3100, 3110, 3120, 8001). Solo usuario 878."
      aria-label="Estado de servicios locales">
      <i class="fa-solid fa-heart-pulse"></i>
      <span>Servicios</span>
    </button>
    <div class="estado-srv-panel" id="estadoSrvPanel" aria-live="polite">
      <div class="estado-srv-hd">
        <span class="estado-srv-title">Servicios locales · estado en vivo</span>
        <span class="estado-srv-summary" id="estadoSrvSummary">—</span>
        <span class="estado-srv-actions">
          <label class="estado-srv-chk" title="Refresca cada 5s">
            <input type="checkbox" id="estadoSrvAuto" /> Auto
          </label>
          <button type="button" class="estado-srv-tbtn" id="estadoSrvBtnRefresh" title="Refrescar ahora">Refrescar</button>
        </span>
      </div>
      <p class="estado-srv-help">Verde = PHP en el servidor pudo hablar con el servicio por <code>127.0.0.1</code>. En la API Python de documentos tambien valida un POST minimo a <code>/validar-expediente</code>; si solo responde health, queda en revisar.</p>
      <div class="estado-srv-grid" id="estadoSrvGrid">
        <div class="estado-srv-loading">Cargando estado…</div>
      </div>
      <div class="estado-srv-foot" id="estadoSrvFoot">
        <div class="estado-srv-foot-generated" id="estadoSrvFootGenerated"></div>
        <div class="estado-srv-foot-action" id="estadoSrvFootAction" role="status" aria-live="polite"></div>
      </div>
    </div>
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
    if (window._cuckooActive) return;
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
  loadCpQuote(); // Frase del día desde servidor (misma para todos)

  // Panel reloj
  var btnOpen = document.getElementById('btnOpenClockPanel');
  var clockPanel = document.getElementById('clockPanel');
  var clockOverlay = document.getElementById('clockOverlay');
  var cdmxNow = getCDMXDate();
  var cpCy = cdmxNow.getFullYear();
  var cpCm = cdmxNow.getMonth();

  // Frase del día: misma para todos (servidor elige por fecha CDMX)
  function loadCpQuote(){
    var elText = document.getElementById('cpQuoteText');
    var elAuthor = document.getElementById('cpQuoteAuthor');
    if(!elText || !elAuthor) return;
    fetch('/inicio/fraseDelDia')
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(data && (data.success || data.texto)){
          elText.textContent = '"' + (data.texto || 'El éxito es la suma de pequeños esfuerzos repetidos día tras día.') + '"';
          elAuthor.textContent = '— ' + (data.autor || 'Robert Collier');
        }
        applyQuoteColors();
      })
      .catch(function(){
        elText.textContent = '"El éxito es la suma de pequeños esfuerzos repetidos día tras día."';
        elAuthor.textContent = '— Robert Collier';
        applyQuoteColors();
      });
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

  // Días de pago quincena: 15 y fin de mes; si caen sábado o domingo → viernes anterior
  function getQuincenaPaymentDays(year, month) {
    var d15 = new Date(year, month, 15);
    var dow15 = d15.getDay(); // 0=Dom, 6=Sab
    var pay15 = (dow15 === 0) ? 13 : (dow15 === 6) ? 14 : 15;

    var lastDay = new Date(year, month + 1, 0).getDate();
    var dLast = new Date(year, month, lastDay);
    var dowLast = dLast.getDay();
    var payLast = (dowLast === 0) ? lastDay - 2 : (dowLast === 6) ? lastDay - 1 : lastDay;

    return pay15 === payLast ? [pay15] : [pay15, payLast];
  }

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
    var now = Date.now();
    if (!btnOpen._clockClicks) btnOpen._clockClicks = { count: 0, firstAt: 0, resetTimer: null, openTimer: null };
    var data = btnOpen._clockClicks;
    data.count++;
    if (data.resetTimer) clearTimeout(data.resetTimer);
    data.resetTimer = setTimeout(function(){ data.count = 0; }, 2000);
    if (data.count >= 5) {
      data.count = 0;
      if (data.openTimer) clearTimeout(data.openTimer);
      startCuckooMode();
      return;
    }
    if (data.count === 1) {
      if (data.openTimer) clearTimeout(data.openTimer);
      data.openTimer = setTimeout(function(){
        if (btnOpen._clockClicks && btnOpen._clockClicks.count >= 1 && btnOpen._clockClicks.count < 5) openPanel();
      }, 320);
    } else if (data.count > 1) {
      if (data.openTimer) clearTimeout(data.openTimer);
      data.openTimer = null;
    }
  });
  clockOverlay.addEventListener('click', closePanel);
  clockPanel.addEventListener('click', function(e){ e.stopPropagation(); });

  // Easter egg: modo cuco (pajarito + ¡Cú-cú!) tras 5 clics rápidos en el reloj. Sonido: public/assets/audio/cuckoo.mp3 en bucle con pausa entre repeticiones.
  function startCuckooMode() {
    var elTime = document.getElementById('inicioTime');
    var bird = document.getElementById('cuckooBird');
    if (!elTime || !btnOpen) return;
    window._cuckooActive = true;
    btnOpen.classList.add('cuckoo-mode');
    var cuckooAudio = new Audio('/assets/audio/cuckoo.mp3');
    cuckooAudio.volume = 0.8;
    var pausaEntreRepeticiones = 350;
    var cuckooSigueActivo = true;
    function reproducirUnaVez() {
      if (!cuckooSigueActivo) return;
      cuckooAudio.currentTime = 0;
      cuckooAudio.play().catch(function(){});
    }
    function enCucuEnded() {
      if (!cuckooSigueActivo) return;
      setTimeout(reproducirUnaVez, pausaEntreRepeticiones);
    }
    cuckooAudio.addEventListener('ended', enCucuEnded);
    reproducirUnaVez();
    var count = 0;
    function tick() {
      elTime.textContent = count % 2 === 0 ? '¡Cú-' : 'cú!';
      if (bird) {
        bird.classList.remove('cuckoo-peek');
        void bird.offsetWidth;
        bird.classList.add('cuckoo-peek');
      }
      count++;
    }
    tick();
    var iv = setInterval(tick, 500);
    setTimeout(function() {
      clearInterval(iv);
      cuckooSigueActivo = false;
      cuckooAudio.pause();
      cuckooAudio.currentTime = 0;
      cuckooAudio.removeEventListener('ended', enCucuEnded);
      btnOpen.classList.remove('cuckoo-mode');
      window._cuckooActive = false;
      updateInicioDateTime();
    }, 8000);
  }

  // Actualizar hora del panel (CDMX)
  function updateClockPanel(){
    var n = getCDMXDate();
    document.getElementById('cpTime').textContent = pad(n.getHours()) + ':' + pad(n.getMinutes()) + ':' + pad(n.getSeconds());
    var dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    var meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    document.getElementById('cpDate').textContent = dias[n.getDay()] + ', ' + n.getDate() + ' de ' + meses[n.getMonth()] + ' ' + n.getFullYear();
  }

  // Calendario con eventos (puntos) y días de pago quincena
  function renderCpCal(){
    var MN = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    document.getElementById('cpCalMonth').textContent = MN[cpCm] + ' ' + cpCy;
    var fd = new Date(cpCy, cpCm, 1).getDay();
    var tot = new Date(cpCy, cpCm+1, 0).getDate();
    var now = getCDMXDate();
    var td = (now.getMonth() === cpCm && now.getFullYear() === cpCy) ? now.getDate() : -1;
    var quincenaDays = getQuincenaPaymentDays(cpCy, cpCm);
    var dns = ['D','L','M','X','J','V','S'];
    var h = dns.map(function(d){ return '<div class="cp-cal-dn">' + d + '</div>'; }).join('');
    for (var i = 0; i < fd; i++) h += '<div class="cp-cal-d empty"></div>';
    for (var d = 1; d <= tot; d++){
      var c = 'cp-cal-d';
      if (d === td) c += ' today';
      if (quincenaDays.indexOf(d) !== -1) c += ' quincena';
      var eventKey = (cpCm + 1) + '-' + d;
      var hasEvent = EVENTOS_MX[eventKey];
      var eventDot = hasEvent ? '<span class="cp-event-dot" title="' + hasEvent + '"></span>' : '';
      var quincenaDot = (quincenaDays.indexOf(d) !== -1) ? '<span class="cp-quincena-dot" aria-hidden="true"></span>' : '';
      h += '<div class="' + c + '" title="' + (quincenaDays.indexOf(d) !== -1 ? 'Día de pago quincena (doble clic)' : '') + '">' + d + eventDot + quincenaDot + '</div>';
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

  // Easter egg: doble clic en día quincena → billete desvaneciente + lluvia de dinero
  document.getElementById('cpCalGrid').addEventListener('dblclick', function(e){
    var cell = e.target.closest('.cp-cal-d.quincena');
    if (!cell) return;
    var wrap = document.getElementById('cpMoneyRainWrap');
    if (wrap) return;

    // Billete desvaneciente en el punto del clic (como condonar)
    var billete = document.createElement('div');
    billete.className = 'cp-billete-fade';
    billete.innerHTML = '💵';
    billete.style.left = e.clientX + 'px';
    billete.style.top = e.clientY + 'px';
    document.body.appendChild(billete);
    setTimeout(function(){ if (billete.parentNode) billete.parentNode.removeChild(billete); }, 1300);

    wrap = document.createElement('div');
    wrap.id = 'cpMoneyRainWrap';
    wrap.className = 'cp-money-rain';
    var chars = ['$', '💰', '💵', '¢', '💲', '💴'];
    for (var i = 0; i < 120; i++) {
      var el = document.createElement('span');
      el.className = 'cp-money-item';
      el.textContent = chars[Math.floor(Math.random() * chars.length)];
      el.style.left = (Math.random() * 100) + '%';
      el.style.animationDelay = (Math.random() * 1.5) + 's';
      el.style.fontSize = (16 + Math.random() * 16) + 'px';
      wrap.appendChild(el);
    }
    document.body.appendChild(wrap);
    var moneySound = new Audio('/assets/audio/coins.mp3');
    moneySound.volume = 0.5;
    moneySound.play().catch(function(){});
    setTimeout(function(){
      if (wrap.parentNode) wrap.parentNode.removeChild(wrap);
    }, 5500);
  });

  // Frase del día (cargada desde servidor: misma para todos) — loadCpQuote() definida arriba con fetch /inicio/fraseDelDia
  
  function applyQuoteColors(){
    var isDark = document.body.classList.contains('dark-mode');
    var labelEl = document.querySelector('.cp-quote-label');
    var authorEl = document.getElementById('cpQuoteAuthor');
    if(labelEl) labelEl.style.color = '#d97706';
    if(authorEl) authorEl.style.color = isDark ? '#9ca3af' : '#6b7280';
  }

  var weatherCache = { temp: null, code: null, time: 0 };
  var WEATHER_CACHE_MS = 2 * 60 * 60 * 1000; // 2 horas

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

  // Easter: long-press (~1.5s) en icono de la mano -> mano grande saludando + hola.mp3
  (function(){
    var HOLD_MS = 1500;
    var timer = null;
    var handJustTriggered = false;
    function runHandEaster(e){
      e.preventDefault();
      e.stopPropagation();
      handJustTriggered = true;
      setTimeout(function(){ handJustTriggered = false; }, 400);
      var wrap = document.createElement('div');
      wrap.className = 'inicio-easter-hand-wrap';
      wrap.setAttribute('aria-hidden', 'true');
      var ico = document.createElement('span');
      ico.className = 'inicio-easter-hand-ico';
      ico.innerHTML = '\uD83D\uDC4B';
      wrap.appendChild(ico);
      document.body.appendChild(wrap);
      var audio = new Audio('/assets/audio/hola.mp3');
      audio.volume = 0.6;
      audio.play().catch(function(){});
      var dur = 3200;
      setTimeout(function(){ audio.pause(); audio.currentTime = 0; }, dur);
      setTimeout(function(){
        wrap.style.opacity = '0';
        wrap.style.transition = 'opacity .3s ease';
        setTimeout(function(){ if(wrap.parentNode) wrap.parentNode.removeChild(wrap); }, 350);
      }, dur);
    }
    function startHold(ev){ if(timer) return; timer = setTimeout(function(){ timer = null; runHandEaster(ev); }, HOLD_MS); }
    function cancelHold(){ if(timer){ clearTimeout(timer); timer = null; } }
    document.addEventListener('click', function(e){
      var trg = e.target.closest('.inicio-easter-hand-trigger');
      if(!trg || !handJustTriggered) return;
      e.preventDefault();
      e.stopPropagation();
    }, true);
    document.querySelectorAll('.inicio-easter-hand-trigger').forEach(function(el){
      el.addEventListener('mousedown', function(ev){ startHold(ev); }, false);
      el.addEventListener('mouseup', cancelHold);
      el.addEventListener('mouseleave', cancelHold);
      el.addEventListener('touchstart', function(ev){ ev.preventDefault(); startHold(ev); }, { passive: false });
      el.addEventListener('touchend', function(ev){ ev.preventDefault(); cancelHold(); }, { passive: false });
      el.addEventListener('touchcancel', cancelHold);
    });
  })();

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

  // API documentación 1-click (solo usuario 878)
  var btnApi1 = document.getElementById('btnApiDocOneClick');
  var panelApi1 = document.getElementById('api1clickPanel');
  var badgeApi1 = document.getElementById('api1clickBadge');
  var outApi1 = document.getElementById('api1clickOutput');
  var api1Timer = null;
  var api1Running = false;
  var selApi1Logs = document.getElementById('api1clickLogSelect');
  var btnApi1RefreshList = document.getElementById('api1clickBtnRefreshList');
  var btnApi1View = document.getElementById('api1clickBtnView');
  var btnApi1Copy = document.getElementById('api1clickBtnCopy');
  var btnApi1Download = document.getElementById('api1clickBtnDownload');
  var btnApi1ClearLogs = document.getElementById('api1clickBtnClearLogs');
  var btnApi1Olvidar = document.getElementById('api1clickBtnOlvidar');
  var btnApi1Parar = document.getElementById('api1clickBtnParar');
  var chkApi1Completo = document.getElementById('api1clickLogCompleto');
  var api1ListaPollTicks = 0;

  /** Cabeceras para que index.php devuelva JSON en errores (evita HTML login y fallo "Unexpected token <"). */
  function api1AjaxHeaders() {
    return {
      'X-Requested-With': 'XMLHttpRequest',
      'Front-Request': 'true'
    };
  }
  function api1ParseJsonBody(text, urlHint) {
    var t = (text || '').trim();
    if (t.charAt(0) === '<') {
      throw new Error('El servidor respondió HTML en lugar de JSON (ruta ' + (urlHint || '') + ' ausiente, sesión caducada o error PHP). Actualice código y vuelva a iniciar sesión.');
    }
    try {
      return JSON.parse(t);
    } catch (e2) {
      throw new Error('JSON inválido en ' + (urlHint || '') + ': ' + (e2 && e2.message ? e2.message : e2));
    }
  }

  function api1RefreshLogList() {
    if (!selApi1Logs) return;
    selApi1Logs.disabled = true;
    var cur = selApi1Logs.value;
    fetch('/inicio/apidocloglistar', { credentials: 'same-origin', headers: api1AjaxHeaders() })
      .then(function(r){
        return r.text().then(function(body){
          if (!r.ok) {
            throw new Error('HTTP ' + r.status + ' — ' + (body.slice(0, 120) || r.statusText));
          }
          return api1ParseJsonBody(body, 'apidocloglistar');
        });
      })
      .then(function(data){
        selApi1Logs.innerHTML = '';
        if (!data || !data.success) {
          var ox = document.createElement('option');
          ox.value = '';
          ox.textContent = data && data.message ? String(data.message) : 'No se pudo listar logs (¿sesión?).';
          selApi1Logs.appendChild(ox);
          return;
        }
        var files = data.files || [];
        if (files.length === 0) {
          var o0 = document.createElement('option');
          o0.value = '';
          o0.textContent = '(sin archivos .log temporales)';
          selApi1Logs.appendChild(o0);
          return;
        }
        files.forEach(function(f){
          var opt = document.createElement('option');
          opt.value = f.name;
          var kb = Math.round((f.size || 0) / 1024);
          opt.textContent = f.name + ' (' + kb + ' KiB)';
          selApi1Logs.appendChild(opt);
        });
        if (cur && Array.prototype.some.call(selApi1Logs.options, function(o){ return o.value === cur; })) {
          selApi1Logs.value = cur;
        }
      })
      .catch(function(e){
        selApi1Logs.innerHTML = '';
        var oe = document.createElement('option');
        oe.value = '';
        oe.textContent = 'Error al cargar lista: ' + (e && e.message ? e.message : e);
        selApi1Logs.appendChild(oe);
      })
      .finally(function(){ selApi1Logs.disabled = false; });
  }
  function api1ViewSelectedLog() {
    if (!selApi1Logs || !selApi1Logs.value) {
      alert('Primero pulsa «Lista» y elige un archivo .log.');
      return;
    }
    var u = '/inicio/apidoclogcontenido?archivo=' + encodeURIComponent(selApi1Logs.value);
    if (chkApi1Completo && chkApi1Completo.checked) u += '&completo=1';
    if (outApi1) outApi1.textContent = 'Cargando…';
    fetch(u, { credentials: 'same-origin', headers: api1AjaxHeaders() })
      .then(function(r){
        return r.text().then(function(body){
          if (!r.ok) throw new Error('HTTP ' + r.status);
          return api1ParseJsonBody(body, 'apidoclogcontenido');
        });
      })
      .then(function(data){
        if (!data || !data.success) {
          if (outApi1) outApi1.textContent = (data && data.message) ? data.message : 'No se pudo leer el log.';
          return;
        }
        var t = data.contenido != null ? String(data.contenido) : '';
        if (data.nota) t = String(data.nota) + '\n\n' + t;
        if (data.truncado && !data.nota) t = '(Fragmento: archivo grande)\n\n' + t;
        if (outApi1) outApi1.textContent = t;
      })
      .catch(function(e){
        if (outApi1) outApi1.textContent = 'Error: ' + (e && e.message ? e.message : e);
      });
  }
  function api1CopyVisibleLog() {
    if (!outApi1) return;
    var t = outApi1.textContent || '';
    if (!t.trim()) {
      alert('No hay texto para copiar.');
      return;
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(t).then(function(){
        alert('Copiado al portapapeles. Pégalo en el chat de soporte.');
      }).catch(function(){
        api1CopyFallback(t);
      });
    } else {
      api1CopyFallback(t);
    }
  }
  function api1CopyFallback(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
      alert('Copiado.');
    } catch (e) {
      alert('No se pudo copiar automáticamente; selecciona el texto manualmente.');
    }
    document.body.removeChild(ta);
  }
  function api1DownloadSelectedLog() {
    if (!selApi1Logs || !selApi1Logs.value) {
      alert('Elige un archivo y usa «Lista» si hace falta.');
      return;
    }
    window.location.href = '/inicio/apidoclogdescargar?archivo=' + encodeURIComponent(selApi1Logs.value);
  }
  function api1ClearLogs() {
    if (!confirm('¿Borrar todos los logs temporales acumulados de la API? Esto no detiene la API; solo limpia archivos .log.')) return;
    if (btnApi1ClearLogs) btnApi1ClearLogs.disabled = true;
    if (outApi1) outApi1.textContent = 'Borrando logs...';
    fetch('/inicio/apidocloglimpiar', { method: 'POST', credentials: 'same-origin', headers: api1AjaxHeaders() })
      .then(function(r){
        return r.text().then(function(body){
          if (!r.ok) throw new Error(body.slice(0, 180) || ('HTTP ' + r.status));
          return api1ParseJsonBody(body, 'apidocloglimpiar');
        });
      })
      .then(function(data){
        var msg = data && data.message ? String(data.message) : 'Limpieza terminada.';
        if (data && data.failed_total) {
          msg += '\nNo borrados (' + data.failed_total + '): ' + (data.failed || []).join(', ');
        }
        if (outApi1) outApi1.textContent = msg;
        if (selApi1Logs) selApi1Logs.value = '';
        api1RefreshLogList();
      })
      .catch(function(e){
        if (outApi1) outApi1.textContent = 'No se pudieron borrar los logs: ' + (e && e.message ? e.message : e);
      })
      .finally(function(){
        if (btnApi1ClearLogs) btnApi1ClearLogs.disabled = false;
      });
  }
  if (btnApi1RefreshList) btnApi1RefreshList.addEventListener('click', api1RefreshLogList);
  if (btnApi1View) btnApi1View.addEventListener('click', api1ViewSelectedLog);
  if (btnApi1Copy) btnApi1Copy.addEventListener('click', api1CopyVisibleLog);
  if (btnApi1Download) btnApi1Download.addEventListener('click', api1DownloadSelectedLog);
  if (btnApi1ClearLogs) btnApi1ClearLogs.addEventListener('click', api1ClearLogs);
  if (btnApi1Olvidar) btnApi1Olvidar.addEventListener('click', function(){
    if (!confirm('¿Desbloquear el panel? Podrás pulsar «API» otra vez. Un proceso ya lanzado puede seguir en el servidor hasta terminar.')) return;
    fetch('/inicio/apidoconeclickolvidar', { method: 'POST', credentials: 'same-origin', headers: api1AjaxHeaders() })
      .then(function(r){
        return r.text().then(function(body){
          return api1ParseJsonBody(body, 'apidoconeclickolvidar');
        });
      })
      .then(function(data){
        if (data && data.success) {
          api1StopPolling();
          api1SetState('ok', 'Listo');
          if (outApi1) outApi1.textContent = data.message || 'Panel desbloqueado.';
          if (btnApi1) btnApi1.classList.remove('running');
        } else if (outApi1) outApi1.textContent = (data && data.message) ? data.message : 'No autorizado.';
      })
      .catch(function(){ if (outApi1) outApi1.textContent = 'No se pudo desbloquear (red o sesión).'; });
  });
  if (btnApi1Parar) btnApi1Parar.addEventListener('click', function(){
    if (!confirm('¿PARAR esta ejecución en el servidor? Se intentará cerrar doctor/instalar/batch relacionados con esta API, liberar el puerto 8001 y procesos Python cuya línea de comando incluya esta carpeta. Luego podrás pulsar «API» otra vez.\n\nSolo otros Python de otros proyectos NO deberían verse afectados si no usan esa ruta.')) return;
    fetch('/inicio/apidoconeclickparar', { method: 'POST', credentials: 'same-origin', headers: api1AjaxHeaders() })
      .then(function(r){
        return r.text().then(function(body){
          if (!r.ok) throw new Error(body.slice(0, 200) || ('HTTP ' + r.status));
          return api1ParseJsonBody(body, 'apidoconeclickparar');
        });
      })
      .then(function(data){
        api1StopPolling();
        if (btnApi1) btnApi1.classList.remove('running');
        if (data && data.success) {
          api1SetState('ok', 'Listo');
          if (outApi1) outApi1.textContent = data.message || 'Parada solicitada.';
          if (data.log_file) api1RefreshLogList();
        } else {
          api1SetState('err', 'Error');
          if (outApi1) outApi1.textContent = (data && data.message) ? data.message : 'No se pudo iniciar parada.';
        }
      })
      .catch(function(){ api1SetState('err', 'Error'); if (outApi1) outApi1.textContent = 'Error de red al parar.'; });
  });

  var btnDocVerDiag878 = document.getElementById('btnDocVerDiag878');
  var outDocVerDiag = document.getElementById('api1docDiagOutput');
  var docVerDiagBusy = false;
  if (btnDocVerDiag878 && outDocVerDiag) {
    btnDocVerDiag878.addEventListener('click', function () {
      if (docVerDiagBusy) return;
      docVerDiagBusy = true;
      btnDocVerDiag878.disabled = true;
      outDocVerDiag.textContent = 'Ejecutando pruebas en el servidor…';
      fetch('/inicio/docVerificacionDiagnostico878', { credentials: 'same-origin', headers: api1AjaxHeaders() })
        .then(function (r) {
          return r.text().then(function (body) {
            if (!r.ok) {
              throw new Error('HTTP ' + r.status + ' — ' + (body.slice(0, 200) || r.statusText));
            }
            return api1ParseJsonBody(body, 'docVerificacionDiagnostico878');
          });
        })
        .then(function (data) {
          outDocVerDiag.textContent = JSON.stringify(data, null, 2);
          try {
            console.log('[Sparta inicio · docVerificacionDiagnostico878] resultado JSON (copiar desde Consola o desde el panel)', data);
          } catch (e) {}
        })
        .catch(function (err) {
          outDocVerDiag.textContent = 'Error: ' + (err && err.message ? err.message : String(err));
          try {
            console.error('[Sparta inicio · docVerificacionDiagnostico878] fallo', { message: err && err.message, stack: err && err.stack });
          } catch (e) {}
        })
        .finally(function () {
          docVerDiagBusy = false;
          btnDocVerDiag878.disabled = false;
        });
    });
  }

  function api1SetState(kind, text) {
    if (!badgeApi1) return;
    badgeApi1.className = 'api1click-badge';
    if (kind === 'ok') badgeApi1.classList.add('ok');
    if (kind === 'err') badgeApi1.classList.add('err');
    if (kind === 'run') badgeApi1.classList.add('run');
    badgeApi1.textContent = text || 'Listo';
  }
  function api1OpenPanel() {
    if (panelApi1) panelApi1.classList.add('open');
  }
  function api1StopPolling() {
    if (api1Timer) {
      clearInterval(api1Timer);
      api1Timer = null;
    }
    api1Running = false;
    if (btnApi1) btnApi1.classList.remove('running');
  }
  function api1StartPolling() {
    if (api1Timer) return;
    api1Timer = setInterval(api1FetchEstado, 2500);
  }
  function api1RenderTail(data) {
    if (!outApi1) return;
    var tail = (data && data.output_tail) ? String(data.output_tail) : '';
    outApi1.textContent = tail || 'Ejecutando...';
  }
  function api1FetchEstado() {
    fetch('/inicio/apidoconeclickestado', { credentials: 'same-origin', headers: api1AjaxHeaders() })
      .then(function(r){
        return r.text().then(function(body){
          if (!r.ok) throw new Error('HTTP ' + r.status);
          return api1ParseJsonBody(body, 'apidoconeclickestado');
        });
      })
      .then(function(data){
        if (!data || data.success === false) {
          api1SetState('err', 'Error');
          if (outApi1) outApi1.textContent = (data && data.message) ? data.message : 'No se pudo consultar estado.';
          api1StopPolling();
          return;
        }
        api1ListaPollTicks += 1;
        if (api1ListaPollTicks === 1 || api1ListaPollTicks % 4 === 0) {
          api1RefreshLogList();
        }
        api1RenderTail(data);
        if (data.completed) {
          if (typeof data.exit_code === 'number' && data.exit_code === 0) {
            api1SetState('ok', 'OK');
          } else {
            api1SetState('err', 'Con errores');
          }
          api1RefreshLogList();
          api1StopPolling();
          return;
        }
        if (data.is_running) {
          api1SetState('run', 'Ejecutando');
          if (btnApi1) btnApi1.classList.add('running');
        } else {
          api1SetState('ok', 'Listo');
        }
      })
      .catch(function(err){
        api1SetState('err', 'Error');
        if (outApi1) outApi1.textContent = 'Error consultando estado: ' + (err && err.message ? err.message : err);
        api1StopPolling();
      });
  }
  if (btnApi1) {
    btnApi1.addEventListener('click', function(){
      api1ListaPollTicks = 0;
      api1OpenPanel();
      api1RefreshLogList();
      if (outApi1) {
        outApi1.textContent = 'Iniciando flujo 1-click...';
      }
      api1SetState('run', 'Lanzando');
      btnApi1.classList.add('running');
      fetch('/inicio/apidoconeclickiniciar', { method: 'POST', credentials: 'same-origin', headers: api1AjaxHeaders() })
        .then(function(r){
          return r.text().then(function(body){
            if (!r.ok) throw new Error(body.slice(0, 160) || ('HTTP ' + r.status));
            return api1ParseJsonBody(body, 'apidoconeclickiniciar');
          });
        })
        .then(function(data){
          if (!data || data.success === false) {
            api1SetState('err', 'Error');
            if (outApi1) outApi1.textContent = (data && data.message) ? data.message : 'No se pudo iniciar la ejecución.';
            api1StopPolling();
            return;
          }
          api1Running = true;
          if (outApi1) {
            var lines = [];
            if (data.message) lines.push(data.message);
            if (data.log_file) lines.push('Log: ' + data.log_file);
            lines.push('Consultando avance...');
            outApi1.textContent = lines.join('\n');
          }
          api1SetState('run', 'Ejecutando');
          api1FetchEstado();
          api1StartPolling();
        })
        .catch(function(err){
          api1SetState('err', 'Error');
          if (outApi1) outApi1.textContent = 'Error al lanzar: ' + (err && err.message ? err.message : err);
          api1StopPolling();
        });
    });
  }
})();

(function(){
  // Panel "Servicios locales" (solo usuario 878).
  var btnEstado = document.getElementById('btnEstadoServicios');
  if (!btnEstado) return;
  var panel = document.getElementById('estadoSrvPanel');
  var grid  = document.getElementById('estadoSrvGrid');
  var summary = document.getElementById('estadoSrvSummary');
  var foot = document.getElementById('estadoSrvFoot');
  var footGen = document.getElementById('estadoSrvFootGenerated');
  var footAct = document.getElementById('estadoSrvFootAction');
  var btnRefresh = document.getElementById('estadoSrvBtnRefresh');
  var chkAuto = document.getElementById('estadoSrvAuto');
  var pollTimer = null;
  var inFlight = false;
  var serviceCtlBusy = false;

  function escapeHtml(s){
    return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
    });
  }
  function srvAjaxHeaders() {
    return { 'X-Requested-With': 'XMLHttpRequest', 'Front-Request': '1' };
  }
  function srvParseJsonBody(text, urlHint) {
    var t = (typeof text === 'string') ? text.trim() : '';
    if (t === '') return {};
    try { return JSON.parse(t); }
    catch (_e) { throw new Error('JSON inválido en ' + urlHint + ': ' + t.slice(0, 180)); }
  }
  function srvPostJson(url, bodyObj) {
    var body = new URLSearchParams();
    Object.keys(bodyObj || {}).forEach(function(k){
      body.append(k, bodyObj[k]);
    });
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: Object.assign({ 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, srvAjaxHeaders()),
      body: body.toString()
    })
      .then(function(r){
        return r.text().then(function(body){
          var data = srvParseJsonBody(body, url);
          if (!r.ok) {
            var msg = (data && (data.message || data.error)) ? (data.message || data.error) : ('HTTP ' + r.status);
            throw new Error(msg);
          }
          return data || {};
        });
      });
  }
  function srvSetActionFoot(text, cls) {
    if (!footAct) return;
    footAct.textContent = text || '';
    footAct.className = 'estado-srv-foot-action' + (cls ? ' ' + cls : '');
  }
  function srvDoServiceAction(serviceId, serviceName, action) {
    if (serviceCtlBusy) return;
    serviceCtlBusy = true;
    if (panel) panel.classList.add('estado-srv-panel--busy');
    var msg = action + ' «' + serviceName + '»…';
    srvSetActionFoot('⏳ Enviando orden: ' + msg, 'estado-srv-foot-action--wait');
    srvPostJson('/inicio/serviciosLocalesAccion', { service: serviceId, action: action })
      .then(function(data){
        var m = (data && data.message) ? data.message : 'Orden registrada.';
        var hint = (data && data.hint_post) ? (' ' + data.hint_post) : '';
        var ok = data && (data.success === true || data.success === 'true');
        var estado = data && data.estado;
        var arriba = estado === 'up';
        var semi = estado === 'listen_no_http' || estado === 'degraded';
        var line = m + hint;
        if (arriba) {
          srvSetActionFoot('✓ ' + line, 'estado-srv-foot-action--ok');
        } else if (semi) {
          srvSetActionFoot('⚠ ' + line + ' El proceso escucha en el puerto pero la URL de comprobación no respondió como se espera.', 'estado-srv-foot-action--wait');
        } else if (!ok) {
          srvSetActionFoot('⚠ ' + line + ' Si acaba de arrancar, pulse Refrescar o espere el auto-actualizado (cada 5 s).', 'estado-srv-foot-action--err');
        } else {
          srvSetActionFoot('ℹ ' + line, 'estado-srv-foot-action--wait');
        }
      })
      .catch(function(err){
        srvSetActionFoot('✗ Error: ' + (err && err.message ? err.message : String(err)), 'estado-srv-foot-action--err');
        try {
          console.error('[Sparta inicio · serviciosLocalesAccion]', { serviceId: serviceId, serviceName: serviceName, action: action, message: err && err.message, stack: err && err.stack });
        } catch (e) {}
      })
      .finally(function(){
        serviceCtlBusy = false;
        if (panel) panel.classList.remove('estado-srv-panel--busy');
        fetchEstado();
      });
  }

  function renderEstado(data){
    if (!data || !data.success) {
      grid.innerHTML = '<div class="estado-srv-loading">No se pudo obtener el estado.</div>';
      summary.textContent = '—';
      summary.className = 'estado-srv-summary err';
      if (footGen) footGen.textContent = 'Generado: —';
      return;
    }
    var s = data.summary || { up:0, down:0, total:0 };
    summary.textContent = s.up + '/' + s.total + ' arriba';
    summary.className = 'estado-srv-summary ' + (s.down === 0 ? 'ok' : (s.up === 0 ? 'err' : 'warn'));
    btnEstado.classList.toggle('alerta', s.down > 0);

    var html = '';
    var services = data.services || [];
    services.forEach(function(srv){
      var cls, tag;
      if (srv.estado === 'up') { cls = 'up'; tag = 'ARRIBA'; }
      else if (srv.estado === 'degraded') { cls = 'warn'; tag = 'REVISA'; }
      else if (srv.estado === 'listen_no_http') { cls = 'warn'; tag = 'EN PUERTO'; }
      else { cls = 'down'; tag = 'CAÍDA'; }
      var lat = (srv.latency_ms != null) ? (srv.latency_ms + ' ms') : '—';
      var status = (srv.http_status != null) ? srv.http_status : '—';
      var pid = srv.pid ? (' · PID ' + srv.pid) : '';
      var browseLine = srv.url_browser
        ? '<a href="' + escapeHtml(srv.url_browser) + '" target="_blank" rel="noopener">' + escapeHtml(srv.url_browser) + '</a>'
        : '<span>' + escapeHtml(srv.browser_note || 'Prueba interna del servidor; sin enlace externo necesario.') + '</span>';
      html += '<div class="estado-srv-card ' + cls + '">';
      html += '  <span class="dot"></span>';
      html += '  <div>';
      html += '    <div class="estado-srv-name">' + escapeHtml(srv.name) + '</div>';
      html += '    <div class="estado-srv-meta">Puerto <strong>' + srv.port + '</strong>' + pid + ' · HTTP ' + escapeHtml(status) + ' · ' + escapeHtml(lat) + '</div>';
      html += '    <div class="estado-srv-meta">' + escapeHtml(srv.role || '') + '</div>';
      if (srv.functional_check) {
        var fc = srv.functional_check || {};
        var fcStatus = fc.status != null ? fc.status : '---';
        var fcMs = fc.ms != null ? (fc.ms + ' ms') : '---';
        html += '    <div class="estado-srv-meta">Check funcional: <strong>' + (fc.ok ? 'OK' : 'FALLA') + '</strong> - HTTP ' + escapeHtml(fcStatus) + ' - ' + escapeHtml(fcMs) + '</div>';
        if (!fc.ok && fc.message) {
          html += '    <div class="estado-srv-meta">' + escapeHtml(fc.message) + '</div>';
        }
      }
      if (cls === 'up') {
        html += '    <div class="estado-srv-meta">Check interno: <code>' + escapeHtml(srv.url_check || '') + '</code></div>';
        html += '    <div class="estado-srv-meta">' + browseLine + '</div>';
      } else {
        html += '    <div class="estado-srv-meta">' + escapeHtml(srv.hint || '') + '</div>';
      }
      if (srv.can_control !== false) {
        var dis = serviceCtlBusy ? ' disabled' : '';
        html += '    <div class="estado-srv-ops">';
        html += '      <button type="button" class="estado-srv-opbtn" data-srv-id="' + escapeHtml(srv.id) + '" data-srv-name="' + escapeHtml(srv.name) + '" data-srv-action="iniciar"' + dis + '>Iniciar</button>';
        html += '      <button type="button" class="estado-srv-opbtn" data-srv-id="' + escapeHtml(srv.id) + '" data-srv-name="' + escapeHtml(srv.name) + '" data-srv-action="reiniciar"' + dis + '>Reiniciar</button>';
        html += '      <button type="button" class="estado-srv-opbtn estado-srv-opbtn--danger" data-srv-id="' + escapeHtml(srv.id) + '" data-srv-name="' + escapeHtml(srv.name) + '" data-srv-action="parar"' + dis + '>Parar</button>';
        html += '    </div>';
      }
      html += '  </div>';
      html += '  <span class="estado-srv-tag">' + tag + '</span>';
      html += '</div>';
    });
    grid.innerHTML = html || '<div class="estado-srv-loading">Sin servicios configurados.</div>';
    if (footGen) {
      footGen.textContent = 'Generado: ' + (data.generated_at || '—');
    } else if (foot) {
      foot.textContent = 'Generado: ' + (data.generated_at || '—');
    }
  }

  function fetchEstado(){
    if (inFlight) return;
    inFlight = true;
    fetch('/inicio/serviciosLocalesEstado', {
      credentials: 'same-origin',
      headers: srvAjaxHeaders()
    })
      .then(function(r){
        return r.text().then(function(body){
          if (!r.ok) throw new Error('HTTP ' + r.status);
          try { return JSON.parse(body); }
          catch (e) { throw new Error('JSON inválido: ' + body.slice(0, 120)); }
        });
      })
      .then(renderEstado)
      .catch(function(err){
        grid.innerHTML = '<div class="estado-srv-loading">Error: ' + escapeHtml(err && err.message ? err.message : String(err)) + '</div>';
        summary.textContent = 'ERR';
        summary.className = 'estado-srv-summary err';
        if (footGen) footGen.textContent = 'Generado: —';
        try {
          console.error('[Sparta inicio · serviciosLocalesEstado]', { message: err && err.message, stack: err && err.stack });
        } catch (e) {}
      })
      .finally(function(){ inFlight = false; });
  }

  function startAuto(){
    stopAuto();
    pollTimer = setInterval(fetchEstado, 5000);
  }
  function stopAuto(){
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
  }

  btnEstado.addEventListener('click', function(){
    var open = panel.classList.toggle('open');
    if (open) {
      fetchEstado();
      if (chkAuto && chkAuto.checked) startAuto();
    } else {
      stopAuto();
    }
  });
  if (btnRefresh) btnRefresh.addEventListener('click', fetchEstado);
  if (grid) {
    grid.addEventListener('click', function(ev){
      var btn = ev.target && ev.target.closest ? ev.target.closest('button[data-srv-action]') : null;
      if (!btn) return;
      var action = btn.getAttribute('data-srv-action') || '';
      var serviceId = btn.getAttribute('data-srv-id') || '';
      var serviceName = btn.getAttribute('data-srv-name') || serviceId;
      if (!action || !serviceId) return;
      srvDoServiceAction(serviceId, serviceName, action);
    });
  }
  if (chkAuto) {
    chkAuto.addEventListener('change', function(){
      if (this.checked && panel.classList.contains('open')) startAuto();
      else stopAuto();
    });
  }
})();
</script>
