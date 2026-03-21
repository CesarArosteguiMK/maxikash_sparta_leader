<?php
/**
 * Scripts CLI que tocan tablas de tickets deben usar esta BD, no [database] ESQUEMA del config.ini
 * (ESQUEMA suele ser otra base, p. ej. reportes / mega-reporte).
 *
 * Base efectiva (en orden):
 *   1) argv[1] del script que llama
 *   2) variable de entorno TICKET_DB_ESQUEMA
 *   3) [database] ESQUEMA_TICKETS en config.ini
 *   4) __SPARTA_SECRET_REDACTED__
 *
 * @param array<int, string> $argv típicamente $GLOBALS['argv']
 * @return string nombre de la base usada
 */
function sparta_bootstrap_db_tickets(array $argv = []): string
{
    static $done = false;
    static $esquema = '';

    if ($done) {
        return $esquema;
    }

    $scriptsDir = __DIR__;
    $raiz = dirname($scriptsDir);
    $configFile = $raiz . '/config/config.ini';
    if (!is_file($configFile)) {
        throw new RuntimeException('No se encontró config.ini en ' . $configFile);
    }
    $config = @parse_ini_file($configFile, true);
    if (empty($config['database'])) {
        throw new RuntimeException('No existe sección [database] en config.ini');
    }
    $dbConfig = $config['database'];

    $esquemaArg = isset($argv[1]) ? trim((string) $argv[1]) : '';
    $esquemaEnv = trim((string) getenv('TICKET_DB_ESQUEMA'));
    $esquemaIni = trim($dbConfig['ESQUEMA_TICKETS'] ?? '');

    if ($esquemaArg !== '') {
        $esquema = $esquemaArg;
    } elseif ($esquemaEnv !== '') {
        $esquema = $esquemaEnv;
    } elseif ($esquemaIni !== '') {
        $esquema = $esquemaIni;
    } else {
        $esquema = '__SPARTA_SECRET_REDACTED__';
    }

    $servidor = trim($dbConfig['SERVIDOR'] ?? '');
    $puerto = trim($dbConfig['PUERTO'] ?? '3306');
    $usuario = trim($dbConfig['USUARIO'] ?? '');
    $password = trim($dbConfig['PASSWORD'] ?? '');

    putenv('DB_SERVIDOR=' . $servidor);
    putenv('DB_HOST=' . $servidor);
    putenv('DB_PUERTO=' . $puerto);
    putenv('DB_ESQUEMA=' . $esquema);
    putenv('DB_NAME=' . $esquema);
    putenv('DB_USUARIO=' . $usuario);
    putenv('DB_USER=' . $usuario);
    putenv('DB_PASSWORD=' . $password);
    putenv('DB_PASS=' . $password);

    $done = true;

    return $esquema;
}
