<?php

namespace Controllers;

use Core\Controller;
use Core\Database;
use Core\TicketsPanelModuloHelper;
use Models\CapHum as CapHumDAO;
use Models\Ticket as TicketDAO;
use Models\FormularioValidacionPregunta as PreguntaDAO;
use Models\FormularioValidacion as FormularioDAO;
use Models\ConfigPanelUsuario as ConfigPanelUsuarioDAO;

/**
 * Panel administración de tickets de Validaciones (URL y script propios).
 */
class Validaciones extends Controller
{
    public function paneladmin()
    {
        $modulos = $_SESSION['modulos'] ?? [];
        if (!is_array($modulos)) $modulos = [];

        // Blindaje extra: solo admin (módulo 19) se queda en Panel Admin.
        if (!in_array(19, $modulos, true)) {
            $personaId = $this->obtenerPersonaIdSesion();
            // Tiene panel validaciones en config pero no módulo 19: no enviar a territorial (allí se redirige otra vez a paneladmin).
            if ($personaId > 0 && $this->personaTienePanelAdminValidaciones($personaId)) {
                header('Location: /sabueso/panelAdminInicio', true, 302);
                exit;
            }
            if ($personaId > 0 && in_array(18, $modulos, true)) {
                $capoInfo = $this->getCapoInfoForTerritorial($personaId);
                if (!empty($capoInfo['departamento_id'])) {
                    header('Location: /validaciones/territorial', true, 302);
                    exit;
                }
                header('Location: /validaciones/gestor', true, 302);
                exit;
            }
            header('Location: /sabueso/panelAdminInicio', true, 302);
            exit;
        }

        TicketsPanelModuloHelper::renderModuloPanel($this, 'validaciones');
    }

    /**
     * Nueva vista: Validaciones/Gestor (solo tickets asignados al gestor autenticado).
     */
    public function gestor()
    {
        $personaId = $this->obtenerPersonaIdSesion();
        if ($this->personaTienePanelAdminValidaciones($personaId)) {
            header('Location: /validaciones/paneladmin', true, 302);
            exit;
        }
        TicketsPanelModuloHelper::renderModuloPanel($this, 'validaciones', [
            'modo' => 'gestor',
        ]);
    }

    /**
     * Nueva vista: Validaciones/Territorial (tickets asignados a gestores subordinados del jefe territorial).
     */
    public function territorial()
    {
        $personaId = $this->obtenerPersonaIdSesion();
        if ($this->personaTienePanelAdminValidaciones($personaId)) {
            header('Location: /validaciones/paneladmin', true, 302);
            exit;
        }
        $capoInfo = $this->getCapoInfoForTerritorial($personaId);

        TicketsPanelModuloHelper::renderModuloPanel($this, 'validaciones', [
            'modo' => 'territorial',
            'campoCapo' => $capoInfo['campo'] ?: '1_7',
            'nombreCapo' => $capoInfo['nombre'] ?: '—',
        ]);
    }

    /**
     * AJAX: tickets asignados al gestor autenticado.
     */
    public function getTicketsGestor()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = $this->obtenerPersonaIdSesion();
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => []]);
            return;
        }
        if ($this->personaTienePanelAdminValidaciones($personaId)) {
            echo json_encode(['success' => false, 'mensaje' => 'Use el panel administrador de validaciones.', 'datos' => []]);
            return;
        }
        $resultado = TicketDAO::getListaTickets($personaId, false, [
            'asignado' => $personaId,
            'categoria_gestion' => 'validaciones',
        ]);
        echo json_encode([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? ($resultado['mensaje'] ?? ''),
            'datos' => $resultado['datos'] ?? [],
        ]);
    }

    /**
     * AJAX: tickets asignados a gestores subordinados del jefe territorial autenticado.
     */
    public function getTicketsTerritorial()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = $this->obtenerPersonaIdSesion();
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => []]);
            return;
        }
        if ($this->personaTienePanelAdminValidaciones($personaId)) {
            echo json_encode(['success' => false, 'mensaje' => 'Use el panel administrador de validaciones.', 'datos' => []]);
            return;
        }

        $capoInfo = $this->getCapoInfoForTerritorial($personaId);
        if (empty($capoInfo['departamento_id'])) {
            echo json_encode(['success' => true, 'mensaje' => 'Sin departamento/puesto de capo.', 'datos' => []]);
            return;
        }

        $gestoresIds = $this->getGestoresSubordinadosIds($personaId, (int)$capoInfo['departamento_id']);
        // Incluir al capo: tickets asignados al propio jefe territorial. Unir subordinados del organigrama (cualquier puesto en el depto).
        $gestoresIds = array_values(array_unique(array_merge([(int)$personaId], $gestoresIds)));

        $resultado = TicketDAO::getListaTickets($personaId, false, [
            'asignado_ids' => $gestoresIds,
            'categoria_gestion' => 'validaciones',
        ]);
        echo json_encode([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? ($resultado['mensaje'] ?? ''),
            'datos' => $resultado['datos'] ?? [],
        ]);
    }

    /**
     * AJAX: lista de gestores (subordinados) para el select del modal territorial.
     * Solo usa el puesto/jefatura del capo autenticado y el departamento asociado.
     */
    public function getGestoresPorCampo()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = $this->obtenerPersonaIdSesion();
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => []]);
            return;
        }
        if ($this->personaTienePanelAdminValidaciones($personaId)) {
            echo json_encode(['success' => false, 'mensaje' => 'Use el panel administrador de validaciones.', 'datos' => []]);
            return;
        }
        $capoInfo = $this->getCapoInfoForTerritorial($personaId);
        if (empty($capoInfo['departamento_id'])) {
            echo json_encode(['success' => true, 'mensaje' => 'Sin departamento/puesto de capo.', 'datos' => []]);
            return;
        }

        // Lista del modal: solo gestores operativos (es_jefe=0), no jefes territoriales ni coordinadores con bandera de jefe.
        $gestores = $this->getGestoresSubordinados($personaId, (int)$capoInfo['departamento_id'], true);
        echo json_encode(['success' => true, 'mensaje' => 'OK', 'datos' => $gestores]);
    }

    /**
     * AJAX: asignar o reasignar gestor de campo por el jefe territorial.
     * Primera asignación (sin gestor de campo previo o solo figuraba el capo): motivo opcional.
     * Cambio de un gestor subordinado a otro: motivo obligatorio; se guarda en tabla asignacion_ticket_motivo.
     */
    public function reasignarGestorTicketTerritorial()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = $this->obtenerPersonaIdSesion();
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        if ($this->personaTienePanelAdminValidaciones($personaId)) {
            echo json_encode(['success' => false, 'mensaje' => 'Use el panel administrador de validaciones.']);
            return;
        }

        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        $idPersonaGestor = (int)($datos['id_persona'] ?? ($datos['id_gestor'] ?? 0));
        $motivo = trim((string)($datos['motivo'] ?? ''));

        if ($idTicket < 1 || $idPersonaGestor < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de ticket y gestor requeridos.']);
            return;
        }

        $capoInfo = $this->getCapoInfoForTerritorial($personaId);
        if (empty($capoInfo['departamento_id'])) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso de jefe territorial.']);
            return;
        }

        // Validar que el gestor seleccionado sea subordinado del capo en ese departamento.
        $gestoresIds = $this->getGestoresSubordinadosIds($personaId, (int)$capoInfo['departamento_id']);
        if (!in_array($idPersonaGestor, $gestoresIds, true)) {
            echo json_encode(['success' => false, 'mensaje' => 'El gestor seleccionado no está permitido para su territorio.']);
            return;
        }

        $idAsignadoActivo = TicketDAO::getIdPersonaAsignadaActivaPorTicket($idTicket);
        $esPrimeraAsignacionGestor = ($idAsignadoActivo < 1) || ($idAsignadoActivo === $personaId);
        if (!$esPrimeraAsignacionGestor && $motivo === '') {
            echo json_encode(['success' => false, 'mensaje' => 'En una reasignación debe escribir el motivo del cambio.']);
            return;
        }

        // Validar que sea categoría validaciones.
        try {
            $db = new Database();
            $rowCat = $db->queryOne(
                "SELECT COALESCE(NULLIF(TRIM(categoria_gestion),''), 'sabueso') AS categoria_gestion
                 FROM ticket
                 WHERE id_ticket = :id_ticket AND (activo = 1 OR activo IS NULL) LIMIT 1",
                ['id_ticket' => $idTicket]
            );
            $categoriaGestion = $rowCat && isset($rowCat['categoria_gestion']) ? strtolower(trim((string)$rowCat['categoria_gestion'])) : '';
            if ($categoriaGestion !== 'validaciones') {
                echo json_encode(['success' => false, 'mensaje' => 'Ticket no pertenece a validaciones.']);
                return;
            }
        } catch (\Exception $e) {
            // no bloquear si falla, pero preferimos bloquear
            echo json_encode(['success' => false, 'mensaje' => 'Error validando categoría.']);
            return;
        }

        // Reasignación (guarda tiempos en asignacion_ticket).
        $resultadoAsig = TicketDAO::asignar($idTicket, $idPersonaGestor);
        if (!($resultadoAsig['success'] ?? false)) {
            echo json_encode(['success' => false, 'mensaje' => $resultadoAsig['mensaje'] ?? 'Error al reasignar.']);
            return;
        }
        $idAsignacion = (int)($resultadoAsig['datos']['id_asignacion'] ?? 0);
        if ($motivo !== '' && $idAsignacion < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Error: no se pudo obtener el id de la asignación para guardar el motivo.']);
            return;
        }

        // Motivo: tabla asignacion_ticket_motivo (script create_tabla_asignacion_ticket_motivo.php). Solo si hay texto.
        if ($motivo !== '') {
            try {
                $db = new Database();
                $campo = $capoInfo['campo'] ?: null;
                $db->CRUD(
                    "INSERT INTO asignacion_ticket_motivo (id_asignacion_ticket, id_persona_capo, id_persona_gestor, campo, motivo, fecha_creacion)
                     VALUES (:id_asig, :id_capo, :id_gestor, :campo, :motivo, :fecha)",
                    [
                        'id_asig' => $idAsignacion,
                        'id_capo' => $personaId,
                        'id_gestor' => $idPersonaGestor,
                        'campo' => $campo,
                        'motivo' => mb_substr($motivo, 0, 5000),
                        'fecha' => date('Y-m-d H:i:s'),
                    ]
                );
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'mensaje' => 'Error al guardar el motivo.']);
                return;
            }
        }

        $msgOk = $esPrimeraAsignacionGestor ? 'Gestor asignado correctamente.' : 'Gestor reasignado correctamente.';
        echo json_encode(['success' => true, 'mensaje' => $msgOk]);
    }

    private function obtenerPersonaIdSesion(): int
    {
        return (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
    }

    /** Panel admin de validaciones (config_panel_usuario): no debe usar vistas gestor/territorial ni sus APIs. */
    private function personaTienePanelAdminValidaciones(int $personaId): bool
    {
        if ($personaId < 1) {
            return false;
        }
        $paneles = ConfigPanelUsuarioDAO::getPanelesPorPersona($personaId);

        return in_array('sabueso_panel_validaciones', $paneles, true);
    }

    /**
     * Obtiene información básica del capo territorial autenticado:
     * - nombre
     * - campo (1_7 | 8_21)
     * - departamento_id
     */
    private function getCapoInfoForTerritorial(int $personaId): array
    {
        if ($personaId < 1) return ['nombre' => '', 'campo' => '', 'departamento_id' => 0];
        try {
            $db = new Database();
            // Buscar un puesto de jefe territorial (es_jefe=1) asignado a la persona.
            $row = $db->queryOne(
                "SELECT pp.nombre AS puesto_nombre, pp.departamento_id AS departamento_id
                 FROM asigna_puesto ap
                 INNER JOIN puesto pp ON pp.id = ap.id_puesto
                 WHERE ap.id_persona = :id_persona
                   AND pp.es_jefe = 1
                   AND (ap.activo = 1 OR ap.activo IS NULL)
                 ORDER BY pp.departamento_id ASC, pp.nivel ASC
                 LIMIT 1",
                ['id_persona' => $personaId]
            );
            $puestoNombre = $row && isset($row['puesto_nombre']) ? strtolower(trim((string)$row['puesto_nombre'])) : '';
            $campo = '';
            if ($puestoNombre !== '') {
                if (strpos($puestoNombre, '1_7') !== false) $campo = '1_7';
                elseif (strpos($puestoNombre, '8_21') !== false) $campo = '8_21';
            }
            // Fallback por patrón en nombre
            if ($campo === '' && $puestoNombre !== '') {
                if (preg_match('/1\s*[-_ ]?\s*7/', $puestoNombre)) $campo = '1_7';
                else if (preg_match('/8\s*[-_ ]?\s*21/', $puestoNombre)) $campo = '8_21';
            }

            $departamentoId = $row && isset($row['departamento_id']) ? (int)$row['departamento_id'] : 0;

            $nombre = '';
            try {
                $n = $db->queryOne(
                    "SELECT CONCAT(TRIM(IFNULL(nombres,'')), ' ', TRIM(IFNULL(apellidop,''))) AS nombre
                     FROM persona WHERE id = :id LIMIT 1",
                    ['id' => $personaId]
                );
                $nombre = $n && isset($n['nombre']) ? trim((string)$n['nombre']) : '';
            } catch (\Exception $e) {
                $nombre = '';
            }

            return ['nombre' => $nombre, 'campo' => $campo, 'departamento_id' => $departamentoId];
        } catch (\Exception $e) {
            return ['nombre' => '', 'campo' => '', 'departamento_id' => 0];
        }
    }

    private function extractSubordinateIdsFromOrganigramaJson($organigramaJson, int $capoId): array
    {
        if (!is_array($organigramaJson)) return [];
        $ids = [];
        $nodes = $organigramaJson['subordinados'] ?? [];
        $walk = function ($arr) use (&$walk, &$ids) {
            if (!is_array($arr)) return;
            foreach ($arr as $n) {
                $id = (int)($n['id'] ?? 0);
                if ($id > 0) $ids[] = $id;
                if (isset($n['subordinados'])) {
                    $walk($n['subordinados']);
                }
            }
        };
        $walk($nodes);
        // Limpieza
        $ids = array_values(array_unique(array_filter($ids, function ($id) use ($capoId) {
            return $id > 0 && $id !== $capoId;
        })));
        return $ids;
    }

    private function getGestoresSubordinadosIds(int $capoId, int $departamentoId): array
    {
        $gestores = $this->getGestoresSubordinados($capoId, $departamentoId, false);
        return array_values(array_unique(array_map(function ($g) {
            return (int)($g['id'] ?? 0);
        }, $gestores)));
    }

    /**
     * @param bool $soloGestoresSinJefatura true = solo puestos con es_jefe=0 (listado para reasignar en modal territorial).
     */
    private function getGestoresSubordinados(int $capoId, int $departamentoId, bool $soloGestoresSinJefatura = false): array
    {
        if ($capoId < 1 || $departamentoId < 1) return [];
        // 1) Obtener jerarquía (subordinados) para ese capo y departamento.
        $resp = CapHumDAO::getConsultaPersonasJerarquia($capoId, $departamentoId);
        $datos = $resp['datos'] ?? [];
        $primera = is_array($datos) && !empty($datos) ? ($datos[0] ?? []) : [];
        $organigramaJsonStr = $primera['organigrama_json'] ?? ($primera['ORGANIGRAMA_JSON'] ?? null);
        if (!$organigramaJsonStr) return [];
        $organigramaJson = json_decode($organigramaJsonStr, true);
        if (!is_array($organigramaJson)) return [];

        $subIds = $this->extractSubordinateIdsFromOrganigramaJson($organigramaJson, $capoId);
        if (empty($subIds)) return [];

        // 2) Personas del organigrama con puesto activo en ese departamento (gestores o jefes de equipo; antes solo es_jefe=0 y ocultaba asignaciones reales).
        try {
            $db = new Database();
            $params = ['dep' => $departamentoId];
            $placeholders = [];
            foreach ($subIds as $i => $id) {
                $key = 'id_' . $i;
                $placeholders[] = ':' . $key;
                $params[$key] = (int)$id;
            }

            $sqlGestor = $soloGestoresSinJefatura ? ' AND pu.es_jefe = 0' : '';
            $rows = $db->queryAll(
                "SELECT DISTINCT p.id,
                        CONCAT_WS(' ', p.nombres, p.apellidop, p.apellidom) AS nombre_completo
                 FROM persona p
                 INNER JOIN asigna_puesto ap ON ap.id_persona = p.id AND (ap.activo = 1 OR ap.activo IS NULL)
                 INNER JOIN puesto pu ON pu.id = ap.id_puesto
                 WHERE p.estatus != 'Baja'
                   AND pu.departamento_id = :dep
                   AND p.id IN (" . implode(',', $placeholders) . ")" . $sqlGestor . "
                 ORDER BY nombre_completo ASC",
                $params
            );
            $out = [];
            foreach (is_array($rows) ? $rows : [] as $r) {
                $id = (int)($r['id'] ?? 0);
                if ($id < 1) continue;
                $out[] = [
                    'id' => $id,
                    'nombre_completo' => trim((string)($r['nombre_completo'] ?? '')),
                ];
            }
            return $out;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Pantalla para agregar/editar preguntas de un formulario (id en URL).
     */
    public function formulario($id)
    {
        $idFormulario = (int) $id;
        $esModal = isset($_GET['modal']) && (int) $_GET['modal'] === 1;
        $esReadOnly = isset($_GET['readonly']) && (int) $_GET['readonly'] === 1;
        if ($idFormulario < 1) {
            header('Location: /validaciones/paneladmin');
            exit;
        }
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            header('Location: /');
            exit;
        }
        $formulario = FormularioDAO::obtenerPorId($idFormulario);
        if (!$formulario) {
            header('Location: /validaciones/paneladmin');
            exit;
        }
        $nombre = $formulario['nombre'] ?? 'Formulario';
        $descripcion = $formulario['descripcion'] ?? '';
        $this->set('idFormulario', $idFormulario);
        $this->set('nombreFormulario', $nombre);
        $this->set('descripcionFormulario', $descripcion);
        $this->set('formBuilderReadOnly', $esReadOnly);
        $this->set('mostrarFormularios', true);
        $this->set('titulo', 'Form Builder: ' . $nombre);
        $builderJsVer = @filemtime(dirname(RAIZ) . '/public/assets/js/form_builder_validacion.js') ?: time();
        if ($esModal) {
            $this->set('formBuilderEmbed', true);
            $this->set('formBuilderJsVer', $builderJsVer);
            $this->render('validaciones_formulario', true);
            return;
        }
        $this->set(
            'script',
            '<script>window.FORM_BUILDER_FORMULARIO_ID=' . $idFormulario . ';window.FORM_BUILDER_TITULO=' . json_encode($nombre, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) . ';window.FORM_BUILDER_DESCRIPCION=' . json_encode($descripcion, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) . ';</script>'
            . '<script src="/assets/js/form_builder_validacion.js?v=' . $builderJsVer . '"></script>'
        );
        $this->render('validaciones_formulario');
    }

    /**
     * GET lista de formularios (para el modal "Elegir formulario").
     */
    public function getFormularios()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => []]);
            return;
        }
        try {
            $datos = FormularioDAO::listar($personaId);
            echo json_encode(['success' => true, 'mensaje' => 'OK', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al listar.', 'datos' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST crear formulario (body JSON: nombre).
     */
    public function guardarFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        $nombre = isset($datos['nombre']) ? trim((string) $datos['nombre']) : '';
        if ($nombre === '') {
            echo json_encode(['success' => false, 'mensaje' => 'El nombre es obligatorio.']);
            return;
        }
        $r = FormularioDAO::crear($nombre, $personaId);
        echo json_encode($r);
    }

    /**
     * POST actualizar formulario (body JSON: id, nombre, descripcion).
     */
    public function actualizarFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        $id = (int) ($datos['id'] ?? 0);
        $nombre = isset($datos['nombre']) ? trim((string) $datos['nombre']) : '';
        $descripcion = isset($datos['descripcion']) ? trim((string) $datos['descripcion']) : '';
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $r = FormularioDAO::actualizar($id, $nombre, $descripcion, $personaId);
        echo json_encode($r);
    }

    /**
     * POST habilitar/inhabilitar formulario (body JSON: id, activo).
     */
    public function toggleFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        $id = (int) ($datos['id'] ?? 0);
        $activo = isset($datos['activo']) ? (int) $datos['activo'] : 0;
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $r = FormularioDAO::toggleActivo($id, $activo, $personaId);
        echo json_encode($r);
    }

    /**
     * POST eliminar formulario (body JSON: id).
     */
    public function eliminarFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        $id = (int) ($datos['id'] ?? 0);
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $r = FormularioDAO::eliminar($id, $personaId);
        echo json_encode($r);
    }

    /**
     * GET lista de preguntas para el panel Formularios (predefinidas + personalizadas).
     * Query opcional: id_formulario para filtrar por formulario.
     */
    public function getPreguntasFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => []]);
            return;
        }
        $idFormulario = isset($_GET['id_formulario']) ? (int) $_GET['id_formulario'] : null;
        if ($idFormulario !== null && $idFormulario < 1) {
            $idFormulario = null;
        }
        try {
            $datos = PreguntaDAO::listarParaPanel($personaId, $idFormulario);
            echo json_encode(['success' => true, 'mensaje' => 'OK', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al listar.', 'datos' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST guardar o actualizar una pregunta (body JSON).
     */
    public function guardarPreguntaFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        if (!is_array($datos)) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos.']);
            return;
        }
        $r = PreguntaDAO::guardar($datos, $personaId);
        echo json_encode($r);
    }

    /**
     * POST marcar/desmarcar pregunta (body JSON: id, activa).
     */
    public function togglePreguntaFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        $id = (int) ($datos['id'] ?? 0);
        $activa = isset($datos['activa']) ? (int) $datos['activa'] : 0;
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de pregunta inválido.']);
            return;
        }
        $r = PreguntaDAO::toggleActiva($id, $activa, $personaId);
        echo json_encode($r);
    }

    /**
     * POST eliminar pregunta personalizada (body JSON: id).
     */
    public function eliminarPreguntaFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        $id = (int) ($datos['id'] ?? 0);
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de pregunta inválido.']);
            return;
        }
        $r = PreguntaDAO::eliminar($id, $personaId);
        echo json_encode($r);
    }
}
