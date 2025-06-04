<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Verificar si se recibieron los datos necesarios
if (!isset($_POST['nombre']) || !isset($_POST['categoria'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit();
}

try {
    $id = isset($_POST['producto_id']) ? $_POST['producto_id'] : null;
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $descripcion = $_POST['descripcion'] ?? null;
    $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;

    if ($id) {
        // Actualizar producto existente
        $query = "UPDATE productos SET 
                  nombre = :nombre,
                  categoria = :categoria,
                  descripcion = :descripcion,
                  fecha_vencimiento = :fecha_vencimiento
                  WHERE id = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
    } else {
        // Insertar nuevo producto
        $query = "INSERT INTO productos (nombre, categoria, descripcion, fecha_vencimiento)
                  VALUES (:nombre, :categoria, :descripcion, :fecha_vencimiento)";
        
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Producto guardado correctamente']);
    } else {
        throw new Exception('Error al guardar el producto');
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al guardar el producto: ' . $e->getMessage()]);
}
?> 