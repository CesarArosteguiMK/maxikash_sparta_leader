<?php

namespace Services;

/**
 * Curated, read-only knowledge supplied to Leonidas.
 *
 * It deliberately indexes product documentation instead of application source
 * code so answers stay useful without exposing implementation details or secrets.
 */
class LeonidasKnowledgeService
{
    private const MAX_DOCUMENTS = 3;
    private const MAX_DOCUMENT_CHARS = 1800;

    public function contextoPara(string $pregunta, array $modulosUsuario): array
    {
        return [
            'identidad_del_producto' => 'Sparta es la plataforma operativa de MaxiKash y Furia Motos para credito, cobranza, capital humano, analitica y operacion.',
            'principios_criticos' => [
                'numero_empleado y codigo_contpac son campos distintos. Nunca se sustituyen ni se mezclan.',
                'La estructura organizacional es Empresa > Direccion > Area > Departamento > Puesto.',
                'Los salarios, documentos sensibles y datos personales requieren permisos especiales y, cuando aplique, segundo paso de autenticacion.',
                'Una accion que modifique datos, permisos o comunicaciones necesita confirmacion explicita y registro de auditoria.',
                'Los reportes deben usar datos autorizados por servidor; Leonidas no inventa resultados ni consulta datos sin una herramienta aprobada.',
            ],
            'modulos_del_sistema' => $this->catalogoModulos(),
            'modulos_disponibles_para_el_usuario' => array_values(array_unique(array_map('intval', $modulosUsuario))),
            'documentacion_relevante' => $this->buscarDocumentacion($pregunta),
        ];
    }

    /** @return array<int, array<string, string>> */
    private function catalogoModulos(): array
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
            ['modulo' => 'Convenios', 'funcion' => 'Opera asignacion de creditos, cartera, convenios, cierre de credito y reporteria.'],
            ['modulo' => 'Motos Adjudicadas', 'funcion' => 'Gestiona operaciones, evidencias, recuperacion, cartera, recepcion, inventario y tracking de motos.'],
            ['modulo' => 'Tickets', 'funcion' => 'Registra y administra tickets de Sabueso y su seguimiento.'],
            ['modulo' => 'Atlas', 'funcion' => 'Administra rutas, presupuestos, creditos operativos, catalogos, riesgos y accesos Atlas.'],
            ['modulo' => 'Organizacion', 'funcion' => 'Mantiene paises, estructura, equivalencias de puestos y sincronizacion con Legacy.'],
            ['modulo' => 'Servicios', 'funcion' => 'Muestra y controla servicios operativos locales como Segundometro, Gastos Cobranza y Cartera.'],
        ];
    }

    /** @return array<int, array{titulo:string, contenido:string}> */
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
        foreach (glob($directorio . '/*.md') ?: [] as $archivo) {
            $contenido = @file_get_contents($archivo);
            if (!is_string($contenido) || $contenido === '') {
                continue;
            }
            $normalizado = $this->normalizar($contenido);
            $puntaje = 0;
            foreach ($terminos as $termino) {
                $puntaje += substr_count($normalizado, $termino);
            }
            if ($puntaje <= 0) {
                continue;
            }
            $candidatos[] = [
                'puntaje' => $puntaje,
                'titulo' => basename($archivo, '.md'),
                'contenido' => $this->recortarDocumento($contenido, $terminos),
            ];
        }

        usort($candidatos, static fn(array $a, array $b): int => $b['puntaje'] <=> $a['puntaje']);
        return array_map(
            static fn(array $item): array => ['titulo' => $item['titulo'], 'contenido' => $item['contenido']],
            array_slice($candidatos, 0, self::MAX_DOCUMENTS)
        );
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
        $lineas = preg_split('/\R/', $contenido) ?: [];
        $seleccionadas = [];
        foreach ($lineas as $linea) {
            $normalizada = $this->normalizar($linea);
            foreach ($terminos as $termino) {
                if (str_contains($normalizada, $termino)) {
                    $seleccionadas[] = trim($linea);
                    break;
                }
            }
            if (strlen(implode("\n", $seleccionadas)) >= self::MAX_DOCUMENT_CHARS) {
                break;
            }
        }
        $texto = trim(implode("\n", $seleccionadas));
        return mb_substr($texto !== '' ? $texto : $contenido, 0, self::MAX_DOCUMENT_CHARS, 'UTF-8');
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return $convertido === false ? $texto : $convertido;
    }
}
