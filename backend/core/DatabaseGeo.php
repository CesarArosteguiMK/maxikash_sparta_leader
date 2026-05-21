<?php

namespace Core;

use PDO;

/**
 * Conexión a BD __SPARTA_SECRET_REDACTED__ (direcciones alternas / oferta_coordenada).
 */
class DatabaseGeo
{
    private $db;

    /** Último error de conexión (para depuración; ver test_geo_ssl.php) */
    public static $lastError = null;

    public function __construct()
    {
        $servidor = '__SPARTA_HOST_REDACTED__';
        $puerto   = '3306';
        $esquema  = '__SPARTA_SECRET_REDACTED__';
        $usuario  = '__SPARTA_SECRET_REDACTED__';
        $password = '__SPARTA_PASSWORD_REDACTED__';
        $cadena   = "mysql:host=$servidor;port=$puerto;dbname=$esquema;charset=utf8mb4";

        $opciones = [
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        // SSL: certificados en backend/BD. Acepta nombres sin espacios (client-cert.pem) o con espacios (client-cert (3).pem)
        $sslDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'BD';
        $ca     = $sslDir . DIRECTORY_SEPARATOR . 'server-ca.pem';
        $cert   = $sslDir . DIRECTORY_SEPARATOR . 'client-cert.pem';
        if (!is_file($cert)) {
            $cert = $sslDir . DIRECTORY_SEPARATOR . 'client-cert (3).pem';
        }
        $key = $sslDir . DIRECTORY_SEPARATOR . 'client-key.pem';
        if (!is_file($key)) {
            $key = $sslDir . DIRECTORY_SEPARATOR . 'client-key (1).pem';
        }
        // En Windows, rutas con espacios pueden romper el handshake SSL; copiar a backend/BD con nombres sin espacios
        $tempFiles = [];
        $ca     = self::sslPathWithoutSpaces($ca, $sslDir, 'ca', $tempFiles);
        $cert   = self::sslPathWithoutSpaces($cert, $sslDir, 'cert', $tempFiles);
        $key    = self::sslPathWithoutSpaces($key, $sslDir, 'key', $tempFiles);
        if (!empty($tempFiles)) {
            register_shutdown_function(function () use ($tempFiles) {
                foreach ($tempFiles as $f) {
                    if (is_file($f)) {
                        @unlink($f);
                    }
                }
            });
        }
        if (is_file($ca)) {
            $opciones[PDO::MYSQL_ATTR_SSL_CA] = $ca;
            // En Windows, mysqlnd suele dar error 2002 si no se desactiva la verificación del certificado del servidor
            $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            if (is_file($cert)) {
                $opciones[PDO::MYSQL_ATTR_SSL_CERT] = $cert;
            }
            if (is_file($key)) {
                $opciones[PDO::MYSQL_ATTR_SSL_KEY] = $key;
            }
        }

        try {
            self::$lastError = null;
            $this->db = new PDO($cadena, $usuario, $password, $opciones);
        } catch (\PDOException $e) {
            self::$lastError = $e->getMessage();
            error_log('[DatabaseGeo] ' . $e->getMessage());
            $this->db = null;
        }
    }

    /** Indica si la conexión está activa (para depuración). */
    public function isConnected()
    {
        return $this->db !== null;
    }

    /** Devuelve el último mensaje de error de conexión (para depuración). */
    public static function getLastError()
    {
        return self::$lastError;
    }

    /**
     * Si la ruta contiene espacios o paréntesis, copia el archivo a $sslDir con nombre sin espacios (evita fallo SSL en Windows).
     * Usamos backend/BD (no sys_get_temp_dir()) porque en Windows el temp suele tener espacios (Users\Tu Nombre\...).
     */
    private static function sslPathWithoutSpaces($path, $sslDir, $suffix, array &$tempFiles)
    {
        if (!is_file($path)) {
            return $path;
        }
        if (strpos($path, ' ') === false && strpos($path, '(') === false) {
            return $path;
        }
        $tmp = rtrim($sslDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'geo_ssl_' . $suffix . '_' . getmypid() . '_' . uniqid('', true) . '.pem';
        if (@copy($path, $tmp)) {
            $tempFiles[] = $tmp;
            return $tmp;
        }
        return $path;
    }

    /** Rutas de certificados SSL usadas (para depuración). Misma lógica que el constructor. */
    public static function getSslPaths()
    {
        $sslDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'BD';
        $cert   = $sslDir . DIRECTORY_SEPARATOR . 'client-cert.pem';
        if (!is_file($cert)) {
            $cert = $sslDir . DIRECTORY_SEPARATOR . 'client-cert (3).pem';
        }
        $key = $sslDir . DIRECTORY_SEPARATOR . 'client-key.pem';
        if (!is_file($key)) {
            $key = $sslDir . DIRECTORY_SEPARATOR . 'client-key (1).pem';
        }
        return [
            'dir'   => $sslDir,
            'ca'    => $sslDir . DIRECTORY_SEPARATOR . 'server-ca.pem',
            'cert'  => $cert,
            'key'   => $key,
        ];
    }

    public function queryOne($sql, $valores = null)
    {
        if (!$this->db) {
            return null;
        }
        try {
            $stmt = $this->db->prepare($sql);
            if ($valores) {
                foreach ($valores as $key => $value) {
                    $stmt->bindValue(is_int($key) ? $key + 1 : ":$key", $value);
                }
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function queryAll($sql, $valores = null)
    {
        if (!$this->db) {
            return [];
        }
        try {
            $stmt = $this->db->prepare($sql);
            if ($valores) {
                foreach ($valores as $key => $value) {
                    $stmt->bindValue(is_int($key) ? $key + 1 : ":$key", $value);
                }
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
