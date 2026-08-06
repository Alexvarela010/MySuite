-- ==========================================================
-- BASE DE DATOS: softwarePHP
-- Sistema de Reservas de Apartamento Turístico
-- ==========================================================

CREATE DATABASE IF NOT EXISTS mysgd5s3m2re_MySuiteBD
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mysgd5s3m2re_MySuiteBD;

-- ==========================================================
-- TABLA: usuarios (clientes y administrador)
-- ==========================================================
CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL UNIQUE,
  contrasena VARCHAR(255),
  telefono VARCHAR(20),
  ciudad VARCHAR(100) DEFAULT 'Cartagena',
  fecha_nacimiento DATE,
  rol ENUM('admin','cliente') DEFAULT 'cliente',
  login_method VARCHAR(50),
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- TABLA: apartamentos
-- ==========================================================
CREATE TABLE apartamentos (
  id_apartamento INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  capacidad_adultos INT DEFAULT 8,
  capacidad_ninos INT DEFAULT 4,
  activo BOOLEAN DEFAULT TRUE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- TABLA: tarifas (precio por día)
-- ==========================================================
CREATE TABLE tarifas (
  id_tarifa INT AUTO_INCREMENT PRIMARY KEY,
  id_apartamento INT NOT NULL,
  fecha DATE NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  temporada ENUM('alta','media','baja','festivo') DEFAULT 'baja',
  UNIQUE KEY (id_apartamento, fecha),
  FOREIGN KEY (id_apartamento) REFERENCES apartamentos(id_apartamento) ON DELETE CASCADE
);

-- ==========================================================
-- TABLA: fechas_bloqueadas (mantenimiento, uso interno)
-- ==========================================================
CREATE TABLE fechas_bloqueadas (
  id_bloqueo INT AUTO_INCREMENT PRIMARY KEY,
  id_apartamento INT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  motivo ENUM('mantenimiento','uso_interno','evento_especial','limpieza','reparacion','otro') NOT NULL,
  descripcion TEXT,
  activo BOOLEAN DEFAULT TRUE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_apartamento) REFERENCES apartamentos(id_apartamento) ON DELETE CASCADE
);

-- ==========================================================
-- TABLA: descuentos (bonos, fidelización, cumpleaños, etc.)
-- ==========================================================
CREATE TABLE descuentos (
  id_descuento INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  tipo ENUM('fidelizacion','cumpleanios','vendedor','promocional') NOT NULL,
  porcentaje DECIMAL(5,2) NOT NULL,
  codigo VARCHAR(50),
  fecha_inicio DATE,
  fecha_fin DATE,
  activo BOOLEAN DEFAULT TRUE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- TABLA: reservas
-- ==========================================================
CREATE TABLE reservas (
  id_reserva INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NULL,
  id_apartamento INT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL,
  telefono VARCHAR(20) NOT NULL,
  fecha_nacimiento DATE,
  vive_palmira BOOLEAN DEFAULT FALSE,
  comentario TEXT,
  fecha_entrada DATE NOT NULL,
  fecha_salida DATE NOT NULL,
  num_adultos INT DEFAULT 1,
  num_ninos INT DEFAULT 0,
  metodo_pago ENUM('efectivo','tarjeta_credito') NOT NULL,
  costo_base DECIMAL(10,2) NOT NULL,
  descuento_fidelizacion DECIMAL(10,2) DEFAULT 0,
  descuento_cumpleanios DECIMAL(10,2) DEFAULT 0,
  descuento_promocional DECIMAL(10,2) DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  estado ENUM('pendiente','aprobada','abonada','rechazada','cancelada') DEFAULT 'pendiente',
  estado_pago ENUM('pendiente','pagada') DEFAULT 'pendiente',
  fecha_pago_confirmado TIMESTAMP NULL,
  email_enviado BOOLEAN DEFAULT FALSE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
  FOREIGN KEY (id_apartamento) REFERENCES apartamentos(id_apartamento) ON DELETE CASCADE
);

-- ==========================================================
-- TRIGGER: actualizar disponibilidad cuando se aprueba reserva
-- ==========================================================
-- TRIGGER ELIMINADO: Este trigger causaba duplicación de fechas bloqueadas
-- Las reservas aprobadas se muestran directamente en el calendario sin crear fechas bloqueadas
-- DELIMITER $$
-- CREATE TRIGGER tr_bloquear_fechas_reserva
-- AFTER UPDATE ON reservas
-- FOR EACH ROW
-- BEGIN
--   IF NEW.estado = 'aprobada' THEN
--     INSERT INTO fechas_bloqueadas (id_apartamento, fecha_inicio, fecha_fin, motivo)
--     VALUES (NEW.id_apartamento, NEW.fecha_entrada, NEW.fecha_salida, 'Reserva aprobada');
--   END IF;
-- END$$
-- DELIMITER ;

-- ==========================================================
-- VISTA: disponibilidad del calendario
-- ==========================================================
CREATE OR REPLACE VIEW vista_disponibilidad AS
SELECT 
  t.fecha,
  a.nombre AS apartamento,
  t.precio,
  t.temporada,
  CASE
    WHEN EXISTS (
      SELECT 1 FROM reservas r
      WHERE r.id_apartamento = a.id_apartamento
        AND r.estado IN ('aprobada', 'abonada')
        AND t.fecha >= r.fecha_entrada AND t.fecha < r.fecha_salida
    ) THEN 'Ocupado'
    WHEN EXISTS (
      SELECT 1 FROM fechas_bloqueadas fb
      WHERE fb.id_apartamento = a.id_apartamento
        AND fb.activo = 1
        AND t.fecha BETWEEN fb.fecha_inicio AND fb.fecha_fin
    ) THEN 'Bloqueado'
    ELSE 'Disponible'
  END AS estado
FROM tarifas t
INNER JOIN apartamentos a ON a.id_apartamento = t.id_apartamento;

-- ==========================================================
-- TABLA: password_reset_tokens (tokens para recuperación de contraseña)
-- ==========================================================
CREATE TABLE password_reset_tokens (
  id_token INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  verification_code VARCHAR(6) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  used BOOLEAN DEFAULT FALSE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  INDEX idx_token (token),
  INDEX idx_user_id (user_id)
);

-- ==========================================================
-- TABLA: descuentos_config (configuración de descuentos)
-- ==========================================================
CREATE TABLE descuentos_config (
  id_config INT AUTO_INCREMENT PRIMARY KEY,
  tipo_descuento ENUM('fidelidad','cumpleanos','vendedor','promocional') NOT NULL UNIQUE,
  porcentaje DECIMAL(5,2) NOT NULL,
  activo BOOLEAN DEFAULT TRUE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================================
-- DATOS INICIALES
-- ==========================================================
INSERT INTO apartamentos (nombre, descripcion, capacidad_adultos, capacidad_ninos)
VALUES ('Apartamento Principal', 'Apartamento turístico en Palmira', 8, 4);

-- Administradores del sistema (3 administradores únicos)
-- NOTA: Los apellidos y teléfonos de Carlos y root se actualizarán después
INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol)
VALUES 
('Carlos', 'Por definir', 'admin1@apto.com', '$2y$10$X0xw.P2eKi44YMKhCD8oj.39nW1syjky07uv8EJdQmjofZBLUjqtG', 'admin'),
('Andrés', 'Diaz', 'admin2@apto.com', '$2y$10$LNIUVVcgiDon.v6BcXSVyuyu37EvFn8jp/c7pwzxUiTWSqdqIDui6', 'admin'),
('root', 'Por definir', 'admin3@apto.com', '$2y$10$M7UY3X2kY5PpwKRmpnMzoOBcN9LwROJs4WeSYu5TGnDZUbx/twK4m', 'admin');

-- Cliente de ejemplo
INSERT INTO usuarios (nombre, apellido, correo, contrasena, telefono, rol)
VALUES ('Sergio', 'Sanchez', 'sergio@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3189974656', 'cliente');

INSERT INTO descuentos (nombre, tipo, porcentaje, codigo, fecha_inicio, fecha_fin)
VALUES 
('Fidelización', 'fidelizacion', 5.00, NULL, NULL, NULL),
('Cumpleaños', 'cumpleanios', 30.00, NULL, NULL, NULL),
('Vendedor', 'vendedor', 5.00, NULL, NULL, NULL),
('Promocional', 'promocional', 10.00, 'PROMO2025', '2025-11-01', '2026-01-15');

-- Configuración inicial de descuentos
INSERT INTO descuentos_config (tipo_descuento, porcentaje, activo)
VALUES 
('fidelidad', 5.00, TRUE),
('cumpleanos', 30.00, TRUE),
('vendedor', 5.00, TRUE),
('promocional', 10.00, TRUE);

-- Insertar tarifas base para los próximos 365 días
INSERT INTO tarifas (id_apartamento, fecha, precio, temporada)
SELECT 1, DATE_ADD(CURDATE(), INTERVAL n DAY), 200000, 'baja'
FROM (
    SELECT a.N + b.N * 10 + c.N * 100 AS n
    FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a
    CROSS JOIN (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
    CROSS JOIN (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) c
) numbers
WHERE n < 365;
