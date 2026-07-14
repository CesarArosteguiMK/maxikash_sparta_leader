<?php

namespace Core;

use PDO;

require_once __DIR__ . '/EnvLoader.php';

class Database
{
    private $db;

    function __construct()
    {
        EnvLoader::load();

        $servidor = getenv('DB_HOST') ?: getenv('DB_SERVIDOR') ?: '';
        $puerto   = getenv('DB_PUERTO') ?: '3306';
        $esquema  = getenv('DB_NAME') ?: getenv('DB_ESQUEMA') ?: '';
        $usuario  = getenv('DB_USER') ?: getenv('DB_USUARIO') ?: '';
        $password = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '';

        if ($servidor === '' || $esquema === '' || $usuario === '' || $password === '') {
            throw new \RuntimeException('Falta configurar la conexion principal en SPARTA_ENV_FILE.');
        }

        $cadena = "mysql:host=$servidor;port=$puerto;dbname=$esquema;charset=utf8mb4";

        $pdoOptions = [
            // Evita reutilizar conexiones muertas: MySQL puede cerrar una conexión persistente
            // y provocar "MySQL server has gone away" en la primera consulta del request.
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Evita cuelgues largos de conexión remota: si la base no responde, falla rápido.
            PDO::ATTR_TIMEOUT => 5,
            // true: permite repetir el mismo nombre de parámetro (:ff) en una sentencia (nativo MySQL no).
            PDO::ATTR_EMULATE_PREPARES => true
        ];

        try {
            $ultimoError = null;
            // Retry corto para picos intermitentes de red (SQLSTATE[HY000] [2002]).
            for ($intento = 1; $intento <= 2; $intento++) {
                try {
                    $this->db = new PDO($cadena, $usuario, $password, $pdoOptions);
                    $ultimoError = null;
                    break;
                } catch (\PDOException $eConn) {
                    $ultimoError = $eConn;
                    if ($intento < 2) {
                        usleep(250000); // 250ms
                        continue;
                    }
                }
            }

            if ($ultimoError instanceof \PDOException) {
                throw $ultimoError;
            }
        } catch (\PDOException $e) {
            error_log(sprintf(
                '[Database] Connection error schema=%s host=%s uri=%s :: %s',
                $esquema,
                $servidor,
                $_SERVER['REQUEST_URI'] ?? 'CLI',
                $e->getMessage()
            ));
            if (DatabaseCliSupport::isCli()
                || DatabaseCliSupport::esGestionesSeguimientoRequest()
                || DatabaseCliSupport::esReporteriaGetAsignacionTableroJsonRequest()
                || DatabaseCliSupport::esReporteriaCapitalHumanoJsonRequest()
                || DatabaseCliSupport::esCaphumDocumentosJsonRequest()) {
                throw new \RuntimeException(
                    'No se pudo conectar a MySQL (__SPARTA_SECRET_REDACTED__): ' . $e->getMessage(),
                    0,
                    $e
                );
            }
            $this->baseNoDisponible();
            $this->db = null;
        }
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
    public function inTransaction(): bool
    {
        return $this->db ? $this->db->inTransaction() : false;
    }

    private function runQuery($sql, $valores = null, &$retorno = null)
    {
        if ($this->db === null) {
            throw new \RuntimeException('Conexión no disponible a MySQL (__SPARTA_SECRET_REDACTED__).');
        }
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

    /** Último AUTO_INCREMENT generado en esta conexión (tras INSERT). */
    public function lastInsertId(): int
    {
        if (!$this->db) {
            return 0;
        }

        return (int) $this->db->lastInsertId();
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
