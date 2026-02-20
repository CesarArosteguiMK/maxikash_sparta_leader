<?php
$assetsBase = '';
if (!empty($_SERVER['SCRIPT_NAME'])) {
  $dir = dirname($_SERVER['SCRIPT_NAME']);
  $assetsBase = ($dir === '/' || $dir === '\\') ? '' : $dir;
}
$cabezaUrl = rtrim($assetsBase, '/') . '/assets/img/cabeza_spartan2.png';
$cabezaUrlFallback = rtrim($assetsBase, '/') . '/assets/img/nu_spartan.png';
?>
<!doctype html>
<html
  lang="es"
  class="light-style layout-wide customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?= htmlspecialchars($assetsBase) ?>/assets/"
  data-template="vertical-menu-template"
  data-style="light">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Login | Sparta</title>

  <link rel="icon" type="image/x-icon" href="/assets/img/logo_ico2.svg" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="/assets/vendor/fonts/fontawesome.css" />
  <link rel="stylesheet" href="/assets/vendor/css/core.css" />
  <link rel="stylesheet" href="/assets/css/demo.css" />

  <link rel="stylesheet" href="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/typeahead-js/typeahead.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/@form-validation/form-validation.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/sweetalert2/sweetalert2.css" />
  <link rel="stylesheet" href="/assets/css/login.css" />

  <script src="/assets/vendor/js/helpers.js"></script>
  <script src="/assets/js/config.js"></script>

 <style>
    
  body {
  background: #ffffff !important;
  min-height: 100vh;
}
/* En móvil: fondo liso (la cabeza va como logo en el flujo, no de fondo) */
@media (max-width: 991.98px) {
  html, body.login-page-mobile {
    background: #e8ecf2 !important;
  }
  .login-cabeza-fondo {
    display: none !important;
  }
}
/* Logo de la cabeza: solo visible en móvil entre título y texto de credenciales */
.login-logo-mobile {
  display: none;
  margin: 0 auto 1rem;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}
.login-logo-mobile img {
  display: block;
  width: 100px;
  max-width: 100%;
  height: auto;
  max-height: 100px;
  object-fit: contain;
  margin: 0 auto;
}
/* Espaciador para bajar la cabeza: solo móvil, no afecta web */
.login-logo-espacio-mobile {
  display: none;
}
@media (max-width: 991.98px) {
  .login-logo-mobile {
    display: flex !important;
    padding-top: 0 !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }
  .login-logo-espacio-mobile {
    display: none !important;
    height: 0 !important;
    min-height: 0 !important;
    overflow: hidden;
  }
  .login-page-mobile .sparta-title {
    margin-bottom: 0 !important;
  }
  .login-page-mobile .login-heading {
    margin-top: 0 !important;
    margin-bottom: 0.5rem !important;
  }
  .login-page-mobile #formAuthentication {
    margin-top: 0 !important;
  }
  .login-page-mobile #formAuthentication .form-group.mb-6,
  .login-page-mobile #formAuthentication .mb-6 {
    margin-bottom: 0.75rem !important;
  }
}
@media (min-width: 992px) {
  .login-logo-mobile {
    display: none !important;
  }
  .login-logo-espacio-mobile {
    display: none !important;
  }
}

/* Contenedor principal */
.authentication-inner {
  min-height: 100vh;
}

/* Columna izquierda (solo escritorio; en móvil la oculta Bootstrap d-none d-lg-flex) */
.authentication-inner > div:first-child {
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding-left: 40px;
  height: 100vh;
  overflow: hidden;
}
@media (max-width: 991.98px) {
  .authentication-inner > div:first-child {
    display: none !important;
    width: 0 !important;
    min-width: 0 !important;
    overflow: hidden;
  }
}

/* Imagen */
.authentication-inner img {
  height: 100vh;
  width: auto;
  object-fit: contain;
  max-height: 100vh;
}

/* Zona login */
.authentication-bg {
  background: transparent !important;
  box-shadow: none !important;
  display: flex;
  align-items: center;
}

/* Contenedor interno: poco padding y espacio mínimo entre hijos */
.authentication-bg .w-px-400 {
  width: 100%;
  max-width: 420px;
  padding: 0;
}
.authentication-bg .w-px-400 > * + * {
  margin-top: 0.4rem;
}

/* Título principal: SPARTA ADMIN – poco margen para juntar con el resto */
.sparta-title {
  font-family: 'Times New Roman', serif;
  font-size: 38px;
  letter-spacing: 4px;
  color: #1f2a44;
  margin: 0 0 0.35rem 0;
  text-align: center;
  white-space: nowrap;
}

/* Encabezado LOGIN – poco margen */
.login-heading {
  font-family: 'Public Sans', sans-serif;
  font-size: 14px;
  font-weight: 400;
  color: #a09a9a;
  margin: 0 0 0.35rem 0;
  text-align: center;
  letter-spacing: 1px;
  text-transform: none;
  white-space: nowrap;
}

/* Botón */
#btnLogin {
  background: linear-gradient(180deg, #1e3a8a 0%, #0f172a 100%);
  border: none;
  font-weight: 600;
  letter-spacing: 1px;
  height: 45px;
  color: white;
}

#btnLogin:hover {
  opacity: 0.95;
}

/* Contraseña: input normal (como Usuario) + botón ojo encima. Sin input-group = mismo borde verde natural */
.password-field-wrap {
  position: relative;
}
.password-field-wrap .form-control {
  padding-right: 2.75rem;
}
.password-field-wrap .btn-password-eye {
  position: absolute !important;
  right: 0;
  top: 0;
  bottom: 0;
  width: 2.5rem;
  margin: 0 !important;
  padding: 0 !important;
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  color: #6c757d;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s ease;
  z-index: 5;
  pointer-events: auto;
  cursor: pointer;
}
.password-field-wrap .btn-password-eye:hover {
  color: #1f2a44;
  background: transparent !important;
}
.password-field-wrap .btn-password-eye:focus {
  box-shadow: none;
  outline: none;
}
.password-field-wrap .btn-password-eye .fa-eye,
.password-field-wrap .btn-password-eye .fa-eye-slash {
  font-size: 1rem;
  pointer-events: none;
}
.btn-password-eye {
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
}

/* Footer con logo */
#footer-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: small;
  color: #666;
  margin-top: 20px;
}

#footer-logo span {
  margin-right: 5px;
}

#maxicash-logo {
  height: 20px;
  width: auto;
}

/* MÓVIL: bloque fijo para que título/cabeza/form se muevan con padding */
@media (max-width: 991.98px) {
  .authentication-wrapper.authentication-cover,
  .authentication-wrapper.authentication-cover .authentication-inner {
    display: flex !important;
    min-height: 100vh !important;
    width: 100% !important;
  }
  /* Fila: contenido arriba (no centrado), así el login puede subir o bajar con padding */
  .authentication-inner.row {
    flex-wrap: wrap;
    width: 100%;
    align-items: flex-start !important;
  }
  /* Columna del login: pegada arriba, poco padding */
  .authentication-inner .authentication-bg {
    display: flex !important;
    flex: 1 1 100% !important;
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    justify-content: center;
    text-align: center;
    padding: 0.5rem 0.75rem !important;
    min-height: auto !important;
    align-items: flex-start !important;
  }
  .authentication-bg .w-px-400 {
    width: 100% !important;
    max-width: 100% !important;
  }
  .sparta-title {
    font-size: 1.75rem;
    white-space: normal;
    word-break: break-word;
    margin-top: 0;
  }
}

/* Tarjeta de login – Liquid Glass, padding compacto */
.authentication-bg .w-px-400 {
  background: rgba(255, 255, 255, 0.95) !important;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1px solid rgba(30, 41, 68, 0.12);
  padding: 1rem 1.5rem 1.25rem;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06), 0 0 0 1px rgba(30, 41, 68, 0.06);
}

/* MÓVIL: tarjeta centrada, compacta y moderna */
@media (max-width: 991.98px) {
  /* Centrar tarjeta verticalmente */
  .authentication-inner .authentication-bg {
    align-items: center !important;
    min-height: 100vh !important;
    padding: 1rem !important;
  }
  /* Tarjeta: más padding horizontal, sombra suave */
  .authentication-bg .w-px-400 {
    padding: 1.5rem 1.25rem 1.25rem !important;
    border-radius: 24px !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08), 0 0 0 1px rgba(30,41,68,0.08) !important;
    max-width: 360px !important;
  }
  .authentication-bg .w-px-400 > * + * {
    margin-top: 0.5rem !important;
  }
  /* Título más pequeño en móvil */
  .login-page-mobile .w-px-400 .sparta-title,
  body.login-page-mobile .w-px-400 h1.sparta-title {
    margin: 0 !important;
    font-size: 1.6rem !important;
    letter-spacing: 3px !important;
  }
  .login-page-mobile .w-px-400 .login-logo-espacio-mobile {
    display: none !important;
  }
  /* Logo: más grande para que destaque */
  .login-page-mobile .w-px-400 .login-logo-mobile {
    margin: 0.5rem 0 !important;
    padding: 0 !important;
  }
  .login-page-mobile .w-px-400 .login-logo-mobile img {
    max-height: 260px !important;
    width: 260px !important;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));
  }
  /* Texto de credenciales */
  .login-page-mobile .w-px-400 .login-heading,
  body.login-page-mobile .w-px-400 h5.login-heading {
    margin: 0 0 0.25rem 0 !important;
    font-size: 13px !important;
  }
  /* Form */
  .login-page-mobile .w-px-400 #formAuthentication {
    margin: 0 !important;
    padding: 0 !important;
  }
  .login-page-mobile .w-px-400 #formAuthentication .form-group.mb-6,
  .login-page-mobile .w-px-400 #formAuthentication .mb-6 {
    margin-bottom: 0.65rem !important;
  }
  .login-page-mobile .w-px-400 #formAuthentication .form-label {
    font-size: 13px !important;
    margin-bottom: 0.25rem !important;
    color: #4b5563;
  }
  .login-page-mobile .w-px-400 #formAuthentication .form-control {
    padding: 0.6rem 0.9rem !important;
    font-size: 14px !important;
    border-radius: 10px !important;
  }
  /* Botón más destacado */
  .login-page-mobile .w-px-400 #btnLogin {
    height: 44px !important;
    font-size: 15px !important;
    border-radius: 12px !important;
    margin-top: 0.5rem !important;
  }
  .login-page-mobile .w-px-400 center {
    margin: 0 !important;
    padding: 0 !important;
    display: block;
  }
}

/* Campos con iconos */
.input-group-text {
  background: transparent;
  border-left: none;
}
.input-group .form-control {
  border-right: none;
}
.input-group .form-control:focus {
  border-color: #1e3a8a;
  box-shadow: none;
}
.form-control {
  background: rgba(255, 255, 255, 0.9) !important;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(226, 232, 240, 0.9);
  padding: 0.75rem 1rem;
  border-radius: 12px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus {
  border-color: #1e3a8a;
  box-shadow: 0 0 0 3px rgba(30,58,138,0.1);
  background: rgba(255, 255, 255, 0.98) !important;
}

/* Botón con sombra y transición */
#btnLogin {
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(30,58,138,0.2);
  transition: all 0.2s;
}
#btnLogin:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 12px rgba(30,58,138,0.3);
}

/* Enlace "Forgot Password?" */
.forgot-password {
  display: block;
  text-align: right;
  margin-top: -10px;
  margin-bottom: 20px;
  font-size: 14px;
}
.forgot-password a {
  color: #1e3a8a;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}
.forgot-password a:hover {
  color: #0f172a;
  text-decoration: underline;
}

body.login-page-mobile .authentication-wrapper {
  position: relative;
  z-index: 1;
}
/* En móvil el wrapper debe ser transparente para que se vea el fondo del body (cabeza) */
@media (max-width: 991.98px) {
  body.login-page-mobile .authentication-wrapper,
  body.login-page-mobile .authentication-inner,
  body.login-page-mobile .authentication-bg {
    background: transparent !important;
  }
}

</style>

</head>

<body class="login-page-mobile" data-cabeza-url="<?= htmlspecialchars($cabezaUrl, ENT_QUOTES, 'UTF-8') ?>" data-cabeza-fallback="<?= htmlspecialchars($cabezaUrlFallback, ENT_QUOTES, 'UTF-8') ?>">

  <!-- Capa con la cabeza del espartano: fixed, z-index -1, solo móvil -->
  <div class="login-cabeza-fondo" id="loginCabezaFondo" aria-hidden="true"></div>

  <div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">

      <!-- Imagen izquierda -->
      <div class="d-none d-lg-flex col-lg-6 col-xl-7 align-items-center justify-content-center p-5">
        <div class="w-100 text-center">
          <img
            src="/assets/img/nu_spartan.png"
            alt="Login Cover"
          >
        </div>
      </div>




      <!-- Login derecho -->
      <div class="d-flex col-12 col-lg-6 col-xl-3 align-items-center authentication-bg p-0">
        <div class="w-px-400 mx-auto">

          <!-- 1) Título arriba -->
          <h1 class="sparta-title">SPARTA LEDGER</h1>

          <!-- Espaciador solo móvil: baja la cabeza (no se muestra en web) -->
          <div class="login-logo-espacio-mobile" aria-hidden="true"></div>

          <!-- 2) Logo (cabeza espartano) solo en móvil -->
          <div class="login-logo-mobile">
            <img src="<?= htmlspecialchars($cabezaUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" onerror="this.onerror=null; this.src='<?= htmlspecialchars($cabezaUrlFallback, ENT_QUOTES, 'UTF-8') ?>';" />
          </div>

          <!-- 3) Texto de credenciales -->
          <center>
            <h5 class="login-heading"><strong>Ingresa tus credenciales de acceso</strong></h5>
          </center>

          <!-- 4) Campos y botón -->
          <div id="formAuthentication">

            <div class="mb-6 form-group">
              <label for="usuario" class="form-label">Usuario</label>
              <input
                type="text"
                class="form-control"
                id="usuario"
                name="usuario"
                placeholder="Ingresa el usuario"
                autofocus />
            </div>

            <div class="mb-6 form-group">
              <label class="form-label" for="password">Contraseña</label>
              <div class="password-field-wrap">
                <input
                  type="password"
                  id="password"
                  class="form-control"
                  name="password"
                  placeholder="Ingresa la contraseña" />
                <button type="button" class="btn btn-password-eye" id="togglePassword" title="Mostrar/ocultar contraseña">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
            </div>

        

            <div class="mb-6 text-center">
              <button type="button" id="btnLogin"
                class="btn d-grid w-100">
                Iniciar sesión
              </button>
            </div>

            <!-- Footer con logo -->
        <!--    <div id="footer-logo">
              <span>Sistema desarrollado por</span> 
              <img src="/assets/img/Logotipo-Maxikash-Outline.webp" alt="Maxicash Logo" id="maxicash-logo">
            </div>
-->
          </div>
          <!-- /Form -->

        </div>
      </div>

    </div>
  </div>

  <!-- JS ORIGINAL COMPLETO -->
  <script src="/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/assets/vendor/libs/popper/popper.js"></script>
  <script src="/assets/vendor/js/bootstrap.js"></script>
  <script src="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="/assets/vendor/libs/hammer/hammer.js"></script>
  <script src="/assets/vendor/libs/typeahead-js/typeahead.js"></script>
  <script src="/assets/vendor/js/menu.js"></script>

  <script src="/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

  <script src="/assets/js/main.js"></script>

  <script>
    (function() {
      if (window.matchMedia && window.matchMedia('(max-width: 991.98px)').matches) {
        var cuerpo = document.body;
        var urlCabeza = cuerpo.getAttribute('data-cabeza-url');
        var fallback = cuerpo.getAttribute('data-cabeza-fallback');
        if (urlCabeza && fallback) {
          var img = new Image();
          img.onerror = function() {
            cuerpo.style.backgroundImage = "url('" + fallback + "')";
            cuerpo.style.backgroundSize = '70% auto';
            cuerpo.style.backgroundPosition = 'center center';
            cuerpo.style.backgroundRepeat = 'no-repeat';
          };
          img.src = urlCabeza;
        }
      }
    })();
  </script>
  <script>
    $(document).ready(function() {
      $('#togglePassword, #togglePassword i').click(function() {
        var input = $(this).closest('.password-field-wrap').find('input');
        var icon = $('#togglePassword').find('i');
        if (input.attr('type') === 'password') {
          input.attr('type', 'text');
          icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
          input.attr('type', 'password');
          icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
      });
    });
  </script>

  <?= $script; ?>

</body>
</html>