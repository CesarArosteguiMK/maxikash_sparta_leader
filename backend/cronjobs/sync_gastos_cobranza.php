<?php
/**
 * ============================================================
 * CRONJOB: sync_gastos_cobranza.php
 * ============================================================
 * Ubicación esperada:
 *   /sparta___SPARTA_SECRET_REDACTED__/backend/cronjobs/sync_gastos_cobranza.php
 *
 * ── MODO NORMAL (aplica cambios en BD) ──────────────────────
 *   c:\xampp\php\php.exe -f C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\sync_gastos_cobranza.php
 *
 * ── MODO DRY RUN (solo lectura, CERO escrituras en BD) ──────
 *   c:\xampp\php\php.exe -f C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\sync_gastos_cobranza.php -- --dry-run
 *
 * ── MODO DRY RUN limitado (ideal para primer test) ──────────
 *   c:\xampp\php\php.exe -f C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\sync_gastos_cobranza.php -- --dry-run --limit=5
 *
 * ── SOLO una lista de Id_credito (un entero por línea; # comentario) ─
 *   c:\xampp\php\php.exe -f C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\sync_gastos_cobranza.php -- --ids-file=C:\ruta\creditos.txt
 *   Con --ids-file no aplica el flag “ya corrió hoy”; dry-run y --force siguen disponibles.
 *
 * ── Desde Excel (.xlsx): columna con encabezado “ID CREDITO” ─
 *   Si la ruta tiene espacios, entre comillas:
 *   ... --ids-xlsx="C:\Users\...\GASTOS COBRANZA APLICAR 25 MAR 2026.xlsx"
 *   Opcional: --ids-xlsx-column="ID CREDITO" (default: ID CREDITO)
 *   No combinar --ids-file y --ids-xlsx en la misma corrida.
 *
 * ── Fecha de corte S2 (misma que en Estado de cuenta si eliges fecha en pantalla) ─
 *   ... --fecha-corte=2026-03-23
 *   Si se omite, usa la fecha de hoy (America/Mexico_City).
 *
 * ── MODO TEST (usa tabla backup en lugar de gastos_cobranza) ─
 *   c:\xampp\php\php.exe -f C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\sync_gastos_cobranza.php -- --test-table
 *
 * ── MODO TEST + DRY RUN ──────────────────────────────────────
 *   c:\xampp\php\php.exe -f C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\sync_gastos_cobranza.php -- --test-table --dry-run --limit=5
 *
 * ── Crontab recomendado (martes 02:00 AM) ───────────────────
 *   0 2 * * 2 /usr/bin/php .../sync_gastos_cobranza.php >> .../logs/cron.log 2>&1
 *
 * ── Para validar sintaxis sin ejecutar ──────────────────────
 *   c:\xampp\php\php.exe -l C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\sync_gastos_cobranza.php
 *
 * ============================================================
 * QUÉ HACE:
 *   Cruce de estatus_pago: solo vía Models\EstadoCuenta::procesarGastosCobranzaDesdeNotas
 *   (mismo código que al abrir el menú Estado de cuenta). No hay segunda lógica de UPDATE salvo
 *   modo --test-table (tabla backup alternativa).
 *   de forma masiva — para TODOS los créditos con gastos de
 *   cobranza pendientes (estatus_pago 0 ó 1, condonado = 0),
 *   sin necesidad de que alguien consulte el estado de cuenta.
 *
 * MEJORAS v2:
 *   [+] Flag anti-duplicado — no procesa si ya corrió hoy
 *   [+] Validación contra asigna_creditos_despacho — skip si
 *       el crédito está asignado a despacho (evita cruce)
 *   [+] START TRANSACTION por crédito — ROLLBACK automático
 *       si cualquier UPDATE falla a mitad del cruce
 *   [+] Modo --test-table — opera sobre tabla backup en lugar
 *       de gastos_cobranza productiva
 *   [+] Logs enriquecidos con razones de skip
 *
 * CÉLULAS QUE PROCESA (campo `celula` en gastos_cobranza):
 *   1 = Despacho
 *   2 = Gestión callcenter CON convenio  ← prioridad 1
 *   3 = Cobranza campo
 *   4 = Gestión callcenter SIN convenio
 *   NULL = Sin célula asignada
 *
 * REGLAS RESPETADAS (idénticas al controller):
 *   - Solo notas concepto = 'NOTA DE DE CARGO GASTOS DE COBRANZA'
 *   - Solo notas >= 2026-01-28 (fecha inicio del módulo)
 *   - Descuenta lo ya pagado/abonado antes de aplicar nuevo cruce
 *   - Si el monto no alcanza para cubrir todo → cierra con
 *     condonación automática de la diferencia
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/../core/EnvLoader.php';
\Core\EnvLoader::load();

// ============================================================
// BOOTSTRAP MÍNIMO
// ============================================================

define('CRONJOB_MODE', true);
define('CRONJOB_START', microtime(true));

date_default_timezone_set('America/Mexico_City');

define('RAIZ', dirname(__DIR__));

$configIni = RAIZ . '/config/config.ini';
if (!file_exists($configIni)) {
    fwrite(STDERR, "[FATAL] No se encontró: {$configIni}\n");
    exit(1);
}
define('CONFIGURACION', parse_ini_file($configIni));

spl_autoload_register(function (string $clase): void {
    if (str_starts_with($clase, 'PhpOffice\\') ||
        str_starts_with($clase, 'ZipStream\\') ||
        str_starts_with($clase, 'Psr\\')) {
        return;
    }
    $ruta = RAIZ . '/' . str_replace('\\', '/', $clase) . '.php';
    if (file_exists($ruta)) {
        require_once $ruta;
    }
});

$composerAutoload = dirname(RAIZ) . '/vendor/autoload.php';
if (is_readable($composerAutoload)) {
    require_once $composerAutoload;
}
define('PHP_SPREADSHEET_AUTOLOAD', $composerAutoload);

use Core\DatabaseSegundometro;
use Models\EstadoCuenta;

// ============================================================
// ARGUMENTOS CLI
// ─────────────────────────────────────────────────────────────
//   --dry-run        Solo lectura. CERO escrituras en BD.
//   --limit=N        Procesar máximo N créditos.
//   --test-table     Opera sobre tabla backup (no productiva).
//   --force          Omite el flag anti-duplicado (forzar re-ejecución).
//   --ids-file=FILE  Procesar únicamente los Id_credito listados (no el SELECT masivo).
//   --ids-xlsx=FILE  Excel .xlsx; lee columna cuyo encabezado coincide con --ids-xlsx-column.
//   --ids-xlsx-column=NOMBRE  Default: ID CREDITO
//   --fecha-corte=YYYY-MM-DD  fechaCorte del POST a API S2 (≤ hoy). Default: hoy.
// ============================================================
$opts = getopt('', [
    'dry-run',
    'limit:',
    'test-table',
    'force',
    'ids-file:',
    'ids-xlsx:',
    'ids-xlsx-column:',
    'fecha-corte:',
]);

define('DRY_RUN',    isset($opts['dry-run']));
define('TEST_TABLE', isset($opts['test-table']));
define('FORCE_RUN',  isset($opts['force']));
define('CLI_LIMIT',  isset($opts['limit']) ? max(1, (int) $opts['limit']) : 0);
$idsFileOpt = isset($opts['ids-file']) ? trim((string) $opts['ids-file']) : '';
// CMD a veces deja comillas en el valor; OneDrive/desktop no debe llevar espacios raros
$idsFileOpt = trim($idsFileOpt, " \t\"'");
define('IDS_FILE', $idsFileOpt);

$idsXlsxOpt = isset($opts['ids-xlsx']) ? trim((string) $opts['ids-xlsx'], " \t\"'") : '';
define('IDS_XLSX', $idsXlsxOpt);
$idsXlsxCol = isset($opts['ids-xlsx-column'])
    ? trim((string) $opts['ids-xlsx-column'], " \t\"'")
    : 'ID CREDITO';
define('IDS_XLSX_COLUMN', $idsXlsxCol);

if (IDS_FILE !== '' && IDS_XLSX !== '') {
    fwrite(STDERR, "[FATAL] Use solo uno: --ids-file o --ids-xlsx (no ambos).\n");
    exit(1);
}

$hoyMx         = date('Y-m-d');
$fechaCorteApi = $hoyMx;
if (isset($opts['fecha-corte'])) {
    $fc = trim((string) $opts['fecha-corte']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fc) || $fc > $hoyMx) {
        fwrite(STDERR, "[FATAL] --fecha-corte inválida o futura. Use YYYY-MM-DD (no mayor a {$hoyMx}).\n");
        exit(1);
    }
    $fechaCorteApi = $fc;
}
define('FECHA_CORTE_API', $fechaCorteApi);

// ============================================================
// TABLAS
// ─────────────────────────────────────────────────────────────
// En modo --test-table se usa la tabla backup para pruebas
// seguras sin tocar gastos_cobranza productiva.
// ============================================================
define('TABLA_GASTOS',
    TEST_TABLE
        ? '`__SPARTA_SECRET_REDACTED__`.gastos_cobranza_backup_despacho_20260324'
        : '`__SPARTA_SECRET_REDACTED__`.gastos_cobranza'
);

define('TABLA_DESPACHO', '`__SPARTA_SECRET_REDACTED__`.asigna_creditos_despacho');

// ============================================================
// CONFIGURACIÓN
// ============================================================

const API_URL     = 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
define('API_TOKEN', getenv('S2_ESTADO_CUENTA_TOKEN') ?: (defined('TOKEN') ? TOKEN : ''));
const API_TIMEOUT = 25;

const FECHA_INICIO  = '2026-01-28';
const CONCEPTO_GDC  = 'NOTA DE DE CARGO GASTOS DE COBRANZA';
const PAUSA_US      = 300000;   // 0.3 s entre créditos
const MAX_CREDITOS  = 0;        // 0 = sin límite

// ============================================================
// LOGGER
// ============================================================
$logDir  = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta___SPARTA_SECRET_REDACTED___cron_logs';
$logFile = $logDir . '/sync_gastos_cobranza_' . date('Y-m-d')
         . (DRY_RUN    ? '_DRYRUN'    : '')
         . (TEST_TABLE ? '_TESTTABLE' : '')
         . '.log';

function log_cron(string $nivel, string $msg, array $ctx = []): void
{
    global $logFile, $logDir;

    if ((string) getenv('SPARTA_ENABLE_FILE_LOGS') === '1' && !is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $ts      = date('Y-m-d H:i:s');
    $dryTag  = DRY_RUN    ? ' [DRY-RUN]'    : '';
    $tstTag  = TEST_TABLE ? ' [TEST-TABLE]'  : '';
    $extra   = $ctx ? ' | ' . json_encode($ctx, JSON_UNESCAPED_UNICODE) : '';
    $line    = "[{$ts}]{$dryTag}{$tstTag} [{$nivel}] {$msg}{$extra}" . PHP_EOL;

    if ((string) getenv('SPARTA_ENABLE_FILE_LOGS') === '1') {
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
    echo $line;
}

// ============================================================
// FLAG ANTI-DUPLICADO
// ─────────────────────────────────────────────────────────────
// Se guarda un archivo de marca por fecha.
// Si existe y no se pasa --force, el cron aborta.
// En DRY_RUN nunca se escribe ni se bloquea.
// ============================================================
function verificar_flag_ejecucion(): void
{
    if (DRY_RUN) {
        log_cron('INFO', 'Anti-duplicado: omitido en DRY_RUN');
        return;
    }

    if (IDS_FILE !== '' || IDS_XLSX !== '') {
        log_cron('INFO', 'Anti-duplicado: omitido (--ids-file / --ids-xlsx es ejecución puntual)');
        return;
    }

    $flagDir  = __DIR__ . '/flags';
    $flagFile = $flagDir . '/sync_gastos_' . date('Y-m-d') . '.lock';

    if (!is_dir($flagDir)) {
        mkdir($flagDir, 0755, true);
    }

    if (file_exists($flagFile) && !FORCE_RUN) {
        log_cron('WARN', '⛔ El cron ya se ejecutó hoy. Usa --force para re-ejecutar.');
        exit(0);
    }

    // Crear el flag ANTES de procesar para evitar race condition
    file_put_contents($flagFile, date('Y-m-d H:i:s') . PHP_EOL);
    log_cron('INFO', "Anti-duplicado: flag creado → {$flagFile}");
}

function limpiar_flag_ejecucion(): void
{
    if (DRY_RUN) return;

    $flagFile = __DIR__ . '/flags/sync_gastos_' . date('Y-m-d') . '.lock';
    // El flag permanece — es intencional. Solo --force lo omite.
    // Si se quiere borrar en caso de error para permitir reintento,
    // llamar a esta función desde el bloque de error.
}

/**
 * @return list<int>
 */
function cargar_ids_desde_archivo(string $ruta): array
{
    if ($ruta === '') {
        log_cron('FATAL', 'Ruta vacía en --ids-file');
        exit(1);
    }
    if (!file_exists($ruta)) {
        log_cron(
            'FATAL',
            'No existe el archivo. Revisa la ruta, o si está en OneDrive: clic derecho en el archivo → “Mantener siempre en este dispositivo”, o copia el .txt a C:\\temp\\',
            ['ruta' => $ruta]
        );
        exit(1);
    }
    if (!is_readable($ruta)) {
        log_cron('FATAL', 'Archivo sin permiso de lectura', ['ruta' => $ruta]);
        exit(1);
    }
    $raw = file_get_contents($ruta);
    if ($raw === false) {
        log_cron('FATAL', 'Error al leer archivo de IDs', ['ruta' => $ruta]);
        exit(1);
    }
    $lineas = preg_split("/\R/", $raw);
    $ids    = [];
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }
        if (preg_match('/^\d+$/', $linea)) {
            $ids[] = (int) $linea;
        } else {
            log_cron('WARN', 'Línea ignorada (no es Id_entero)', ['linea' => $linea]);
        }
    }
    return array_values(array_unique($ids));
}

function normalizar_encabezado_excel(string $s): string
{
    $s = trim(preg_replace('/\s+/u', ' ', $s));

    return $s;
}

/**
 * Id_credito desde la primera hoja del .xlsx: busca la fila de encabezados (hasta 50)
 * donde exista una celda igual a $nombreColumna (sin distinguir mayúsculas; espacios normalizados).
 *
 * @return list<int>
 */
function cargar_ids_desde_excel(string $ruta, string $nombreColumna): array
{
    if ($ruta === '') {
        log_cron('FATAL', 'Ruta vacía en --ids-xlsx');
        exit(1);
    }
    if (!file_exists($ruta)) {
        log_cron('FATAL', 'No existe el Excel. Revisa ruta y OneDrive (mantener en dispositivo).', ['ruta' => $ruta]);
        exit(1);
    }
    if (!is_readable($ruta)) {
        log_cron('FATAL', 'Excel sin permiso de lectura', ['ruta' => $ruta]);
        exit(1);
    }
    $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    if ($ext !== 'xlsx') {
        log_cron('FATAL', 'Solo se admite .xlsx', ['ruta' => $ruta]);
        exit(1);
    }

    if (!file_exists(PHP_SPREADSHEET_AUTOLOAD)) {
        log_cron('FATAL', 'PhpSpreadsheet no encontrado. Ejecute composer install en la raíz del proyecto.', ['ruta' => PHP_SPREADSHEET_AUTOLOAD]);
        exit(1);
    }
    require_once PHP_SPREADSHEET_AUTOLOAD;

    $objetivo = normalizar_encabezado_excel($nombreColumna);
    if ($objetivo === '') {
        log_cron('FATAL', 'Nombre de columna vacío (--ids-xlsx-column)');
        exit(1);
    }

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
        $sheet       = $spreadsheet->getActiveSheet();
        $maxRow      = (int) $sheet->getHighestDataRow();
        $maxColStr   = $sheet->getHighestDataColumn();
        $maxColIdx   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxColStr);
    } catch (\Throwable $e) {
        log_cron('FATAL', 'No se pudo leer el Excel', ['error' => $e->getMessage()]);
        exit(1);
    }

    if ($maxRow < 1 || $maxColIdx < 1) {
        log_cron('FATAL', 'Hoja vacía o sin datos');
        exit(1);
    }

    $headerRow = null;
    $colIdx    = null;
    $limBusq   = min(50, $maxRow);

    for ($r = 1; $r <= $limBusq; $r++) {
        for ($c = 1; $c <= $maxColIdx; $c++) {
            $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c) . $r;
            $raw   = $sheet->getCell($coord)->getValue();
            $txt   = normalizar_encabezado_excel((string) $raw);
            if ($txt !== '' && strcasecmp($txt, $objetivo) === 0) {
                $headerRow = $r;
                $colIdx    = $c;
                break 2;
            }
        }
    }

    if ($headerRow === null || $colIdx === null) {
        log_cron(
            'FATAL',
            'No se encontró la columna de encabezado en las primeras filas',
            ['buscado' => $nombreColumna, 'normalizado' => $objetivo]
        );
        exit(1);
    }

    log_cron('INFO', "Excel: encabezado «{$nombreColumna}» en fila {$headerRow}, columna " . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx));

    $ids = [];
    for ($r = $headerRow + 1; $r <= $maxRow; $r++) {
        $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $r;
        $val   = $sheet->getCell($coord)->getCalculatedValue();

        if ($val === null || $val === '') {
            continue;
        }
        if (is_numeric($val)) {
            $id = (int) round((float) $val);
            if ($id > 0) {
                $ids[] = $id;
            }
            continue;
        }
        $s = normalizar_encabezado_excel((string) $val);
        $s = preg_replace('/[^\d]/', '', $s);
        if ($s !== '' && strlen($s) <= 20) {
            $ids[] = (int) $s;
        }
    }

    return array_values(array_unique($ids));
}

// ============================================================
// VALIDACIÓN CONTRA DESPACHO
// ─────────────────────────────────────────────────────────────
// Si el crédito aparece en asigna_creditos_despacho,
// se descarta completamente para evitar cruce de información.
// Se cachea el SET completo al inicio para no hacer N queries.
// ============================================================
function cargar_creditos_despacho(DatabaseSegundometro $db): array
{
    try {
        $rows = $db->queryAll(
            "SELECT DISTINCT Id_credito FROM " . TABLA_DESPACHO,
            []
        );
        $ids = array_column($rows, 'Id_credito');
        return array_flip(array_map('intval', $ids)); // hashmap para O(1) lookup
    } catch (\Exception $e) {
        log_cron('ERROR', 'No se pudo cargar asigna_creditos_despacho', ['error' => $e->getMessage()]);
        return [];
    }
}

// ============================================================
// OBTENER NOTAS DE CARGO DESDE LA API S2
// ============================================================
function obtener_notas_api(int $idCredito): array
{
    $payload = json_encode([
        'idCredito'  => $idCredito,
        'fechaCorte' => FECHA_CORTE_API,
    ]);

    $ch = curl_init(API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Token: ' . API_TOKEN,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => API_TIMEOUT,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlErr) {
        log_cron('ERROR', "CURL falló — crédito #{$idCredito}", ['error' => $curlErr]);
        return [];
    }
    if ($httpCode !== 200) {
        log_cron('WARN', "API HTTP {$httpCode} — crédito #{$idCredito}");
        return [];
    }

    $json = json_decode($response, true);
    if (!is_array($json) || !isset($json['estadoCuenta'])) {
        log_cron('WARN', "API sin estadoCuenta — crédito #{$idCredito}");
        return [];
    }

    $notas = $json['estadoCuenta']['datosNotasCargos'] ?? [];
    return is_array($notas) ? $notas : [];
}

// ============================================================
// PROCESAR UN CRÉDITO
// ─────────────────────────────────────────────────────────────
// Por defecto: Models\EstadoCuenta::procesarGastosCobranzaDesdeNotas (idéntico a abrir Estado de cuenta).
// --test-table: ruta alternativa con UPDATE directo a TABLA_GASTOS (solo laboratorio).
// ============================================================
function procesar_credito(DatabaseSegundometro $db, int $idCredito, array $notasCargos): array
{
    if (TEST_TABLE) {
        return procesar_credito_tabla_alternativa($db, $idCredito, $notasCargos);
    }

    $persistir = !DRY_RUN;
    $res       = EstadoCuenta::procesarGastosCobranzaDesdeNotas($notasCargos, $idCredito, $persistir);

    if (!empty($res['_sin_actualizacion'])) {
        return [
            'procesados'  => 0,
            'saldo_favor' => 0.0,
            'skip'        => true,
            'razon'       => (string) ($res['_causa'] ?? 'sin_cambios'),
        ];
    }

    foreach ($res['gastos_procesados'] as $g) {
        $idGasto   = (int) $g['id_gasto'];
        $fechaPago = (string) ($g['fecha_pago'] ?? '—');
        if ((int) $g['estatus'] === 2) {
            $msg = "    " . (DRY_RUN ? '[SIMULADO] ' : '✔ ') . "Gasto #{$idGasto} → PAGADO TOTAL \${$g['monto']} fecha_pago={$fechaPago}";
            log_cron(DRY_RUN ? 'DRY' : 'INFO', $msg);
        } else {
            $pend = $g['pendiente'] ?? 0;
            $msg  = "    " . (DRY_RUN ? '[SIMULADO] ' : '🔶 ') . "Gasto #{$idGasto} → PAGO PARCIAL pagado=\${$g['aplicado']} aun_debe=\${$pend} fecha_pago={$fechaPago}";
            log_cron(DRY_RUN ? 'DRY' : 'INFO', $msg);
        }
    }

    $n = count($res['gastos_procesados']);
    if ($n > 0 && !DRY_RUN) {
        log_cron('INFO', "    ✅ Misma lógica que Estado de cuenta — crédito #{$idCredito} ({$n} gasto(s))");
    }

    return [
        'procesados'  => $n,
        'saldo_favor' => (float) ($res['saldo_favor'] ?? 0.0),
        'skip'        => false,
        'razon'       => null,
    ];
}

/**
 * Solo para --test-table: UPDATE directo sobre TABLA_GASTOS (no usa el modelo / tabla productiva).
 */
function procesar_credito_tabla_alternativa(DatabaseSegundometro $db, int $idCredito, array $notasCargos): array
{
    $filtradas = array_filter($notasCargos, fn($n) =>
        ($n['concepto']        ?? '') === CONCEPTO_GDC &&
        ($n['fechaMovimiento'] ?? '') >= FECHA_INICIO
    );

    if (empty($filtradas)) {
        return ['procesados' => 0, 'saldo_favor' => 0.0, 'skip' => true, 'razon' => 'sin_notas_validas'];
    }

    $ultimaNotaFecha = null;
    foreach ($filtradas as $notaF) {
        $fn = $notaF['fechaMovimiento'] ?? null;
        if ($fn && ($ultimaNotaFecha === null || $fn > $ultimaNotaFecha)) {
            $ultimaNotaFecha = $fn;
        }
    }
    $fechaPagoRef = !empty($ultimaNotaFecha) ? $ultimaNotaFecha : date('Y-m-d');

    $totalNotas      = array_sum(array_column($filtradas, 'monto'));
    $montoDisponible = round((float) $totalNotas, 2);

    $todos = $db->queryAll(
        "SELECT
             id_gastos_cobranza,
             monto_valor,
             COALESCE(condonacion_parcial_monto, 0) AS condonacion_parcial_monto,
             COALESCE(estatus_pago, 0)              AS estatus_pago,
             COALESCE(monto_parcial_pagado, 0)      AS monto_parcial_pagado
         FROM " . TABLA_GASTOS . "
         WHERE Id_credito = :id
           AND (condonado IS NULL OR condonado = 0)
         ORDER BY periodo_inicio ASC",
        ['id' => $idCredito]
    );

    foreach ($todos as $g) {
        $estatus = (int) ($g['estatus_pago'] ?? 0);
        if ($estatus === 2) {
            $consumido = round((float) ($g['monto_parcial_pagado'] ?? 0), 2);
            if ($consumido <= 0) {
                $consumido = round((float) ($g['monto_valor'] ?? 0), 2);
            }
            $montoDisponible = round($montoDisponible - $consumido, 2);
        } elseif ($estatus === 1) {
            $montoDisponible = round($montoDisponible - (float) $g['monto_parcial_pagado'], 2);
        }
    }

    $montoDisponible = max(0.0, $montoDisponible);

    if ($montoDisponible <= 0) {
        return ['procesados' => 0, 'saldo_favor' => 0.0, 'skip' => true, 'razon' => 'monto_disponible_cero'];
    }

    $pendientes = array_filter($todos, fn($g) =>
        (int) ($g['estatus_pago'] ?? 0) === 0 ||
        (int) ($g['estatus_pago'] ?? 0) === 1
    );

    if (empty($pendientes)) {
        return ['procesados' => 0, 'saldo_favor' => 0.0, 'skip' => true, 'razon' => 'sin_pendientes'];
    }

    if (DRY_RUN) {
        $montoSim     = $montoDisponible;
        $procesados   = 0;

        foreach ($pendientes as $gasto) {
            if ($montoSim <= 0) {
                break;
            }

            $idGasto       = (int) $gasto['id_gastos_cobranza'];
            $montoNom      = round((float) ($gasto['monto_valor'] ?? 0), 2);
            $condona       = round((float) ($gasto['condonacion_parcial_monto'] ?? 0), 2);
            $montoOriginal = round(max(0.0, $montoNom - $condona), 2);
            $yaPagado      = (int) $gasto['estatus_pago'] === 1
                               ? round((float) $gasto['monto_parcial_pagado'], 2)
                               : 0.0;
            $montoRestante = round($montoOriginal - $yaPagado, 2);

            $fechaPago = $fechaPagoRef;

            if ($montoSim >= $montoRestante) {
                $montoSim = round($montoSim - $montoRestante, 2);
                log_cron('DRY', "    [SIMULADO][TEST-TABLE] Gasto #{$idGasto} → PAGADO TOTAL \${$montoOriginal} fecha_pago={$fechaPago} (ya_pagado=\${$yaPagado})");
            } else {
                $totalPagadoFinal = round($yaPagado + $montoSim, 2);
                $pendiente        = round($montoOriginal - $totalPagadoFinal, 2);
                log_cron('DRY', "    [SIMULADO][TEST-TABLE] Gasto #{$idGasto} → PAGO PARCIAL pagado=\${$totalPagadoFinal} aun_debe=\${$pendiente} fecha_pago={$fechaPago}");
                $montoSim = 0;
            }

            $procesados++;
        }

        return [
            'procesados'  => $procesados,
            'saldo_favor' => round($montoSim, 2),
            'skip'        => false,
            'razon'       => null,
        ];
    }

    try {
        $db->beginTransaction();

        $montoSimulado = $montoDisponible;
        $procesados    = 0;

        foreach ($pendientes as $gasto) {
            if ($montoSimulado <= 0) {
                break;
            }

            $idGasto       = (int) $gasto['id_gastos_cobranza'];
            $montoNom      = round((float) ($gasto['monto_valor'] ?? 0), 2);
            $condona       = round((float) ($gasto['condonacion_parcial_monto'] ?? 0), 2);
            $montoOriginal = round(max(0.0, $montoNom - $condona), 2);
            $yaPagado      = (int) $gasto['estatus_pago'] === 1
                               ? round((float) $gasto['monto_parcial_pagado'], 2)
                               : 0.0;
            $montoRestante = round($montoOriginal - $yaPagado, 2);

            $fechaPago = $fechaPagoRef;

            if ($montoSimulado >= $montoRestante) {
                $montoSimulado = round($montoSimulado - $montoRestante, 2);

                $db->CRUD(
                    "UPDATE " . TABLA_GASTOS . "
                     SET estatus_pago         = 2,
                         monto_parcial_pagado  = :monto_pagado,
                         fecha_pago            = :fecha_pago
                     WHERE id_gastos_cobranza  = :id",
                    [
                        'monto_pagado' => $montoOriginal,
                        'fecha_pago'   => $fechaPago,
                        'id'           => $idGasto,
                    ]
                );
                log_cron('INFO', "    ✔ [TEST-TABLE] Gasto #{$idGasto} → PAGADO TOTAL \${$montoOriginal} fecha_pago={$fechaPago}");

            } else {
                $totalPagadoFinal = round($yaPagado + $montoSimulado, 2);
                $pendiente        = round($montoOriginal - $totalPagadoFinal, 2);

                $db->CRUD(
                    "UPDATE " . TABLA_GASTOS . "
                     SET estatus_pago         = 1,
                         monto_parcial_pagado  = :monto_pagado,
                         fecha_pago            = :fecha_pago
                     WHERE id_gastos_cobranza  = :id",
                    [
                        'monto_pagado' => $totalPagadoFinal,
                        'fecha_pago'   => $fechaPago,
                        'id'           => $idGasto,
                    ]
                );
                log_cron('INFO', "    🔶 [TEST-TABLE] Gasto #{$idGasto} → PAGO PARCIAL pagado=\${$totalPagadoFinal} aun_debe=\${$pendiente} fecha_pago={$fechaPago}");

                $montoSimulado = 0;
            }

            $procesados++;
        }

        $db->commit();
        log_cron('INFO', "    ✅ COMMIT [TEST-TABLE] — crédito #{$idCredito} ({$procesados} gastos)");

        return [
            'procesados'  => $procesados,
            'saldo_favor' => round($montoSimulado, 2),
            'skip'        => false,
            'razon'       => null,
        ];

    } catch (\Exception $e) {
        if ($db->inTransaction()) {
            $db->rollback();
            log_cron('ERROR', "    🔴 ROLLBACK [TEST-TABLE] — crédito #{$idCredito}", ['error' => $e->getMessage()]);
        }
        throw $e;
    }
}

// ============================================================
// PUNTO DE ENTRADA
// ============================================================

log_cron('INFO', str_repeat('═', 60));
log_cron('INFO', 'INICIO — sync_gastos_cobranza v2');
if (DRY_RUN)    log_cron('INFO', '⚠️  MODO DRY RUN    — CERO escrituras en BD');
if (TEST_TABLE) log_cron('INFO', '🧪 MODO TEST TABLE  — tabla: ' . TABLA_GASTOS);
if (FORCE_RUN)  log_cron('INFO', '⚡ MODO FORCE       — flag anti-duplicado ignorado');
if (IDS_FILE !== '') {
    log_cron('INFO', '📋 IDS-FILE          — ' . IDS_FILE);
}
if (IDS_XLSX !== '') {
    log_cron('INFO', '📊 IDS-XLSX          — ' . IDS_XLSX);
    log_cron('INFO', '    columna           — ' . IDS_XLSX_COLUMN);
}
log_cron('INFO', '📅 Fecha corte API S2  — ' . FECHA_CORTE_API);
log_cron('INFO', str_repeat('═', 60));

// ── Flag anti-duplicado ───────────────────────────────────────
verificar_flag_ejecucion();

// ── Conectar a BD ─────────────────────────────────────────────
try {
    $db = new DatabaseSegundometro();
} catch (\Exception $e) {
    log_cron('FATAL', 'No se pudo conectar a DatabaseSegundometro', ['error' => $e->getMessage()]);
    exit(1);
}

// ── Cargar créditos en despacho (hashmap para lookup O(1)) ───
log_cron('INFO', 'Cargando créditos asignados a despacho...');
$creditosDespacho = cargar_creditos_despacho($db);
log_cron('INFO', 'Créditos en despacho cargados: ' . count($creditosDespacho));

// ── Lista de créditos: Excel, txt o SELECT de pendientes ───────
if (IDS_XLSX !== '') {
    $idsArchivo = cargar_ids_desde_excel(IDS_XLSX, IDS_XLSX_COLUMN);
    $creditos   = [];
    foreach ($idsArchivo as $idc) {
        $creditos[] = ['Id_credito' => $idc, 'celula' => null];
    }
} elseif (IDS_FILE !== '') {
    $idsArchivo = cargar_ids_desde_archivo(IDS_FILE);
    $creditos   = [];
    foreach ($idsArchivo as $idc) {
        $creditos[] = ['Id_credito' => $idc, 'celula' => null];
    }
} else {
    $sql = "
    SELECT DISTINCT
        Id_credito,
        celula
    FROM " . TABLA_GASTOS . "
    WHERE (condonado IS NULL OR condonado = 0)
      AND (estatus_pago IS NULL OR estatus_pago IN (0, 1))
    ORDER BY
        CASE
            WHEN celula = 2 THEN 1   -- Callcenter CON convenio
            WHEN celula = 1 THEN 2   -- Despacho
            WHEN celula = 3 THEN 3   -- Cobranza campo
            WHEN celula = 4 THEN 4   -- Callcenter SIN convenio
            ELSE 5                   -- Sin célula
        END,
        Id_credito ASC
";

    try {
        $creditos = $db->queryAll($sql);
    } catch (\Exception $e) {
        log_cron('FATAL', 'Error al obtener créditos pendientes', ['error' => $e->getMessage()]);
        exit(1);
    }
}

$total = count($creditos);
if (IDS_XLSX !== '') {
    log_cron('INFO', "Id_credito en Excel (únicos): {$total}");
} elseif (IDS_FILE !== '') {
    log_cron('INFO', "Id_credito en archivo (únicos): {$total}");
} else {
    log_cron('INFO', "Créditos con gastos pendientes encontrados: {$total}");
}

if ($total === 0) {
    log_cron('INFO', 'Nada que procesar. Finalizando.');
    exit(0);
}

// Límite
$limite = CLI_LIMIT > 0 ? CLI_LIMIT : (MAX_CREDITOS > 0 ? MAX_CREDITOS : 0);
if ($limite > 0 && $total > $limite) {
    $creditos = array_slice($creditos, 0, $limite);
    log_cron('INFO', "Límite activo: procesando {$limite} de {$total}");
    $total = $limite;
}

$labelCelula = [
    1 => 'Despacho',
    2 => 'Callcenter c/convenio',
    3 => 'Cobranza campo',
    4 => 'Callcenter s/convenio',
];

$stats = [
    'procesados'        => 0,
    'skipped'           => 0,
    'skipped_despacho'  => 0,   // ← nuevo: skips por estar en despacho
    'errores'           => 0,
    'gastos_cerrados'   => 0,
    'saldo_favor'       => 0.0,
    'rollbacks'         => 0,   // ← nuevo: cuántos créditos hicieron rollback
];

// ── Iterar ────────────────────────────────────────────────────
foreach ($creditos as $i => $row) {
    $idCredito = (int)   $row['Id_credito'];
    $celula    = isset($row['celula']) ? (int) $row['celula'] : null;
    $label     = $celula !== null
        ? ($labelCelula[$celula] ?? "Célula {$celula}")
        : (IDS_XLSX !== '' ? 'lista excel' : (IDS_FILE !== '' ? 'lista ids-file' : 'Sin célula'));
    $pos       = $i + 1;

    log_cron('INFO', "── [{$pos}/{$total}] Crédito {$idCredito} [{$label}]");

    // ── Validación despacho ───────────────────────────────────
    if (isset($creditosDespacho[$idCredito])) {
        log_cron('INFO', "  ⏭ SKIP — crédito en asigna_creditos_despacho (evita cruce)");
        $stats['skipped']++;
        $stats['skipped_despacho']++;
        usleep(PAUSA_US);
        continue;
    }

    try {
        $notas = obtener_notas_api($idCredito);

        if (empty($notas)) {
            log_cron('WARN', "  Sin notas en API — crédito {$idCredito}");
            $stats['skipped']++;
            usleep(PAUSA_US);
            continue;
        }

        $res = procesar_credito($db, $idCredito, $notas);

        if ($res['skip']) {
            log_cron('INFO', "  Sin cambios ({$res['razon']})");
            $stats['skipped']++;
        } else {
            $accion = DRY_RUN ? 'Simularía cerrar' : 'Gastos cerrados';
            log_cron('INFO', "  ✓ {$accion}: {$res['procesados']} | Saldo favor: \${$res['saldo_favor']}");
            $stats['procesados']++;
            $stats['gastos_cerrados'] += $res['procesados'];
            $stats['saldo_favor']     += $res['saldo_favor'];
        }

    } catch (\Exception $e) {
        log_cron('ERROR', "  Excepción — crédito #{$idCredito}", ['msg' => $e->getMessage()]);
        $stats['errores']++;
        $stats['rollbacks']++;
    }

    usleep(PAUSA_US);
}

// ── Resumen ───────────────────────────────────────────────────
$dur = round(microtime(true) - CRONJOB_START, 2);

log_cron('INFO', str_repeat('═', 60));
log_cron('INFO', DRY_RUN ? 'RESUMEN FINAL — DRY RUN (ningún cambio en BD)' : 'RESUMEN FINAL');
if (TEST_TABLE) log_cron('INFO', '🧪 Tabla usada: ' . TABLA_GASTOS);
log_cron('INFO', str_repeat('═', 60));
log_cron('INFO', "Total evaluados              : {$total}");
log_cron('INFO', "Con cambios " . (DRY_RUN ? '(simulados)    ' : '               ') . ": {$stats['procesados']}");
log_cron('INFO', "Sin cambios (skip total)     : {$stats['skipped']}");
log_cron('INFO', "  └ Por despacho (skip)      : {$stats['skipped_despacho']}");
log_cron('INFO', "Con error                    : {$stats['errores']}");
log_cron('INFO', "Rollbacks ejecutados         : {$stats['rollbacks']}");
log_cron('INFO', "Gastos " . (DRY_RUN ? 'a cerrar (sim.)      ' : 'cerrados (filas)     ') . ": {$stats['gastos_cerrados']}");
log_cron('INFO', "Saldo a favor acumulado      : \${$stats['saldo_favor']}");
log_cron('INFO', "Duración total               : {$dur}s");
log_cron('INFO', 'FIN — sync_gastos_cobranza v2');
log_cron('INFO', str_repeat('═', 60));

exit(0);
