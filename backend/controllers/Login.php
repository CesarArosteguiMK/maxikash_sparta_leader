<?php

namespace Controllers;

use Core\Controller;
use Core\LoginRateLimit;
use Models\Login as LoginDao;
use Models\Perfil as PerfilDao;

class login extends Controller
{
    public function index()
    {
        $script = <<<HTML
            <script>
                const validaUsuario = (btn) => {
                    const datos = {
                        usuario: $("#usuario").val(),
                        password: $("#password").val()
                    }

                    $.ajax({
                        url: "/login/validaUsuario",
                        type: "POST",
                        data: datos,
                        success: (respuesta) => {
                            if (respuesta.success) {
                                window.location.href = respuesta.datos.url;
                            } else {
                                showError(respuesta.mensaje);
                                if (respuesta.error) console.log(respuesta.error);
                                btn.removeAttribute("disabled");
                            }
                        },
                        error: () => {
                            showError("Error al procesar la solicitud.")
                            btn.removeAttribute("disabled")
                        }
                    })
                }

                const showError = (error) => {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: error,
                        showConfirmButton: true,
                        timer: 3000
                    })
                }

                $(document).ready(() => {
                    const formAuthentication = document.querySelector("#formAuthentication")
                    const validacion = FormValidation.formValidation(formAuthentication, {
                        fields: {
                            usuario: {
                                validators: {
                                    notEmpty: {
                                        message: "Debe ingresar su nombre de usuario"
                                    }
                                }
                            },
                            password: {
                                validators: {
                                    notEmpty: {
                                        message: "Debe ingresar su contraseña"
                                    }
                                }
                            }
                        },
                        plugins: {
                            submitButton: new FormValidation.plugins.SubmitButton(),
                            defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                            trigger: new FormValidation.plugins.Trigger(),
                            autoFocus: new FormValidation.plugins.AutoFocus(),
                            bootstrap5: new FormValidation.plugins.Bootstrap5({
                                rowSelector: ".form-group"
                            })
                        },
                        init: (instance) => {
                            instance.on("plugins.message.placed", (e) => {
                                if (e.element.parentElement.classList.contains("input-group")) {
                                    e.element.parentElement.insertAdjacentElement("afterend", e.messageElement)
                                }
                            })
                        }
                    })

                    $("#usuario").on("keyup", (e) => {
                        e.target.value = e.target.value.toUpperCase()
                        if (e.keyCode === 13) $("#password").focus()
                    })

                    $("#password").on("keyup", (e) => {
                        if (e.keyCode === 13) {
                            e.preventDefault()
                            $("#btnLogin").click()
                        }
                    })

                    $("#btnLogin").on("click", (e) => {
                        e.preventDefault()
                        e.target.setAttribute("disabled", true)
                        
                        validacion.validate().then((validacion) => {
                            if (validacion === "Valid") validaUsuario(e.target)
                            else e.target.removeAttribute("disabled")
                        })
                    })
                })

            </script>
        HTML;

        self::set('script', $script);
        self::render("login", true);
    }

    public function validaUsuario()
    {
        $respuesta = self::respuesta(false, 'Credenciales incorrectas.');

        $rateCheck = LoginRateLimit::check();
        if ($rateCheck[0] === false) {
            $respuesta = self::respuesta(false, $rateCheck[1] ?? 'Demasiados intentos.');
            self::respuestaJSON($respuesta);
        }

        $validacion = LoginDao::validaUsuario($_POST);

        if ($validacion['success'] && !empty($validacion['datos'])) {

            $datos = $validacion['datos'];

            $_SESSION['login'] = true;
            $_SESSION['usuario_id'] = (int)$datos['id'];
            $_SESSION['usuario'] = $datos['user_name'];
            $_SESSION['usuario_nombre'] = $datos['nombres'] . ' ' . $datos['segundo_nombre']. ' ' . $datos['apellidop'];


            $_SESSION['nivel_puesto'] = $datos['id_puesto'];
            $_SESSION['nombre_puesto'] = $datos['nombre_puesto'];
            $_SESSION['id_puesto'] = $datos['id_puesto'];
            $_SESSION['departamento'] = $datos['departamento_id'];

            // 🔐 CONTROL DE SESIÓN
            $_SESSION['session_version'] = (int)($datos['session_version'] ?? 1);
            $_SESSION['last_session_check'] = time();

            // 🔐 MÓDULOS PERMITIDOS
            $_SESSION['modulos'] = LoginDao::getModulosUsuario($datos['id']);

            $_SESSION['foto_perfil'] = "/assets/img/misc/user.svg";
            $perfil = PerfilDao::getByPersonaId($datos['id']);
            if ($perfil && !empty($perfil['foto'])) {
                $_SESSION['foto_perfil'] = $perfil['foto'];
            } elseif (!empty($datos['FOTO'])) {
                $_SESSION['foto_perfil'] = "/CapHum/getFotoPersona?personaId={$datos['FOTO']}";
            }

            LoginRateLimit::clear();
            $respuesta = self::respuesta(true, 'Bienvenido', [
                'url' => '/' . VISTA_DEFECTO
            ]);
        } else {
            LoginRateLimit::recordFailure();
        }

        self::respuestaJSON($respuesta);
    }


    public function cerrarSesion()
    {
        unset($_SESSION);
        session_unset();
        session_destroy();
        header('Location: /' . LOGIN);
        exit;
    }
}
