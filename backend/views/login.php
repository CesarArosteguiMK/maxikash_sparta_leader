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
  font-size: 20px;
  font-weight: 400;
  color: #1e3a8a;
  margin-bottom: 30px;
  text-align: center;
  letter-spacing: 1px;
  text-transform: none;
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

            <div class="mb-6 form-group form-password-toggle">
              <label class="form-label" for="password">Contraseña</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control"
                  name="password"
                  placeholder="Ingresa la contraseña" />
                <span class="input-group-text cursor-pointer">
                  <i class="fa fa-eye"></i>
                </span>
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

  <?= $script; ?>

</body>
</html>