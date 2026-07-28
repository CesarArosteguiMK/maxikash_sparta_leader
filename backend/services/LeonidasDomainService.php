<?php

namespace Services;

/**
 * Resuelve preguntas de alcance y orienta consultas incompletas por dominio.
 */
class LeonidasDomainService
{
    private const CRITERIOS = [
        'creditos' => 'el numero de credito, cliente y la fecha de corte que deseas revisar',
        'capital_humano' => 'el nombre o numero de empleado y la consulta concreta del expediente',
        'convenios' => 'el numero de credito o convenio y la regla, pago o periodo que deseas validar',
        'motos_adjudicadas' => 'el ID del credito y la accion o etapa de Motos Adjudicadas que deseas revisar',
        'direcciones' => 'la direccion, ruta, colaborador o periodo que deseas consultar',
        'legacy' => 'el credito, usuario, tarea, campana o rango de fechas que deseas consultar',
        'atlas' => 'la operacion, zona, cartera, usuario o periodo que deseas analizar',
        'tickets' => 'el folio, solicitante, responsable, estatus o periodo del ticket',
        'analitica' => 'el indicador, bucket, semana, pantalla y fecha de corte que deseas comparar',
        'gastos_cobranza' => 'el concepto, responsable, centro de costo y periodo que deseas revisar',
        'organizacion' => 'la empresa, direccion, area, departamento, puesto o colaborador',
        'servicios' => 'el nombre del servicio o agente y si deseas consultar, iniciar, detener o reiniciar',
    ];

    private LeonidasCapabilityRegistry $registro;

    public function __construct(?LeonidasCapabilityRegistry $registro = null)
    {
        $this->registro = $registro ?? new LeonidasCapabilityRegistry();
    }

    public function explicar(string $mensaje): ?array
    {
        $dominio = $this->registro->detectar($mensaje);
        if ($dominio === null || !$this->esPreguntaDeDominio($mensaje)) {
            return null;
        }

        $ejecutables = $dominio['acciones_ejecutables'] ?? [];
        $ejecucion = $ejecutables !== []
            ? "\nAcciones que Leonidas ejecuta actualmente: " . implode(', ', $ejecutables)
                . '. Antes de escribir valida permisos, muestra una vista previa, '
                . 'solicita confirmacion y registra auditoria.'
            : "\nAcciones con ejecutor propio de Leonidas: ninguna por ahora. "
                . 'Puedo consultar datos, explicar el flujo y abrir el modulo autorizado; '
                . 'las escrituras se realizan en la pantalla del modulo hasta conectar '
                . 'un ejecutor auditado.';

        $mensajeRespuesta = $dominio['nombre'] . ': ' . $dominio['proposito']
            . "\n\nSubmodulos: " . implode(', ', $dominio['submodulos']) . '.'
            . "\nConsultas disponibles: " . implode('; ', $dominio['consultas']) . '.'
            . "\nOperaciones existentes en Sparta: " . implode('; ', $dominio['acciones']) . '.'
            . $ejecucion
            . "\nFuentes verificables: " . implode(', ', $dominio['fuentes']) . '.';

        return [
            'mensaje' => $mensajeRespuesta,
            'tipo' => 'dominio_sparta',
            'dominio' => $dominio['id'],
            'fuente' => 'registro_capacidades_sparta',
            'cobertura' => $dominio,
        ];
    }

    public function orientar(string $mensaje): ?array
    {
        $dominio = $this->registro->detectar($mensaje);
        if ($dominio === null) {
            return null;
        }

        $criterio = self::CRITERIOS[$dominio['id']]
            ?? 'el registro y periodo exactos que deseas consultar';

        return [
            'mensaje' => 'Identifique la consulta como ' . $dominio['nombre'] . '. '
                . 'Para responder con datos reales necesito ' . $criterio . '. '
                . 'Consultare ' . implode(', ', $dominio['fuentes'])
                . ' y te devolvere el resultado con su fuente y fecha de corte.',
            'tipo' => 'dominio_requiere_criterio',
            'dominio' => $dominio['id'],
            'fuente' => 'registro_capacidades_sparta',
            'cobertura' => $dominio,
        ];
    }

    /**
     * Convierte errores genericos de una fuente en un diagnostico util del dominio.
     *
     * @param array<string, mixed> $error
     * @return array<string, mixed>|null
     */
    public function errorConsulta(string $mensaje, array $error = []): ?array
    {
        $dominio = $this->registro->detectar($mensaje);
        if ($dominio === null) {
            return null;
        }

        $detalle = trim((string) ($error['mensaje'] ?? ''));
        if ($detalle === '') {
            $detalle = 'La fuente no devolvio una respuesta utilizable.';
        }

        $fuente = trim((string) ($error['fuente'] ?? ''));
        $fuentes = $fuente !== '' ? [$fuente] : (array) ($dominio['fuentes'] ?? []);
        $motivo = trim((string) (
            $error['motivo']
            ?? ($error['metricas']['motivo'] ?? '')
            ?? ($error['tipo'] ?? 'consulta_error')
        ));
        if ($motivo === '') {
            $motivo = 'consulta_error';
        }
        $criterio = self::CRITERIOS[$dominio['id']]
            ?? 'un identificador y periodo exactos';

        return [
            'mensaje' => 'No pude completar la consulta de ' . $dominio['nombre'] . '. '
                . $detalle . ' Fuentes revisadas o previstas: ' . implode(', ', $fuentes) . '. '
                . 'Motivo: ' . str_replace('_', ' ', $motivo) . '. '
                . 'No se realizo ningun cambio. Puedes repetir la consulta con ' . $criterio . '.',
            'tipo' => 'dominio_fuente_error',
            'dominio' => $dominio['id'],
            'fuente' => $fuente !== '' ? $fuente : 'registro_capacidades_sparta',
            'motivo_original' => $motivo,
            'cobertura' => $dominio,
        ];
    }

    public function capacidadesGenerales(): string
    {
        $nombres = array_map(
            static fn(array $dominio): string => (string) $dominio['nombre'],
            $this->registro->catalogoPublico()
        );

        return 'Puedo consultar y explicar con fuentes reales: '
            . implode(', ', $nombres)
            . '. Tambien ejecuto las acciones que ya tienen un conector auditado y que '
            . 'tu perfil permite. Antes de cualquier cambio muestro una vista previa, '
            . 'solicito confirmacion y dejo auditoria.';
    }

    private function esPreguntaDeDominio(string $mensaje): bool
    {
        $normalizado = mb_strtolower($mensaje, 'UTF-8');
        $normalizado = strtr($normalizado, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
        ]);

        return preg_match(
            '/\b(que es|que hace|como funciona|como se usa|para que sirve|explica|explicame|'
                . 'cuentame|platica|platicame|hablame|quiero saber|necesito entender|'
                . 'que puedo hacer|que puedes hacer|de que eres capaz|submodulos|fuentes)\b/',
            $normalizado
        ) === 1;
    }
}
