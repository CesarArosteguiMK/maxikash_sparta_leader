<?php
$fotoPerfil = $_SESSION['foto_perfil'] ?? '/assets/img/misc/user.svg';
$flash = $_SESSION['perfil_flash'] ?? '';
if ($flash) unset($_SESSION['perfil_flash']);
$datos = $datos ?? [
  'nombre' => $_SESSION['usuario_nombre'] ?? 'Usuario',
  'apellidopat' => '', 'apellidomat' => '', 'telefono' => '', 'correo' => '', 'direccion' => '', 'username' => $_SESSION['usuario'] ?? '',
];
$nombreVal = $datos['nombre'] ?? '';
$apellidopatVal = $datos['apellidopat'] ?? '';
$apellidomatVal = $datos['apellidomat'] ?? '';
$correoVal = $datos['correo'] ?? '';
$telefonoVal = $datos['telefono'] ?? '';
$direccionVal = $datos['direccion'] ?? '';
$usernameVal = $datos['username'] ?? '';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
<style>
.pf-mkx .card{border-radius:14px;border:1px solid var(--bs-border-color-translucent, #e2e8f0);}
.pf-mkx .pf-photo-wrap img{width:88px;height:88px;border-radius:50%;object-fit:cover;border:3px solid var(--bs-border-color-translucent);}
.pf-mkx .pf-field label{font-size:12px;font-weight:600;color:var(--bs-secondary-color);margin-bottom:6px;}
.pf-mkx .pf-btn{background:#1A52A8;color:#fff;border:none;border-radius:9px;padding:10px 20px;font-size:13.5px;font-weight:600;cursor:pointer;}
.pf-mkx .pf-btn:hover{background:#2563C4;color:#fff;}
.pf-mkx .pf-flash{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:20px;font-size:13px;}
body.dark-mode .pf-mkx .pf-flash{background:#14532d;border-color:#166534;color:#86efac;}
.pf-crop-modal{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;}
.pf-crop-modal .pf-crop-box{background:var(--bs-body-bg,#fff);border-radius:14px;padding:20px;max-width:500px;width:100%;}
.pf-crop-modal .pf-crop-container{height:320px;background:#111;}
.pf-crop-modal .pf-crop-container img{max-width:100%;display:block;}
.pf-crop-modal .pf-crop-actions{margin-top:16px;display:flex;gap:10px;justify-content:flex-end;}
.pf-crop-modal .cropper-view-box,.pf-crop-modal .cropper-face{border-radius:50%;}
.pf-crop-modal .cropper-drag-box{background:transparent;}
.pf-crop-modal .cropper-modal{opacity:0.5;}
/* Solo lectura: aspecto de texto pegado, no editable */
.pf-mkx .pf-readonly.form-control{background:transparent !important;border:none !important;border-bottom:1px solid var(--bs-border-color-translucent,rgba(0,0,0,.12)) !important;border-radius:0 !important;padding-left:0 !important;cursor:default !important;pointer-events:none !important;box-shadow:none !important;}
.pf-mkx .pf-readonly.form-control:focus{outline:none !important;box-shadow:none !important;}
body.dark-mode .pf-mkx .pf-readonly.form-control{border-bottom-color:rgba(255,255,255,.2) !important;}
</style>
<div class="pf-mkx">
  <h4 class="fw-bold mb-4"><i class="fa-solid fa-gear me-2"></i>Ajustes</h4>
  <?php if ($flash): ?><div class="pf-flash"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form action="/perfil/guardar" method="post" enctype="multipart/form-data" id="formPerfil">
        <input type="hidden" name="foto_base64" id="fotoBase64" value="" />
        <div class="pf-photo-wrap d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
          <img id="previewFoto" src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil" />
          <div class="pf-photo-actions">
            <label class="form-label mb-1 small fw-semibold">Cambiar foto de perfil</label>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" id="inputFoto" class="form-control form-control-sm" style="max-width:240px;" />
            <small class="text-muted">Podrás elegir el encuadre en círculo antes de guardar.</small>
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="pf-field">
              <label for="nombre" class="form-label">Nombre(s)</label>
              <input type="text" id="nombre" name="nombre" class="form-control pf-readonly" value="<?= htmlspecialchars($nombreVal) ?>" placeholder="Nombre(s)" readonly />
            </div>
          </div>
          <div class="col-md-6">
            <div class="pf-field">
              <label for="apellidopat" class="form-label">Apellido paterno</label>
              <input type="text" id="apellidopat" name="apellidopat" class="form-control pf-readonly" value="<?= htmlspecialchars($apellidopatVal) ?>" placeholder="Apellido paterno" readonly />
            </div>
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="pf-field">
              <label for="apellidomat" class="form-label">Apellido materno</label>
              <input type="text" id="apellidomat" name="apellidomat" class="form-control pf-readonly" value="<?= htmlspecialchars($apellidomatVal) ?>" placeholder="Apellido materno" readonly />
            </div>
          </div>
          <div class="col-md-6">
            <div class="pf-field">
              <label for="username" class="form-label">Usuario (login)</label>
              <input type="text" id="username" name="username" class="form-control pf-readonly" value="<?= htmlspecialchars($usernameVal) ?>" placeholder="Nombre de usuario" readonly />
            </div>
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="pf-field">
              <label for="correo" class="form-label">Correo electrónico</label>
              <input type="email" id="correo" name="correo" class="form-control pf-readonly" placeholder="tu@correo.com" value="<?= htmlspecialchars($correoVal) ?>" readonly />
            </div>
          </div>
          <div class="col-md-6">
            <div class="pf-field">
              <label for="telefono" class="form-label">Teléfono</label>
              <input type="text" id="telefono" name="telefono" class="form-control pf-readonly" placeholder="Ej. 55 1234 5678" value="<?= htmlspecialchars($telefonoVal) ?>" readonly />
            </div>
          </div>
        </div>
        <div class="row g-3 mb-4">
          <div class="col-12">
            <div class="pf-field">
              <label for="direccion" class="form-label">Dirección</label>
              <input type="text" id="direccion" name="direccion" class="form-control pf-readonly" placeholder="Calle, número, colonia, CP" value="<?= htmlspecialchars($direccionVal) ?>" readonly />
            </div>
          </div>
        </div>
        <div class="pf-field mb-4 pb-2 border-bottom">
          <label class="form-label small text-muted">Cambiar contraseña (próximamente)</label>
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <input type="text" class="form-control pf-readonly" value="—" readonly tabindex="-1" aria-disabled="true" />
            </div>
            <div class="col-md-6">
              <input type="text" class="form-control pf-readonly" value="—" readonly tabindex="-1" aria-disabled="true" />
            </div>
          </div>
        </div>
        <button type="submit" class="pf-btn btn">Guardar cambios</button>
      </form>
    </div>
  </div>
</div>

<div class="pf-crop-modal" id="cropModal" style="display:none;" aria-hidden="true">
  <div class="pf-crop-box">
    <p class="mb-2 small text-muted">Mueve y acerca la imagen para elegir qué parte se verá en el círculo de perfil.</p>
    <div class="pf-crop-container">
      <img id="cropImage" src="" alt="Recortar" />
    </div>
    <div class="pf-crop-actions">
      <button type="button" class="btn btn-outline-secondary" id="cropCancel">Cancelar</button>
      <button type="button" class="btn btn-primary" id="cropApply">Aplicar recorte</button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
(function(){
  var inputFoto = document.getElementById('inputFoto');
  var previewFoto = document.getElementById('previewFoto');
  var cropModal = document.getElementById('cropModal');
  var cropImage = document.getElementById('cropImage');
  var cropCancel = document.getElementById('cropCancel');
  var cropApply = document.getElementById('cropApply');
  var fotoBase64 = document.getElementById('fotoBase64');
  var formPerfil = document.getElementById('formPerfil');
  var cropper = null;

  function toCircleCanvas(sourceCanvas) {
    var size = Math.min(sourceCanvas.width, sourceCanvas.height);
    var circle = document.createElement('canvas');
    circle.width = size;
    circle.height = size;
    var ctx = circle.getContext('2d');
    ctx.beginPath();
    ctx.arc(size/2, size/2, size/2, 0, Math.PI*2);
    ctx.closePath();
    ctx.clip();
    var dx = (sourceCanvas.width - size) / 2;
    var dy = (sourceCanvas.height - size) / 2;
    ctx.drawImage(sourceCanvas, dx, dy, size, size, 0, 0, size, size);
    return circle;
  }

  if (inputFoto && previewFoto && cropModal && cropImage) {
    inputFoto.addEventListener('change', function(){
      var f = this.files && this.files[0];
      if (!f || !f.type.match(/^image\//)) return;
      fotoBase64.value = '';
      var r = new FileReader();
      r.onload = function(){
        cropImage.src = r.result;
        cropModal.style.display = 'flex';
        if (cropper) cropper.destroy();
        cropper = new Cropper(cropImage, {
          aspectRatio: 1,
          viewMode: 1,
          dragMode: 'move',
          autoCropArea: 0.8,
          restore: false,
          guides: false,
          center: true,
          highlight: false,
          cropBoxMovable: false,
          cropBoxResizable: false,
        });
      };
      r.readAsDataURL(f);
    });

    cropCancel.addEventListener('click', function(){
      cropModal.style.display = 'none';
      if (cropper) { cropper.destroy(); cropper = null; }
      inputFoto.value = '';
    });

    cropApply.addEventListener('click', function(){
      if (!cropper) return;
      var canvas = cropper.getCroppedCanvas({ width: 400, height: 400, imageSmoothingEnabled: true, fillColor: '#fff' });
      var circleCanvas = toCircleCanvas(canvas);
      fotoBase64.value = circleCanvas.toDataURL('image/png');
      previewFoto.src = fotoBase64.value;
      inputFoto.value = '';
      cropModal.style.display = 'none';
      cropper.destroy();
      cropper = null;
    });
  }
})();
</script>
