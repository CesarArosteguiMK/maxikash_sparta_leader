<?php

/**
 * Asignación de tickets (Validaciones): líderes por segmento Campo 1–7 / 8–21.
 *
 * Debe coincidir con quienes son “máximo rango” en organigrama para cada segmento.
 * En phpMyAdmin: puesto.departamento_id = 5 (Sabueso), copie los id de puesto
 * de los cargos directivos de 1–7 y de 8–21.
 *
 * Si deja los arrays vacíos, solo se usarán jefes (es_jefe=1) cuyo nombre de puesto
 * contenga texto reconocible (1–7, 8–21, b) 1 a 7, etc.).
 *
 * Organigrama: si existen departamentos "Campo 1-7" y "Campo 8-21", indique sus id abajo.
 * Así la lista de personas será por segmento y el árbol se cargará desde Sabueso (no solo un nodo).
 */
return [
    /** @var int Departamento Sabueso (jerarquía real para el árbol del organigrama). */
    'id_departamento_sabueso' => 5,

    /** @var int|null ID del departamento "Campo 1-7" en tabla departamento. Si existe, organigrama usa lista por segmento y árbol Sabueso. Consulta: SELECT id, nombre FROM departamento ORDER BY nombre; */
    'id_departamento_campo_1_7' => null,

    /** @var int|null ID del departamento "Campo 8-21" en tabla departamento. */
    'id_departamento_campo_8_21' => null,

    /** @var int[] IDs de puesto Sparta: titulares elegibles en Campo 1–7 */
    'puestos_jefe_campo_1_7' => [],

    /** @var int[] IDs de puesto Sparta: titulares elegibles en Campo 8–21 */
    'puestos_jefe_campo_8_21' => [],

    /** @var int[] Opcional: IDs de persona fijos (ignora puesto) para 1–7 */
    'personas_campo_1_7' => [],

    /** @var int[] Opcional: IDs de persona fijos para 8–21 */
    'personas_campo_8_21' => [],
];
