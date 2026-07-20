<?php

namespace Services;

use Core\Database;

/**
 * Read and command boundary for Leonidas in Capital Humano.
 * Every write is validated again when it is executed.
 */
class LeonidasCapitalHumanoService
{
    private const TASK_KEY = 'leonidas_capital_humano_task';
    private const TASK_TTL = 1200;

    /** @var array<string, callable> */
    private array $adapters;

    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters + [
            'persona_buscar' => fn(string $criterio): array => $this->buscarPersonas($criterio),
            'persona_detalle' => static fn(int $id): array => \Models\CapHum::getPersonaDetalle($id),
            'persona_documentos' => static fn(int $id): array => \Models\CapHum::getResumenDocumentosColaborador($id),
            'vacaciones_resumen' => static fn(int $id): array => \Models\Vacaciones::resumenPersona($id),
            'vacaciones_solicitar' => static fn(array $p, int $actor): array => \Models\Vacaciones::solicitar(
                (int) $p['id_persona'],
                (string) $p['fecha_inicio'],
                (string) $p['fecha_fin'],
                (string) ($p['comentario'] ?? ''),
                $actor,
                (string) ($p['modo_fechas'] ?? 'rango'),
                is_array($p['fechas_separadas'] ?? null) ? $p['fechas_separadas'] : [],
                (string) ($p['firma_colaborador'] ?? '')
            ),
            'vacaciones_resolver' => static fn(array $p, int $actor): array => \Models\Vacaciones::resolverAdmin(
                (int) $p['id_solicitud'],
                (string) $p['etapa'],
                (string) $p['decision'],
                (string) ($p['comentario'] ?? ''),
                $actor,
                (string) ($p['firma_responsable'] ?? '')
            ),
            'rrhh_registrar' => static fn(array $p, int $actor): array => \Models\CapHumRrhh::registrarUsuario($p, $actor),
            'rrhh_actualizar' => static fn(array $p, int $actor): array => \Models\CapHumRrhh::actualizarUsuario($p, $actor),
            'rrhh_actualizar_campo' => fn(array $p, int $actor): array => $this->actualizarCampoRrhh($p, $actor),
            'persona_baja' => static fn(array $p, int $actor): array => \Models\CapHum::registrarBajaGestor($p + ['usuario_baja' => $actor]),
            'persona_reingreso' => static fn(array $p, int $actor): array => \Models\CapHum::registrarReingresoGestor($p + ['usuario_reingreso' => (string) $actor]),
            'estructura_importar' => static fn(array $filas, int $actor, bool $aplicar): array => \Models\CapHum::importarCambiosEstructuraPorExternalId($filas, $actor, $aplicar),
            'candidato_detalle' => static fn(int $id): array => \Models\Candidatos::getById($id),
            'candidato_documentos' => static function (int $id): array {
                return ['success' => true, 'datos' => \Models\Candidatos::getDocumentosYVerificacion($id)];
            },
            'candidato_registrar' => static fn(array $p): array => \Models\Candidatos::insert($p),
            'candidato_actualizar' => fn(array $p): array => $this->actualizarCandidato($p),
            'candidato_etapa' => fn(int $id, string $etapa): array => $this->actualizarEtapaCandidato($id, $etapa),
            'documento_reevaluar' => static fn(int $id): array => \Models\Candidatos::encolarVerificacionDocumental($id, [], true, 'leonidas'),
            'documento_validar' => static fn(int $id, bool $validado): array => \Models\Candidatos::toggleValidadoDocumento($id, $validado),
            'documento_clasificar' => fn(int $id, string $tipo): array => $this->clasificarDocumento($id, $tipo),
            'permiso_actualizar' => static fn(int $persona, int $modulo, bool $asignado): array => \Models\CapHum::actualizarModuloPerfil($persona, $modulo, $asignado),
            'salario_consultar' => static fn(int $persona): array => \Models\CapHum::getSalarioSensiblePersona($persona),
            'salario_guardar' => static fn(int $persona, $salario, int $actor): array => \Models\CapHum::guardarSalarioSensiblePersona($persona, $salario, $actor),
            'rrhh_auditoria' => fn(): array => $this->consultarAuditoriaRrhh(),
            'auditar' => static function (array $evento): void {
                \Models\CapHum::registrarAuditoriaInternaRrhh($evento);
            },
        ];
    }

    public static function accionesEjecutables(): array
    {
        return [
            'rrhh_registrar', 'rrhh_actualizar', 'persona_baja', 'persona_reingreso',
            'vacaciones_solicitar', 'vacaciones_resolver', 'estructura_cambiar',
            'estructura_importar', 'candidato_registrar', 'candidato_actualizar',
            'candidato_etapa', 'documento_reevaluar', 'documento_validar', 'documento_clasificar',
            'permiso_actualizar', 'salario_actualizar',
        ];
    }

    public static function puedeEjecutar(string $accion): bool
    {
        return in_array($accion, self::accionesEjecutables(), true);
    }

    public function resolver(string $mensaje, string $normalizado, array $contexto): ?array
    {
        $tarea = $this->tareaActual((int) ($contexto['actor_id'] ?? 0));
        if ($tarea !== null) {
            if (preg_match('/\b(cancelar|cancela|olvida|detener)\b/u', $normalizado)) {
                $this->limpiarTarea();
                return $this->respuesta('Tarea de Capital Humano cancelada. No se modifico ningun dato.', 'agente_cancelado');
            }
            return $this->continuarTarea($mensaje, $tarea, $contexto);
        }

        if (preg_match('/\b(ficha|resumen)\s*(360)?\s*(?:de|del)?\s*(.+)$/u', $normalizado, $m)) {
            return $this->ficha360(trim((string) ($m[3] ?? '')), $contexto);
        }
        if (preg_match('/\b(audita|auditoria|revisa)\b.*\b(rrhh|recursos humanos|plantilla)\b/u', $normalizado)) {
            return $this->auditoriaRrhh($contexto);
        }
        if (preg_match('/\b(seguimiento|estatus|explica)\b.*\bcandidat[oa]\b[^0-9]*(\d+)/u', $normalizado, $m)) {
            return $this->seguimientoCandidato((int) $m[2], $contexto);
        }
        if (preg_match('/\b(documentos?|expediente)\b.*\bcandidat[oa]\b[^0-9]*(\d+)/u', $normalizado, $m)) {
            return $this->auditoriaExpediente((int) $m[2], $contexto);
        }

        if (preg_match('/\b(reevalua|reevaluar|reprocesa)\b.*\bcandidat[oa]\b[^0-9]*(\d+)/u', $normalizado, $m)) {
            return $this->propuestaSimple('documento_reevaluar', ['id_candidato' => (int) $m[2]], 'reevaluar el expediente del candidato ' . (int) $m[2], $contexto, 'documentos');
        }
        if (preg_match('/\b(clasifica|reclasifica|corrige)\b.*\bdocumento\b[^0-9]*(\d+)\s+(?:como|a|tipo)\s+(.+)$/u', $normalizado, $m)) {
            return $this->propuestaSimple('documento_clasificar', [
                'id_documento' => (int) $m[2],
                'tipo_documento' => trim((string) $m[3]),
            ], 'clasificar el documento ' . (int) $m[2] . ' como ' . trim((string) $m[3]), $contexto, 'documentos');
        }
        if (preg_match('/\b(otorga|asigna|concede|retira|quita|revoca)\b.*\bpermiso\b[^0-9]*(\d+)\D+(?:persona|usuario)\D*(\d+)/u', $normalizado, $m)) {
            $asignado = !preg_match('/\b(retira|quita|revoca)\b/u', $normalizado);
            return $this->propuestaSimple('permiso_actualizar', [
                'id_modulo' => (int) $m[2],
                'id_persona' => (int) $m[3],
                'asignado' => $asignado,
            ], ($asignado ? 'otorgar' : 'retirar') . ' el permiso ' . (int) $m[2] . ' a la persona ' . (int) $m[3], $contexto, 'permisos');
        }
        if (preg_match('/\b(cambia|actualiza|mueve)\b.*\betapa\b.*\bcandidat[oa]\b[^0-9]*(\d+)\s+(?:a\s+)?(.+)$/u', $normalizado, $m)) {
            return $this->propuestaSimple('candidato_etapa', [
                'id_candidato' => (int) $m[2],
                'etapa' => trim($m[3]),
            ], 'cambiar la etapa del candidato ' . (int) $m[2] . ' a ' . trim($m[3]), $contexto, 'candidatos');
        }
        if (preg_match('/\b(consulta|muestra|ver)\b.*\bsalario\b.*\b(?:persona|usuario|colaborador)?\s*(\d+)/u', $normalizado, $m)) {
            return $this->consultarSalario((int) $m[2], $contexto);
        }
        if (preg_match('/\b(cambia|actualiza|guarda)\b.*\bsalario\b.*\b(?:persona|usuario|colaborador)?\s*(\d+)\D+(\d[\d,.]*)/u', $normalizado, $m)) {
            return $this->propuestaSimple('salario_actualizar', [
                'id_persona' => (int) $m[2],
                'salario' => str_replace([',', ' '], '', $m[3]),
            ], 'actualizar el salario protegido de la persona ' . (int) $m[2], $contexto, 'salarios');
        }
        if (preg_match('/\b(solicita|pedir|registrar)\b.*\bvacaciones\b/u', $normalizado)) {
            $datos = [];
            if (preg_match_all('/\b(20\d{2}-\d{2}-\d{2})\b/', $mensaje, $fechas) && count($fechas[1]) >= 2) {
                $datos['fecha_inicio'] = $fechas[1][0];
                $datos['fecha_fin'] = $fechas[1][1];
            }
            $datos['id_persona'] = (int) ($contexto['actor_id'] ?? 0);
            return $this->iniciarTarea('vacaciones_solicitar', $datos, $contexto);
        }
        if (preg_match('/\b(aprobar|aprueba|autorizar|autoriza|rechazar|rechaza)\b.*\bvacacion(?:es)?\b[^0-9]*(\d+)/u', $normalizado, $m)) {
            $decision = preg_match('/\b(rechazar|rechaza)\b/u', $normalizado) ? 'rechazar' : 'aprobar';
            return $this->iniciarTarea('vacaciones_resolver', [
                'id_solicitud' => (int) $m[2],
                'decision' => $decision,
            ], $contexto);
        }
        if (preg_match('/\b(da de baja|registrar baja|baja a)\b.*\b(?:persona|usuario|colaborador)?\s*(\d+)/u', $normalizado, $m)) {
            return $this->iniciarTarea('persona_baja', ['id_gestor' => (int) $m[2]], $contexto);
        }
        if (preg_match('/\b(reingresa|registrar reingreso|reactiva)\b.*\b(?:persona|usuario|colaborador)?\s*(\d+)/u', $normalizado, $m)) {
            return $this->iniciarTarea('persona_reingreso', ['id_gestor' => (int) $m[2]], $contexto);
        }
        if (preg_match('/\b(registra|registrar|agrega|alta)\b.*\b(colaborador|persona rrhh|usuario rrhh)\b/u', $normalizado)) {
            return $this->iniciarTarea('rrhh_registrar', [], $contexto);
        }
        if (preg_match('/\b(edita|editar|actualiza|modifica)\b.*\b(colaborador|persona|usuario rrhh)\b[^0-9]*(\d+)/u', $normalizado, $m)) {
            return $this->iniciarTarea('rrhh_actualizar', ['id_persona' => (int) $m[3]], $contexto);
        }
        if (preg_match('/\b(registra|registrar|agrega|alta)\b.*\bcandidat[oa]\b/u', $normalizado)) {
            return $this->iniciarTarea('candidato_registrar', [], $contexto);
        }
        if (preg_match('/\b(edita|editar|actualiza|modifica)\b.*\bcandidat[oa]\b[^0-9]*(\d+)/u', $normalizado, $m)) {
            return $this->iniciarTarea('candidato_actualizar', ['id' => (int) $m[2]], $contexto);
        }
        if (preg_match('/\b(valida|invalidar|invalida|rechaza)\b.*\b(documento)\b[^0-9]*(\d+)/u', $normalizado, $m)) {
            $validado = !preg_match('/\b(invalidar|invalida|rechaza)\b/u', $normalizado);
            return $this->propuestaSimple('documento_validar', [
                'id_documento' => (int) $m[3],
                'validado' => $validado,
            ], ($validado ? 'validar' : 'invalidar') . ' el documento ' . (int) $m[3], $contexto, 'documentos');
        }
        if (preg_match('/\b(carga|importa|actualiza)\b.*\b(estructura masiva|excel de estructura|estructura por excel)\b/u', $normalizado)) {
            return $this->iniciarTarea('estructura_importar', [], $contexto);
        }
        if (preg_match('/\b(cambia|actualiza|mueve)\b.*\b(estructura|puesto|jefe|departamento)\b/u', $normalizado)) {
            return $this->iniciarTarea('estructura_cambiar', [], $contexto);
        }
        if (preg_match('/\b(reevalua|reevaluar|reprocesa)\b.*\b(documentos?|expediente)\b/u', $normalizado)) {
            return $this->iniciarTarea('documento_reevaluar', [], $contexto);
        }
        if (preg_match('/\b(clasifica|reclasifica|corrige)\b.*\bdocumento\b/u', $normalizado)) {
            return $this->iniciarTarea('documento_clasificar', [], $contexto);
        }
        if (preg_match('/\b(cambia|actualiza|mueve)\b.*\betapa\b.*\bcandidat[oa]\b/u', $normalizado)) {
            return $this->iniciarTarea('candidato_etapa', [], $contexto);
        }
        if (preg_match('/\b(otorga|asigna|concede|retira|quita|revoca)\b.*\bpermiso\b/u', $normalizado)) {
            return $this->iniciarTarea('permiso_actualizar', [
                'asignado' => !preg_match('/\b(retira|quita|revoca)\b/u', $normalizado),
            ], $contexto);
        }
        if (preg_match('/\b(cambia|actualiza|guarda)\b.*\bsalario\b/u', $normalizado)) {
            return $this->iniciarTarea('salario_actualizar', [], $contexto);
        }

        return null;
    }

    public function ejecutar(string $accion, array $payload, array $contexto): array
    {
        if (!self::puedeEjecutar($accion)) {
            throw new \RuntimeException('Leonidas no reconoce esta operacion de Capital Humano.');
        }
        $this->autorizar($accion, $contexto);
        $actor = (int) ($contexto['actor_id'] ?? 0);
        $this->validarPayload($accion, $payload);

        switch ($accion) {
            case 'rrhh_registrar':
                $resultado = $this->llamar('rrhh_registrar', $this->normalizarRegistroRrhh($payload), $actor);
                break;
            case 'rrhh_actualizar':
                $resultado = isset($payload['campo'])
                    ? $this->llamar('rrhh_actualizar_campo', $payload, $actor)
                    : $this->llamar('rrhh_actualizar', $payload, $actor);
                break;
            case 'persona_baja':
                $resultado = $this->llamar('persona_baja', $payload, $actor);
                break;
            case 'persona_reingreso':
                $resultado = $this->llamar('persona_reingreso', $payload, $actor);
                break;
            case 'vacaciones_solicitar':
                $resultado = $this->llamar('vacaciones_solicitar', $payload, $actor);
                break;
            case 'vacaciones_resolver':
                $resultado = $this->llamar('vacaciones_resolver', $payload, $actor);
                break;
            case 'estructura_cambiar':
                $vista = $this->llamar('estructura_importar', [$payload['fila']], $actor, false);
                if (!$this->resultadoAplicableEstructura($vista)) {
                    $resultado = $this->resultadoEstructuraNoAplicable($vista);
                    break;
                }
                $resultado = $this->llamar('estructura_importar', [$payload['fila']], $actor, true);
                break;
            case 'estructura_importar':
                $vista = $this->llamar('estructura_importar', $payload['filas'], $actor, false);
                if (!$this->resultadoAplicableEstructura($vista)) {
                    $resultado = $this->resultadoEstructuraNoAplicable($vista);
                    break;
                }
                $resultado = $this->llamar('estructura_importar', $payload['filas'], $actor, true);
                break;
            case 'candidato_registrar':
                $resultado = $this->llamar('candidato_registrar', $payload);
                break;
            case 'candidato_actualizar':
                $resultado = $this->llamar('candidato_actualizar', $payload);
                break;
            case 'candidato_etapa':
                $resultado = $this->llamar('candidato_etapa', (int) $payload['id_candidato'], (string) $payload['etapa']);
                break;
            case 'documento_reevaluar':
                $resultado = $this->llamar('documento_reevaluar', (int) $payload['id_candidato']);
                break;
            case 'documento_validar':
                $resultado = $this->llamar('documento_validar', (int) $payload['id_documento'], (bool) $payload['validado']);
                break;
            case 'documento_clasificar':
                $resultado = $this->llamar('documento_clasificar', (int) $payload['id_documento'], (string) $payload['tipo_documento']);
                break;
            case 'permiso_actualizar':
                $resultado = $this->llamar('permiso_actualizar', (int) $payload['id_persona'], (int) $payload['id_modulo'], (bool) $payload['asignado']);
                break;
            case 'salario_actualizar':
                $this->autorizarSalario($contexto);
                $resultado = $this->llamar('salario_guardar', (int) $payload['id_persona'], $payload['salario'], $actor);
                break;
            default:
                throw new \RuntimeException('Operacion de Capital Humano sin ejecutor.');
        }

        if (!is_array($resultado) || empty($resultado['success'])) {
            $this->auditar($contexto, $accion, 'fallido', $payload, $resultado);
            throw new \RuntimeException($this->mensajeResultado($resultado));
        }
        $this->auditar($contexto, $accion, 'autorizado', $payload, $resultado);
        return [
            'mensaje' => (string) ($resultado['mensaje'] ?? 'Operacion aplicada correctamente.'),
            'tipo' => 'agente_ejecucion_exitosa',
            'ejecucion' => ['accion' => $accion, 'datos' => $resultado['datos'] ?? null],
        ];
    }

    public function limpiarTarea(): void
    {
        unset($_SESSION[self::TASK_KEY]);
    }

    private function ficha360(string $criterio, array $contexto): array
    {
        $this->exigirPermiso($contexto, 'rrhh_lectura');
        $persona = $this->resolverPersonaUnica($criterio);
        if (!$persona['success']) {
            return $persona;
        }
        $id = (int) $persona['persona']['id'];
        $detalle = $this->llamar('persona_detalle', $id);
        $vacaciones = $this->llamar('vacaciones_resumen', $id);
        $documentos = $this->llamar('persona_documentos', $id);
        $datos = is_array($detalle['datos'] ?? null) ? $detalle['datos'] : $persona['persona'];
        $vac = is_array($vacaciones['datos'] ?? null) ? $vacaciones['datos'] : [];
        $docs = is_array($documentos['datos'] ?? null) ? $documentos['datos'] : [];
        $faltantes = [];
        foreach (['curp' => 'CURP', 'rfc' => 'RFC', 'nss' => 'NSS', 'correo' => 'correo', 'numero_empleado' => 'numero de empleado'] as $campo => $etiqueta) {
            if (trim((string) ($datos[$campo] ?? '')) === '') {
                $faltantes[] = $etiqueta;
            }
        }
        $nombre = trim(implode(' ', array_filter([
            $datos['nombres'] ?? '', $datos['segundo_nombre'] ?? '', $datos['apellidop'] ?? '', $datos['apellidom'] ?? '',
        ])));
        $periodo = is_array($vac['periodo'] ?? null) ? $vac['periodo'] : [];
        $mensaje = "Ficha 360 de {$nombre}:"
            . "\nEstatus: " . ($datos['estatus'] ?? 'Sin dato')
            . "\nNumero de empleado: " . ($datos['numero_empleado'] ?? 'Sin dato')
            . "\nCodigo CONTPAC: " . ($datos['codigo_contpac'] ?? 'Sin dato')
            . "\nPuesto: " . ($datos['puesto'] ?? $datos['nombre_puesto'] ?? 'Sin puesto')
            . "\nDepartamento: " . ($datos['departamento'] ?? $datos['nombre_departamento'] ?? 'Sin departamento')
            . "\nJefe: " . ($datos['jefe'] ?? $datos['nombre_jefe'] ?? 'Sin jefe')
            . "\nIngreso: " . ($datos['fecha_ingreso'] ?? 'Sin fecha')
            . "\nVacaciones disponibles: " . ($periodo['dias_disponibles'] ?? 'No calculadas')
            . "\nDatos faltantes: " . ($faltantes ? implode(', ', $faltantes) : 'ninguno de los campos criticos revisados')
            . "\nDocumentos: " . $this->resumenDocumentos($docs);
        return ['mensaje' => $mensaje, 'tipo' => 'rrhh_ficha_360', 'fuente' => 'Capital Humano', 'datos' => ['persona' => $datos, 'vacaciones' => $vac, 'documentos' => $docs, 'faltantes' => $faltantes]];
    }

    private function auditoriaRrhh(array $contexto): array
    {
        $this->exigirPermiso($contexto, 'auditoria_rrhh');
        $resumen = $this->llamar('rrhh_auditoria');
        $mensaje = 'Auditoria RR.HH.:';
        foreach ($resumen as $clave => $valor) {
            $mensaje .= "\n" . ucfirst(str_replace('_', ' ', $clave)) . ': ' . $valor;
        }
        return ['mensaje' => $mensaje, 'tipo' => 'rrhh_auditoria', 'fuente' => 'Capital Humano', 'datos' => $resumen];
    }

    private function consultarAuditoriaRrhh(): array
    {
        $db = new Database();
        return [
            'activos' => (int) (($db->queryOne("SELECT COUNT(*) total FROM estado_cuenta.persona WHERE estatus = 'Activo'")['total'] ?? 0)),
            'sin_curp' => (int) (($db->queryOne("SELECT COUNT(*) total FROM estado_cuenta.persona WHERE estatus = 'Activo' AND TRIM(COALESCE(curp,'')) = ''")['total'] ?? 0)),
            'sin_rfc' => (int) (($db->queryOne("SELECT COUNT(*) total
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.persona_datos_rrhh r ON r.id_persona = p.id
                WHERE p.estatus = 'Activo'
                  AND TRIM(COALESCE(NULLIF(r.rfc, ''), p.rfc, '')) = ''")['total'] ?? 0)),
            'sin_nss' => (int) (($db->queryOne("SELECT COUNT(*) total
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.persona_datos_rrhh r ON r.id_persona = p.id
                WHERE p.estatus = 'Activo'
                  AND TRIM(COALESCE(r.nss, '')) = ''")['total'] ?? 0)),
            'sin_jefe' => (int) (($db->queryOne("SELECT COUNT(*) total FROM estado_cuenta.persona p WHERE p.estatus='Activo' AND NOT EXISTS (SELECT 1 FROM estado_cuenta.asigna_jefe aj WHERE aj.id_persona=p.id AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE()))")['total'] ?? 0)),
            'numero_empleado_duplicado' => (int) (($db->queryOne("SELECT COUNT(*) total FROM (SELECT numero_empleado FROM estado_cuenta.persona WHERE TRIM(COALESCE(numero_empleado,'')) <> '' GROUP BY numero_empleado HAVING COUNT(*) > 1) x")['total'] ?? 0)),
            'curp_duplicada' => $this->contarDuplicados($db, 'curp'),
            'rfc_duplicado' => $this->contarDuplicadosRrhh($db, 'rfc'),
            'nss_duplicado' => $this->contarDuplicadosRrhh($db, 'nss'),
            'nombre_completo_duplicado' => (int) (($db->queryOne("SELECT COUNT(*) total FROM (SELECT UPPER(TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom))) valor FROM estado_cuenta.persona WHERE TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) <> '' GROUP BY valor HAVING COUNT(*) > 1) x")['total'] ?? 0)),
            'sin_puesto_activo' => (int) (($db->queryOne("SELECT COUNT(*) total FROM estado_cuenta.persona p WHERE p.estatus='Activo' AND NOT EXISTS (SELECT 1 FROM estado_cuenta.asigna_puesto ap WHERE ap.id_persona=p.id AND COALESCE(ap.activo,1)=1)")['total'] ?? 0)),
            'activo_con_baja_posterior' => (int) (($db->queryOne("SELECT COUNT(*) total FROM estado_cuenta.persona p WHERE p.estatus='Activo' AND (SELECT MAX(bp.fecha_baja) FROM estado_cuenta.baja_persona bp WHERE bp.id_persona=p.id) > COALESCE((SELECT MAX(r.fecha_reingreso) FROM estado_cuenta.reingresos r WHERE r.id_persona=p.id), '1000-01-01')")['total'] ?? 0)),
            'baja_con_reingreso_posterior' => (int) (($db->queryOne("SELECT COUNT(*) total FROM estado_cuenta.persona p WHERE p.estatus='Baja' AND (SELECT MAX(r.fecha_reingreso) FROM estado_cuenta.reingresos r WHERE r.id_persona=p.id) > COALESCE((SELECT MAX(bp.fecha_baja) FROM estado_cuenta.baja_persona bp WHERE bp.id_persona=p.id), '1000-01-01')")['total'] ?? 0)),
        ];
    }

    private function seguimientoCandidato(int $id, array $contexto): array
    {
        $this->exigirPermiso($contexto, 'candidatos');
        $detalle = $this->llamar('candidato_detalle', $id);
        if (empty($detalle['success']) || !is_array($detalle['datos'] ?? null)) {
            return $this->respuesta('No encontre el candidato ' . $id . '.', 'candidato_no_encontrado');
        }
        $c = $detalle['datos'];
        $docs = $this->llamar('candidato_documentos', $id);
        $d = is_array($docs['datos'] ?? null) ? $docs['datos'] : [];
        $diagnostico = $this->diagnosticarCandidato($c, $d);
        $nombre = trim(implode(' ', array_filter([$c['nombres'] ?? '', $c['segundo_nombre'] ?? '', $c['apellidop'] ?? '', $c['apellidom'] ?? ''])));
        $mensaje = "Seguimiento del candidato {$id} - {$nombre}:"
            . "\nEtapa: " . ($c['estatus'] ?? 'Sin etapa')
            . "\nPuesto: " . ($c['nombre_puesto'] ?? 'Sin puesto')
            . "\nJefe: " . ($c['nombre_jefe'] ?? 'Sin jefe')
            . "\nIngreso programado: " . ($c['fecha_ingreso_programada'] ?? 'Sin fecha')
            . "\nContrato firmado: " . ($c['contrato_firmado_en'] ?? 'No')
            . "\nReingreso: " . (!empty($c['es_reingreso']) ? 'Si' : 'No')
            . "\nTiempo en la etapa: " . $diagnostico['dias_etapa'] . ' dia(s)'
            . "\nExpediente: " . $this->resumenDocumentos($d)
            . "\nDiagnostico: " . implode('; ', $diagnostico['motivos']);
        return ['mensaje' => $mensaje, 'tipo' => 'candidato_seguimiento', 'fuente' => 'Seleccion de Personal', 'datos' => ['candidato' => $c, 'documentos' => $d, 'diagnostico' => $diagnostico]];
    }

    private function auditoriaExpediente(int $id, array $contexto): array
    {
        $this->exigirPermiso($contexto, 'documentos');
        $docs = $this->llamar('candidato_documentos', $id);
        if (empty($docs['success'])) {
            return $this->respuesta($this->mensajeResultado($docs), 'documento_error');
        }
        $datos = is_array($docs['datos'] ?? null) ? $docs['datos'] : [];
        return ['mensaje' => 'Auditoria del expediente del candidato ' . $id . ': ' . $this->resumenDocumentos($datos), 'tipo' => 'documento_auditoria', 'fuente' => 'Expedientes RR.HH.', 'datos' => $datos];
    }

    private function consultarSalario(int $persona, array $contexto): array
    {
        $this->autorizarSalario($contexto);
        $resultado = $this->llamar('salario_consultar', $persona);
        if (empty($resultado['success'])) {
            return $this->respuesta($this->mensajeResultado($resultado), 'salario_error');
        }
        $datos = is_array($resultado['datos'] ?? null) ? $resultado['datos'] : [];
        $this->auditar($contexto, 'salario_consultar', 'autorizado', ['id_persona' => $persona], ['success' => true]);
        return ['mensaje' => !empty($datos['tiene_salario']) ? 'Salario protegido: $' . number_format((float) $datos['salario'], 2) . ' ' . ($datos['moneda'] ?? 'MXN') : 'La persona no tiene un salario protegido registrado.', 'tipo' => 'salario_consulta', 'datos' => $datos];
    }

    private function iniciarTarea(string $accion, array $datos, array $contexto): array
    {
        $this->autorizar($accion, $contexto);
        $faltantes = $this->faltantes($accion, $datos);
        if (!$faltantes) {
            $payload = $this->normalizarPayloadTarea($accion, $datos);
            return $this->propuestaSimple($accion, $payload, $this->resumenAccion($accion, $datos), $contexto, $this->permisoAccion($accion));
        }
        $_SESSION[self::TASK_KEY] = ['actor_id' => (int) $contexto['actor_id'], 'accion' => $accion, 'datos' => $datos, 'expira_en' => time() + self::TASK_TTL];
        return $this->respuesta($this->instruccionFaltantes($accion, $faltantes), 'agente_datos_requeridos');
    }

    private function continuarTarea(string $mensaje, array $tarea, array $contexto): array
    {
        $accion = (string) ($tarea['accion'] ?? '');
        $datos = is_array($tarea['datos'] ?? null) ? $tarea['datos'] : [];
        $capturados = $this->parsearCampos($mensaje);
        if ($accion === 'estructura_importar' && $this->esLista($capturados)) {
            $capturados = ['filas' => $capturados];
        }
        foreach ($capturados as $campo => $valor) {
            $datos[$campo] = $valor;
        }
        $faltantes = $this->faltantes($accion, $datos);
        if ($faltantes) {
            $_SESSION[self::TASK_KEY]['datos'] = $datos;
            $_SESSION[self::TASK_KEY]['expira_en'] = time() + self::TASK_TTL;
            return $this->respuesta($this->instruccionFaltantes($accion, $faltantes, true), 'agente_datos_requeridos');
        }
        $this->limpiarTarea();
        $payload = $this->normalizarPayloadTarea($accion, $datos);
        return $this->propuestaSimple($accion, $payload, $this->resumenAccion($accion, $datos), $contexto, $this->permisoAccion($accion));
    }

    private function propuestaSimple(string $accion, array $payload, string $resumen, array $contexto, string $permiso): array
    {
        $this->exigirPermiso($contexto, $permiso);
        $this->validarPayload($accion, $payload);
        return [
            'mensaje' => "Vista previa:\n" . ucfirst($resumen) . ".\nConfirma para ejecutar. Antes de aplicar volvere a validar permisos y estado actual.",
            'tipo' => 'agente_propuesta',
            'propuesta_especificacion' => ['accion' => $accion, 'resumen' => $resumen, 'payload' => $payload],
        ];
    }

    private function validarPayload(string $accion, array $p): void
    {
        $faltantes = $this->faltantes($accion, $p);
        if ($faltantes) {
            throw new \InvalidArgumentException('Faltan datos requeridos: ' . implode(', ', $faltantes) . '.');
        }
        if (isset($p['id_persona']) && (int) $p['id_persona'] <= 0) {
            throw new \InvalidArgumentException('La persona no es valida.');
        }
        if (isset($p['id_candidato']) && (int) $p['id_candidato'] <= 0) {
            throw new \InvalidArgumentException('El candidato no es valido.');
        }
        if (isset($p['id_documento']) && (int) $p['id_documento'] <= 0) {
            throw new \InvalidArgumentException('El documento no es valido.');
        }
        if (isset($p['id_gestor']) && (int) $p['id_gestor'] <= 0) {
            throw new \InvalidArgumentException('El colaborador no es valido.');
        }
        if ($accion === 'vacaciones_solicitar') {
            $inicio = strtotime((string) $p['fecha_inicio']);
            $fin = strtotime((string) $p['fecha_fin']);
            if ($inicio === false || $fin === false) {
                throw new \InvalidArgumentException('Las fechas de vacaciones no tienen un formato valido.');
            }
            if ($fin < $inicio) {
                throw new \InvalidArgumentException('La fecha final no puede ser anterior a la inicial.');
            }
        }
        if ($accion === 'vacaciones_resolver') {
            if ((int) $p['id_solicitud'] <= 0) {
                throw new \InvalidArgumentException('La solicitud de vacaciones no es valida.');
            }
            if (!in_array(mb_strtolower(trim((string) $p['decision'])), ['aprobar', 'rechazar'], true)) {
                throw new \InvalidArgumentException('La decision debe ser aprobar o rechazar.');
            }
        }
        if ($accion === 'permiso_actualizar' && (int) $p['id_modulo'] <= 0) {
            throw new \InvalidArgumentException('El permiso no es valido.');
        }
        if ($accion === 'documento_clasificar') {
            $tipo = trim((string) $p['tipo_documento']);
            if ($tipo === '' || mb_strlen($tipo) > 120 || preg_match('/[^\p{L}\p{N}\s().,\/\-]/u', $tipo)) {
                throw new \InvalidArgumentException('El tipo de documento no es valido.');
            }
        }
        if ($accion === 'salario_actualizar') {
            $salario = filter_var(str_replace(',', '', (string) $p['salario']), FILTER_VALIDATE_FLOAT);
            if ($salario === false || $salario < 0 || $salario > 100000000) {
                throw new \InvalidArgumentException('El salario no es un importe valido.');
            }
        }
        if (in_array($accion, ['estructura_cambiar', 'estructura_importar'], true)) {
            $filas = $accion === 'estructura_cambiar' ? [$p['fila']] : $p['filas'];
            if (!is_array($filas) || !$filas || count($filas) > 5000) {
                throw new \InvalidArgumentException('La estructura debe contener entre 1 y 5,000 filas.');
            }
            foreach ($filas as $indice => $fila) {
                if (!is_array($fila) || trim((string) ($fila['external_id'] ?? '')) === '') {
                    throw new \InvalidArgumentException('La fila ' . ($indice + 1) . ' no contiene external_id.');
                }
            }
        }
    }

    private function faltantes(string $accion, array $p): array
    {
        if ($accion === 'rrhh_registrar') {
            return isset($p['persona'], $p['rrhh'])
                ? $this->camposFaltantes($p, ['persona', 'rrhh'])
                : $this->camposFaltantes($p, ['nombres', 'apellidop', 'id_pais', 'area_id', 'departamento_id', 'puesto_id']);
        }
        if ($accion === 'rrhh_actualizar') {
            return isset($p['persona'], $p['rrhh'])
                ? $this->camposFaltantes($p, ['id_persona', 'persona', 'rrhh'])
                : $this->camposFaltantes($p, ['id_persona', 'campo', 'valor']);
        }
        if ($accion === 'candidato_actualizar') {
            return isset($p['campo'])
                ? $this->camposFaltantes($p, ['id', 'campo', 'valor'])
                : $this->camposFaltantes($p, ['id', 'nombres', 'apellidop']);
        }
        $requeridos = [
            'persona_baja' => ['id_gestor', 'motivo', 'descripcion', 'fecha_baja'],
            'persona_reingreso' => ['id_gestor', 'motivo_reingreso', 'descripcion_reingreso', 'fecha_reingreso'],
            'vacaciones_solicitar' => ['id_persona', 'fecha_inicio', 'fecha_fin', 'firma_colaborador'],
            'vacaciones_resolver' => ['id_solicitud', 'etapa', 'decision', 'firma_responsable'],
            'estructura_cambiar' => isset($p['fila'])
                ? ['fila']
                : ['external_id', 'puesto_legacy', 'departamento'],
            'estructura_importar' => ['filas'],
            'candidato_registrar' => ['nombres', 'apellidop'],
            'candidato_etapa' => ['id_candidato', 'etapa'],
            'documento_reevaluar' => ['id_candidato'],
            'documento_validar' => ['id_documento', 'validado'],
            'documento_clasificar' => ['id_documento', 'tipo_documento'],
            'permiso_actualizar' => ['id_persona', 'id_modulo', 'asignado'],
            'salario_actualizar' => ['id_persona', 'salario'],
        ];
        return $this->camposFaltantes($p, $requeridos[$accion] ?? []);
    }

    private function autorizar(string $accion, array $contexto): void
    {
        $this->exigirPermiso($contexto, $this->permisoAccion($accion));
        if ((int) ($contexto['actor_id'] ?? 0) <= 0) {
            throw new \RuntimeException('La sesion no es valida.');
        }
    }

    private function autorizarSalario(array $contexto): void
    {
        $this->exigirPermiso($contexto, 'salarios');
        if (empty($contexto['salario_totp_vigente'])) {
            throw new \RuntimeException('El salario esta protegido. Desbloquea salarios con Google Authenticator y vuelve a solicitar la operacion.');
        }
    }

    private function exigirPermiso(array $contexto, string $permiso): void
    {
        if (empty($contexto['permisos_agente'][$permiso])) {
            throw new \RuntimeException('Tu usuario no tiene el permiso requerido para esta operacion de Capital Humano.');
        }
    }

    private function permisoAccion(string $accion): string
    {
        return match ($accion) {
            'rrhh_registrar' => 'rrhh_registrar',
            'rrhh_actualizar' => 'rrhh_editar',
            'persona_baja' => 'bajas',
            'persona_reingreso' => 'reingresos',
            'vacaciones_solicitar' => 'vacaciones',
            'vacaciones_resolver' => 'vacaciones_admin',
            'estructura_cambiar', 'estructura_importar' => 'estructura',
            'candidato_registrar', 'candidato_actualizar', 'candidato_etapa' => 'candidatos',
            'documento_reevaluar', 'documento_validar', 'documento_clasificar' => 'documentos',
            'permiso_actualizar' => 'permisos',
            'salario_actualizar' => 'salarios',
            default => 'rrhh_lectura',
        };
    }

    private function tareaActual(int $actor): ?array
    {
        $tarea = is_array($_SESSION[self::TASK_KEY] ?? null) ? $_SESSION[self::TASK_KEY] : null;
        if (!$tarea || (int) ($tarea['actor_id'] ?? 0) !== $actor || (int) ($tarea['expira_en'] ?? 0) < time()) {
            $this->limpiarTarea();
            return null;
        }
        return $tarea;
    }

    private function resolverPersonaUnica(string $criterio): array
    {
        $resultado = $this->llamar('persona_buscar', $criterio);
        $personas = is_array($resultado['datos'] ?? null) ? $resultado['datos'] : [];
        if (count($personas) === 0) {
            return $this->respuesta('No encontre una persona con ese criterio.', 'persona_no_encontrada') + ['success' => false];
        }
        if (count($personas) > 1) {
            $lineas = ['Encontre varias personas. Indica el ID exacto:'];
            foreach (array_slice($personas, 0, 8) as $p) {
                $lineas[] = (int) $p['id'] . ' - ' . ($p['nombre_completo'] ?? 'Sin nombre') . ' (' . ($p['estatus'] ?? 'Sin estatus') . ')';
            }
            return $this->respuesta(implode("\n", $lineas), 'persona_ambigua') + ['success' => false];
        }
        return ['success' => true, 'persona' => $personas[0]];
    }

    private function buscarPersonas(string $criterio): array
    {
        $db = new Database();
        $criterio = trim($criterio);
        if ($criterio === '') {
            return ['success' => true, 'datos' => []];
        }
        $id = ctype_digit($criterio) ? (int) $criterio : 0;
        $rows = $db->queryAll("SELECT p.id, TRIM(CONCAT_WS(' ',p.nombres,p.segundo_nombre,p.apellidop,p.apellidom)) nombre_completo, p.numero_empleado, p.codigo_contpac, p.estatus FROM estado_cuenta.persona p WHERE (:id > 0 AND p.id=:id) OR (:id = 0 AND (TRIM(CONCAT_WS(' ',p.nombres,p.segundo_nombre,p.apellidop,p.apellidom)) LIKE :q OR p.numero_empleado=:exacto)) ORDER BY p.estatus='Activo' DESC, p.id DESC LIMIT 12", ['id' => $id, 'q' => '%' . $criterio . '%', 'exacto' => $criterio]);
        return ['success' => true, 'datos' => $rows];
    }

    private function actualizarEtapaCandidato(int $id, string $etapa): array
    {
        $antes = $this->llamar('candidato_detalle', $id);
        if (empty($antes['success'])) {
            return ['success' => false, 'mensaje' => 'Candidato no encontrado.'];
        }
        \Models\Candidatos::updateEstatus($id, $etapa);
        $despues = \Models\Candidatos::getById($id);
        $actual = is_array($despues['datos'] ?? null) ? trim((string) ($despues['datos']['estatus'] ?? '')) : '';
        return $actual === trim($etapa)
            ? ['success' => true, 'mensaje' => 'Etapa del candidato actualizada.', 'datos' => ['id' => $id, 'etapa' => $actual]]
            : ['success' => false, 'mensaje' => 'La etapa no pudo verificarse despues de actualizar.'];
    }

    private function resultadoAplicableEstructura(array $resultado): bool
    {
        if (empty($resultado['success'])) {
            return false;
        }
        $datos = is_array($resultado['datos'] ?? null) ? $resultado['datos'] : [];
        $resumen = is_array($datos['resumen'] ?? null) ? $datos['resumen'] : [];
        return (int) ($resumen['errores'] ?? 0) === 0 && (int) ($resumen['con_cambios'] ?? 0) > 0;
    }

    private function resultadoEstructuraNoAplicable(array $resultado): array
    {
        $datos = is_array($resultado['datos'] ?? null) ? $resultado['datos'] : [];
        $resumen = is_array($datos['resumen'] ?? null) ? $datos['resumen'] : [];
        $errores = (int) ($resumen['errores'] ?? 0);
        $cambios = (int) ($resumen['con_cambios'] ?? 0);
        $motivos = [];
        foreach ((array) ($datos['detalles'] ?? []) as $detalle) {
            if (($detalle['estado'] ?? '') !== 'error') {
                continue;
            }
            $motivo = trim((string) ($detalle['motivo'] ?? $detalle['mensaje'] ?? ''));
            if ($motivo !== '') {
                $motivos[] = $motivo;
            }
            if (count($motivos) >= 5) {
                break;
            }
        }

        $mensaje = $errores > 0
            ? 'La estructura no se modifico porque la prevalidacion encontro ' . $errores . ' error(es).'
            : ($cambios <= 0
                ? 'La estructura no se modifico porque no se detectaron cambios pendientes.'
                : $this->mensajeResultado($resultado));
        if ($motivos) {
            $mensaje .= ' Motivos: ' . implode(' | ', array_values(array_unique($motivos)));
        }

        return [
            'success' => false,
            'mensaje' => $mensaje,
            'datos' => $datos,
        ];
    }

    private function camposFaltantes(array $datos, array $requeridos): array
    {
        $faltantes = [];
        foreach ($requeridos as $campo) {
            if (!array_key_exists($campo, $datos) || $datos[$campo] === '' || $datos[$campo] === null || (is_array($datos[$campo]) && !$datos[$campo])) {
                $faltantes[] = $campo;
            }
        }
        return $faltantes;
    }

    private function normalizarPayloadTarea(string $accion, array $datos): array
    {
        if ($accion === 'estructura_cambiar' && !isset($datos['fila'])) {
            $fila = [];
            foreach (['external_id', 'nombre_completo', 'puesto_legacy', 'departamento', 'supervisor', 'subgerente', 'gerente', 'subdirector'] as $campo) {
                if (array_key_exists($campo, $datos) && $datos[$campo] !== '') {
                    $fila[$campo] = $datos[$campo];
                }
            }
            return ['fila' => $fila];
        }
        if ($accion === 'estructura_importar' && is_string($datos['filas'] ?? null)) {
            $filas = json_decode((string) $datos['filas'], true);
            if (!is_array($filas)) {
                throw new \InvalidArgumentException('El contenido de filas no es un arreglo JSON valido.');
            }
            return ['filas' => $filas];
        }
        return $datos;
    }

    private function normalizarRegistroRrhh(array $datos): array
    {
        if (isset($datos['persona'], $datos['rrhh'])) {
            return $datos;
        }
        $persona = [];
        foreach ([
            'nombres', 'segundo_nombre', 'apellidop', 'apellidom', 'numero_empleado',
            'codigo_contpac', 'correo', 'telefono_uno', 'telefono_dos', 'usuario',
            'contrasena', 'fecha_ingreso', 'id_pais', 'domicilio', 'codigo_postal',
            'curp', 'rfc', 'nss', 'sexo', 'estado_civil', 'fecha_nacimiento',
        ] as $campo) {
            if (array_key_exists($campo, $datos)) {
                $persona[$campo] = $datos[$campo];
            }
        }
        $rrhh = [];
        foreach ([
            'empresa_id', 'direccion_id', 'area_id', 'departamento_id', 'puesto_id',
            'jefe_id', 'registro_patronal', 'codigo_contpaq', 'fecha_contpaq',
            'fecha_imss_alta', 'ubicacion_laboral', 'municipio_laboral',
        ] as $campo) {
            if (array_key_exists($campo, $datos)) {
                $rrhh[$campo] = $datos[$campo];
            }
        }
        return ['persona' => $persona, 'rrhh' => $rrhh];
    }

    private function actualizarCampoRrhh(array $payload, int $actor): array
    {
        $id = (int) ($payload['id_persona'] ?? 0);
        $campoSolicitado = strtolower(trim((string) ($payload['campo'] ?? '')));
        $valor = $payload['valor'] ?? null;
        $mapa = [
            'nombre' => ['persona', 'nombres'], 'nombres' => ['persona', 'nombres'],
            'segundo_nombre' => ['persona', 'segundo_nombre'],
            'apellido_paterno' => ['persona', 'apellidop'], 'apellidop' => ['persona', 'apellidop'],
            'apellido_materno' => ['persona', 'apellidom'], 'apellidom' => ['persona', 'apellidom'],
            'numero_empleado' => ['persona', 'numero_empleado'],
            'codigo_contpac' => ['persona', 'codigo_contpac'],
            'curp' => ['persona', 'curp'], 'rfc' => ['persona', 'rfc'], 'nss' => ['persona', 'nss'],
            'correo' => ['persona', 'correo'], 'telefono' => ['persona', 'telefono_uno'],
            'fecha_ingreso' => ['persona', 'fecha_ingreso'], 'id_pais' => ['persona', 'id_pais'],
            'area' => ['rrhh', 'area_id'], 'area_id' => ['rrhh', 'area_id'],
            'departamento' => ['rrhh', 'departamento_id'], 'departamento_id' => ['rrhh', 'departamento_id'],
            'puesto' => ['rrhh', 'puesto_id'], 'puesto_id' => ['rrhh', 'puesto_id'],
            'jefe' => ['rrhh', 'jefe_id'], 'jefe_id' => ['rrhh', 'jefe_id'],
            'empresa' => ['rrhh', 'empresa_id'], 'empresa_id' => ['rrhh', 'empresa_id'],
            'direccion' => ['rrhh', 'direccion_id'], 'direccion_id' => ['rrhh', 'direccion_id'],
        ];
        if (!isset($mapa[$campoSolicitado])) {
            return ['success' => false, 'mensaje' => 'El campo solicitado no esta habilitado para edicion guiada.'];
        }
        $actual = \Models\CapHumRrhh::obtenerUsuario($id, $actor);
        if (empty($actual['success']) || !is_array($actual['datos'] ?? null)) {
            return ['success' => false, 'mensaje' => $this->mensajeResultado($actual)];
        }
        $datos = $actual['datos'];
        [$seccion, $campo] = $mapa[$campoSolicitado];
        $datos[$seccion] = is_array($datos[$seccion] ?? null) ? $datos[$seccion] : [];
        $datos[$seccion][$campo] = $valor;
        $datos['id_persona'] = $id;

        // Estos identificadores pertenecen a sistemas distintos y nunca se copian entre si.
        if ($campo === 'numero_empleado') {
            $datos['persona']['codigo_contpac'] = $actual['datos']['persona']['codigo_contpac'] ?? '';
        } elseif ($campo === 'codigo_contpac') {
            $datos['persona']['numero_empleado'] = $actual['datos']['persona']['numero_empleado'] ?? '';
        }
        return \Models\CapHumRrhh::actualizarUsuario($datos, $actor);
    }

    private function actualizarCandidato(array $payload): array
    {
        $id = (int) ($payload['id'] ?? 0);
        if (!isset($payload['campo'])) {
            $datos = $payload;
            unset($datos['id']);
            return \Models\Candidatos::update($id, $datos);
        }
        $permitidos = [
            'nombres', 'segundo_nombre', 'apellidop', 'apellidom', 'email', 'telefono',
            'id_pais', 'id_div_nivel1', 'id_div_nivel2', 'id_div_nivel3',
            'domicilio_calle_texto', 'domicilio_num_exterior', 'domicilio_num_interior',
            'codigo_postal', 'id_puesto', 'id_departamento', 'id_posible_jefe',
            'id_jefe_divisional', 'fecha_postulacion', 'id_legion', 'usuario',
            'contrasena', 'notas', 'es_reingreso', 'id_persona_reingreso',
        ];
        $campo = strtolower(trim((string) $payload['campo']));
        if (!in_array($campo, $permitidos, true)) {
            return ['success' => false, 'mensaje' => 'Ese campo del candidato no esta habilitado para edicion guiada.'];
        }
        $actual = \Models\Candidatos::getById($id);
        if (empty($actual['success']) || !is_array($actual['datos'] ?? null)) {
            return ['success' => false, 'mensaje' => $this->mensajeResultado($actual)];
        }
        $datos = $actual['datos'];
        $datos[$campo] = $payload['valor'];
        return \Models\Candidatos::update($id, $datos);
    }

    private function clasificarDocumento(int $id, string $tipo): array
    {
        $tipo = trim((string) preg_replace('/\s+/u', ' ', $tipo));
        if ($id <= 0) {
            return ['success' => false, 'mensaje' => 'El ID del documento no es valido.'];
        }
        if ($tipo === '' || mb_strlen($tipo) > 120 || preg_match('/[^\p{L}\p{N}\s().,\/\-]/u', $tipo)) {
            return ['success' => false, 'mensaje' => 'El tipo de documento esta vacio o contiene caracteres no permitidos.'];
        }

        $db = new Database();
        $actual = $db->queryOne(
            'SELECT id, id_candidato, tipo_documento, nombre_archivo FROM estado_cuenta.candidato_documento WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        if (!$actual) {
            return ['success' => false, 'mensaje' => 'No se encontro el documento ' . $id . '.'];
        }

        $tipoAnterior = trim((string) ($actual['tipo_documento'] ?? ''));
        if (mb_strtoupper($tipoAnterior) === mb_strtoupper($tipo)) {
            return [
                'success' => true,
                'mensaje' => 'El documento ya estaba clasificado como ' . $tipo . '. No se realizaron cambios.',
                'datos' => ['id' => $id, 'id_candidato' => (int) $actual['id_candidato'], 'tipo_anterior' => $tipoAnterior, 'tipo_nuevo' => $tipo, 'sin_cambios' => true],
            ];
        }

        $db->CRUD(
            'UPDATE estado_cuenta.candidato_documento SET tipo_documento = :tipo WHERE id = :id',
            ['tipo' => $tipo, 'id' => $id]
        );
        $verificado = $db->queryOne(
            'SELECT id, id_candidato, tipo_documento, nombre_archivo FROM estado_cuenta.candidato_documento WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        if (!$verificado || mb_strtoupper(trim((string) ($verificado['tipo_documento'] ?? ''))) !== mb_strtoupper($tipo)) {
            return ['success' => false, 'mensaje' => 'La base de datos no confirmo la nueva clasificacion del documento.'];
        }

        return [
            'success' => true,
            'mensaje' => 'Documento clasificado correctamente como ' . $tipo . '.',
            'datos' => [
                'id' => $id,
                'id_candidato' => (int) $verificado['id_candidato'],
                'nombre_archivo' => (string) ($verificado['nombre_archivo'] ?? ''),
                'tipo_anterior' => $tipoAnterior,
                'tipo_nuevo' => (string) $verificado['tipo_documento'],
            ],
        ];
    }

    private function contarDuplicados(Database $db, string $campo): int
    {
        if ($campo !== 'curp') {
            throw new \InvalidArgumentException('Campo no permitido para auditoria de duplicados.');
        }
        $sql = "SELECT COUNT(*) total FROM (
                    SELECT UPPER(TRIM({$campo})) valor
                    FROM estado_cuenta.persona
                    WHERE TRIM(COALESCE({$campo},'')) <> ''
                      AND UPPER(TRIM({$campo})) NOT IN ('N/A','NA','NO APLICA','PENDIENTE','SIN DATO','0')
                    GROUP BY UPPER(TRIM({$campo}))
                    HAVING COUNT(*) > 1
                ) x";
        return (int) (($db->queryOne($sql)['total'] ?? 0));
    }

    private function contarDuplicadosRrhh(Database $db, string $campo): int
    {
        if (!in_array($campo, ['rfc', 'nss'], true)) {
            throw new \InvalidArgumentException('Campo RR.HH. no permitido para auditoria de duplicados.');
        }
        $expresion = $campo === 'rfc'
            ? "COALESCE(NULLIF(r.rfc, ''), p.rfc, '')"
            : "COALESCE(r.nss, '')";
        $sql = "SELECT COUNT(*) total FROM (
                    SELECT UPPER(TRIM({$expresion})) valor
                    FROM estado_cuenta.persona p
                    LEFT JOIN estado_cuenta.persona_datos_rrhh r ON r.id_persona = p.id
                    WHERE TRIM({$expresion}) <> ''
                      AND UPPER(TRIM({$expresion})) NOT IN ('N/A','NA','NO APLICA','PENDIENTE','SIN DATO','0')
                    GROUP BY UPPER(TRIM({$expresion}))
                    HAVING COUNT(*) > 1
                ) x";
        return (int) (($db->queryOne($sql)['total'] ?? 0));
    }

    private function diagnosticarCandidato(array $candidato, array $expediente): array
    {
        $documentos = is_array($expediente['documentos'] ?? null)
            ? $expediente['documentos']
            : ($this->esLista($expediente) ? $expediente : []);
        $pendientes = 0;
        $invalidos = 0;
        $sinArchivo = 0;
        foreach ($documentos as $documento) {
            if (!is_array($documento)) {
                continue;
            }
            if (!array_key_exists('validado', $documento) || $documento['validado'] === null || $documento['validado'] === '') {
                $pendientes++;
            } elseif ((int) $documento['validado'] === 0) {
                $invalidos++;
            }
            if ((isset($documento['archivo_disponible']) && (int) $documento['archivo_disponible'] === 0)
                || (isset($documento['tiene_contenido']) && (int) $documento['tiene_contenido'] === 0 && trim((string) ($documento['ruta_archivo'] ?? '')) === '')) {
                $sinArchivo++;
            }
        }

        $verificacion = is_array($expediente['verificacion'] ?? null) ? $expediente['verificacion'] : [];
        $estadoVerificacion = mb_strtolower(trim((string) ($verificacion['estado'] ?? $verificacion['status'] ?? $verificacion['resultado'] ?? '')));
        $motivos = [];
        if (!$documentos) {
            $motivos[] = 'no tiene documentos cargados';
        }
        if ($pendientes > 0) {
            $motivos[] = $pendientes . ' documento(s) siguen pendientes de validacion';
        }
        if ($invalidos > 0) {
            $motivos[] = $invalidos . ' documento(s) fueron marcados como no validos';
        }
        if ($sinArchivo > 0) {
            $motivos[] = $sinArchivo . ' registro(s) documental(es) no tienen un archivo disponible';
        }
        if ($estadoVerificacion !== '' && preg_match('/pendiente|error|fall|rechaz|no coincide|incomplet/u', $estadoVerificacion)) {
            $motivos[] = 'la verificacion documental esta en estado ' . $estadoVerificacion;
        }
        if ((int) ($candidato['id_puesto'] ?? 0) <= 0 && trim((string) ($candidato['nombre_puesto'] ?? '')) === '') {
            $motivos[] = 'no tiene puesto asignado';
        }
        if ((int) ($candidato['id_posible_jefe'] ?? $candidato['id_jefe'] ?? 0) <= 0 && trim((string) ($candidato['nombre_jefe'] ?? '')) === '') {
            $motivos[] = 'no tiene jefe asignado';
        }
        $etapa = mb_strtolower(trim((string) ($candidato['estatus'] ?? $candidato['etapa'] ?? '')));
        if (preg_match('/validacion final|contrato/u', $etapa) && empty($candidato['contrato_firmado_en'])) {
            $motivos[] = 'el contrato aun no aparece firmado';
        }

        $fechaEtapa = trim((string) ($candidato['fecha_actualizacion'] ?? $candidato['fecha_modificacion'] ?? $candidato['fecha_registro'] ?? $candidato['created_at'] ?? ''));
        $diasEtapa = 0;
        if ($fechaEtapa !== '') {
            try {
                $inicio = new \DateTimeImmutable($fechaEtapa);
                $hoy = new \DateTimeImmutable('today');
                if ($inicio <= $hoy) {
                    $diasEtapa = (int) $inicio->diff($hoy)->days;
                }
            } catch (\Throwable $e) {
                $diasEtapa = 0;
            }
        }
        if (!$motivos) {
            $motivos[] = 'no se detecto un bloqueo obvio; conviene revisar la bitacora de la etapa y la ultima respuesta del servicio documental';
        }

        return [
            'dias_etapa' => $diasEtapa,
            'documentos_total' => count($documentos),
            'documentos_pendientes' => $pendientes,
            'documentos_invalidos' => $invalidos,
            'documentos_sin_archivo' => $sinArchivo,
            'estado_verificacion' => $estadoVerificacion !== '' ? $estadoVerificacion : 'sin lectura final',
            'motivos' => $motivos,
        ];
    }

    private function instruccionFaltantes(string $accion, array $faltantes, bool $continuacion = false): string
    {
        $etiquetas = [
            'nombres' => 'nombre(s)', 'apellidop' => 'apellido paterno', 'id_pais' => 'ID del pais',
            'area_id' => 'ID del area', 'departamento_id' => 'ID del departamento', 'puesto_id' => 'ID del puesto',
            'campo' => 'campo que deseas cambiar', 'valor' => 'nuevo valor',
            'motivo' => 'motivo', 'descripcion' => 'descripcion', 'fecha_baja' => 'fecha de baja (AAAA-MM-DD)',
            'motivo_reingreso' => 'motivo del reingreso', 'descripcion_reingreso' => 'descripcion del reingreso',
            'fecha_reingreso' => 'fecha de reingreso (AAAA-MM-DD)', 'firma_colaborador' => 'firma del colaborador',
            'firma_responsable' => 'firma del responsable', 'etapa' => 'etapa de aprobacion',
            'external_id' => 'numero de empleado/external_id', 'puesto_legacy' => 'puesto Legacy',
            'departamento' => 'departamento', 'filas' => 'arreglo JSON de filas',
            'id_documento' => 'ID del documento', 'tipo_documento' => 'nombre correcto del tipo de documento',
        ];
        $nombres = array_map(static fn(string $campo): string => $etiquetas[$campo] ?? $campo, $faltantes);
        $inicio = $continuacion ? 'Aun necesito' : 'Para preparar la operacion necesito';
        return $inicio . ': ' . implode(', ', $nombres)
            . '. Responde con campo=valor; puedes enviar varios campos separados por coma. Escribe cancelar para salir sin cambios.';
    }

    private function parsearCampos(string $mensaje): array
    {
        $json = json_decode(trim($mensaje), true);
        if (is_array($json)) {
            return $json;
        }
        $datos = [];
        if (preg_match_all('/([a-zA-Z_]+)\s*=\s*("[^"]*"|[^,;\n]+)/', $mensaje, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $campo = strtolower(trim($item[1]));
                $valor = trim($item[2], " \t\n\r\0\x0B\"");
                if (in_array(strtolower($valor), ['si', 'true'], true)) {
                    $valor = true;
                } elseif (in_array(strtolower($valor), ['no', 'false'], true)) {
                    $valor = false;
                }
                $datos[$campo] = $valor;
            }
        }
        return $datos;
    }

    private function resumenAccion(string $accion, array $datos): string
    {
        return match ($accion) {
            'persona_baja' => 'registrar la baja de la persona ' . (int) ($datos['id_gestor'] ?? 0),
            'persona_reingreso' => 'registrar el reingreso de la persona ' . (int) ($datos['id_gestor'] ?? 0),
            'vacaciones_solicitar' => 'solicitar vacaciones del ' . ($datos['fecha_inicio'] ?? '') . ' al ' . ($datos['fecha_fin'] ?? ''),
            'vacaciones_resolver' => ($datos['decision'] ?? 'resolver') . ' la solicitud de vacaciones ' . (int) ($datos['id_solicitud'] ?? 0),
            'rrhh_registrar' => 'registrar al colaborador ' . trim(($datos['nombres'] ?? '') . ' ' . ($datos['apellidop'] ?? '')),
            'rrhh_actualizar' => 'cambiar ' . ($datos['campo'] ?? 'los datos') . ' de la persona ' . (int) ($datos['id_persona'] ?? 0) . ' a ' . ($datos['valor'] ?? ''),
            'candidato_registrar' => 'registrar al candidato ' . trim(($datos['nombres'] ?? '') . ' ' . ($datos['apellidop'] ?? '')),
            'candidato_actualizar' => 'cambiar ' . ($datos['campo'] ?? 'los datos') . ' del candidato ' . (int) ($datos['id'] ?? 0),
            'estructura_cambiar' => 'actualizar la estructura del numero de empleado ' . ($datos['external_id'] ?? ''),
            'estructura_importar' => 'prevalidar y aplicar la carga masiva de estructura',
            'documento_clasificar' => 'clasificar el documento ' . (int) ($datos['id_documento'] ?? 0) . ' como ' . ($datos['tipo_documento'] ?? ''),
            default => str_replace('_', ' ', $accion),
        };
    }

    private function resumenDocumentos(array $datos): string
    {
        $documentos = $datos['documentos'] ?? $datos;
        if (!is_array($documentos)) {
            return 'sin informacion';
        }
        $total = $this->esLista($documentos) ? count($documentos) : 0;
        if (isset($datos['total'])) {
            $total = (int) $datos['total'];
        }
        $pendientes = 0;
        $invalidos = 0;
        $sinArchivo = 0;
        if ($this->esLista($documentos)) {
            foreach ($documentos as $documento) {
                if (!is_array($documento)) {
                    continue;
                }
                if (!array_key_exists('validado', $documento) || $documento['validado'] === null || $documento['validado'] === '') {
                    $pendientes++;
                } elseif ((int) $documento['validado'] === 0) {
                    $invalidos++;
                }
                if ((isset($documento['archivo_disponible']) && (int) $documento['archivo_disponible'] === 0)
                    || (isset($documento['tiene_contenido']) && (int) $documento['tiene_contenido'] === 0 && trim((string) ($documento['ruta_archivo'] ?? '')) === '')) {
                    $sinArchivo++;
                }
            }
        }
        $verificacion = is_array($datos['verificacion'] ?? null) ? $datos['verificacion'] : [];
        $partes = [$total . ' documento(s)'];
        if ($pendientes > 0) {
            $partes[] = $pendientes . ' pendiente(s)';
        }
        if ($invalidos > 0) {
            $partes[] = $invalidos . ' no valido(s)';
        }
        if ($sinArchivo > 0) {
            $partes[] = $sinArchivo . ' sin archivo disponible';
        }
        if (!empty($verificacion)) {
            $estado = trim((string) ($verificacion['estado'] ?? $verificacion['status'] ?? ''));
            $partes[] = $estado !== '' ? 'verificacion ' . $estado : 'con verificacion disponible';
        }
        return implode(', ', $partes);
    }

    private function auditar(array $contexto, string $accion, string $resultado, array $payload, $detalle): void
    {
        $seguro = $payload;
        foreach (['salario', 'firma_colaborador', 'firma_responsable', 'password', 'contrasena'] as $sensible) {
            if (array_key_exists($sensible, $seguro)) {
                $seguro[$sensible] = '[PROTEGIDO]';
            }
        }
        try {
            $this->llamar('auditar', [
                'id_actor' => (int) ($contexto['actor_id'] ?? 0),
                'modulo' => 'leonidas_capital_humano',
                'entidad_tipo' => 'accion_agente',
                'entidad_id' => (int) ($payload['id_persona'] ?? $payload['id_gestor'] ?? $payload['id_candidato'] ?? 0),
                'accion' => $accion,
                'resumen' => $resultado,
                'cambios' => $seguro,
                'detalle' => is_array($detalle) ? $detalle : ['detalle' => (string) $detalle],
            ]);
        } catch (\Throwable $e) {
            error_log('Leonidas audit error: ' . $e->getMessage());
        }
    }

    private function llamar(string $adapter, ...$args)
    {
        if (!isset($this->adapters[$adapter])) {
            throw new \RuntimeException('Adaptador no disponible: ' . $adapter);
        }
        return ($this->adapters[$adapter])(...$args);
    }

    private function mensajeResultado($resultado): string
    {
        if (is_array($resultado)) {
            return trim((string) ($resultado['mensaje'] ?? $resultado['error'] ?? 'La operacion no pudo completarse.'));
        }
        return 'La operacion no pudo completarse.';
    }

    private function esLista(array $datos): bool
    {
        $indice = 0;
        foreach ($datos as $clave => $_valor) {
            if ($clave !== $indice++) {
                return false;
            }
        }
        return true;
    }

    private function respuesta(string $mensaje, string $tipo): array
    {
        return ['mensaje' => $mensaje, 'tipo' => $tipo];
    }
}
