<?php

namespace Controllers;

use Core\Controller;
use Models\Usuarios as UsuariosDao;

class Inicio extends Controller
{
    public function index()
    {
        self::render("inicio");
        // Validar si el usuario debe cambiar su contraseña
        $this->validarActualizacionPassword();

    }

    /**
     * Ejecuta diagnóstico completo del sistema Segundómetro
     */
    public function diagnosticoSegundometro()
    {
        $scriptPath = __DIR__ . '/../scripts/diagnostico_segundometro.php';
        
        if (!file_exists($scriptPath)) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            echo '<h1>Error</h1><p>Script de diagnóstico no encontrado</p>';
            exit;
        }
        
        // Ejecutar script y capturar salida
        ob_start();
        include $scriptPath;
        $output = ob_get_clean();
        
        // Si ya es HTML completo, mostrarlo directamente
        if (strpos($output, '<!DOCTYPE') !== false || strpos($output, '<html>') !== false) {
            echo $output;
            exit;
        }
        
        // Si no, es texto plano - no hacer htmlspecialchars en los emojis
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Diagnóstico Shell Segundómetro - Servidor</title>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    background: #1e1e1e;
                    color: #d4d4d4;
                    padding: 20px;
                    margin: 0;
                }
                .container {
                    max-width: 1400px;
                    margin: 0 auto;
                    background: #252526;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
                }
                pre {
                    white-space: pre-wrap;
                    word-wrap: break-word;
                    line-height: 1.6;
                    font-size: 13px;
                }
                .ok { color: #4ec9b0; }
                .error { color: #f48771; }
                .warning { color: #dcdcaa; }
                .info { color: #9cdcfe; }
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    background: #0e639c;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    margin: 10px 5px;
                    cursor: pointer;
                    border: none;
                    font-size: 14px;
                }
                .btn:hover {
                    background: #1177bb;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 20px;
                    border-bottom: 2px solid #444;
                }
                .header h1 {
                    color: #4ec9b0;
                    margin: 0 0 10px 0;
                }
                .header p {
                    color: #888;
                    margin: 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔍 Diagnóstico Shell Segundómetro</h1>
                    <p>Ejecutado en: <?php echo $_SERVER['SERVER_NAME'] ?? 'servidor'; ?> | <?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
                <pre><?php
                // Colorear salida sin escapar los emojis
                $colored = $output;
                $colored = str_replace('✅', '<span class="ok">✅</span>', $colored);
                $colored = str_replace('❌', '<span class="error">❌</span>', $colored);
                $colored = str_replace('⚠️', '<span class="warning">⚠️</span>', $colored);
                $colored = str_replace('ℹ️', '<span class="info">ℹ️</span>', $colored);
                echo $colored;
                ?></pre>
                <div style="text-align: center; margin-top: 30px;">
                    <button onclick="window.location.reload()" class="btn">🔄 Ejecutar de nuevo</button>
                    <button onclick="window.close()" class="btn">✖️ Cerrar</button>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    private function validarActualizacionPassword()
    {
        // Suponiendo que en la sesión se guarda el nombre de usuario
        $usuarioSesion = $_SESSION['usuario'] ?? null;

        if (!$usuarioSesion) {
            return; // si no hay sesión, no hacemos nada
        }

        // Obtener datos del usuario desde la base
        $usuarioData = UsuariosDao::getUsuarioPorNombre($usuarioSesion);

        if (!$usuarioData) {
            return;
        }

        // Calcular SHA256 del usuario (como se guarda en PASS)
        $usuarioHash = strtoupper(hash('sha256', $usuarioSesion));

        $excluirUsuarios = ['ALSO', 'ALSO']; // usuarios que NO deben actualizar
        // Comparar con el campo PASS
        if ($usuarioData['PASS'] === $usuarioHash && !in_array($usuarioSesion, $excluirUsuarios)) {
            // SweetAlert2 para actualizar contraseña
            $usuarioSesion = $_SESSION['usuario'] ?? '';
            echo <<<HTML
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
             const usuarioSesion = '{$usuarioSesion}';
            Swal.fire({
                title: 'Actualiza tu contraseña',
                html:
                    '<div style="position:relative;">' +
                        '<input id="newPass" type="password" class="swal2-input" placeholder="Nueva contraseña">' +
                        '<button type="button" id="toggleNew" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:none; cursor:pointer;">👁️</button>' +
                    '</div>' +
                    '<div style="position:relative;">' +
                        '<input id="confirmPass" type="password" class="swal2-input" placeholder="Confirmar contraseña">' +
                        '<button type="button" id="toggleConfirm" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:none; cursor:pointer;">👁️</button>' +
                    '</div>',
                confirmButtonText: 'Actualizar',
                focusConfirm: false,
                allowOutsideClick: false,
                didOpen: () => {
                    const toggleNew = document.getElementById('toggleNew');
                    const toggleConfirm = document.getElementById('toggleConfirm');
                    const newPass = document.getElementById('newPass');
                    const confirmPass = document.getElementById('confirmPass');
            
                    toggleNew.addEventListener('click', () => {
                        newPass.type = newPass.type === 'password' ? 'text' : 'password';
                    });
                    toggleConfirm.addEventListener('click', () => {
                        confirmPass.type = confirmPass.type === 'password' ? 'text' : 'password';
                    });
                },
                preConfirm: () => {
                    const newPass = document.getElementById('newPass').value;
                    const confirmPass = document.getElementById('confirmPass').value;
                    if (!newPass || !confirmPass) {
                        Swal.showValidationMessage('Debes llenar ambos campos');
                        return false;
                    }
                    // Validación de longitud mínima
                    if (newPass.length < 8) {
                        Swal.showValidationMessage('La contraseña debe tener al menos 8 caracteres');
                        return false;
                    }
                    
                    if (newPass.length > 15) {
                        Swal.showValidationMessage('La contraseña no puede tener más de 20 caracteres');
                        return false;
                    }
                    
                    if (newPass !== confirmPass) {
                        Swal.showValidationMessage('Las contraseñas no coinciden');
                        return false;
                    }
                    
                    if (newPass.toUpperCase() === usuarioSesion.toUpperCase()) {
                        Swal.showValidationMessage('La contraseña no puede ser igual al usuario');
                        return false;
                    }
                      // No solo números
                    if (/^\\d+$/.test(newPass)) {
                        Swal.showValidationMessage('La contraseña no puede ser solo números');
                        return false;
                    }
                    
                    return { newPass: newPass };
                }
            }).then((result) => {
                if(result.isConfirmed) {
                    fetch('/Inicio/actualizar_password', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ password: result.value.newPass })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success){
                            Swal.fire('¡Listo!', 'Tu contraseña se actualizó correctamente', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error')
                                .then(() => location.reload());
                        }
                    })
                    .catch(err => Swal.fire('Error', 'Ocurrió un error inesperado', 'error')
                        .then(() => location.reload()));
                }
            });
            </script>
HTML;
            exit;
        }
    }

    public function actualizar_password()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevaPassword = $input['password'] ?? null;
        $usuario = $_SESSION['usuario'] ?? null;

        if (!$nuevaPassword || !$usuario) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        // Llamamos al modelo para actualizar
        $resultado = UsuariosDao::actualizarPassword($usuario, $nuevaPassword);

        echo json_encode($resultado);
    }

    // Método de debug para probar la búsqueda de documentos vía router
    public function debug_document_search()
    {
        $this->set('titulo', 'Debug Búsqueda de Documento');
        self::render('debug_document_search');
    }


}
