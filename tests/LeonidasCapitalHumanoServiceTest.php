<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasCapitalHumanoService.php';

use Services\LeonidasCapitalHumanoService;

function chAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function chSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}

function chContext(array $overrides = []): array
{
    $base = [
        'actor_id' => 878,
        'nombre_corto' => 'Lazaro',
        'salario_totp_vigente' => true,
        'permisos_agente' => [
            'rrhh_lectura' => true,
            'auditoria_rrhh' => true,
            'rrhh_registrar' => true,
            'rrhh_editar' => true,
            'estructura' => true,
            'bajas' => true,
            'reingresos' => true,
            'vacaciones' => true,
            'vacaciones_admin' => true,
            'candidatos' => true,
            'documentos' => true,
            'permisos' => true,
            'salarios' => true,
        ],
    ];
    return array_replace_recursive($base, $overrides);
}

function chService(array $overrides = []): LeonidasCapitalHumanoService
{
    $base = [
        'persona_buscar' => static fn(string $criterio): array => ['success' => true, 'datos' => [[
            'id' => 31,
            'nombre_completo' => 'JENIFER ITZEL PEINADO ALCALA',
            'estatus' => 'Activo',
            'numero_empleado' => '999999080',
            'codigo_contpac' => '31',
        ]]],
        'persona_detalle' => static fn(int $id): array => ['success' => true, 'datos' => [
            'id' => $id,
            'nombres' => 'JENIFER',
            'segundo_nombre' => 'ITZEL',
            'apellidop' => 'PEINADO',
            'apellidom' => 'ALCALA',
            'estatus' => 'Activo',
            'numero_empleado' => '999999080',
            'codigo_contpac' => '31',
            'curp' => 'PEAJ971015MDFNLN00',
            'rfc' => 'PEAJ9710156B8',
            'nss' => '63169710470',
            'correo' => 'jenifer@example.com',
            'puesto' => 'Gerente',
            'departamento' => 'Atencion a Clientes',
            'jefe' => 'JONNATHAN FLORES',
            'fecha_ingreso' => '2023-05-01',
        ]],
        'persona_documentos' => static fn(int $id): array => ['success' => true, 'datos' => ['total' => 10, 'documentos' => array_fill(0, 10, ['id' => 1])]],
        'vacaciones_resumen' => static fn(int $id): array => ['success' => true, 'datos' => ['periodo' => ['dias_disponibles' => 8]]],
        'rrhh_auditoria' => static fn(): array => [
            'activos' => 1200,
            'sin_curp' => 3,
            'sin_rfc' => 4,
            'sin_nss' => 5,
            'sin_jefe' => 2,
            'numero_empleado_duplicado' => 0,
            'curp_duplicada' => 0,
        ],
        'candidato_detalle' => static fn(int $id): array => ['success' => true, 'datos' => [
            'id' => $id,
            'nombres' => 'RODRIGO',
            'apellidop' => 'ROMERO',
            'apellidom' => 'MIRANDA',
            'estatus' => 'Validacion final',
            'nombre_puesto' => 'Gestor',
            'nombre_jefe' => 'HECTOR RUIZ',
            'fecha_ingreso_programada' => '2026-07-25',
            'contrato_firmado_en' => null,
            'es_reingreso' => 0,
        ]],
        'candidato_documentos' => static fn(int $id): array => ['success' => true, 'datos' => [
            'total' => 10,
            'documentos' => array_fill(0, 10, ['id' => 1]),
            'verificacion' => ['estado' => 'pendiente'],
        ]],
        'rrhh_registrar' => static fn(array $p, int $actor): array => ['success' => true, 'mensaje' => 'Colaborador registrado.', 'datos' => ['payload' => $p, 'actor' => $actor]],
        'rrhh_actualizar' => static fn(array $p, int $actor): array => ['success' => true, 'mensaje' => 'Colaborador actualizado.', 'datos' => $p],
        'rrhh_actualizar_campo' => static fn(array $p, int $actor): array => ['success' => true, 'mensaje' => 'Campo actualizado.', 'datos' => $p],
        'persona_baja' => static fn(array $p, int $actor): array => ['success' => true, 'mensaje' => 'Baja registrada.', 'datos' => $p],
        'persona_reingreso' => static fn(array $p, int $actor): array => ['success' => true, 'mensaje' => 'Reingreso registrado.', 'datos' => $p],
        'vacaciones_solicitar' => static fn(array $p, int $actor): array => ['success' => true, 'mensaje' => 'Vacaciones solicitadas.', 'datos' => $p],
        'vacaciones_resolver' => static fn(array $p, int $actor): array => ['success' => true, 'mensaje' => 'Vacaciones resueltas.', 'datos' => $p],
        'estructura_importar' => static fn(array $filas, int $actor, bool $aplicar): array => [
            'success' => true,
            'mensaje' => $aplicar ? 'Estructura actualizada.' : 'Prevalidacion correcta.',
            'datos' => ['resumen' => ['errores' => 0, 'con_cambios' => count($filas)], 'detalles' => []],
        ],
        'candidato_registrar' => static fn(array $p): array => ['success' => true, 'mensaje' => 'Candidato registrado.', 'datos' => $p],
        'candidato_actualizar' => static fn(array $p): array => ['success' => true, 'mensaje' => 'Candidato actualizado.', 'datos' => $p],
        'candidato_etapa' => static fn(int $id, string $etapa): array => ['success' => true, 'mensaje' => 'Etapa actualizada.', 'datos' => compact('id', 'etapa')],
        'documento_reevaluar' => static fn(int $id): array => ['success' => true, 'mensaje' => 'Reevaluacion encolada.', 'datos' => ['id' => $id]],
        'documento_validar' => static fn(int $id, bool $validado): array => ['success' => true, 'mensaje' => 'Documento actualizado.', 'datos' => compact('id', 'validado')],
        'documento_clasificar' => static fn(int $id, string $tipo): array => ['success' => true, 'mensaje' => 'Documento clasificado.', 'datos' => compact('id', 'tipo')],
        'permiso_actualizar' => static fn(int $persona, int $modulo, bool $asignado): array => ['success' => true, 'mensaje' => 'Permiso actualizado.', 'datos' => compact('persona', 'modulo', 'asignado')],
        'salario_consultar' => static fn(int $persona): array => ['success' => true, 'datos' => ['tiene_salario' => true, 'salario' => 18500, 'moneda' => 'MXN']],
        'salario_guardar' => static fn(int $persona, $salario, int $actor): array => ['success' => true, 'mensaje' => 'Salario actualizado.', 'datos' => compact('persona', 'salario', 'actor')],
        'auditar' => static function (array $evento): void {},
    ];
    return new LeonidasCapitalHumanoService($overrides + $base);
}

// Consultas: ficha 360, auditoria, seguimiento y expediente.
$_SESSION = [];
$service = chService();
$ficha = $service->resolver('ficha 360 de Jenifer Peinado', 'ficha 360 de jenifer peinado', chContext());
chSame('rrhh_ficha_360', $ficha['tipo'], 'Debe generar la ficha 360.');
chAssert(str_contains($ficha['mensaje'], 'Numero de empleado: 999999080'), 'Debe mostrar el numero de empleado real.');
chAssert(str_contains($ficha['mensaje'], 'Codigo CONTPAC: 31'), 'Debe mostrar CONTPAC por separado.');

$audit = $service->resolver('auditoria de rrhh', 'auditoria de rrhh', chContext());
chSame('rrhh_auditoria', $audit['tipo'], 'Debe generar la auditoria de RR.HH.');
chAssert(str_contains($audit['mensaje'], 'Sin curp: 3'), 'Debe explicar las anomalías encontradas.');

$candidate = $service->resolver('seguimiento candidato 42', 'seguimiento candidato 42', chContext());
chSame('candidato_seguimiento', $candidate['tipo'], 'Debe consultar al candidato indicado.');
chAssert(str_contains($candidate['mensaje'], 'candidato 42'), 'No debe confundir el verbo con el ID.');
chAssert(str_contains($candidate['mensaje'], 'siguen pendientes de validacion'), 'Debe explicar por que puede estar detenido el candidato.');

$documents = $service->resolver('documentos candidato 42', 'documentos candidato 42', chContext());
chSame('documento_auditoria', $documents['tipo'], 'Debe auditar el expediente del candidato.');

// Escrituras directas: siempre producen una propuesta y solo escriben al ejecutar.
$reevaluate = $service->resolver('reevaluar candidato 42', 'reevaluar candidato 42', chContext());
chSame(42, $reevaluate['propuesta_especificacion']['payload']['id_candidato'], 'La reevaluacion debe conservar el ID.');
$reevaluated = $service->ejecutar('documento_reevaluar', $reevaluate['propuesta_especificacion']['payload'], chContext());
chSame('agente_ejecucion_exitosa', $reevaluated['tipo'], 'Debe ejecutar la reevaluacion confirmada.');

$stage = $service->resolver('cambia etapa candidato 42 a Contrato firmado', 'cambia etapa candidato 42 a contrato firmado', chContext());
chSame(42, $stage['propuesta_especificacion']['payload']['id_candidato'], 'Debe conservar el candidato al cambiar etapa.');
chSame('contrato firmado', $stage['propuesta_especificacion']['payload']['etapa'], 'Debe conservar la etapa solicitada.');
$service->ejecutar('candidato_etapa', $stage['propuesta_especificacion']['payload'], chContext());

$validated = $service->resolver('valida documento 803', 'valida documento 803', chContext());
chSame(803, $validated['propuesta_especificacion']['payload']['id_documento'], 'Debe conservar el ID del documento.');
$service->ejecutar('documento_validar', $validated['propuesta_especificacion']['payload'], chContext());

$classified = $service->resolver('clasifica documento 803 como CURP', 'clasifica documento 803 como curp', chContext());
chSame(['id_documento' => 803, 'tipo_documento' => 'curp'], $classified['propuesta_especificacion']['payload'], 'Debe preparar la clasificacion exacta.');
$classifiedResult = $service->ejecutar('documento_clasificar', $classified['propuesta_especificacion']['payload'], chContext());
chSame('agente_ejecucion_exitosa', $classifiedResult['tipo'], 'Debe ejecutar la clasificacion confirmada.');

$permission = $service->resolver('otorga permiso 191 a persona 878', 'otorga permiso 191 a persona 878', chContext());
chSame(['id_modulo' => 191, 'id_persona' => 878, 'asignado' => true], $permission['propuesta_especificacion']['payload'], 'Debe preparar el permiso exacto.');
$service->ejecutar('permiso_actualizar', $permission['propuesta_especificacion']['payload'], chContext());

$salary = $service->resolver('consulta salario persona 31', 'consulta salario persona 31', chContext());
chSame('salario_consulta', $salary['tipo'], 'Debe consultar salario con TOTP vigente.');
chAssert(str_contains($salary['mensaje'], '18,500.00'), 'Debe mostrar el salario autorizado.');
try {
    $service->resolver('consulta salario persona 31', 'consulta salario persona 31', chContext(['salario_totp_vigente' => false]));
    throw new RuntimeException('El salario no debe abrir sin segundo factor.');
} catch (RuntimeException $e) {
    chAssert(str_contains($e->getMessage(), 'Google Authenticator'), 'Debe explicar el segundo factor requerido.');
}

$salaryChange = $service->resolver('actualiza salario persona 31 19500', 'actualiza salario persona 31 19500', chContext());
$service->ejecutar('salario_actualizar', $salaryChange['propuesta_especificacion']['payload'], chContext());
try {
    $service->ejecutar('salario_actualizar', ['id_persona' => 31, 'salario' => -1], chContext());
    throw new RuntimeException('No debe aceptar salarios negativos.');
} catch (InvalidArgumentException $e) {
    chAssert(str_contains($e->getMessage(), 'importe valido'), 'Debe rechazar importes salariales invalidos.');
}

// Flujo guiado de colaborador: transforma datos planos sin mezclar identificadores.
$_SESSION = [];
$register = $service->resolver('registra colaborador', 'registra colaborador', chContext());
chSame('agente_datos_requeridos', $register['tipo'], 'Debe pedir los datos del colaborador.');
$registerProposal = $service->resolver(json_encode([
    'nombres' => 'ANA',
    'apellidop' => 'PEREZ',
    'numero_empleado' => 'FM-0042',
    'codigo_contpac' => '2201',
    'id_pais' => 1,
    'area_id' => 5,
    'departamento_id' => 9,
    'puesto_id' => 14,
]), '', chContext());
chSame('rrhh_registrar', $registerProposal['propuesta_especificacion']['accion'], 'Debe preparar el alta del colaborador.');
$registered = $service->ejecutar('rrhh_registrar', $registerProposal['propuesta_especificacion']['payload'], chContext());
$registeredPayload = $registered['ejecucion']['datos']['payload']['persona'];
chSame('FM-0042', $registeredPayload['numero_empleado'], 'Debe conservar numero_empleado.');
chSame('2201', $registeredPayload['codigo_contpac'], 'Debe conservar codigo_contpac de forma independiente.');

$_SESSION = [];
$service->resolver('edita colaborador 31', 'edita colaborador 31', chContext());
$editProposal = $service->resolver('campo=correo, valor=nuevo@example.com', '', chContext());
chSame('rrhh_actualizar', $editProposal['propuesta_especificacion']['accion'], 'Debe preparar una edicion de RR.HH.');
$service->ejecutar('rrhh_actualizar', $editProposal['propuesta_especificacion']['payload'], chContext());

// Baja y reingreso guiados.
$_SESSION = [];
$service->resolver('da de baja persona 31', 'da de baja persona 31', chContext());
$downProposal = $service->resolver('motivo=Renuncia, descripcion=Separacion voluntaria, fecha_baja=2026-07-20', '', chContext());
chSame('persona_baja', $downProposal['propuesta_especificacion']['accion'], 'Debe preparar la baja.');
$service->ejecutar('persona_baja', $downProposal['propuesta_especificacion']['payload'], chContext());

$_SESSION = [];
$service->resolver('reingresa persona 31', 'reingresa persona 31', chContext());
$reentryProposal = $service->resolver('motivo_reingreso=Recontratacion, descripcion_reingreso=Regresa a plantilla, fecha_reingreso=2026-07-21', '', chContext());
chSame('persona_reingreso', $reentryProposal['propuesta_especificacion']['accion'], 'Debe preparar el reingreso.');
$service->ejecutar('persona_reingreso', $reentryProposal['propuesta_especificacion']['payload'], chContext());

// Vacaciones: solicitud y resolucion separadas, ambas confirmables.
$_SESSION = [];
$service->resolver('solicita vacaciones 2026-08-03 2026-08-07', 'solicita vacaciones 2026-08-03 2026-08-07', chContext());
$vacationProposal = $service->resolver('firma_colaborador=LAZARO GONZALEZ', '', chContext());
chSame('vacaciones_solicitar', $vacationProposal['propuesta_especificacion']['accion'], 'Debe preparar vacaciones.');
$service->ejecutar('vacaciones_solicitar', $vacationProposal['propuesta_especificacion']['payload'], chContext());

$_SESSION = [];
$service->resolver('aprobar vacaciones 501', 'aprobar vacaciones 501', chContext());
$resolveProposal = $service->resolver('etapa=jefe, firma_responsable=JEFE PRUEBA', '', chContext());
chSame('vacaciones_resolver', $resolveProposal['propuesta_especificacion']['accion'], 'Debe preparar la aprobacion.');
$service->ejecutar('vacaciones_resolver', $resolveProposal['propuesta_especificacion']['payload'], chContext());

// Estructura individual: previsualiza y aplica solo cuando no hay errores.
$_SESSION = [];
$calls = [];
$structure = chService(['estructura_importar' => static function (array $rows, int $actor, bool $apply) use (&$calls): array {
    $calls[] = $apply;
    return ['success' => true, 'mensaje' => $apply ? 'Aplicada.' : 'Prevalidada.', 'datos' => ['resumen' => ['errores' => 0, 'con_cambios' => 1], 'detalles' => []]];
}]);
$structure->resolver('cambia estructura', 'cambia estructura', chContext());
$structureProposal = $structure->resolver('external_id=999136, puesto_legacy=Supervisor, departamento=Campo 1-7, supervisor=SANDRA YUNUETH', '', chContext());
chSame('999136', $structureProposal['propuesta_especificacion']['payload']['fila']['external_id'], 'Debe construir la fila internamente.');
$structure->ejecutar('estructura_cambiar', $structureProposal['propuesta_especificacion']['payload'], chContext());
chSame([false, true], $calls, 'Debe prevalidar antes de aplicar estructura.');

$blockedCalls = [];
$blockedStructure = chService(['estructura_importar' => static function (array $rows, int $actor, bool $apply) use (&$blockedCalls): array {
    $blockedCalls[] = $apply;
    return ['success' => true, 'datos' => ['resumen' => ['errores' => 1, 'con_cambios' => 0], 'detalles' => [['estado' => 'error', 'motivo' => 'Supervisor no encontrado']]]];
}]);
try {
    $blockedStructure->ejecutar('estructura_cambiar', ['fila' => ['external_id' => '999136', 'puesto_legacy' => 'Gestor', 'departamento' => 'Campo 1-7']], chContext());
    throw new RuntimeException('No debe aplicar una estructura con errores.');
} catch (RuntimeException $e) {
    chAssert(str_contains($e->getMessage(), 'Supervisor no encontrado'), 'Debe explicar el motivo de prevalidacion.');
}
chSame([false], $blockedCalls, 'No debe ejecutar la fase de escritura si falla la prevalidacion.');

try {
    $structure->ejecutar('estructura_cambiar', ['fila' => ['puesto_legacy' => 'Gestor', 'departamento' => 'Campo 1-7']], chContext());
    throw new RuntimeException('No debe aceptar una estructura sin external_id.');
} catch (InvalidArgumentException $e) {
    chAssert(str_contains($e->getMessage(), 'external_id'), 'Debe identificar la fila incompleta.');
}

// Carga masiva: la intencion no debe confundirse con el cambio individual.
$_SESSION = [];
$mass = $service->resolver('actualiza estructura masiva', 'actualiza estructura masiva', chContext());
chSame('agente_datos_requeridos', $mass['tipo'], 'Debe iniciar el flujo masivo.');
$massProposal = $service->resolver(json_encode([[
    'external_id' => '1001',
    'puesto_legacy' => 'Gestor',
    'departamento' => 'Campo 1-7',
]]), '', chContext());
chSame('estructura_importar', $massProposal['propuesta_especificacion']['accion'], 'Debe preparar la carga masiva.');
$service->ejecutar('estructura_importar', $massProposal['propuesta_especificacion']['payload'], chContext());

// Candidatos: alta y edicion guiadas.
$_SESSION = [];
$service->resolver('registra candidato', 'registra candidato', chContext());
$candidateProposal = $service->resolver('nombres=LUIS, apellidop=TORRES', '', chContext());
chSame('candidato_registrar', $candidateProposal['propuesta_especificacion']['accion'], 'Debe preparar el candidato.');
$service->ejecutar('candidato_registrar', $candidateProposal['propuesta_especificacion']['payload'], chContext());

$_SESSION = [];
$service->resolver('edita candidato 42', 'edita candidato 42', chContext());
$candidateEdit = $service->resolver('campo=telefono, valor=5512345678', '', chContext());
chSame('candidato_actualizar', $candidateEdit['propuesta_especificacion']['accion'], 'Debe preparar la edicion del candidato.');
$service->ejecutar('candidato_actualizar', $candidateEdit['propuesta_especificacion']['payload'], chContext());

// Permisos: una sesion sin autorizacion no puede ni preparar la accion.
try {
    $service->resolver('otorga permiso 191 a persona 878', 'otorga permiso 191 a persona 878', chContext(['permisos_agente' => ['permisos' => false]]));
    throw new RuntimeException('No debe preparar permisos sin autorizacion.');
} catch (RuntimeException $e) {
    chAssert(str_contains($e->getMessage(), 'permiso requerido'), 'Debe denegar de forma explicita.');
}

try {
    $service->ejecutar('documento_clasificar', ['id_documento' => 803, 'tipo_documento' => '<script>'], chContext());
    throw new RuntimeException('No debe aceptar una clasificacion insegura.');
} catch (InvalidArgumentException $e) {
    chAssert(str_contains($e->getMessage(), 'tipo de documento'), 'Debe rechazar tipos documentales invalidos.');
}

echo "LeonidasCapitalHumanoService: OK\n";
