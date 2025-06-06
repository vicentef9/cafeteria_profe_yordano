<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID no proporcionado']);
    exit();
}

$id = $_GET['id'];

try {
    // Obtener datos de la venta
    $query_venta = "SELECT v.*, e.nombre as empleado_nombre 
                    FROM ventas v 
                    JOIN empleados e ON v.usuario_id = e.id 
                    WHERE v.id = :id";
    $stmt_venta = $conn->prepare($query_venta);
    $stmt_venta->bindParam(':id', $id);
    $stmt_venta->execute();
    $venta = $stmt_venta->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Venta no encontrada']);
        exit();
    }

    // Obtener detalles de la venta
    $query_detalles = "SELECT dv.*, p.nombre as producto_nombre 
                       FROM detalles_venta dv 
                       JOIN productos p ON dv.producto_id = p.id 
                       WHERE dv.venta_id = :venta_id";
    $stmt_detalles = $conn->prepare($query_detalles);
    $stmt_detalles->bindParam(':venta_id', $id);
    $stmt_detalles->execute();
    $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

    // Combinar resultados
    $resultado = [
        'venta' => $venta,
        'detalles' => $detalles
    ];

    header('Content-Type: application/json');
    echo json_encode($resultado);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al obtener la venta: ' . $e->getMessage()]);
}
?> 