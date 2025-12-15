<?php

namespace Core;
use PDO;

class DatabaseSegundometro
{
    private $db;

    function __construct()
    {
        // 🔧 Ajusta tus valores aquí
        $servidor = "__SPARTA_HOST_REDACTED__";   // tu host
        $puerto   = "3306";        // puerto MySQL
        $esquema  = "__SPARTA_SECRET_REDACTED__";     // nombre de tu BD
        $usuario  = "__SPARTA_SECRET_REDACTED__";        // usuario
        $password = "__SPARTA_PASSWORD_REDACTED__";            // contraseña

        // Cadena MySQL
        $cadena = "mysql:host=$servidor;port=$puerto;dbname=$esquema;charset=utf8mb4";

        try {
            $this->db = new PDO(
                $cadena,
                $usuario,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (\PDOException $e) {
            $this->baseNoDisponible("{$e->getMessage()}\nDatos de conexión: $cadena");
            $this->db = null;
        }
    }

    private function baseNoDisponible($mensaje)
    {
        http_response_code(503);
        echo <<<HTML
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Sistema fuera de línea</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        background-color: #f4f4f4;
                        color: #333;
                        margin: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                    }
                    .container {
                        background-color: #fff;
                        padding: 20px;
                        border-radius: 10px;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    }
                    h1 {
                        font-size: 2em;
                        color: #d9534f;
                    }
                    p {
                        font-size: 1.2em;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Sistema fuera de línea</h1>
                    <p>Estamos trabajando para resolver la situación. Por favor, vuelva a intentarlo más tarde.</p>
                </div>
            </body>
            <script>
                window.onload = () => {
                    console.log("$mensaje")
                }
            </script>
            </html>
        HTML;
        exit();
    }

    private function getError($e, $sql = null, $valores = null, $retorno = null)
    {
        $error = "Error en DB: {$e->getMessage()}\n";
        if ($sql != null) $error .= "Query: $sql\n";
        if ($valores != null) $error .= 'Datos: ' . print_r($valores, 1);
        if ($retorno != null) $error .= 'Retorno: ' . print_r($retorno, 1);
        return $error;
    }

    private function registrarLog($sql, $valores = null)
    {
        $operacionesModificacion = ['INSERT', 'UPDATE', 'DELETE'];
        $sqlUpper = strtoupper(trim($sql));
        $tipoOperacion = null;

        $esModificacion = false;
        foreach ($operacionesModificacion as $operacion) {
            if (strpos($sqlUpper, $operacion) === 0) {
                $esModificacion = true;
                $tipoOperacion = $operacion;
                break;
            }
        }

        $esSelect = strpos($sqlUpper, 'SELECT') === 0;
        if ($esSelect) $tipoOperacion = 'SELECT';

        // Regla para SELECT solo nocturnos
        $registrarSelect = false;
        if ($esSelect) {
            $horaActual = (int)date('G');
            $registrarSelect = ($horaActual >= 19 || $horaActual < 8);
        }

        // No registrar si no aplica
        if ((!$esModificacion && !$registrarSelect)) return;

        if ($this->db === null || !is_object($this->db)) return;

        try {
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            $personaId = $_SESSION['persona_id'] ?? null;

            $ip = $this->getClientIP();
            $trace = $this->getSimpleTrace();
            $parametersJson = $valores ? json_encode($valores, JSON_UNESCAPED_UNICODE) : null;

            $logSql = "INSERT INTO LOG (USUARIO, PERSONA, IP, QUERY_TEXT, PARAMETERS_JSON, TRACE, TIPO) 
                       VALUES (:usuario, :persona, :ip, :query_text, :parameters_json, :trace, :tipo)";

            $stmtLog = $this->db->prepare($logSql);

            $stmtLog->execute([
                ":usuario" => $usuarioId,
                ":persona" => $personaId,
                ":ip" => $ip,
                ":query_text" => $sql,
                ":parameters_json" => $parametersJson,
                ":trace" => $trace,
                ":tipo" => $tipoOperacion,
            ]);
        } catch (\Exception $e) {
            error_log("Error al registrar LOG: " . $e->getMessage());
        }
    }

    private function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private function getSimpleTrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $out = [];

        foreach ($trace as $item) {
            if (($item['class'] ?? '') === 'Core\Database') continue;

            $line = '';
            if (isset($item['file'])) $line .= $item['file'];
            if (isset($item['line'])) $line .= ':' . $item['line'];
            if (isset($item['function'])) {
                $line .= ' - ' . ($item['class'] ?? '') . ($item['type'] ?? '') . $item['function'] . '()';
            }
            $out[] = $line;
        }

        return implode("\n", $out);
    }

    public function beginTransaction()  { if ($this->db) $this->db->beginTransaction(); }
    public function commit()           { if ($this->db) $this->db->commit(); }
    public function rollback()         { if ($this->db) $this->db->rollBack(); }

    private function runQuery($sql, $valores = null, &$retorno = null)
    {
        try {
            $stmt = $this->db->prepare($sql);

            if ($valores) {
                foreach ($valores as $key => $value) $stmt->bindValue(":$key", $value);
            }

            if ($retorno) {
                foreach ($retorno as $key => &$value) {
                    $stmt->bindParam(":$key", $value['valor'], $value['tipo'], $value['largo'] ?? null);
                }
            }

            $stmt->execute();
            $this->registrarLog($sql, $valores);

            return $stmt;
        } catch (\Exception $e) {
            throw new \Exception($this->getError($e, $sql, $valores, $retorno));
        }
    }

    public function queryOne($sql, $valores = null)
    {
        $stmt = $this->runQuery($sql, $valores);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function queryAll($sql, $valores = null)
    {
        $stmt = $this->runQuery($sql, $valores);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function CRUD($sql, $valores = null, &$retorno = null)
    {
        $stmt = $this->runQuery($sql, $valores, $retorno);
        return $stmt->rowCount();
    }

    public function CRUD_multiple($sql, $valores, &$retorno = null)
    {
        try {
            $this->beginTransaction();
            foreach ($sql as $k => $query) {
                $ret = $retorno[$k] ?? null;
                $this->runQuery($query, $valores[$k], $ret);
                if ($retorno[$k] !== null) $retorno[$k] = $ret;
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
