-- =========================================================
-- Agenda Personal de Contactos - Script de base de datos (v2)
-- Motor: MySQL 8.x / MariaDB 10.x
-- Josue Jara Panchez UTPL Desarrollo Web
--
-- Uso LOCAL (XAMPP / MySQL Workbench): crea la base desde cero.
-- Para el hosting use database/agenda_contactos_hosting.sql
-- =========================================================

DROP DATABASE IF EXISTS agenda_contactos;
CREATE DATABASE agenda_contactos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE agenda_contactos;

-- ---------------------------------------------------------
-- Tabla: usuarios
-- ---------------------------------------------------------
CREATE TABLE usuarios (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nombre         VARCHAR(100)  NOT NULL,
  correo         VARCHAR(150)  NOT NULL,
  password       VARCHAR(255)  NOT NULL COMMENT 'Hash generado con password_hash()',
  fecha_registro DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Tabla: categorias  (Mejora C)
-- ---------------------------------------------------------
CREATE TABLE categorias (
  id     TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50)      NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (id, nombre) VALUES
  (1, 'Familia'),
  (2, 'Amigos'),
  (3, 'Trabajo'),
  (4, 'Universidad'),
  (5, 'Otro');

-- ---------------------------------------------------------
-- Tabla: contactos
--   id_categoria   -> Mejora C (cada contacto pertenece a una categoría)
--   favorito       -> Mejora D (Sí = 1 / No = 0)
--   fecha_creacion -> Mejora B (ordenamiento por fecha de creación)
-- ---------------------------------------------------------
CREATE TABLE contactos (
  id             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  id_usuario     INT UNSIGNED     NOT NULL,
  id_categoria   TINYINT UNSIGNED NOT NULL DEFAULT 5,
  nombre         VARCHAR(100)     NOT NULL,
  telefono       VARCHAR(20)      NOT NULL,
  correo         VARCHAR(150)     NOT NULL,
  ciudad         VARCHAR(80)      NOT NULL,
  favorito       TINYINT(1)       NOT NULL DEFAULT 0,
  fecha_creacion DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contactos_usuario (id_usuario),
  KEY idx_contactos_categoria (id_categoria),
  CONSTRAINT fk_contactos_usuarios
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_contactos_categorias
    FOREIGN KEY (id_categoria) REFERENCES categorias (id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
