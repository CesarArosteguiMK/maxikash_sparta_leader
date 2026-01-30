-- =============================================================================
-- Ticket por WhatsApp: origen "WhatsApp" y usuario "Bot WhatsApp"
-- Ejecutar una vez contra la BD __SPARTA_SECRET_REDACTED__ (o la que use la aplicación).
-- =============================================================================

-- 1) Origen de ticket "WhatsApp" (si no existe)
INSERT INTO origen_ticket (nombre, activo)
SELECT 'WhatsApp', 1
FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM origen_ticket WHERE LOWER(TRIM(nombre)) = 'whatsapp');

-- 2) Usuario "Bot WhatsApp" en persona (solo para id_persona_creador en tickets)
--    user_name = 'bot_whatsapp', no se usa para login en web.
INSERT INTO persona (nombres, apellidop, apellidom, numero_empleado, correo, telefono_uno, telefono_dos, estatus, user_name, password, fecha_ingreso, fecha_registro)
SELECT 'Bot WhatsApp', '', '', '', '', '', '', 'Activo', 'bot_whatsapp', MD5(CONCAT('bot_whatsapp', UNIX_TIMESTAMP())), CURDATE(), NOW()
FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM persona WHERE user_name = 'bot_whatsapp');
