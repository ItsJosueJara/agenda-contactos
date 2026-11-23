-- =========================================================
-- Migración: agrega las mejoras a una base v1 YA EXISTENTE
-- (conserva usuarios y contactos registrados)
-- =========================================================
USE agenda_contactos;

CREATE TABLE IF NOT EXISTS categorias (
  id     TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50)      NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (id, nombre) VALUES
  (1, 'Familia'), (2, 'Amigos'), (3, 'Trabajo'), (4, 'Universidad'), (5, 'Otro');

ALTER TABLE contactos
  ADD COLUMN id_categoria   TINYINT UNSIGNED NOT NULL DEFAULT 5 AFTER id_usuario,
  ADD COLUMN favorito       TINYINT(1)       NOT NULL DEFAULT 0 AFTER ciudad,
  ADD COLUMN fecha_creacion DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER favorito,
  ADD KEY idx_contactos_categoria (id_categoria),
  ADD CONSTRAINT fk_contactos_categorias
    FOREIGN KEY (id_categoria) REFERENCES categorias (id)
    ON DELETE RESTRICT ON UPDATE CASCADE;
