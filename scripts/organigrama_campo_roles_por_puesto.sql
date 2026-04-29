-- Organigrama Campo 1-7 / 8-21: columnas supervisor, subgerente, gerente, subdirector
-- según el TEXTO del puesto real de cada jefe en la cadena (s1→s4), no por nivel fijo.
--
-- >>> DBeaver: NO uses la barra "Filter" / filtro de filas del grid para esta consulta.
--     Esa barra NO ejecuta un SELECT completo; si pegas un WHERE suelta obtendras 1064.
--     Abre este .sql en el editor SQL, Ctrl+A, ejecuta (Ctrl+Enter con todo seleccionado).
--
-- Error 1064 "near 'WHERE p.estatus...' at line 1" (verificado con MySQL remoto vía PHP):
-- Ese mensaje aparece si el cliente envía SOLO el bloque WHERE (sin SELECT...FROM delante).
-- Con la consulta COMPLETA, el motor no devuelve 1064; falla con otro código si falta la tabla
-- (p. ej. 1146 "Table ... doesn't exist"). Comprueba: php scripts/test_organigrama_sql_explain.php
-- y opcional: php scripts/test_organigrama_sql_explain.php both --demo-1064
--
-- Cómo ejecutar sin trocear:
--  - DBeaver: "Execute SQL script" (todo el archivo), no "Execute SQL statement" con el cursor
--    en medio de la subconsulta; o selecciona TODO el texto (Ctrl+A) y ejecuta el script.
--  - Línea de órdenes: mysql -h ... -u ... -p esquema < organigrama_campo_roles_por_puesto.sql
--
-- Alternativa sin subconsulta envolvente (misma lógica): organigrama_campo_roles_por_puesto_flat.sql
--
-- Prueba rápida de subconsulta (debe funcionar):
--   SELECT * FROM (SELECT 1 AS x) AS t;

SELECT
    b.external_id,
    b.nombre_completo,
    b.estatus,
    b.es_gestor,
    b.puesto_legacy,
    b.puesto_actual,
    b.departamento,

    COALESCE(
        CASE WHEN b.s1_puesto LIKE '%supervisor%' THEN b.s1_nombre END,
        CASE WHEN b.s2_puesto LIKE '%supervisor%' THEN b.s2_nombre END,
        CASE WHEN b.s3_puesto LIKE '%supervisor%' THEN b.s3_nombre END,
        CASE WHEN b.s4_puesto LIKE '%supervisor%' THEN b.s4_nombre END,
        ''
    ) AS supervisor,

    COALESCE(
        CASE WHEN b.s1_puesto LIKE '%supervisor%' THEN b.s1_estatus END,
        CASE WHEN b.s2_puesto LIKE '%supervisor%' THEN b.s2_estatus END,
        CASE WHEN b.s3_puesto LIKE '%supervisor%' THEN b.s3_estatus END,
        CASE WHEN b.s4_puesto LIKE '%supervisor%' THEN b.s4_estatus END,
        ''
    ) AS supervisor_estatus,

    COALESCE(
        CASE WHEN b.s1_puesto LIKE '%subgerente%' THEN b.s1_nombre END,
        CASE WHEN b.s2_puesto LIKE '%subgerente%' THEN b.s2_nombre END,
        CASE WHEN b.s3_puesto LIKE '%subgerente%' THEN b.s3_nombre END,
        CASE WHEN b.s4_puesto LIKE '%subgerente%' THEN b.s4_nombre END,
        ''
    ) AS subgerente,

    COALESCE(
        CASE WHEN b.s1_puesto LIKE '%subgerente%' THEN b.s1_estatus END,
        CASE WHEN b.s2_puesto LIKE '%subgerente%' THEN b.s2_estatus END,
        CASE WHEN b.s3_puesto LIKE '%subgerente%' THEN b.s3_estatus END,
        CASE WHEN b.s4_puesto LIKE '%subgerente%' THEN b.s4_estatus END,
        ''
    ) AS subgerente_estatus,

    COALESCE(
        CASE WHEN b.s1_puesto LIKE '%gerente%' AND b.s1_puesto NOT LIKE '%subgerente%' THEN b.s1_nombre END,
        CASE WHEN b.s2_puesto LIKE '%gerente%' AND b.s2_puesto NOT LIKE '%subgerente%' THEN b.s2_nombre END,
        CASE WHEN b.s3_puesto LIKE '%gerente%' AND b.s3_puesto NOT LIKE '%subgerente%' THEN b.s3_nombre END,
        CASE WHEN b.s4_puesto LIKE '%gerente%' AND b.s4_puesto NOT LIKE '%subgerente%' THEN b.s4_nombre END,
        ''
    ) AS gerente,

    COALESCE(
        CASE WHEN b.s1_puesto LIKE '%gerente%' AND b.s1_puesto NOT LIKE '%subgerente%' THEN b.s1_estatus END,
        CASE WHEN b.s2_puesto LIKE '%gerente%' AND b.s2_puesto NOT LIKE '%subgerente%' THEN b.s2_estatus END,
        CASE WHEN b.s3_puesto LIKE '%gerente%' AND b.s3_puesto NOT LIKE '%subgerente%' THEN b.s3_estatus END,
        CASE WHEN b.s4_puesto LIKE '%gerente%' AND b.s4_puesto NOT LIKE '%subgerente%' THEN b.s4_estatus END,
        ''
    ) AS gerente_estatus,

    COALESCE(
        CASE WHEN b.s1_puesto LIKE '%subdirector%' THEN b.s1_nombre END,
        CASE WHEN b.s2_puesto LIKE '%subdirector%' THEN b.s2_nombre END,
        CASE WHEN b.s3_puesto LIKE '%subdirector%' THEN b.s3_nombre END,
        CASE WHEN b.s4_puesto LIKE '%subdirector%' THEN b.s4_nombre END,
        ''
    ) AS subdirector,

    COALESCE(
        CASE WHEN b.s1_puesto LIKE '%subdirector%' THEN b.s1_estatus END,
        CASE WHEN b.s2_puesto LIKE '%subdirector%' THEN b.s2_estatus END,
        CASE WHEN b.s3_puesto LIKE '%subdirector%' THEN b.s3_estatus END,
        CASE WHEN b.s4_puesto LIKE '%subdirector%' THEN b.s4_estatus END,
        ''
    ) AS subdirector_estatus

FROM (
    SELECT
        p.numero_empleado AS external_id,
        TRIM(CONCAT_WS(' ', p.nombres, NULLIF(p.segundo_nombre, ''), p.apellidop, NULLIF(p.apellidom, ''))) AS nombre_completo,
        CASE
            WHEN p.estatus = 'Baja' THEN 'baja'
            WHEN av_p.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_p.razon_ausencia))
            WHEN p.estatus = 'Activo' THEN 'activo'
            ELSE LOWER(TRIM(COALESCE(p.estatus, '')))
        END AS estatus,
        CASE WHEN pl_self.clave = 'gestor' THEN 'Si' ELSE 'No' END AS es_gestor,
        pl_self.clave AS puesto_legacy,
        pu_self.nombre AS puesto_actual,
        d.nombre AS departamento,

        COALESCE(TRIM(CONCAT_WS(' ', s1.nombres, NULLIF(s1.segundo_nombre, ''), s1.apellidop, NULLIF(s1.apellidom, ''))), '') AS s1_nombre,
        CASE
            WHEN s1.id IS NULL THEN ''
            WHEN s1.estatus = 'Baja' THEN 'baja'
            WHEN av_s1.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s1.razon_ausencia))
            WHEN s1.estatus = 'Activo' THEN 'activo'
            ELSE LOWER(TRIM(COALESCE(s1.estatus, '')))
        END AS s1_estatus,
        LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) AS s1_puesto,

        COALESCE(TRIM(CONCAT_WS(' ', s2.nombres, NULLIF(s2.segundo_nombre, ''), s2.apellidop, NULLIF(s2.apellidom, ''))), '') AS s2_nombre,
        CASE
            WHEN s2.id IS NULL THEN ''
            WHEN s2.estatus = 'Baja' THEN 'baja'
            WHEN av_s2.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s2.razon_ausencia))
            WHEN s2.estatus = 'Activo' THEN 'activo'
            ELSE LOWER(TRIM(COALESCE(s2.estatus, '')))
        END AS s2_estatus,
        LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) AS s2_puesto,

        COALESCE(TRIM(CONCAT_WS(' ', s3.nombres, NULLIF(s3.segundo_nombre, ''), s3.apellidop, NULLIF(s3.apellidom, ''))), '') AS s3_nombre,
        CASE
            WHEN s3.id IS NULL THEN ''
            WHEN s3.estatus = 'Baja' THEN 'baja'
            WHEN av_s3.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s3.razon_ausencia))
            WHEN s3.estatus = 'Activo' THEN 'activo'
            ELSE LOWER(TRIM(COALESCE(s3.estatus, '')))
        END AS s3_estatus,
        LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) AS s3_puesto,

        COALESCE(TRIM(CONCAT_WS(' ', s4.nombres, NULLIF(s4.segundo_nombre, ''), s4.apellidop, NULLIF(s4.apellidom, ''))), '') AS s4_nombre,
        CASE
            WHEN s4.id IS NULL THEN ''
            WHEN s4.estatus = 'Baja' THEN 'baja'
            WHEN av_s4.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s4.razon_ausencia))
            WHEN s4.estatus = 'Activo' THEN 'activo'
            ELSE LOWER(TRIM(COALESCE(s4.estatus, '')))
        END AS s4_estatus,
        LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) AS s4_puesto

    FROM persona p

    LEFT JOIN (
        SELECT a.id_persona, ra.nombre AS razon_ausencia
        FROM ausencia a
        INNER JOIN razon_ausencia ra ON ra.id = a.id_razon
        INNER JOIN (
            SELECT id_persona, MIN(id) AS min_id
            FROM ausencia
            WHERE activo = 1
              AND NOW() BETWEEN fecha_inicio AND fecha_fin
            GROUP BY id_persona
        ) pick ON pick.min_id = a.id
    ) av_p ON av_p.id_persona = p.id

    LEFT JOIN (
        SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
        FROM asigna_puesto ap
        WHERE ap.activo = 1
        GROUP BY ap.id_persona
    ) pu_one ON pu_one.id_persona = p.id
    LEFT JOIN puesto pu_self ON pu_self.id = pu_one.id_puesto
    LEFT JOIN equivalencias_legacy_puestos el_self ON el_self.id_puesto = pu_self.id
    LEFT JOIN puestos_legacy pl_self ON pl_self.id = el_self.id_puesto_legacy
    LEFT JOIN departamento d ON d.id = pu_self.departamento_id

    LEFT JOIN (
        SELECT id_persona, MIN(id_jefe) AS id_jefe
        FROM asigna_jefe
        GROUP BY id_persona
    ) j1 ON j1.id_persona = p.id
    LEFT JOIN persona s1 ON s1.id = j1.id_jefe AND s1.estatus <> 'Baja'
    LEFT JOIN (
        SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
        FROM asigna_puesto ap
        WHERE ap.activo = 1
        GROUP BY ap.id_persona
    ) pa_s1 ON pa_s1.id_persona = s1.id
    LEFT JOIN puesto pu_s1 ON pu_s1.id = pa_s1.id_puesto
    LEFT JOIN equivalencias_legacy_puestos el_s1 ON el_s1.id_puesto = pu_s1.id
    LEFT JOIN puestos_legacy pl_s1 ON pl_s1.id = el_s1.id_puesto_legacy
    LEFT JOIN (
        SELECT a.id_persona, ra.nombre AS razon_ausencia
        FROM ausencia a
        INNER JOIN razon_ausencia ra ON ra.id = a.id_razon
        INNER JOIN (
            SELECT id_persona, MIN(id) AS min_id
            FROM ausencia
            WHERE activo = 1
              AND NOW() BETWEEN fecha_inicio AND fecha_fin
            GROUP BY id_persona
        ) pick ON pick.min_id = a.id
    ) av_s1 ON av_s1.id_persona = s1.id

    LEFT JOIN (
        SELECT id_persona, MIN(id_jefe) AS id_jefe
        FROM asigna_jefe
        GROUP BY id_persona
    ) j2 ON j2.id_persona = j1.id_jefe
    LEFT JOIN persona s2 ON s2.id = j2.id_jefe AND s2.estatus <> 'Baja'
    LEFT JOIN (
        SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
        FROM asigna_puesto ap
        WHERE ap.activo = 1
        GROUP BY ap.id_persona
    ) pa_s2 ON pa_s2.id_persona = s2.id
    LEFT JOIN puesto pu_s2 ON pu_s2.id = pa_s2.id_puesto
    LEFT JOIN equivalencias_legacy_puestos el_s2 ON el_s2.id_puesto = pu_s2.id
    LEFT JOIN puestos_legacy pl_s2 ON pl_s2.id = el_s2.id_puesto_legacy
    LEFT JOIN (
        SELECT a.id_persona, ra.nombre AS razon_ausencia
        FROM ausencia a
        INNER JOIN razon_ausencia ra ON ra.id = a.id_razon
        INNER JOIN (
            SELECT id_persona, MIN(id) AS min_id
            FROM ausencia
            WHERE activo = 1
              AND NOW() BETWEEN fecha_inicio AND fecha_fin
            GROUP BY id_persona
        ) pick ON pick.min_id = a.id
    ) av_s2 ON av_s2.id_persona = s2.id

    LEFT JOIN (
        SELECT id_persona, MIN(id_jefe) AS id_jefe
        FROM asigna_jefe
        GROUP BY id_persona
    ) j3 ON j3.id_persona = j2.id_jefe
    LEFT JOIN persona s3 ON s3.id = j3.id_jefe AND s3.estatus <> 'Baja'
    LEFT JOIN (
        SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
        FROM asigna_puesto ap
        WHERE ap.activo = 1
        GROUP BY ap.id_persona
    ) pa_s3 ON pa_s3.id_persona = s3.id
    LEFT JOIN puesto pu_s3 ON pu_s3.id = pa_s3.id_puesto
    LEFT JOIN equivalencias_legacy_puestos el_s3 ON el_s3.id_puesto = pu_s3.id
    LEFT JOIN puestos_legacy pl_s3 ON pl_s3.id = el_s3.id_puesto_legacy
    LEFT JOIN (
        SELECT a.id_persona, ra.nombre AS razon_ausencia
        FROM ausencia a
        INNER JOIN razon_ausencia ra ON ra.id = a.id_razon
        INNER JOIN (
            SELECT id_persona, MIN(id) AS min_id
            FROM ausencia
            WHERE activo = 1
              AND NOW() BETWEEN fecha_inicio AND fecha_fin
            GROUP BY id_persona
        ) pick ON pick.min_id = a.id
    ) av_s3 ON av_s3.id_persona = s3.id

    LEFT JOIN (
        SELECT id_persona, MIN(id_jefe) AS id_jefe
        FROM asigna_jefe
        GROUP BY id_persona
    ) j4 ON j4.id_persona = j3.id_jefe
    LEFT JOIN persona s4 ON s4.id = j4.id_jefe AND s4.estatus <> 'Baja'
    LEFT JOIN (
        SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
        FROM asigna_puesto ap
        WHERE ap.activo = 1
        GROUP BY ap.id_persona
    ) pa_s4 ON pa_s4.id_persona = s4.id
    LEFT JOIN puesto pu_s4 ON pu_s4.id = pa_s4.id_puesto
    LEFT JOIN equivalencias_legacy_puestos el_s4 ON el_s4.id_puesto = pu_s4.id
    LEFT JOIN puestos_legacy pl_s4 ON pl_s4.id = el_s4.id_puesto_legacy
    LEFT JOIN (
        SELECT a.id_persona, ra.nombre AS razon_ausencia
        FROM ausencia a
        INNER JOIN razon_ausencia ra ON ra.id = a.id_razon
        INNER JOIN (
            SELECT id_persona, MIN(id) AS min_id
            FROM ausencia
            WHERE activo = 1
              AND NOW() BETWEEN fecha_inicio AND fecha_fin
            GROUP BY id_persona
        ) pick ON pick.min_id = a.id
    ) av_s4 ON av_s4.id_persona = s4.id

    /* NO ejecutar SOLO desde aqui: hace falta TODO el archivo desde el SELECT de la linea ~20.
       Si tu editor solo muestra WHERE... ) AS b, falta el SELECT externo y el FROM ( ... */
    WHERE p.estatus = 'Activo'
      AND UPPER(TRIM(COALESCE(p.user_name, ''))) <> 'REPORTERIA'
      AND (
          d.nombre LIKE 'Campo 1-7%'
          OR d.nombre LIKE 'Campo 8-21%'
      )
) AS b
ORDER BY b.external_id;
