<!doctype html>
<html
  lang="es"
  class="light-style layout-wide customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="/assets/"
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
  background-color: #ffffff !important;
}

/* Contenedor principal */
.authentication-inner {
  min-height: 100vh;
}

/* Columna izquierda */
.authentication-inner > div:first-child {
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding-left: 40px;
  height: 100vh;
  overflow: hidden;
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

/* Contenedor interno */
.authentication-bg .w-px-400 {
  width: 100%;
  max-width: 420px;
  padding: 0;
}

/* Título principal: SPARTA ADMIN */
.sparta-title {
  font-family: 'Times New Roman', serif;
  font-size: 38px;
  letter-spacing: 4px;
  color: #1f2a44;
  margin-bottom: 20px;
  text-align: center;
  white-space: nowrap;
}

/* Encabezado LOGIN */
.login-heading {
  font-family: 'Public Sans', sans-serif;
  font-size: 14px;
  font-weight: 400;
  color: #a09a9a;
  margin-bottom: 30px;
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

/* RESPONSIVE FIX */
@media (max-width: 991.98px) {

  .authentication-bg {
    justify-content: center;
    text-align: center;
  }

}

/* Tarjeta de login */
.authentication-bg .w-px-400 {
  background: white;
  padding: 2.5rem 2rem;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
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
  border: 1px solid #e2e8f0;
  padding: 0.75rem 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus {
  border-color: #1e3a8a;
  box-shadow: 0 0 0 3px rgba(30,58,138,0.1);
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

</style>

</head>

<body>

  <div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">

      <!-- Imagen izquierda (NO se toca estructura) -->
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

          <!-- Título principal: SPARTA ADMIN -->
          <h1 class="sparta-title">SPARTA LEDGER</h1>

          <center>
             <!-- Encabezado LOGIN -->
          <h5 class="login-heading"><strong>Ingresa tus credenciales de acceso</strong></h5>
            </center>
         

          <!-- FORM ORIGINAL — MODIFICADO -->
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
            <div id="footer-logo">
              <span>Sistema desarrollado por</span>
              <img src="/assets/img/Logotipo-Maxikash-Outline.webp" alt="Maxicash Logo" id="maxicash-logo">
            </div>

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