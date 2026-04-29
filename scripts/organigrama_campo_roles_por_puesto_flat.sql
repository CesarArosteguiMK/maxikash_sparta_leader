-- Misma lógica que organigrama_campo_roles_por_puesto.sql pero SIN subconsulta envolvente
-- FROM ( SELECT ... ) AS b
--
-- DBeaver: NO pegues esta consulta en la barra "Filter" del grid; usa el editor SQL,
-- selecciona TODO (Ctrl+A) y ejecuta. Un WHERE suelto siempre da error 1064.
--
-- Clasificación por texto de puesto real (Sparta + legacy) en la cadena s1→s4.

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
    LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) AS s4_puesto,

    COALESCE(
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) LIKE '%supervisor%' THEN COALESCE(TRIM(CONCAT_WS(' ', s1.nombres, NULLIF(s1.segundo_nombre, ''), s1.apellidop, NULLIF(s1.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) LIKE '%supervisor%' THEN COALESCE(TRIM(CONCAT_WS(' ', s2.nombres, NULLIF(s2.segundo_nombre, ''), s2.apellidop, NULLIF(s2.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) LIKE '%supervisor%' THEN COALESCE(TRIM(CONCAT_WS(' ', s3.nombres, NULLIF(s3.segundo_nombre, ''), s3.apellidop, NULLIF(s3.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) LIKE '%supervisor%' THEN COALESCE(TRIM(CONCAT_WS(' ', s4.nombres, NULLIF(s4.segundo_nombre, ''), s4.apellidop, NULLIF(s4.apellidom, ''))), '') END,
        ''
    ) AS supervisor,

    COALESCE(
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) LIKE '%supervisor%' THEN CASE WHEN s1.id IS NULL THEN '' WHEN s1.estatus = 'Baja' THEN 'baja' WHEN av_s1.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s1.razon_ausencia)) WHEN s1.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s1.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) LIKE '%supervisor%' THEN CASE WHEN s2.id IS NULL THEN '' WHEN s2.estatus = 'Baja' THEN 'baja' WHEN av_s2.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s2.razon_ausencia)) WHEN s2.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s2.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) LIKE '%supervisor%' THEN CASE WHEN s3.id IS NULL THEN '' WHEN s3.estatus = 'Baja' THEN 'baja' WHEN av_s3.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s3.razon_ausencia)) WHEN s3.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s3.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) LIKE '%supervisor%' THEN CASE WHEN s4.id IS NULL THEN '' WHEN s4.estatus = 'Baja' THEN 'baja' WHEN av_s4.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s4.razon_ausencia)) WHEN s4.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s4.estatus, ''))) END END,
        ''
    ) AS supervisor_estatus,

    COALESCE(
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) LIKE '%subgerente%' THEN COALESCE(TRIM(CONCAT_WS(' ', s1.nombres, NULLIF(s1.segundo_nombre, ''), s1.apellidop, NULLIF(s1.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) LIKE '%subgerente%' THEN COALESCE(TRIM(CONCAT_WS(' ', s2.nombres, NULLIF(s2.segundo_nombre, ''), s2.apellidop, NULLIF(s2.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) LIKE '%subgerente%' THEN COALESCE(TRIM(CONCAT_WS(' ', s3.nombres, NULLIF(s3.segundo_nombre, ''), s3.apellidop, NULLIF(s3.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) LIKE '%subgerente%' THEN COALESCE(TRIM(CONCAT_WS(' ', s4.nombres, NULLIF(s4.segundo_nombre, ''), s4.apellidop, NULLIF(s4.apellidom, ''))), '') END,
        ''
    ) AS subgerente,

    COALESCE(
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) LIKE '%subgerente%' THEN CASE WHEN s1.id IS NULL THEN '' WHEN s1.estatus = 'Baja' THEN 'baja' WHEN av_s1.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s1.razon_ausencia)) WHEN s1.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s1.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) LIKE '%subgerente%' THEN CASE WHEN s2.id IS NULL THEN '' WHEN s2.estatus = 'Baja' THEN 'baja' WHEN av_s2.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s2.razon_ausencia)) WHEN s2.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s2.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) LIKE '%subgerente%' THEN CASE WHEN s3.id IS NULL THEN '' WHEN s3.estatus = 'Baja' THEN 'baja' WHEN av_s3.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s3.razon_ausencia)) WHEN s3.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s3.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) LIKE '%subgerente%' THEN CASE WHEN s4.id IS NULL THEN '' WHEN s4.estatus = 'Baja' THEN 'baja' WHEN av_s4.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s4.razon_ausencia)) WHEN s4.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s4.estatus, ''))) END END,
        ''
    ) AS subgerente_estatus,

    COALESCE(
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) LIKE '%gerente%' AND LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) NOT LIKE '%subgerente%' THEN COALESCE(TRIM(CONCAT_WS(' ', s1.nombres, NULLIF(s1.segundo_nombre, ''), s1.apellidop, NULLIF(s1.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) LIKE '%gerente%' AND LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) NOT LIKE '%subgerente%' THEN COALESCE(TRIM(CONCAT_WS(' ', s2.nombres, NULLIF(s2.segundo_nombre, ''), s2.apellidop, NULLIF(s2.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) LIKE '%gerente%' AND LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) NOT LIKE '%subgerente%' THEN COALESCE(TRIM(CONCAT_WS(' ', s3.nombres, NULLIF(s3.segundo_nombre, ''), s3.apellidop, NULLIF(s3.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) LIKE '%gerente%' AND LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) NOT LIKE '%subgerente%' THEN COALESCE(TRIM(CONCAT_WS(' ', s4.nombres, NULLIF(s4.segundo_nombre, ''), s4.apellidop, NULLIF(s4.apellidom, ''))), '') END,
        ''
    ) AS gerente,

    COALESCE(
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) LIKE '%gerente%' AND LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) NOT LIKE '%subgerente%' THEN CASE WHEN s1.id IS NULL THEN '' WHEN s1.estatus = 'Baja' THEN 'baja' WHEN av_s1.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s1.razon_ausencia)) WHEN s1.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s1.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) LIKE '%gerente%' AND LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) NOT LIKE '%subgerente%' THEN CASE WHEN s2.id IS NULL THEN '' WHEN s2.estatus = 'Baja' THEN 'baja' WHEN av_s2.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s2.razon_ausencia)) WHEN s2.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s2.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) LIKE '%gerente%' AND LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) NOT LIKE '%subgerente%' THEN CASE WHEN s3.id IS NULL THEN '' WHEN s3.estatus = 'Baja' THEN 'baja' WHEN av_s3.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s3.razon_ausencia)) WHEN s3.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s3.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) LIKE '%gerente%' AND LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) NOT LIKE '%subgerente%' THEN CASE WHEN s4.id IS NULL THEN '' WHEN s4.estatus = 'Baja' THEN 'baja' WHEN av_s4.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s4.razon_ausencia)) WHEN s4.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s4.estatus, ''))) END END,
        ''
    ) AS gerente_estatus,

    COALESCE(
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) LIKE '%subdirector%' THEN COALESCE(TRIM(CONCAT_WS(' ', s1.nombres, NULLIF(s1.segundo_nombre, ''), s1.apellidop, NULLIF(s1.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) LIKE '%subdirector%' THEN COALESCE(TRIM(CONCAT_WS(' ', s2.nombres, NULLIF(s2.segundo_nombre, ''), s2.apellidop, NULLIF(s2.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) LIKE '%subdirector%' THEN COALESCE(TRIM(CONCAT_WS(' ', s3.nombres, NULLIF(s3.segundo_nombre, ''), s3.apellidop, NULLIF(s3.apellidom, ''))), '') END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) LIKE '%subdirector%' THEN COALESCE(TRIM(CONCAT_WS(' ', s4.nombres, NULLIF(s4.segundo_nombre, ''), s4.apellidop, NULLIF(s4.apellidom, ''))), '') END,
        ''
    ) AS subdirector,

    COALESCE(
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s1.nombre, ''), ' ', COALESCE(pl_s1.clave, '')))) LIKE '%subdirector%' THEN CASE WHEN s1.id IS NULL THEN '' WHEN s1.estatus = 'Baja' THEN 'baja' WHEN av_s1.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s1.razon_ausencia)) WHEN s1.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s1.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s2.nombre, ''), ' ', COALESCE(pl_s2.clave, '')))) LIKE '%subdirector%' THEN CASE WHEN s2.id IS NULL THEN '' WHEN s2.estatus = 'Baja' THEN 'baja' WHEN av_s2.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s2.razon_ausencia)) WHEN s2.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s2.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s3.nombre, ''), ' ', COALESCE(pl_s3.clave, '')))) LIKE '%subdirector%' THEN CASE WHEN s3.id IS NULL THEN '' WHEN s3.estatus = 'Baja' THEN 'baja' WHEN av_s3.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s3.razon_ausencia)) WHEN s3.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s3.estatus, ''))) END END,
        CASE WHEN LOWER(TRIM(CONCAT(COALESCE(pu_s4.nombre, ''), ' ', COALESCE(pl_s4.clave, '')))) LIKE '%subdirector%' THEN CASE WHEN s4.id IS NULL THEN '' WHEN s4.estatus = 'Baja' THEN 'baja' WHEN av_s4.razon_ausencia IS NOT NULL THEN LOWER(TRIM(av_s4.razon_ausencia)) WHEN s4.estatus = 'Activo' THEN 'activo' ELSE LOWER(TRIM(COALESCE(s4.estatus, ''))) END END,
        ''
    ) AS subdirector_estatus

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

WHERE p.estatus = 'Activo'
  AND UPPER(TRIM(COALESCE(p.user_name, ''))) <> 'REPORTERIA'
  AND (
      d.nombre LIKE 'Campo 1-7%'
      OR d.nombre LIKE 'Campo 8-21%'
  )
ORDER BY p.numero_empleado;
