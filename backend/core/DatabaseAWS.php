<?php
namespace Core;
use PDO;

require_once __DIR__ . '/EnvLoader.php';

class DatabaseAWS
{
    private $db;

    /**
     * @param string|null $esquema Nombre de la BD: "__SPARTA_SECRET_REDACTED__" (por defecto) o "__SPARTA_SECRET_REDACTED__" (p. ej. oferta_documentos / Documentación S3)
     */
    function __construct($esquema = null)
    {
        EnvLoader::load();

        $servidor = getenv('AWS_DB_HOST') ?: '';
        $puerto   = getenv('AWS_DB_PORT') ?: '3306';
        $esquema  = $esquema ?? (getenv('AWS_DB_DEFAULT_SCHEMA') ?: '');
        $usuario  = getenv('AWS_DB_USER') ?: '';
        $password = getenv('AWS_DB_PASSWORD') ?: '';

        if ($servidor === '' || $esquema === '' || $usuario === '' || $password === '') {
            throw new \RuntimeException('Falta configurar la conexion AWS en SPARTA_ENV_FILE.');
        }

        // Cadena MySQL
        $cadena = "mysql:host=$servidor;port=$puerto;dbname=$esquema;charset=utf8mb4";

        $pdoOptions = [
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            $ultimoError = null;
            // La red hacia RDS puede tener cortes breves. Un segundo intento evita
            // que una consulta de documentos falle por un pico transitorio.
            for ($intento = 1; $intento <= 2; $intento++) {
                try {
                    $this->db = new PDO($cadena, $usuario, $password, $pdoOptions);
                    $ultimoError = null;
                    break;
                } catch (\PDOException $eConn) {
                    $ultimoError = $eConn;
                    if ($intento < 2) {
                        usleep(250000);
                    }
                }
            }
            if ($ultimoError instanceof \PDOException) {
                throw $ultimoError;
            }
        } catch (\PDOException $e) {
            error_log(sprintf(
                '[DatabaseAWS] Connection error schema=%s host=%s uri=%s :: %s',
                $esquema,
                $servidor,
                $_SERVER['REQUEST_URI'] ?? 'CLI',
                $e->getMessage()
            ));
            if (
                DatabaseCliSupport::isCli()
                || DatabaseCliSupport::esEstadoCuentaDocumentoRequest()
                || DatabaseCliSupport::esSabuesoRastreoJsonRequest()
            ) {
                throw new \RuntimeException(
                    'No se pudo conectar a MySQL (AWS ' . $esquema . '): ' . $e->getMessage(),
                    0,
                    $e
                );
            }
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
