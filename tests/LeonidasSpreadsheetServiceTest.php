<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasSpreadsheetService.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Services\LeonidasSpreadsheetService;

function spreadsheetAssert(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

function spreadsheetFile(array $headers, array $rows): string
{
    $book = new Spreadsheet();
    $sheet = $book->getActiveSheet();
    $sheet->fromArray($headers, null, 'A1');
    if ($rows) $sheet->fromArray($rows, null, 'A2');
    $path = tempnam(sys_get_temp_dir(), 'leonidas_excel_') . '.xlsx';
    (new Xlsx($book))->save($path);
    $book->disconnectWorksheets();
    return $path;
}

function spreadsheetToken(string $path, int $actorId = 878): string
{
    $token = bin2hex(random_bytes(8));
    $_SESSION['leonidas_spreadsheet_uploads'][$token] = [
        'actor_id' => $actorId,
        'nombre' => basename($path),
        'ruta' => $path,
        'hash' => hash_file('sha256', $path),
        'expira_en' => time() + 600,
    ];
    return $token;
}

function spreadsheetContext(array $overrides = []): array
{
    return $overrides + [
        'actor_id' => 878,
        'permisos_agente' => ['estructura' => true, 'salarios' => true],
        'salario_totp_vigente' => true,
    ];
}

$_SESSION = [];
$personas = [
    ['id' => 1, 'numero_empleado' => '100', 'codigo_contpac' => '900', 'curp' => 'AAAA900101HDFRRN01', 'estatus' => 'Activo', 'nombre_completo' => 'MARIA LOPEZ DIAZ'],
    ['id' => 2, 'numero_empleado' => '101', 'codigo_contpac' => '901', 'curp' => 'BBBB900101HDFRRN02', 'estatus' => 'Activo', 'nombre_completo' => 'JOSE PEREZ CRUZ'],
    ['id' => 3, 'numero_empleado' => '102', 'codigo_contpac' => '902', 'curp' => 'CCCC900101HDFRRN03', 'estatus' => 'Baja', 'nombre_completo' => 'NOMBRE REPETIDO'],
    ['id' => 4, 'numero_empleado' => '103', 'codigo_contpac' => '903', 'curp' => 'DDDD900101HDFRRN04', 'estatus' => 'Activo', 'nombre_completo' => 'NOMBRE REPETIDO'],
];
$structureCalls = [];
$salaryCalls = [];
$service = new LeonidasSpreadsheetService([
    'personas' => static fn(): array => $personas,
    'estructura_importar' => static function (array $rows, int $actor, bool $apply) use (&$structureCalls): array {
        $structureCalls[] = ['rows' => $rows, 'actor' => $actor, 'apply' => $apply];
        return ['success' => true, 'datos' => ['detalles' => [], 'aplicados' => $apply ? count($rows) : 0]];
    },
    'salarios_importar' => static function (array $rows, int $actor) use (&$salaryCalls): array {
        $salaryCalls[] = ['rows' => $rows, 'actor' => $actor];
        return ['success' => true, 'datos' => ['aplicados' => count($rows)]];
    },
]);

$structurePath = spreadsheetFile(
    ['external_id', 'nombre_completo', 'puesto', 'departamento', 'supervisor', 'subgerente', 'gerente'],
    [
        ['100', 'Maria Lopez Diaz', 'Gestor', 'Campo 1-7', 'JEFE UNO', '', 'GERENTE UNO'],
        ['', 'Jose Perez Cruz', 'Supervisor', 'Campo 1-7', '', 'SUBGERENTE UNO', 'GERENTE UNO'],
    ]
);
$structureToken = spreadsheetToken($structurePath);
$preview = $service->analizar($structureToken, 'actualiza la estructura', spreadsheetContext());
spreadsheetAssert(($preview['tipo'] ?? '') === 'excel_prevalidacion', 'Debe generar una prevalidacion del Excel.');
spreadsheetAssert(($preview['reporte']['total'] ?? null) === 2, 'Debe revisar todas las filas.');
spreadsheetAssert(isset($preview['propuesta_especificacion']), 'Una carga valida debe producir propuesta confirmable.');
spreadsheetAssert($structureCalls[0]['apply'] === false, 'La primera lectura debe ser exclusivamente vista previa.');
spreadsheetAssert($structureCalls[0]['rows'][1]['external_id'] === '101', 'La coincidencia por nombre debe resolver el numero_empleado real.');
$execution = $service->ejecutar($preview['propuesta_especificacion']['payload'], spreadsheetContext());
spreadsheetAssert(($execution['tipo'] ?? '') === 'agente_ejecucion_exitosa', 'La confirmacion debe ejecutar el lote valido.');
spreadsheetAssert($structureCalls[1]['apply'] === false && $structureCalls[2]['apply'] === true, 'Debe revalidar antes de aplicar.');

$conflictPath = spreadsheetFile(['external_id', 'codigo_contpac', 'puesto', 'departamento'], [['100', '901', 'Gestor', 'Campo 1-7']]);
$conflict = $service->analizar(spreadsheetToken($conflictPath), 'actualiza estructura', spreadsheetContext());
spreadsheetAssert(!isset($conflict['propuesta_especificacion']), 'Identificadores de personas distintas deben bloquear el lote.');
spreadsheetAssert(str_contains($conflict['reporte']['filas'][0]['detalle'], 'personas distintas'), 'Debe explicar el conflicto de identidad.');

$ambiguousPath = spreadsheetFile(['nombre_completo', 'puesto', 'departamento'], [['Nombre Repetido', 'Gestor', 'Campo 1-7']]);
$ambiguous = $service->analizar(spreadsheetToken($ambiguousPath), 'actualiza estructura', spreadsheetContext());
spreadsheetAssert(!isset($ambiguous['propuesta_especificacion']), 'Un nombre ambiguo nunca debe actualizar automaticamente.');
spreadsheetAssert(str_contains($ambiguous['reporte']['filas'][0]['detalle'], 'varias personas'), 'Debe identificar la ambiguedad por nombre.');

$mixedPath = spreadsheetFile(
    ['nombre_completo', 'puesto', 'departamento'],
    [['Maria Lopez Diaz', 'Gestor', 'Campo 1-7'], ['Persona Inexistente', 'Gestor', 'Campo 1-7']]
);
$mixed = $service->analizar(spreadsheetToken($mixedPath), 'actualiza estructura', spreadsheetContext());
spreadsheetAssert(($mixed['reporte']['total'] ?? 0) === 2 && !isset($mixed['propuesta_especificacion']), 'Una sola fila invalida debe bloquear todo el lote.');

$omittedPath = spreadsheetFile(['external_id', 'puesto', 'departamento'], [['102', 'Gestor', 'Campo 1-7']]);
$omittedService = new LeonidasSpreadsheetService([
    'personas' => static fn(): array => $personas,
    'estructura_importar' => static fn(array $rows, int $actor, bool $apply): array => [
        'success' => true,
        'datos' => ['detalles' => [['fila' => 2, 'estado' => 'omitido', 'mensajes' => ['La persona esta en estatus Baja.']]]],
    ],
    'salarios_importar' => static fn(array $rows, int $actor): array => ['success' => true, 'datos' => []],
]);
$omitted = $omittedService->analizar(spreadsheetToken($omittedPath), 'actualiza estructura', spreadsheetContext());
spreadsheetAssert(!isset($omitted['propuesta_especificacion']), 'Una fila omitida debe bloquear una aplicacion parcial silenciosa.');
spreadsheetAssert(str_contains($omitted['reporte']['filas'][0]['detalle'], 'estatus Baja'), 'Debe explicar por que la fila fue omitida.');

$salaryPath = spreadsheetFile(['codigo_contpac', 'nombre_completo', 'sueldo'], [['900', 'Maria Lopez Diaz', '$12,345.67']]);
$salaryToken = spreadsheetToken($salaryPath);
$salaryPreview = $service->analizar($salaryToken, 'actualiza los salarios', spreadsheetContext());
spreadsheetAssert(isset($salaryPreview['propuesta_especificacion']), 'Un salario valido debe quedar listo para confirmar.');
spreadsheetAssert(empty($salaryCalls), 'La vista previa de salarios no debe escribir datos.');
$totpBlocked = false;
try {
    $service->ejecutar($salaryPreview['propuesta_especificacion']['payload'], spreadsheetContext(['salario_totp_vigente' => false]));
} catch (RuntimeException $error) {
    $totpBlocked = str_contains($error->getMessage(), 'Google Authenticator');
}
spreadsheetAssert($totpBlocked, 'La carga salarial debe exigir Google Authenticator.');
$service->ejecutar($salaryPreview['propuesta_especificacion']['payload'], spreadsheetContext());
spreadsheetAssert($salaryCalls[0]['rows'][0]['curp'] === 'AAAA900101HDFRRN01', 'El salario debe vincularse por la CURP real.');
spreadsheetAssert($salaryCalls[0]['rows'][0]['salario'] === '12345.67', 'El salario debe normalizarse sin perder centavos.');

echo "LeonidasSpreadsheetServiceTest OK\n";
