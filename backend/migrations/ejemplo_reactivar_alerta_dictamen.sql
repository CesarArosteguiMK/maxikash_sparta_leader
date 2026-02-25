-- ============================================================
-- Prueba: reactivar alerta roja de dictamen en el menú Ticket
-- ============================================================
-- Si borras "quién vio" y "cuándo se vio" el dictamen,
-- la fila del ticket volverá a mostrarse con la alerta roja
-- (borde palpitante) en Sabueso → Ticket.
--
-- Sustituye 123 por el id_ticket que quieras probar.
-- ============================================================

UPDATE dictamen
SET fecha_visto_gestor = NULL,
    id_persona_visto_gestor = NULL
WHERE id_ticket = 123
  AND estado = 'enviado_al_gestor';

-- Después recarga la página del menú Ticket: la fila de ese ticket
-- debería aparecer de nuevo con el borde rojo y el icono de ojo cerrado.
