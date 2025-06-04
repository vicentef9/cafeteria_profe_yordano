-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS cafeteria_db;
USE cafeteria_db;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado') NOT NULL DEFAULT 'empleado',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria ENUM('cafe', 'postres', 'bebidas', 'insumos') NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de inventario
CREATE TABLE IF NOT EXISTS inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 0,
    precio_base DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(5,2) DEFAULT 0,
    notas TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Eliminar datos existentes para evitar duplicados
DELETE FROM inventario;
DELETE FROM productos;
DELETE FROM usuarios;

-- Insertar algunos datos de ejemplo
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Admin', 'admin@cafeteria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Empleado', 'empleado@cafeteria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado');

INSERT INTO productos (nombre, categoria, descripcion) VALUES
('Café Americano', 'cafe', 'Café negro tradicional'),
('Café Latte', 'cafe', 'Café con leche y espuma'),
('Croissant', 'postres', 'Panadería francesa'),
('Tarta de Manzana', 'postres', 'Postre tradicional'),
('Agua Mineral', 'bebidas', 'Agua mineral natural'),
('Jugo de Naranja', 'bebidas', 'Jugo natural de naranja'),
('Azúcar', 'insumos', 'Azúcar refinada'),
('Leche', 'insumos', 'Leche entera');

INSERT INTO inventario (producto_id, stock_actual, stock_minimo, precio_base, descuento) VALUES
(1, 50, 20, 2.50, 0),
(2, 45, 15, 3.00, 10),
(3, 30, 10, 1.80, 0),
(4, 20, 5, 4.50, 15),
(5, 100, 30, 1.00, 0),
(6, 60, 20, 2.00, 5),
(7, 200, 50, 0.50, 0),
(8, 150, 40, 1.20, 0); 