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
 */
return [
    /** @var int[] IDs de puesto Sparta: titulares elegibles al asignar en Campo 1–7 */
    'puestos_jefe_campo_1_7' => [],

    /** @var int[] IDs de puesto Sparta: titulares elegibles en Campo 8–21 */
    'puestos_jefe_campo_8_21' => [],

    /** @var int[] Opcional: IDs de persona fijos (ignora puesto) para 1–7 */
    'personas_campo_1_7' => [],

    /** @var int[] Opcional: IDs de persona fijos para 8–21 */
    'personas_campo_8_21' => [],
];
