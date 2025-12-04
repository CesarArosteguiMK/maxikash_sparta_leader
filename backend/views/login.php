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
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Login | Sparta  </title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/logo_ico.svg" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="/assets/vendor/fonts/fontawesome.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/assets/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="/assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="/assets/vendor/libs/sweetalert2/sweetalert2.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="/assets/css/login.css" />

    <!-- Helpers -->
    <script src="/assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <!-- <script src="/assets/vendor/js/template-customizer.js"></script> -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="/assets/js/config.js"></script>
</head>

<body>

    <!-- Content -->
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">

                <!-- Login Card -->
                <div class="card px-sm-6 px-0">
                    <div class="card-body">

                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <span class="app-brand-logo demo">
                                <img src="https://__SPARTA_SECRET_REDACTED__.mx/cdn/shop/files/Logotipo-Maxikash-Outline.png?v=1749328460" alt="Logo de la empresa" height="100" />
                            </span>
                        </div>
                        <!-- /Logo -->

                        <div id="formAuthentication" class="mb-6">
                            <div class="mb-6 form-group" style="height: 75px;">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="usuario"
                                    name="usuario"
                                    placeholder="Ingresa el usuario"
                                    autofocus />
                            </div>
                            <div class="mb-6 form-group form-password-toggle" style="height: 75px;">
                                <label class="form-label" for="password">Contraseña</label>
                                <div class="input-group input-group-merge">
                                    <input
                                        type="password"
                                        id="password"
                                        class="form-control"
                                        name="password"
                                        placeholder="Ingresa la contraseña"
                                        aria-describedby="password" />
                                    <i class="input-group-text cursor-pointer fa fa-eye z-5"></i>
                                </div>
                            </div>
                            <div class="mb-6">
                                <button type="button" id="btnLogin" class="btn btn-primary d-grid w-100">Entrar</button>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- /Login Card -->

            </div>
        </div>
    </div>
    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js /assets/vendor/js/core.js -->
    <script src="/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/assets/vendor/libs/popper/popper.js"></script>
    <script src="/assets/vendor/js/bootstrap.js"></script>
    <script src="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="/assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="/assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

    <!-- Main JS -->
    <script src="/assets/js/main.js"></script>

    <!-- Page JS -->
    <?= $script; ?>
</body>

</html>