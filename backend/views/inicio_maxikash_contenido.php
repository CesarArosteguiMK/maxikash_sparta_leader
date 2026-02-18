<?php
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$puestoUsuario = $_SESSION['nombre_puesto'] ?? 'Usuario';
$nombreCorto = trim(preg_replace('/\s+/', ' ', $nombreUsuario));
if (strpos($nombreCorto, ' ') !== false) {
    $partes = explode(' ', $nombreCorto);
    $nombreCorto = $partes[0] . ' ' . end($partes);
}
$accesosRapidos = $accesosRapidos ?? [];
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
.inicio-mkx .hero-datetime{position:relative;z-index:1;display:flex;align-items:center;gap:14px;padding:14px 18px;background:rgba(255,255,255,0.12);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.2);border-radius:14px;}
.inicio-mkx .hero-datetime-icon{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,rgba(200,214,43,0.4),rgba(200,214,43,0.18));display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;color:#C8D62B;animation:hero-clock-pulse 2.5s ease-in-out infinite;box-shadow:0 2px 12px rgba(0,0,0,0.12),inset 0 1px 0 rgba(255,255,255,0.25);border:1px solid rgba(200,214,43,0.35);}
.inicio-mkx .hero-datetime-icon i{color:#C8D62B;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.15));}
.inicio-mkx .hero-datetime-time{font-weight:700;font-size:1.35rem;line-height:1.2;color:#fff;}
.inicio-mkx .hero-datetime-date{font-size:0.8rem;opacity:0.95;margin-top:2px;color:rgba(255,255,255,0.9);}
@keyframes hero-clock-pulse{0%,100%{opacity:1;transform:scale(1);box-shadow:0 2px 12px rgba(0,0,0,0.12),0 0 0 0 rgba(200,214,43,0.35);}50%{opacity:0.95;transform:scale(1.02);box-shadow:0 4px 16px rgba(0,0,0,0.18),0 0 14px 2px rgba(200,214,43,0.25);}}
body.dark-mode .inicio-mkx .hero-datetime-icon{background:linear-gradient(135deg,rgba(200,214,43,0.35),rgba(200,214,43,0.12));border-color:rgba(200,214,43,0.4);box-shadow:0 2px 14px rgba(0,0,0,0.35),inset 0 1px 0 rgba(255,255,255,0.08);}
body.dark-mode .inicio-mkx .hero-datetime-icon i{color:#c8d62b;filter:drop-shadow(0 1px 3px rgba(0,0,0,0.3));}
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
      <div class="hero-datetime">
        <span class="hero-datetime-icon"><i class="fa-solid fa-clock"></i></span>
        <div>
          <div class="hero-datetime-time" id="inicioTime">--:--</div>
          <div class="hero-datetime-date" id="inicioDate">--</div>
        </div>
      </div>
    </div>

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
</div>

<script>
(function(){
  function updateInicioDateTime(){
    var elTime = document.getElementById('inicioTime');
    var elDate = document.getElementById('inicioDate');
    if (!elTime || !elDate) return;
    var n = new Date();
    var h = n.getHours(), m = n.getMinutes();
    elTime.textContent = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
    var d = n.getDate(), mes = n.getMonth(), dia = n.getDay();
    var meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    var dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    elDate.textContent = dias[dia] + ' ' + d + ' ' + meses[mes];
  }
  updateInicioDateTime();
  setInterval(updateInicioDateTime, 60000);
})();
</script>
