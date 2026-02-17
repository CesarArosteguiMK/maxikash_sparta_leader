<?php
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$puestoUsuario = $_SESSION['nombre_puesto'] ?? 'Usuario';
$fotoPerfil = $_SESSION['foto_perfil'] ?? '/assets/img/misc/user.svg';
$iniciales = preg_match_all('/\b\w/u', $nombreUsuario, $m) ? mb_substr(implode('', array_map(function($x){ return mb_substr($x,0,1); }, $m[0])), 0, 2) : 'U';
$iniciales = strtoupper($iniciales);
$nombreCorto = trim(preg_replace('/\s+/', ' ', $nombreUsuario));
if (strpos($nombreCorto, ' ') !== false) {
    $partes = explode(' ', $nombreCorto);
    $nombreCorto = $partes[0] . ' ' . end($partes);
}
$flash = $_SESSION['perfil_flash'] ?? '';
if ($flash) unset($_SESSION['perfil_flash']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Maxikash | Ajustes</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --blue:#1A52A8;--white:#ffffff;--bg:#eef1f7;--surface:#ffffff;--border:#e2e8f0;
  --text:#1e293b;--muted:#64748b;--soft:#94a3b8;--sw:244px;--r:14px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);min-height:100vh;display:flex;color:var(--text);}
body::before{content:'';position:fixed;inset:0;background-image:radial-gradient(circle,#c8d6e128 1px,transparent 1px);background-size:24px 24px;pointer-events:none;z-index:0;}
.sidebar{width:var(--sw);background:var(--white);border-right:1px solid var(--border);position:fixed;top:0;left:0;bottom:0;z-index:50;display:flex;flex-direction:column;padding:24px 0 20px;}
.sb-logo{padding:0 18px 22px;border-bottom:1px solid var(--border);margin-bottom:12px;display:flex;align-items:center;text-decoration:none;color:inherit;}
.sb-logo img{height:56px;width:auto;object-fit:contain;}
.sb-section{padding:0 10px;flex:1;overflow-y:auto;}
.sb-label{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:1.3px;color:var(--soft);padding:14px 8px 5px;}
.sb-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:9px;cursor:pointer;color:var(--muted);font-size:13.5px;font-weight:500;transition:all .18s;position:relative;text-decoration:none;border:none;background:none;width:100%;text-align:left;font-family:inherit;}
.sb-item:hover{background:#f1f5f9;color:var(--blue);}
.sb-item.active{background:linear-gradient(120deg,#e8efff,#edf2ff);color:var(--blue);font-weight:600;}
.sb-item.active::after{content:'';position:absolute;left:-10px;top:25%;bottom:25%;width:3px;background:var(--blue);border-radius:0 3px 3px 0;}
.sb-icon{font-size:15px;width:22px;text-align:center;flex-shrink:0;}
.sb-footer{padding:14px 10px 0;border-top:1px solid var(--border);position:relative;}
.sb-user-wrap{position:relative;}
.sb-user{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:9px;cursor:pointer;transition:background .18s;}
.sb-user:hover{background:#f1f5f9;}
.sb-user-dropdown{position:absolute;left:0;right:0;bottom:100%;margin-bottom:6px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);box-shadow:0 8px 24px rgba(0,0,0,.12);min-width:200px;padding:8px 10px;display:none;z-index:60;}
.sb-user-dropdown.open{display:block;}
.sb-user-dropdown a,.sb-user-dropdown button{display:flex;align-items:center;gap:10px;width:100%;padding:9px 10px;border:none;background:none;cursor:pointer;font-size:13.5px;font-weight:500;color:var(--muted);font-family:'DM Sans',sans-serif;text-align:left;transition:all .18s;text-decoration:none;border-radius:9px;box-sizing:border-box;}
.sb-user-dropdown a:hover,.sb-user-dropdown button:hover{background:#f1f5f9;color:var(--blue);}
.sb-user-dropdown .ud-ico{font-size:15px;width:22px;text-align:center;flex-shrink:0;}
.sb-user-dropdown .ud-divider{height:1px;background:var(--border);margin:6px 0;}
.sb-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--blue),#5b95f9);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0;}
.sb-uname{font-size:12.5px;font-weight:600;color:var(--text);}
.sb-urole{font-size:11px;color:var(--soft);}
.sb-online{width:7px;height:7px;border-radius:50%;background:#22c55e;flex-shrink:0;box-shadow:0 0 0 2px #fff;}
.main{margin-left:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh;position:relative;z-index:1;}
.topbar{height:60px;background:rgba(255,255,255,.88);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 32px;position:sticky;top:0;z-index:40;}
.tb-left{font-size:13px;color:var(--muted);}
.tb-left strong{color:var(--text);}
.tb-right{display:flex;align-items:center;gap:10px;}
.tb-chip{background:#f1f5f9;border:1px solid var(--border);border-radius:8px;padding:5px 12px;font-size:12.5px;font-weight:500;color:var(--muted);}
.tb-notif{width:34px;height:34px;border-radius:9px;border:1px solid var(--border);background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:15px;transition:all .2s;}
.tb-notif:hover{background:#f1f5f9;}
.page{padding:26px 34px 56px;}
.panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px;}
.p-title{font-family:'Syne',sans-serif;font-size:14px;font-weight:700;color:var(--text);margin-bottom:18px;}
.pf-photo-wrap{display:flex;align-items:center;gap:20px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border);}
.pf-photo-wrap img{width:88px;height:88px;border-radius:50%;object-fit:cover;border:3px solid var(--border);}
.pf-photo-actions{display:flex;flex-direction:column;gap:8px;}
.pf-photo-actions label{display:inline-flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);cursor:pointer;}
.pf-photo-actions input[type=file]{font-size:12px;}
.pf-field{margin-bottom:18px;}
.pf-field label{display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:6px;}
.pf-field input{width:100%;max-width:320px;padding:10px 12px;border:1px solid var(--border);border-radius:9px;font-size:13.5px;font-family:'DM Sans',sans-serif;}
.pf-field input:focus{outline:none;border-color:var(--active-accent);}
.pf-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:var(--blue);color:#fff;border:none;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .18s;}
.pf-btn:hover{background:#2563C4;}
.pf-flash{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:20px;font-size:13px;}
body.dark-mode{--bg:#0f172a;--surface:#1e293b;--border:#334155;--text:#f1f5f9;--muted:#94a3b8;--soft:#64748b;}
body.dark-mode .sidebar{background:var(--surface);border-color:var(--border);}
body.dark-mode .sb-item:hover{background:#334155;color:#93c5fd;}
body.dark-mode .sb-item.active{background:linear-gradient(120deg,#1e3a5f,#2563eb33);color:#93c5fd;}
body.dark-mode .sb-user:hover{background:#334155;}
body.dark-mode .sb-user-dropdown{background:var(--surface);border-color:var(--border);}
body.dark-mode .sb-user-dropdown a:hover,body.dark-mode .sb-user-dropdown button:hover{background:#334155;color:#93c5fd;}
body.dark-mode .topbar{background:rgba(30,41,59,.9);border-color:var(--border);}
body.dark-mode .panel{background:var(--surface);border-color:var(--border);}
body.dark-mode .pf-field input{background:#1e293b;border-color:var(--border);color:var(--text);}
body.dark-mode .pf-flash{background:#14532d;border-color:#166534;color:#86efac;}
</style>
</head>
<body>

<aside class="sidebar">
  <a class="sb-logo" href="/inicio"><img src="/assets/img/Logotipo-Maxikash-Outline.webp" alt="Maxikash" /></a>
  <div class="sb-section">
    <div class="sb-label">Menú</div>
    <a class="sb-item" href="/inicio"><span class="sb-icon">🏠</span>Inicio</a>
    <span class="sb-item active"><span class="sb-icon">⚙️</span>Ajustes</span>
  </div>
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
    <div class="tb-left">Portal Maxikash &nbsp;·&nbsp; <strong><?= htmlspecialchars($nombreCorto) ?></strong></div>
    <div class="tb-right">
      <span class="tb-chip" id="tbDate"></span>
      <a href="/login/cerrarSesion" class="tb-notif" title="Cerrar sesión">🚪</a>
    </div>
  </header>

  <div class="page">
    <h1 class="p-title" style="margin-bottom:22px;">⚙️ Ajustes</h1>
    <?php if ($flash): ?><div class="pf-flash"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

    <div class="panel">
      <form action="/perfil/guardar" method="post" enctype="multipart/form-data">
        <div class="pf-photo-wrap">
          <img id="previewFoto" src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil" />
          <div class="pf-photo-actions">
            <label>Cambiar foto de perfil</label>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" id="inputFoto" />
          </div>
        </div>
        <div class="pf-field">
          <label for="nombre">Nombre completo</label>
          <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombreUsuario) ?>" />
        </div>
        <div class="pf-field">
          <label for="correo">Correo electrónico</label>
          <input type="email" id="correo" name="correo" placeholder="tu@correo.com" />
        </div>
        <div class="pf-field">
          <label for="telefono">Teléfono</label>
          <input type="text" id="telefono" name="telefono" placeholder="Ej. 55 1234 5678" />
        </div>
        <button type="submit" class="pf-btn">Guardar cambios</button>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  var tbDate = document.getElementById('tbDate');
  if (tbDate) {
    var n = new Date();
    var dns = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    var mns = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    tbDate.textContent = dns[n.getDay()] + ' ' + n.getDate() + ' ' + mns[n.getMonth()];
  }
  var sbUserBtn = document.getElementById('sbUserBtn');
  var sbUserDropdown = document.getElementById('sbUserDropdown');
  if (sbUserBtn && sbUserDropdown) {
    sbUserBtn.addEventListener('click', function(e){ e.stopPropagation(); sbUserDropdown.classList.toggle('open'); });
    document.addEventListener('click', function(){ sbUserDropdown.classList.remove('open'); });
    sbUserDropdown.addEventListener('click', function(e){ e.stopPropagation(); });
  }
  var darkStored = localStorage.getItem('darkMode') === 'enabled';
  if (darkStored) document.body.classList.add('dark-mode');
  var btnDark = document.getElementById('btnDarkMode');
  if (btnDark) {
    function updateDarkLabel(){
      var isDark = document.body.classList.contains('dark-mode');
      btnDark.innerHTML = '<span class="ud-ico" id="darkModeIco">' + (isDark ? '☀️' : '🌙') + '</span><span class="ud-label">' + (isDark ? 'Modo claro' : 'Modo oscuro') + '</span>';
    }
    updateDarkLabel();
    btnDark.addEventListener('click', function(){
      document.body.classList.toggle('dark-mode');
      localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
      updateDarkLabel();
    });
  }
  var inputFoto = document.getElementById('inputFoto');
  var previewFoto = document.getElementById('previewFoto');
  if (inputFoto && previewFoto) {
    inputFoto.addEventListener('change', function(){
      var f = this.files && this.files[0];
      if (f && f.type.match(/^image\//)) {
        var r = new FileReader();
        r.onload = function(){ previewFoto.src = r.result; };
        r.readAsDataURL(f);
      }
    });
  }
})();
</script>
</body>
</html>
