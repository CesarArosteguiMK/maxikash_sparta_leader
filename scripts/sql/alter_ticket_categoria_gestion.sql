-- Ticket: categoría de gestión (sabueso, viáticos, etc.)
-- Ejecutar una vez en la BD que usa el módulo Ticket.
-- Panel Admin Sabueso solo lista tickets con categoria_gestion = 'sabueso'.

ALTER TABLE ticket
    ADD COLUMN categoria_gestion VARCHAR(64) NOT NULL DEFAULT 'sabueso'
    AFTER id_origen_ticket;

-- Opcional: índice para filtrar por categoría en listados
-- CREATE INDEX idx_ticket_categoria_gestion ON ticket (categoria_gestion);
