<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Preguntas del formulario de validaciones: predefinidas (globales) y personalizadas.
 * activa = 1/0 para marcar o desmarcar en el cuestionario.
 */
class FormularioValidacionPregunta extends Model
{
    /** Tipos de pregunta permitidos */
    const TIPOS = ['abierta', 'cerrada', 'multiple', 'si_no', 'escala', 'fecha', 'numero'];

    /**
     * Lista predefinidas + personalizadas para armar el cuestionario.
     * Incluye solo activa=1, ordenadas por es_predefinida DESC, orden ASC.
     */
    public static function listarParaFormulario(int $idPersona): array
    {
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT id, tipo, texto, opciones, indice_correcto, indices_correctos,
                       escala_min, escala_max, num_min, num_max, es_predefinida, activa, orden
                FROM formulario_validacion_pregunta
                WHERE activa = 1
                  AND (es_predefinida = 1 OR id_persona_creador = :id_persona)
                ORDER BY es_predefinida DESC, orden ASC, id ASC
            ", ['id_persona' => $idPersona]);
            return is_array($r) ? self::decodeJsonFields($r) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Lista todas las preguntas para la pantalla Formularios (predefinidas + personalizadas del usuario).
     * Incluye activa para poder marcar/desmarcar.
     */
    public static function listarParaPanel(int $idPersona): array
    {
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT id, tipo, texto, opciones, indice_correcto, indices_correctos,
                       escala_min, escala_max, num_min, num_max, es_predefinida, activa, orden, id_persona_creador, fecha_creacion
                FROM formulario_validacion_pregunta
                WHERE es_predefinida = 1 OR id_persona_creador = :id_persona
                ORDER BY es_predefinida DESC, orden ASC, id ASC
            ", ['id_persona' => $idPersona]);
            return is_array($r) ? self::decodeJsonFields($r) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Guardar o actualizar una pregunta. Si id viene en datos, actualiza; si no, inserta.
     * Personalizadas: id_persona_creador = $idPersona. Predefinidas: solo si se permite desde backend.
     */
    public static function guardar(array $datos, int $idPersona): array
    {
        $id = isset($datos['id']) ? (int) $datos['id'] : 0;
        $tipo = isset($datos['tipo']) ? trim((string) $datos['tipo']) : '';
        $texto = isset($datos['texto']) ? trim((string) $datos['texto']) : '';
        $esPredefinida = isset($datos['es_predefinida']) ? (int) $datos['es_predefinida'] : 0;

        if (!in_array($tipo, self::TIPOS, true)) {
            return self::resultado(false, 'Tipo de pregunta no válido.', null);
        }
        if ($texto === '') {
            return self::resultado(false, 'El texto de la pregunta es obligatorio.', null);
        }

        $opciones = null;
        $indiceCorrecto = null;
        $indicesCorrectos = null;
        $escalaMin = isset($datos['escala_min']) ? trim((string) $datos['escala_min']) : null;
        $escalaMax = isset($datos['escala_max']) ? trim((string) $datos['escala_max']) : null;
        $numMin = isset($datos['num_min']) ? self::parseNum($datos['num_min']) : null;
        $numMax = isset($datos['num_max']) ? self::parseNum($datos['num_max']) : null;

        if (isset($datos['opciones']) && is_array($datos['opciones'])) {
            $opciones = json_encode(array_values($datos['opciones']), JSON_UNESCAPED_UNICODE);
        }
        if (array_key_exists('indice_correcto', $datos) && $datos['indice_correcto'] !== null && $datos['indice_correcto'] !== '') {
            $indiceCorrecto = (int) $datos['indice_correcto'];
        }
        if (isset($datos['indices_correctos']) && is_array($datos['indices_correctos'])) {
            $indicesCorrectos = json_encode(array_values($datos['indices_correctos']), JSON_UNESCAPED_UNICODE);
        }

        try {
            $db = new Database();
            if ($id > 0) {
                $existente = $db->queryOne("SELECT id, es_predefinida, id_persona_creador FROM formulario_validacion_pregunta WHERE id = :id", ['id' => $id]);
                if (!$existente) {
                    return self::resultado(false, 'Pregunta no encontrada.', null);
                }
                if ((int) $existente['es_predefinida'] === 0 && (int) ($existente['id_persona_creador'] ?? 0) !== $idPersona) {
                    return self::resultado(false, 'No puede editar esta pregunta.', null);
                }
                $db->CRUD("
                    UPDATE formulario_validacion_pregunta SET
                        tipo = :tipo, texto = :texto, opciones = :opciones, indice_correcto = :indice_correcto,
                        indices_correctos = :indices_correctos, escala_min = :escala_min, escala_max = :escala_max,
                        num_min = :num_min, num_max = :num_max
                    WHERE id = :id
                ", [
                    'id' => $id,
                    'tipo' => $tipo,
                    'texto' => $texto,
                    'opciones' => $opciones,
                    'indice_correcto' => $indiceCorrecto,
                    'indices_correctos' => $indicesCorrectos,
                    'escala_min' => $escalaMin,
                    'escala_max' => $escalaMax,
                    'num_min' => $numMin,
                    'num_max' => $numMax,
                ]);
                return self::resultado(true, 'Pregunta actualizada.', ['id' => $id]);
            }

            $orden = (int) ($datos['orden'] ?? 0);
            $activa = isset($datos['activa']) ? (int) $datos['activa'] : 1;
            $idCreador = $esPredefinida ? null : $idPersona;

            $db->CRUD("
                INSERT INTO formulario_validacion_pregunta (tipo, texto, opciones, indice_correcto, indices_correctos, escala_min, escala_max, num_min, num_max, es_predefinida, activa, orden, id_persona_creador)
                VALUES (:tipo, :texto, :opciones, :indice_correcto, :indices_correctos, :escala_min, :escala_max, :num_min, :num_max, :es_predefinida, :activa, :orden, :id_persona_creador)
            ", [
                'tipo' => $tipo,
                'texto' => $texto,
                'opciones' => $opciones,
                'indice_correcto' => $indiceCorrecto,
                'indices_correctos' => $indicesCorrectos,
                'escala_min' => $escalaMin,
                'escala_max' => $escalaMax,
                'num_min' => $numMin,
                'num_max' => $numMax,
                'es_predefinida' => $esPredefinida,
                'activa' => $activa,
                'orden' => $orden,
                'id_persona_creador' => $idCreador,
            ]);
            $row = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $nuevoId = $row ? (int) $row['id'] : 0;
            return self::resultado(true, 'Pregunta guardada.', ['id' => $nuevoId]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar una pregunta personalizada (solo el creador).
     */
    public static function eliminar(int $idPregunta, int $idPersona): array
    {
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id, es_predefinida, id_persona_creador FROM formulario_validacion_pregunta WHERE id = :id", ['id' => $idPregunta]);
            if (!$row) {
                return self::resultado(false, 'Pregunta no encontrada.', null);
            }
            if ((int) $row['es_predefinida'] === 1) {
                return self::resultado(false, 'No se pueden eliminar preguntas predefinidas.', null);
            }
            if ((int) ($row['id_persona_creador'] ?? 0) !== $idPersona) {
                return self::resultado(false, 'No puede eliminar esta pregunta.', null);
            }
            $db->CRUD("DELETE FROM formulario_validacion_pregunta WHERE id = :id", ['id' => $idPregunta]);
            return self::resultado(true, 'Pregunta eliminada.', null);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar.', null, $e->getMessage());
        }
    }

    /**
     * Marcar o desmarcar una pregunta (activa = 1 o 0).
     * Personalizadas: solo el creador. Predefinidas: cualquiera puede.
     */
    public static function toggleActiva(int $idPregunta, int $activa, int $idPersona): array
    {
        $activa = $activa ? 1 : 0;
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id, es_predefinida, id_persona_creador FROM formulario_validacion_pregunta WHERE id = :id", ['id' => $idPregunta]);
            if (!$row) {
                return self::resultado(false, 'Pregunta no encontrada.', null);
            }
            $esPredefinida = (int) $row['es_predefinida'];
            $idCreador = (int) ($row['id_persona_creador'] ?? 0);
            if ($esPredefinida === 0 && $idCreador !== $idPersona) {
                return self::resultado(false, 'No puede cambiar esta pregunta.', null);
            }
            $db->CRUD("UPDATE formulario_validacion_pregunta SET activa = :activa WHERE id = :id", ['activa' => $activa, 'id' => $idPregunta]);
            return self::resultado(true, $activa ? 'Pregunta marcada.' : 'Pregunta desmarcada.', ['activa' => $activa]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', null, $e->getMessage());
        }
    }

    private static function parseNum($v)
    {
        if ($v === null || $v === '') return null;
        $n = is_numeric($v) ? $v : null;
        return $n !== null ? (float) $n : null;
    }

    private static function decodeJsonFields(array $rows): array
    {
        foreach ($rows as &$r) {
            if (!empty($r['opciones'])) {
                $dec = @json_decode($r['opciones'], true);
                $r['opciones'] = is_array($dec) ? $dec : [];
            }
            if (!empty($r['indices_correctos'])) {
                $dec = @json_decode($r['indices_correctos'], true);
                $r['indices_correctos'] = is_array($dec) ? $dec : [];
            }
        }
        return $rows;
    }
}
