-- =========================================================
-- Agenda Personal de Contactos - Script de base de datos
-- Motor: MySQL 8.x / MariaDB 10.x
-- Josue Jara Panchez UTPL Desarrollo Web
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
-- Tabla: contactos
-- ---------------------------------------------------------
CREATE TABLE contactos (
  id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  id_usuario INT UNSIGNED  NOT NULL,
  nombre     VARCHAR(100)  NOT NULL,
  telefono   VARCHAR(20)   NOT NULL,
  correo     VARCHAR(150)  NOT NULL,
  ciudad     VARCHAR(80)   NOT NULL,
  PRIMARY KEY (id),
  KEY idx_contactos_usuario (id_usuario),
  CONSTRAINT fk_contactos_usuarios
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
