<?php
namespace core;

use PDO;

require_once __DIR__ . '/EnvLoader.php';

class DatabaseMaxiProd
{
    private $db;
    private static ?array $configApiCache = null;

    function __construct()
    {
        \Core\EnvLoader::load();

        $servidor = $this->envValue(['DB_MAXI_HOST', 'MAXI_PROD_DB_HOST']);
        $puerto   = $this->envValue(['DB_MAXI_PORT', 'MAXI_PROD_DB_PORT'], '3306');
        $esquema  = $this->envValue(['DB_MAXI_DATABASE', 'DB_MAXI_NAME', 'MAXI_PROD_DB_NAME']);
        $usuario  = $this->envValue(['DB_MAXI_USER', 'MAXI_PROD_DB_USER']);
        $password = $this->envValue(['DB_MAXI_PASSWORD', 'DB_MAXI_PASS', 'MAXI_PROD_DB_PASSWORD']);

        $faltantes = [];
        foreach ([
            'DB_MAXI_HOST' => $servidor,
            'DB_MAXI_DATABASE' => $esquema,
            'DB_MAXI_USER' => $usuario,
            'DB_MAXI_PASSWORD' => $password,
        ] as $nombre => $valor) {
            if ($valor === null || trim((string)$valor) === '') {
                $faltantes[] = $nombre;
            }
        }
        if ($faltantes) {
            $this->configuracionNoDisponible($faltantes);
        }

        $cadena = "mysql:host=$servidor;port=$puerto;dbname=$esquema;charset=utf8mb4";

        try {
            $this->db = new PDO(
                $cadena,
                $usuario,
                $password,
                [
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (\PDOException $e) {
            error_log(sprintf(
                '[DatabaseMaxiProd] Connection error schema=%s host=%s uri=%s :: %s',
                $esquema,
                $servidor,
                $_SERVER['REQUEST_URI'] ?? 'CLI',
                $this->mensajeSeguro($e)
            ));
            if (\Core\DatabaseCliSupport::isCli() || \Core\DatabaseCliSupport::esEstadoCuentaValidarCreditoRequest()) {
                throw new \RuntimeException(
                    'No se pudo conectar a Maxi para lectura; no se ejecutó sincronización.',
                    0,
                    $e
                );
            }
            $this->baseNoDisponible();
            $this->db = null;
        }
    }

    private function envValue(array $names, ?string $default = null): ?string
    {
        foreach ($names as $name) {
            $value = getenv($name);
            if ($value !== false && trim((string)$value) !== '') {
                return (string)$value;
            }
            if (isset($_ENV[$name]) && trim((string)$_ENV[$name]) !== '') {
                return (string)$_ENV[$name];
            }
            if (isset($_SERVER[$name]) && trim((string)$_SERVER[$name]) !== '') {
                return (string)$_SERVER[$name];
            }
        }

        $configApi = $this->configApiValues();
        foreach ($names as $name) {
            if (isset($configApi[$name]) && trim((string)$configApi[$name]) !== '') {
                return (string)$configApi[$name];
            }
        }

        return $default;
    }

    private function configApiValues(): array
    {
        if (self::$configApiCache !== null) {
            return self::$configApiCache;
        }

        self::$configApiCache = [];
        $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ConfigApi.php';
        if (!function_exists('config_api_load_from_db') && is_file($configPath)) {
            require_once $configPath;
        }

        if (function_exists('config_api_load_from_db')) {
            $values = config_api_load_from_db();
            if (is_array($values)) {
                self::$configApiCache = $values;
            }
        }

        return self::$configApiCache;
    }

    private function configuracionNoDisponible(array $faltantes): void
    {
        error_log('[DatabaseMaxiProd] Missing environment variables: ' . implode(', ', $faltantes));
        if (\Core\DatabaseCliSupport::isCli() || \Core\DatabaseCliSupport::esEstadoCuentaValidarCreditoRequest()) {
            throw new \RuntimeException('No se pudo conectar a Maxi para lectura; configuración incompleta.');
        }
        $this->baseNoDisponible();
    }

    private function mensajeSeguro(\Throwable $e): string
    {
        return preg_replace('/password\s*=\s*[^;\s]+/i', 'password=***', $e->getMessage()) ?: 'Error de conexión';
    }

    private function baseNoDisponible()
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
