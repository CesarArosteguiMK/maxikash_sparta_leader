<?php

namespace Services;

/**
 * Catalogo central de dominios que Leonidas puede explicar, consultar u operar.
 *
 * No concede permisos ni ejecuta acciones. Describe las fuentes y ejecutores
 * existentes para que el enrutador seleccione el dominio correcto.
 */
class LeonidasCapabilityRegistry
{
    /**
     * Acciones con un ejecutor real conectado al agente.
     *
     * Las operaciones que existen en una pantalla, pero no aparecen aqui,
     * solo pueden explicarse, consultarse o abrirse desde Leonidas.
     *
     * @var array<string, list<string>>
     */
    private const ACCIONES_EJECUTABLES = [
        'creditos' => [
            'cartera_reactivar_tarea_movil',
        ],
        'capital_humano' => [
            'rrhh_registrar',
            'rrhh_actualizar',
            'persona_baja',
            'persona_reingreso',
            'vacaciones_solicitar',
            'vacaciones_resolver',
            'estructura_cambiar',
            'estructura_importar',
            'candidato_registrar',
            'candidato_actualizar',
            'candidato_etapa',
            'documento_reevaluar',
            'documento_validar',
            'documento_clasificar',
            'permiso_actualizar',
            'salario_actualizar',
        ],
        'convenios' => [
            'convenio_crear',
        ],
        'motos_adjudicadas' => [
            'moto_asignar',
            'moto_enviar_evidencias',
            'moto_forzar_evidencias',
            'moto_guardar_datos',
        ],
        'direcciones' => [],
        'legacy' => [],
        'atlas' => [],
        'tickets' => [],
        'analitica' => [],
        'gastos_cobranza' => [
            'servicio_local_control',
        ],
        'organizacion' => [
            'estructura_cambiar',
            'estructura_importar',
            'excel_aplicar',
        ],
        'servicios' => [
            'servicio_local_control',
        ],
    ];

    /** @return array<string, array<string, mixed>> */
    public function todos(): array
    {
        return [
            'creditos' => $this->dominio(
                'Créditos',
                ['credito', 'creditos', 'estado de cuenta', 'saldo', 'pago', 'pagos', 'cargos moratorios', 'domiciliado'],
                'Consulta el estado financiero y operativo de un crédito, sus pagos, saldos, cargos, gestiones y documentación.',
                ['Estados de cuenta', 'Documentación', 'Histórico de gestiones', 'Cierre de crédito'],
                ['s2_estado_cuenta', 'legacy', 'sparta_principal'],
                ['Estado de cuenta y saldo actual', 'Pagos y conciliación', 'Cargos de cobranza y moratorios', 'Asignación o bucket del crédito', 'Historial de gestiones'],
                ['Abrir módulos autorizados', 'Preparar consultas y reportes', 'Ejecutar acciones soportadas por los flujos de crédito con confirmación'],
                ['LeonidasS2Service', 'LeonidasEstadoCuentaService', 'LeonidasSemanticQueryService']
            ),
            'capital_humano' => $this->dominio(
                'Capital Humano',
                ['capital humano', 'rrhh', 'recursos humanos', 'colaborador', 'colaboradores', 'candidato', 'candidatos', 'vacaciones', 'baja', 'reingreso', 'expediente'],
                'Administra el ciclo de vida de colaboradores y candidatos, estructura laboral, expedientes, accesos, vacaciones y auditoría.',
                ['Gestión de personal', 'Selección de personal', 'Control de bajas', 'Vacaciones', 'Expedientes RR.HH.', 'Revisión RR.HH.', 'Auditoría', 'Accesos', 'Organigrama'],
                ['sparta_principal', 'legacy', 'geografia'],
                ['Ficha 360 del colaborador', 'Altas, bajas y reingresos', 'Candidatos por etapa', 'Vacaciones', 'Documentos faltantes o mal clasificados', 'Estructura y jefe', 'Permisos y auditoría'],
                ['Preparar y ejecutar acciones habilitadas de RR.HH.', 'Generar reportes', 'Abrir módulos autorizados', 'Procesar archivos mediante el flujo de carga correspondiente'],
                ['LeonidasCapitalHumanoService', 'LeonidasSpreadsheetService', 'LeonidasMessagingService']
            ),
            'convenios' => $this->dominio(
                'Convenios',
                ['convenio', 'convenios', 'oferta', 'pendiente de conciliar', 'conciliacion', 'incumplido'],
                'Calcula, registra y administra acuerdos de pago, comprobantes, conciliación, cancelación y reactivación.',
                ['Ofertas', 'Convenios activos', 'Comprobantes', 'Conciliación', 'Cancelaciones', 'Reactivaciones', 'Cierre de crédito'],
                ['sparta_principal', 'legacy', 's2_estado_cuenta'],
                ['Elegibilidad de un crédito', 'Plazo permitido', 'Monto y calendario', 'Pagos pendientes de conciliar', 'Incumplimiento', 'Cancelación y reactivación'],
                ['Preparar convenio', 'Registrar o modificar mediante ejecutor autorizado', 'Conciliar comprobantes', 'Cancelar o reactivar con permisos y confirmación'],
                ['LeonidasConveniosService', 'LeonidasAgentService']
            ),
            'motos_adjudicadas' => $this->dominio(
                'Motos Adjudicadas',
                ['moto', 'motos', 'motos adjudicadas', 'adjudicacion', 'evidencias', 'dictamen', 'tracking', 'inventario', 'motocicleta'],
                'Gestiona créditos adjudicados, asignaciones, recuperación, datos de motocicleta, evidencias, dictámenes, inventario y tracking.',
                ['Asignaciones', 'Evidencias', 'Dictamen', 'Recuperación', 'Cartera', 'Recepción', 'Inventario', 'Tracking', 'BlackList'],
                ['sparta_principal', 'legacy', 'segundometro', 's2_estado_cuenta'],
                ['Diagnóstico cruzado de crédito', 'Asignación vigente', 'Tareas y dictámenes Legacy', 'Datos de motocicleta', 'Evidencias', 'Historial de estatus'],
                ['Asignar o reasignar crédito', 'Capturar datos de motocicleta', 'Mover estatus', 'Enviar a evidencias', 'Simular o desbloquear dictamen con permisos, NIP y confirmación'],
                ['LeonidasMotosService', 'LeonidasDictamenAgentService', 'LeonidasAgentService']
            ),
            'direcciones' => $this->dominio(
                'Direcciones',
                ['direccion', 'direcciones', 'domicilio', 'domicilios', 'geolocalizacion', 'ubicacion', 'colonia', 'codigo postal'],
                'Consulta y mantiene direcciones relacionadas con créditos y personas, su prioridad y sincronización operativa.',
                ['Consulta por crédito', 'Captura de dirección', 'Orden de prioridad', 'Sincronización desde Segundómetro', 'Catálogos geográficos'],
                ['sparta_principal', 'geografia', 'segundometro'],
                ['Direcciones de un crédito', 'Estado, municipio y colonia', 'Prioridad de contacto', 'Origen y sincronización'],
                ['Guardar dirección', 'Reordenar direcciones', 'Sincronizar desde Segundómetro con permisos y confirmación'],
                ['Models\\Direcciones']
            ),
            'legacy' => $this->dominio(
                'Legacy',
                ['legacy', 'campania', 'campana', 'task', 'tasks', 'dictum', 'dictums', 'tarea', 'tareas', 'gestion', 'gestiones'],
                'Consulta la operación histórica de cobranza: créditos, campañas, tareas, asignaciones, dictámenes, usuarios y gestiones.',
                ['Campañas', 'Tareas', 'Asignaciones', 'Dictámenes', 'Usuarios', 'Gestiones', 'Layout Legacy'],
                ['legacy'],
                ['Historial de tareas', 'Responsable vigente', 'Campaña activa', 'Dictámenes', 'Gestiones y pagos reflejados', 'Correspondencia con Sparta'],
                ['Consultar y reconciliar información', 'Crear tareas o asignaciones solo mediante ejecutores aprobados y auditados'],
                ['LeonidasUniversalQueryService', 'LeonidasMotosService', 'LeonidasAgentService']
            ),
            'atlas' => $this->dominio(
                'Atlas',
                ['atlas', 'ruta', 'rutas', 'presupuesto', 'presupuestos', 'sucursal', 'sucursales', 'riesgo operativo', 'abanderamiento'],
                'Administra rutas, sucursales, presupuestos, distribuidores, riesgos, catálogos operativos y accesos de Atlas.',
                ['Sucursales', 'Rutas y gestores', 'Presupuestos', 'Distribuidores', 'Riesgos operativos', 'Catálogos', 'Notificaciones', 'Accesos'],
                ['sparta_principal', 'geografia', 'legacy'],
                ['Rutas por gestor', 'Detalle y ranking de presupuesto', 'Sucursales asignadas', 'Riesgos', 'Distribuidores', 'Accesos Atlas'],
                ['Guardar presupuestos', 'Asignar rutas o sucursales', 'Importar distribuidores', 'Administrar catálogos y accesos con permiso y confirmación'],
                ['Models\\Atlas', 'Controllers\\Atlas']
            ),
            'tickets' => $this->dominio(
                'Tickets',
                ['ticket', 'tickets', 'sabueso', 'aclaracion', 'prorroga', 'ilocalizable', 'reconsulta'],
                'Registra y da seguimiento a incidencias operativas, aclaraciones, dictámenes, evidencias y conversaciones.',
                ['Bandeja', 'Asignación', 'Chat', 'Dictamen', 'Evidencias', 'Prórrogas', 'Ilocalizables', 'Reconsulta de pagos', 'Reportes'],
                ['sparta_principal', 'legacy', 's2_estado_cuenta'],
                ['Tickets por crédito o persona', 'Estatus y responsable', 'Historial', 'Conversación', 'Evidencias', 'Indicadores y reportes semanales'],
                ['Crear ticket', 'Asignar o cerrar', 'Agregar seguimiento', 'Registrar dictamen, evidencia o prórroga con permisos y confirmación'],
                ['Models\\Ticket', 'Controllers\\Ticket']
            ),
            'analitica' => $this->dominio(
                'Analítica',
                ['analitica', 'analisis', 'bucket', 'buckets', 'segundometro', 'comparativo', 'historico bucket', 'avance', 'indicador', 'grafica'],
                'Explica y compara métricas operativas de cartera, buckets, pagos, campo, asignación y desempeño.',
                ['Histórico bucket', 'Avance', 'Comparativo', 'Segundómetro', 'Primeros pagos', 'Campo', 'Asignación', 'Sabueso'],
                ['segundometro', 'legacy', 'sparta_principal', 's2_estado_cuenta', 'gastos_cobranza'],
                ['Bucket actual, de nacimiento y de cierre', 'Diferencias entre cortes', 'Pagos puntuales', 'Movimientos de cartera', 'Comparativos semanales', 'Créditos que explican una diferencia'],
                ['Generar reportes, tablas y gráficas', 'Diagnosticar diferencias por crédito y corte', 'Exportar resultados autorizados'],
                ['LeonidasAnaliticaService', 'LeonidasSemanticQueryService', 'LeonidasArtifactBuilder']
            ),
            'gastos_cobranza' => $this->dominio(
                'Gastos de Cobranza',
                ['gastos de cobranza', 'gasto cobranza', 'gastos cobranza', 'shell gastos', 'agente gastos'],
                'Consulta indicadores, archivos y procesos de gastos de cobranza y controla su agente operativo.',
                ['Estadística', 'Dashboard', 'Reportes', 'Carga de archivos', 'Agente de gastos', 'Logs y ejecución semanal'],
                ['gastos_cobranza', 'sparta_principal', 'legacy'],
                ['Resumen de gastos', 'Distribución y tendencia', 'Estado del agente', 'Última ejecución', 'Archivos y logs'],
                ['Generar reportes', 'Subir archivos autorizados', 'Iniciar, detener o reiniciar el agente con permiso y confirmación'],
                ['Models\\GastosCobranzaEstadistica', 'LeonidasLocalAgentService']
            ),
            'organizacion' => $this->dominio(
                'Organización',
                ['organizacion', 'estructura', 'empresa', 'empresas', 'area', 'areas', 'departamento', 'departamentos', 'puesto', 'puestos', 'organigrama', 'equivalencia'],
                'Mantiene la estructura Empresa > Dirección > Área > Departamento > Puesto y su relación con personas y Legacy.',
                ['Empresas', 'Países', 'Direcciones', 'Áreas', 'Departamentos', 'Puestos', 'Equivalencias', 'Asignación por puestos', 'Sincronización Legacy'],
                ['sparta_principal', 'legacy', 'geografia'],
                ['Estructura por empresa', 'Personas sin jefe', 'Puestos y equivalencias', 'Cambios jerárquicos', 'Diferencias con Legacy'],
                ['Crear o editar estructura', 'Cambiar puesto, jefe o departamento', 'Sincronizar y cargar estructura con vista previa, permiso y confirmación'],
                ['LeonidasCapitalHumanoService', 'LeonidasSpreadsheetService']
            ),
            'servicios' => $this->dominio(
                'Servicios',
                ['servicio', 'servicios', 'agente', 'agentes', 'shell', 'proceso', 'procesos', 'segundometro agent', 'primeros pagos'],
                'Supervisa los servicios y agentes locales que alimentan Segundómetro, primeros pagos, cartera y gastos de cobranza.',
                ['Estado en vivo', 'Segundómetro', 'Correos primeros pagos', 'Gastos de cobranza', 'Cartera', 'Logs'],
                ['sparta_principal', 'segundometro', 'gastos_cobranza'],
                ['Disponibilidad', 'Health check', 'Última ejecución', 'Errores y logs', 'Autoinicio'],
                ['Iniciar, detener o reiniciar agentes con permiso, confirmación y auditoría'],
                ['LeonidasLocalAgentService']
            ),
        ];
    }

    /** @return array<string, mixed>|null */
    public function detectar(string $texto): ?array
    {
        $normalizado = $this->normalizar($texto);
        $mejor = null;
        $mejorPuntaje = 0;
        foreach ($this->todos() as $id => $dominio) {
            $puntaje = 0;
            foreach ($dominio['aliases'] as $alias) {
                $aliasNormalizado = $this->normalizar((string) $alias);
                if ($aliasNormalizado !== '' && str_contains($normalizado, $aliasNormalizado)) {
                    $palabras = substr_count($aliasNormalizado, ' ') + 1;
                    $puntaje = max($puntaje, strlen($aliasNormalizado) + ($palabras * 10));
                }
            }
            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $mejor = $this->enriquecer(['id' => $id] + $dominio);
            }
        }
        return $mejorPuntaje > 0 ? $mejor : null;
    }

    /** @return list<array<string, mixed>> */
    public function relevantes(string $texto, int $limite = 3): array
    {
        $detectado = $this->detectar($texto);
        if ($detectado !== null) {
            return [$this->publico($detectado)];
        }
        return array_slice($this->catalogoPublico(), 0, max(1, $limite));
    }

    /** @return list<array<string, mixed>> */
    public function catalogoPublico(): array
    {
        $salida = [];
        foreach ($this->todos() as $id => $dominio) {
            $salida[] = $this->publico(['id' => $id] + $dominio);
        }
        return $salida;
    }

    /** @return array<string, mixed> */
    private function dominio(
        string $nombre,
        array $aliases,
        string $proposito,
        array $submodulos,
        array $fuentes,
        array $consultas,
        array $acciones,
        array $ejecutores
    ): array {
        return compact('nombre', 'aliases', 'proposito', 'submodulos', 'fuentes', 'consultas', 'acciones', 'ejecutores') + [
            'control' => 'Toda lectura respeta los módulos y permisos de la sesión. Toda escritura exige ejecutor aprobado, vista previa, confirmación y auditoría.',
        ];
    }

    /** @param array<string, mixed> $dominio */
    private function publico(array $dominio): array
    {
        $dominio = $this->enriquecer($dominio);
        unset($dominio['aliases']);
        return $dominio;
    }

    /** @param array<string, mixed> $dominio */
    private function enriquecer(array $dominio): array
    {
        $id = (string) ($dominio['id'] ?? '');
        $acciones = self::ACCIONES_EJECUTABLES[$id] ?? [];
        $dominio['acciones_ejecutables'] = $acciones;
        $dominio['modo_operativo'] = $acciones === []
            ? 'consulta_explicacion_y_navegacion'
            : 'consulta_y_ejecucion_auditada';

        return $dominio;
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = strtr($texto, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
        ]);
        return trim(preg_replace('/\s+/u', ' ', $texto) ?? $texto);
    }
}
