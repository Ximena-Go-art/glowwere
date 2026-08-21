-- ============================================================
-- Tabla de Registros (Bitácora del sistema)
-- Guarda quién hizo qué, cuándo, desde dónde y con qué S.O.
-- ============================================================

CREATE TABLE IF NOT EXISTS registros (
    id_registros INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL DEFAULT 0,
    seccion VARCHAR(100) DEFAULT NULL,
    accion VARCHAR(255) DEFAULT NULL,
    link VARCHAR(255) DEFAULT NULL,
    `S.O` VARCHAR(50) DEFAULT NULL,
    deleted TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_registros_usuario (id_usuario),
    INDEX idx_registros_fecha (fecha_hora),
    INDEX idx_registros_seccion (seccion)
);

-- Si tu tabla ya existe pero le faltan índices, podés agregarlos con:
-- ALTER TABLE registros ADD INDEX idx_registros_fecha (fecha_hora);
