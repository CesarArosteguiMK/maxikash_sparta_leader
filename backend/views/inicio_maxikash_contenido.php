<?php
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$puestoUsuario = $_SESSION['nombre_puesto'] ?? 'Usuario';
$nombreCorto = trim(preg_replace('/\s+/', ' ', $nombreUsuario));
if (strpos($nombreCorto, ' ') !== false) {
    $partes = explode(' ', $nombreCorto);
    $nombreCorto = $partes[0] . ' ' . end($partes);
}
$accesosRapidos = $accesosRapidos ?? [];
$logoUrl = '/assets/img/logo-empresa-hero.png';
?>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
.inicio-mkx { --blue:#1A52A8; --yellow:#C8D62B; --r:14px; }
.inicio-mkx .page{padding:0 0 2rem;}
.inicio-mkx .hero{background:linear-gradient(128deg,#0d2757 0%,var(--blue) 50%,#1c67c2 100%);border-radius:var(--r);padding:36px 38px;margin-bottom:22px;position:relative;overflow:hidden;display:grid;grid-template-columns:1fr auto;gap:24px;align-items:center;box-shadow:0 8px 32px rgba(26,82,168,.22);}
.inicio-mkx .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 65% 90% at 90% -20%,rgba(200,214,43,.1) 0%,transparent 55%);pointer-events:none;z-index:0;}
.inicio-mkx .hero::after{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(to bottom,var(--yellow),#8fa010);border-radius:var(--r) 0 0 var(--r);z-index:0;}
.inicio-mkx .hero-logo-bg{position:absolute;right:20px;top:50%;transform:translateY(-50%);width:180px;height:180px;background:url('<?= $logoUrl ?>') no-repeat center;background-size:contain;opacity:0.1;pointer-events:none;z-index:0;filter:brightness(0) invert(1);}
body.dark-mode .inicio-mkx .hero-logo-bg{opacity:0.12;}
.inicio-mkx .hero-content{position:relative;z-index:1;}
.inicio-mkx .hero-badge{display:inline-flex;align-items:center;gap:5px;background:rgba(200,214,43,.14);border:1px solid rgba(200,214,43,.28);border-radius:20px;padding:3px 10px;font-size:10.5px;font-weight:600;color:var(--yellow);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;}
.inicio-mkx .hero-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:#fff;line-height:1.15;margin-bottom:8px;}
.inicio-mkx .hero-desc{font-size:13.5px;color:rgba(255,255,255,.55);max-width:380px;line-height:1.65;}
.inicio-mkx .hero-datetime{position:relative;z-index:1;display:flex;align-items:center;gap:14px;padding:14px 18px;background:rgba(255,255,255,0.12);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.2);border-radius:14px;}
.inicio-mkx .hero-datetime-icon{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,rgba(200,214,43,0.35),rgba(200,214,43,0.15));display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;color:#C8D62B;animation:hero-clock-pulse 2.5s ease-in-out infinite;}
.inicio-mkx .hero-datetime-icon i{color:#C8D62B;}
.inicio-mkx .hero-datetime-time{font-weight:700;font-size:1.35rem;line-height:1.2;color:#fff;}
.inicio-mkx .hero-datetime-date{font-size:0.8rem;opacity:0.95;margin-top:2px;color:rgba(255,255,255,0.9);}
@keyframes hero-clock-pulse{0%,100%{opacity:1;transform:scale(1);box-shadow:0 0 0 0 rgba(200,214,43,0.3);}50%{opacity:0.9;transform:scale(1.03);box-shadow:0 0 12px 2px rgba(200,214,43,0.2);}}
.inicio-mkx .sec-hd{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.inicio-mkx .sec-txt{font-family:'Syne',sans-serif;font-size:14px;font-weight:700;color:inherit;}
.inicio-mkx .sec-line{flex:1;height:1px;background:var(--bs-border-color, #e2e8f0);}
.inicio-mkx .grid-cards{display:flex;flex-wrap:wrap;justify-content:center;gap:13px;}
.inicio-mkx .qcard{width:188px;min-width:188px;max-width:188px;background:var(--bs-body-bg, #fff);border:1px solid var(--bs-border-color, #e2e8f0);border-radius:var(--r);padding:22px 17px;cursor:pointer;position:relative;overflow:hidden;transition:all .22s;text-decoration:none;color:inherit;display:block;}
.inicio-mkx .qcard:hover{transform:translateY(-5px);box-shadow:0 14px 36px rgba(26,82,168,.12);}
.inicio-mkx .qcard::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2.5px;background:linear-gradient(90deg,var(--blue),var(--yellow));transform:scaleX(0);transform-origin:left;transition:transform .28s ease;border-radius:0 0 var(--r) var(--r);}
.inicio-mkx .qcard:hover::after{transform:scaleX(1);}
.inicio-mkx .qico{width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:12px;}
.inicio-mkx .qico i{color:inherit;}
.inicio-mkx .qico.bg-blue{background:#eff4ff;} .inicio-mkx .qico.bg-blue i{color:#1A52A8;}
.inicio-mkx .qico.bg-green{background:#f0fdf4;} .inicio-mkx .qico.bg-green i{color:#16a34a;}
.inicio-mkx .qico.bg-orange{background:#fff7ed;} .inicio-mkx .qico.bg-orange i{color:#ea580c;}
.inicio-mkx .qico.bg-purple{background:#faf5ff;} .inicio-mkx .qico.bg-purple i{color:#7c3aed;}
.inicio-mkx .qico.bg-red{background:#fef2f2;} .inicio-mkx .qico.bg-red i{color:#dc2626;}
.inicio-mkx .qico.bg-teal{background:#f0fdfa;} .inicio-mkx .qico.bg-teal i{color:#0d9488;}
.inicio-mkx .qico.bg-yellow{background:#fefce8;} .inicio-mkx .qico.bg-yellow i{color:#ca8a04;}
.inicio-mkx .qt{font-size:13.5px;font-weight:700;margin-bottom:4px;}
.inicio-mkx .qd{font-size:12px;opacity:.85;}
.inicio-mkx .qarr{position:absolute;top:15px;right:13px;width:21px;height:21px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:11px;opacity:0;transition:all .2s;}
.inicio-mkx .qcard:hover .qarr{opacity:1;background:var(--blue);color:#fff;}
</style>

<div class="inicio-mkx">
  <div class="page">
    <div class="hero">
      <div class="hero-logo-bg" aria-hidden="true"></div>
      <div class="hero-content">
        <div class="hero-badge"><?= htmlspecialchars($puestoUsuario) ?></div>
        <h1 class="hero-title">¡Hola, <?= htmlspecialchars($nombreCorto) ?>! 👋</h1>
        <p class="hero-desc">Tienes acceso al portal. Usa los accesos rápidos o el menú lateral para navegar.</p>
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
          <div class="qico <?= htmlspecialchars($acc['bg'] ?? 'bg-blue') ?>"><i class="<?= htmlspecialchars($acc['icon']) ?>"></i></div>
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
