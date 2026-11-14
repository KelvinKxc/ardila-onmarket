-- =====================================================================
-- Esquema de la base de datos: sistema web de propiedades On Market
-- MySQL 8. Es seguro ejecutarlo más de una vez: no borra datos existentes.
-- Lo ejecuta `php bin/instalar.php`; también puedes abrirlo en HeidiSQL.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS ardila_onmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE ardila_onmarket;

CREATE TABLE IF NOT EXISTS rol (
  id_rol  TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre  VARCHAR(30)      NOT NULL COMMENT 'administrador, analista o encargado',
  PRIMARY KEY (id_rol),
  UNIQUE KEY uq_rol_nombre (nombre)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuario (
  id_usuario      INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  id_rol          TINYINT UNSIGNED NOT NULL,
  nombre          VARCHAR(80)      NOT NULL COMMENT 'Nombre con el que inicia sesión; es único',
  contrasena_hash VARCHAR(255)     NOT NULL COMMENT 'Contraseña cifrada (hash). Nunca se guarda en texto plano',
  activo          BOOLEAN          NOT NULL DEFAULT 1 COMMENT '1 = puede entrar al sistema',
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuario_nombre (nombre),
  KEY ix_usuario_rol (id_rol),
  CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES rol (id_rol)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS agente (
  id_agente INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre    VARCHAR(80)  NOT NULL,
  telefono  VARCHAR(30)  NULL,
  correo    VARCHAR(120) NULL COMMENT 'Correo al que se envía la oferta',
  PRIMARY KEY (id_agente)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS propiedad (
  id_propiedad          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  direccion             VARCHAR(150) NOT NULL COMMENT 'Dirección completa tal como se escribe',
  direccion_normalizada VARCHAR(150) GENERATED ALWAYS AS (
      LOWER(TRIM(REGEXP_REPLACE(REGEXP_REPLACE(direccion, '[^A-Za-z0-9 ]', ''), ' +', ' ')))
    ) STORED COMMENT 'Calculada: evita propiedades duplicadas',
  precio_lista          INT UNSIGNED NOT NULL COMMENT 'Dólares',
  habitaciones          TINYINT UNSIGNED NULL,
  banos                 DECIMAL(3,1) NULL COMMENT 'Admite medios baños (1.5, 2.5)',
  garaje                ENUM('si','no','no_se') NOT NULL DEFAULT 'no_se',
  sqft                  INT UNSIGNED NULL COMMENT 'Pies cuadrados',
  taxes_anuales         INT UNSIGNED NULL COMMENT 'Dólares por año',
  ultimo_valor_compra   INT UNSIGNED NULL COMMENT 'Dólares',
  fecha_listado         DATE         NULL,
  tipo_remodelacion     ENUM('liviana','mediana','pesada') NULL,
  descripcion           TEXT         NULL COMMENT 'Descripción original del anuncio',
  arv                   INT UNSIGNED NULL COMMENT 'Valor máximo (ARV/VDR) calculado a partir de los comparables',
  oferta                INT UNSIGNED NULL COMMENT 'Dólares',
  oferta_maxima         INT UNSIGNED NULL COMMENT 'Dólares',
  taxes_precio          DECIMAL(7,3) GENERATED ALWAYS AS (
      ROUND(taxes_anuales / NULLIF(precio_lista, 0) * 100, 3)
    ) STORED COMMENT 'Calculado: porcentaje de taxes sobre el precio de lista',
  estado_contacto       ENUM('sin_contactar','contactada') NOT NULL DEFAULT 'sin_contactar',
  resultado_oferta      ENUM('aceptada','rechazada','contraoferta','sin_respuesta') NOT NULL DEFAULT 'sin_respuesta',
  id_agente             INT UNSIGNED NULL,
  id_usuario_registro   INT UNSIGNED NOT NULL COMMENT 'Analista que la registró',
  fecha_registro        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_propiedad),
  UNIQUE KEY uq_propiedad_direccion (direccion_normalizada),
  KEY ix_propiedad_agente (id_agente),
  KEY ix_propiedad_usuario (id_usuario_registro),
  KEY ix_propiedad_contacto (estado_contacto),
  KEY ix_propiedad_fecha_listado (fecha_listado),
  CONSTRAINT fk_propiedad_agente FOREIGN KEY (id_agente) REFERENCES agente (id_agente)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_propiedad_usuario FOREIGN KEY (id_usuario_registro) REFERENCES usuario (id_usuario)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comparable (
  id_comparable INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_propiedad  INT UNSIGNED NOT NULL,
  url           VARCHAR(500) NOT NULL COMMENT 'Enlace del comparable',
  precio_venta  INT UNSIGNED NOT NULL COMMENT 'Dólares en que se vendió',
  PRIMARY KEY (id_comparable),
  KEY ix_comparable_propiedad (id_propiedad),
  CONSTRAINT fk_comparable_propiedad FOREIGN KEY (id_propiedad) REFERENCES propiedad (id_propiedad)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS extraccion_ia (
  id_extraccion INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_propiedad  INT UNSIGNED NULL COMMENT 'Se asocia al guardar la propiedad',
  id_usuario    INT UNSIGNED NOT NULL COMMENT 'Analista que la solicitó',
  proveedor_api VARCHAR(40)  NOT NULL COMMENT 'API de IA utilizada',
  texto_enviado TEXT         NOT NULL COMMENT 'Descripción enviada a la API (sin datos personales)',
  respuesta     JSON         NULL COMMENT 'Respuesta completa de la API',
  sqft_ia       INT UNSIGNED NULL,
  habitaciones  TINYINT UNSIGNED NULL,
  banos         DECIMAL(3,1) NULL,
  garaje        ENUM('si','no','no_se') NOT NULL DEFAULT 'no_se' COMMENT 'no_se = la descripción no lo menciona',
  condicion     VARCHAR(30)  NULL,
  confirmado    BOOLEAN      NOT NULL DEFAULT 0 COMMENT '1 = el analista revisó y confirmó',
  PRIMARY KEY (id_extraccion),
  KEY ix_extraccion_propiedad (id_propiedad),
  KEY ix_extraccion_usuario (id_usuario),
  CONSTRAINT fk_extraccion_propiedad FOREIGN KEY (id_propiedad) REFERENCES propiedad (id_propiedad)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_extraccion_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historial_cambio (
  id_cambio      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_propiedad   INT UNSIGNED    NOT NULL,
  id_usuario     INT UNSIGNED    NOT NULL COMMENT 'Quién hizo el cambio',
  campo          VARCHAR(50)     NOT NULL COMMENT 'Campo modificado',
  valor_anterior VARCHAR(255)    NULL,
  valor_nuevo    VARCHAR(255)    NULL,
  fecha          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_cambio),
  KEY ix_historial_propiedad (id_propiedad),
  KEY ix_historial_usuario (id_usuario),
  CONSTRAINT fk_historial_propiedad FOREIGN KEY (id_propiedad) REFERENCES propiedad (id_propiedad)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_historial_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW v_propiedad_resumen AS
SELECT p.id_propiedad,
       p.direccion,
       p.precio_lista,
       p.habitaciones,
       p.banos,
       p.garaje,
       p.sqft,
       p.taxes_anuales,
       p.taxes_precio,
       p.fecha_listado,
       DATEDIFF(CURDATE(), p.fecha_listado) AS dias_en_mercado,
       COUNT(c.id_comparable)               AS n_comparables,
       ROUND(AVG(c.precio_venta))           AS promedio_comparables,
       p.arv,
       p.oferta_maxima,
       p.estado_contacto,
       p.resultado_oferta,
       a.nombre                             AS agente,
       a.correo                             AS correo_agente
FROM propiedad p
LEFT JOIN comparable c ON c.id_propiedad = p.id_propiedad
LEFT JOIN agente a     ON a.id_agente    = p.id_agente
GROUP BY p.id_propiedad, a.id_agente;

INSERT IGNORE INTO rol (nombre) VALUES ('administrador'), ('analista'), ('encargado');
