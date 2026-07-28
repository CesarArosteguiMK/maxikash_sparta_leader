<?php

namespace Services;

use Core\Database;

/**
 * Curated, read-only knowledge supplied to Leonidas.
 *
 * It deliberately indexes product documentation instead of application source
 * code so answers stay useful without exposing implementation details or secrets.
 */
class LeonidasKnowledgeService
{
    private const MAX_DOCUMENTS = 6;
    private const MAX_DOCUMENT_CHARS = 2800;

    public function contextoPara(string $pregunta, array $modulosUsuario): array
    {
        $capacidades = new LeonidasCapabilityRegistry();
        $contexto = [
            'identidad_del_producto' => 'Sparta es la plataforma operativa de MaxiKash y Furia Motos para credito, cobranza, capital humano, analitica y operacion.',
            'principios_criticos' => [
                'numero_empleado y codigo_contpac son campos distintos. Nunca se sustituyen ni se mezclan.',
                'La estructura organizacional es Empresa > Direccion > Area > Departamento > Puesto.',
                'Los salarios, documentos sensibles y datos personales requieren permisos especiales y, cuando aplique, segundo paso de autenticacion.',
                'Una accion que modifique datos, permisos o comunicaciones necesita confirmacion explicita y registro de auditoria.',
                'Los reportes deben usar datos autorizados por servidor; Leonidas no inventa resultados ni consulta datos sin una herramienta aprobada.',
            ],
            'fuentes_de_datos' => (new LeonidasDataSourceRegistry())->catalogoPublico(),
            'dominios_operativos_relevantes' => $capacidades->relevantes($pregunta),
            'cobertura_operativa_sparta' => $capacidades->catalogoPublico(),
            'mapa_seguro_del_codigo' => (new LeonidasCodeKnowledgeService())->contextoPara($pregunta),
            'modulos_del_sistema' => $this->catalogoModulos(),
            'catalogo_real_relevante' => $this->buscarModulosReales($pregunta, $modulosUsuario),
            'modulos_disponibles_para_el_usuario' => array_values(array_unique(array_map('intval', $modulosUsuario))),
            'inventario_documental' => $this->resumenDocumentacion(),
            'documentacion_relevante' => $this->buscarDocumentacion($pregunta),
        ];

        if ($this->esConsultaConvenios($pregunta)) {
            $contexto['reglas_operativas_convenios'] = (new LeonidasConveniosService())->conocimiento();
        }

        return $contexto;
    }

    /** @return array<int, array<string, mixed>> */
    private function buscarModulosReales(string $pregunta, array $modulosUsuario): array
    {
        try {
            $rows = (new Database())->queryAll(
                "SELECT id, nombre, pestana, descripcion
                 FROM modulos_web
                 WHERE COALESCE(activo, 1) = 1
                 ORDER BY pestana, nombre"
            );
        } catch (\Throwable $error) {
            error_log('[Leonidas] No se pudo leer el catalogo de modulos: ' . $error->getMessage());
            return [];
        }

        $terms = $this->terminos($pregunta);
        $allowed = array_fill_keys(array_map('intval', $modulosUsuario), true);
        $scored = [];
        foreach ($rows as $row) {
            $text = $this->normalizar(implode(' ', [
                (string) ($row['nombre'] ?? ''),
                (string) ($row['pestana'] ?? ''),
                (string) ($row['descripcion'] ?? ''),
            ]));
            $score = 0;
            foreach ($terms as $term) {
                if (str_contains($text, $term)) {
                    $score += str_contains($this->normalizar((string) ($row['nombre'] ?? '')), $term) ? 4 : 1;
                }
            }
            if ($terms && $score === 0) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            $scored[] = [
                'score' => $score,
                'id' => $id,
                'nombre' => (string) ($row['nombre'] ?? ''),
                'categoria' => (string) ($row['pestana'] ?? 'Otros'),
                'descripcion' => (string) ($row['descripcion'] ?? ''),
                'asignado_al_usuario' => isset($allowed[$id]),
            ];
        }

        usort($scored, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);
        return array_map(static function (array $item): array {
            unset($item['score']);
            return $item;
        }, array_slice($scored, 0, 12));
    }

    /** @return array<int, array<string, string>> */
    public function catalogoModulos(): array
    {
        return [
            ['modulo' => 'Creditos', 'funcion' => 'Consulta estados de cuenta, documentacion asociada e historico de gestiones.'],
            ['modulo' => 'Capital Humano - Gestion', 'funcion' => 'Administra colaboradores, estructura laboral, altas, bajas, reingresos y sus expedientes.'],
            ['modulo' => 'Capital Humano - Seleccion de Personal', 'funcion' => 'Registra candidatos, captura informacion por etapas, valida documentos y programa ingresos.'],
            ['modulo' => 'Capital Humano - Expedientes RR.HH.', 'funcion' => 'Carga, clasifica, protege y consulta documentos del expediente laboral.'],
            ['modulo' => 'Capital Humano - Accesos', 'funcion' => 'Administra modulos, puestos, permisos especiales y acceso a informacion sensible.'],
            ['modulo' => 'Capital Humano - Auditoria', 'funcion' => 'Consulta accesos sensibles, intentos denegados y eventos de seguridad.'],
            ['modulo' => 'Capital Humano - Organigrama', 'funcion' => 'Visualiza la jerarquia organizacional por empresa y estructura laboral.'],
            ['modulo' => 'Capital Humano - Estructura', 'funcion' => 'Configura empresas, direcciones, areas, departamentos y puestos.'],
            ['modulo' => 'Capital Humano - Control de Bajas', 'funcion' => 'Gestiona bajas, motivos, fechas y documentacion de finiquito cuando corresponde.'],
            ['modulo' => 'Capital Humano - Vacaciones', 'funcion' => 'Consulta y administra solicitudes y paneles de vacaciones.'],
            ['modulo' => 'Analitica', 'funcion' => 'Ofrece reportes de cartera, primeros pagos, campo, comparativas, asignacion y avance operativo.'],
            ['modulo' => 'Convenios', 'funcion' => 'Calcula ofertas por reglas de credito y producto, registra convenios, valida pagos, concilia comprobantes y administra cancelaciones y reactivaciones auditadas.'],
            ['modulo' => 'Motos Adjudicadas', 'funcion' => 'Gestiona operaciones, evidencias, recuperacion, cartera, recepcion, inventario y tracking de motos.'],
            ['modulo' => 'Direcciones', 'funcion' => 'Consulta y administra la estructura operativa de direcciones, rutas, zonas y responsables autorizados.'],
            ['modulo' => 'Legacy', 'funcion' => 'Consulta creditos, usuarios, gestiones, campanias, tareas, asignaciones, dictamenes y pagos del sistema Legacy.'],
            ['modulo' => 'Tickets', 'funcion' => 'Registra y administra tickets de Sabueso y su seguimiento.'],
            ['modulo' => 'Atlas', 'funcion' => 'Administra rutas, presupuestos, creditos operativos, catalogos, riesgos y accesos Atlas.'],
            ['modulo' => 'Gastos de Cobranza', 'funcion' => 'Consulta conceptos, solicitudes, responsables, estatus y operacion del agente de Gastos de Cobranza.'],
            ['modulo' => 'Organizacion', 'funcion' => 'Mantiene paises, estructura, equivalencias de puestos y sincronizacion con Legacy.'],
            ['modulo' => 'Servicios', 'funcion' => 'Muestra y controla servicios operativos locales como Segundometro, Gastos Cobranza y Cartera.'],
            ['modulo' => 'Aclaracion de credito', 'funcion' => 'Administra el panel especializado para investigar diferencias y solicitudes relacionadas con creditos.'],
            ['modulo' => 'Adjudicacion', 'funcion' => 'Asigna creditos, gestiona responsables y soporta diagnosticos y dictamenes de motos.'],
            ['modulo' => 'Almacen Virtual', 'funcion' => 'Controla inventario, ubicaciones, recepcion, revision mecanica, Kanban y unidades de motos.'],
            ['modulo' => 'Aplicaciones de pago', 'funcion' => 'Ofrece el panel administrativo de aplicaciones y aclaraciones operativas de pagos.'],
            ['modulo' => 'Atencion a Clientes', 'funcion' => 'Gestiona evidencias, recuperacion, cierre documental, recepcion y listas de control de motos.'],
            ['modulo' => 'Cierre de Credito', 'funcion' => 'Orquesta en proceso, visto bueno, envio, finalizacion, descarte e historial de cierres.'],
            ['modulo' => 'Clima CDMX', 'funcion' => 'Muestra la utilidad interna de clima configurada para Ciudad de Mexico.'],
            ['modulo' => 'Condonaciones', 'funcion' => 'Consulta, crea y da seguimiento a solicitudes, estados, detalle e historial de condonaciones.'],
            ['modulo' => 'Configuracion Motos Adjudicadas', 'funcion' => 'Mantiene rutas, FAD, reglas, excepciones, pendientes y recordatorios del flujo de motos.'],
            ['modulo' => 'Configuracion de Tickets por Puesto', 'funcion' => 'Configura visibilidad, estadisticas y paneles de tickets por puesto o usuario.'],
            ['modulo' => 'Credito problematico', 'funcion' => 'Presenta el panel administrativo de clasificaciones y casos operativos especiales.'],
            ['modulo' => 'Departamentos y puestos', 'funcion' => 'Administra direcciones organizacionales, departamentos, puestos y su orden.'],
            ['modulo' => 'Despachos', 'funcion' => 'Asigna cartera a despachos, maneja documentos, importaciones, comentarios e historial de gestores.'],
            ['modulo' => 'Dynamo Validations', 'funcion' => 'Integra vistas previas de oferta y coordenadas mediante un componente tecnico controlado.'],
            ['modulo' => 'Empresas', 'funcion' => 'Consulta empresas existentes y sus relaciones organizacionales.'],
            ['modulo' => 'Equivalencias', 'funcion' => 'Relaciona puestos de Sparta con puestos de Legacy para sincronizacion.'],
            ['modulo' => 'Gestion de Campo', 'funcion' => 'Inicia, lista, evalua y da seguimiento a gestiones operativas de campo.'],
            ['modulo' => 'Indicadores', 'funcion' => 'Calcula KPI, eficiencia, intensidad, promesas, cartera y matrices de bucket.'],
            ['modulo' => 'Inicio', 'funcion' => 'Presenta accesos, diagnosticos de conexiones y estado autorizado de servicios locales.'],
            ['modulo' => 'Motos API', 'funcion' => 'Consulta motos por credito o serie, informa estado y administra su cache tecnico.'],
            ['modulo' => 'Notificaciones', 'funcion' => 'Lista avisos, controla su estado de lectura y diagnostica sincronizacion.'],
            ['modulo' => 'Onboarding', 'funcion' => 'Presenta contenido de incorporacion y capacitacion, incluido video.'],
            ['modulo' => 'Paises', 'funcion' => 'Administra el catalogo de paises y su estado activo.'],
            ['modulo' => 'Perfil', 'funcion' => 'Permite al usuario consultar y actualizar los campos habilitados de su perfil.'],
            ['modulo' => 'Plantilla', 'funcion' => 'Presenta el panel administrativo de plantilla y colaboradores.'],
            ['modulo' => 'Primeros Pagos S2', 'funcion' => 'Ejecuta y reporta el proceso especializado de primeros pagos y vencimientos.'],
            ['modulo' => 'Reporteria', 'funcion' => 'Concentra reportes de RR.HH., campo, asignacion, cartera, Legacy, Sabueso y primeros pagos.'],
            ['modulo' => 'Segundometro', 'funcion' => 'Supervisa cortes, archivos, monitoreo, reportes y el agente de Segundometro.'],
            ['modulo' => 'Solicitud de Adjudicacion', 'funcion' => 'Recibe, lista, detalla y asigna solicitudes originadas por diferentes areas.'],
            ['modulo' => 'Tracking de Recoleccion', 'funcion' => 'Administra rutas, planeacion, CEDIS, transportistas, ubicacion, chat, evidencias y OTP.'],
            ['modulo' => 'Usuarios', 'funcion' => 'Consulta usuarios existentes, detalles y empresas relacionadas sin sustituir a la persona laboral.'],
            ['modulo' => 'Validaciones', 'funcion' => 'Gestiona paneles, formularios, preguntas y reasignaciones de validacion territorial.'],
            ['modulo' => 'Viaticos', 'funcion' => 'Presenta el panel administrativo y seguimiento de solicitudes de viaticos mediante Tickets.'],
        ];
    }

    /** @return array<int, array{titulo:string, ruta:string, contenido:string}> */
    private function buscarDocumentacion(string $pregunta): array
    {
        $directorio = dirname(__DIR__, 2) . '/public/assets/docs';
        if (!is_dir($directorio)) {
            return [];
        }

        $terminos = $this->terminos($pregunta);
        if (!$terminos) {
            return [];
        }

        $candidatos = [];
        foreach ($this->archivosDocumentacion($directorio) as $archivo) {
            $contenido = @file_get_contents($archivo);
            if (!is_string($contenido) || $contenido === '') {
                continue;
            }
            $normalizado = $this->normalizar($contenido);
            $rutaRelativa = ltrim(str_replace('\\', '/', substr($archivo, strlen($directorio))), '/');
            $nombreNormalizado = $this->normalizar($rutaRelativa);
            $tituloDocumento = $this->tituloDocumento($contenido, $rutaRelativa);
            $puntaje = 0;
            foreach ($terminos as $termino) {
                $puntaje += substr_count($normalizado, $termino);
                if (str_contains($nombreNormalizado, $termino)) {
                    $puntaje += 12;
                }
                if (str_contains($this->normalizar($tituloDocumento), $termino)) {
                    $puntaje += 8;
                }
            }
            if ($puntaje <= 0) {
                continue;
            }
            $candidatos[] = [
                'puntaje' => $puntaje,
                'titulo' => $tituloDocumento,
                'ruta' => $rutaRelativa,
                'contenido' => $this->recortarDocumento($contenido, $terminos),
            ];
        }

        usort($candidatos, static function (array $a, array $b): int {
            $puntaje = $b['puntaje'] <=> $a['puntaje'];
            return $puntaje !== 0 ? $puntaje : strcmp((string) $a['ruta'], (string) $b['ruta']);
        });
        return array_map(
            static fn(array $item): array => [
                'titulo' => $item['titulo'],
                'ruta' => $item['ruta'],
                'contenido' => $item['contenido'],
            ],
            array_slice($candidatos, 0, self::MAX_DOCUMENTS)
        );
    }

    /** @return array<string, mixed> */
    private function resumenDocumentacion(): array
    {
        $directory = dirname(__DIR__, 2) . '/public/assets/docs';
        if (!is_dir($directory)) {
            return ['documentos' => 0, 'actualizado' => null];
        }
        $files = $this->archivosDocumentacion($directory);
        $lastModified = 0;
        $leonidasDocuments = 0;
        foreach ($files as $file) {
            $lastModified = max($lastModified, (int) (@filemtime($file) ?: 0));
            $relative = str_replace('\\', '/', substr($file, strlen($directory)));
            if (str_starts_with(ltrim($relative, '/'), 'leonidas/')) {
                $leonidasDocuments++;
            }
        }
        return [
            'documentos_markdown' => count($files),
            'documentos_especializados_leonidas' => $leonidasDocuments,
            'maximo_fragmentos_por_consulta' => self::MAX_DOCUMENTS,
            'maximo_caracteres_por_fragmento' => self::MAX_DOCUMENT_CHARS,
            'actualizado' => $lastModified > 0 ? date(DATE_ATOM, $lastModified) : null,
        ];
    }

    /** @return list<string> */
    private function archivosDocumentacion(string $directorio): array
    {
        $archivos = [];
        $iterador = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directorio, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterador as $archivo) {
            if (!$archivo->isFile() || strtolower($archivo->getExtension()) !== 'md') {
                continue;
            }
            $archivos[] = $archivo->getPathname();
        }
        sort($archivos, SORT_STRING);
        return $archivos;
    }

    private function tituloDocumento(string $contenido, string $rutaRelativa): string
    {
        if (preg_match('/^#\s+(.+)$/m', $contenido, $coincidencia)) {
            return trim((string) $coincidencia[1]);
        }
        return basename($rutaRelativa, '.md');
    }

    /** @return list<string> */
    private function terminos(string $texto): array
    {
        $texto = $this->normalizar($texto);
        $palabras = preg_split('/[^a-z0-9]+/', $texto) ?: [];
        $ignorar = ['como', 'para', 'sobre', 'quiero', 'puede', 'puedes', 'dime', 'este', 'esta', 'todo', 'sparta'];
        $terminos = [];
        foreach ($palabras as $palabra) {
            if (strlen($palabra) < 4 || in_array($palabra, $ignorar, true)) {
                continue;
            }
            $terminos[$palabra] = true;
        }
        return array_keys($terminos);
    }

    /** @param list<string> $terminos */
    private function recortarDocumento(string $contenido, array $terminos): string
    {
        $contenido = trim($contenido);
        if (mb_strlen($contenido, 'UTF-8') <= self::MAX_DOCUMENT_CHARS) {
            return $contenido;
        }

        $normalizado = $this->normalizar($contenido);
        $posicion = null;
        foreach ($terminos as $termino) {
            $encontrada = strpos($normalizado, $termino);
            if ($encontrada !== false && ($posicion === null || $encontrada < $posicion)) {
                $posicion = $encontrada;
            }
        }

        $inicio = 0;
        if ($posicion !== null && $posicion > self::MAX_DOCUMENT_CHARS) {
            $inicio = max(0, $posicion - 600);
            $encabezado = strrpos(substr($contenido, 0, $inicio + 1), "\n## ");
            if ($encabezado !== false) {
                $inicio = $encabezado + 1;
            }
        }

        $extracto = trim(mb_substr($contenido, $inicio, self::MAX_DOCUMENT_CHARS, 'UTF-8'));
        if ($inicio === 0) {
            return $extracto;
        }

        $titulo = $this->tituloDocumento($contenido, 'documento');
        return '# ' . $titulo . "\n\n" . $extracto;
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return $convertido === false ? $texto : $convertido;
    }

    private function esConsultaConvenios(string $pregunta): bool
    {
        $normalizada = $this->normalizar($pregunta);
        return str_contains($normalizada, 'convenio')
            || str_contains($normalizada, 'pendiente de conciliar')
            || str_contains($normalizada, 'oferta de convenio');
    }
}
