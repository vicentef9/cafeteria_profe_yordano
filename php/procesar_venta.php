<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Obtener y validar datos de la venta
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['productos']) || !isset($data['metodo_pago'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Datos incompletos']);
    exit();
}

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Insertar la venta
    $query_venta = "INSERT INTO ventas (empleado_id, total, metodo_pago) VALUES (:empleado_id, :total, :metodo_pago)";
    $stmt_venta = $conn->prepare($query_venta);
    
    $total = 0;
    foreach ($data['productos'] as $producto) {
        $total += $producto['cantidad'] * $producto['precio'];
    }

    $stmt_venta->bindParam(':empleado_id', $_SESSION['usuario_id']);
    $stmt_venta->bindParam(':total', $total);
    $stmt_venta->bindParam(':metodo_pago', $data['metodo_pago']);
    $stmt_venta->execute();

    $venta_id = $conn->lastInsertId();

    // Insertar detalles de la venta
    $query_detalle = "INSERT INTO detalles_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal) 
                      VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
    $stmt_detalle = $conn->prepare($query_detalle);

    foreach ($data['productos'] as $producto) {
        $subtotal = $producto['cantidad'] * $producto['precio'];
        
        $stmt_detalle->bindParam(':venta_id', $venta_id);
        $stmt_detalle->bindParam(':producto_id', $producto['id']);
        $stmt_detalle->bindParam(':cantidad', $producto['cantidad']);
        $stmt_detalle->bindParam(':precio_unitario', $producto['precio']);
        $stmt_detalle->bindParam(':subtotal', $subtotal);
        $stmt_detalle->execute();

        // Actualizar inventario
        $query_inventario = "UPDATE inventario 
                            SET stock_actual = stock_actual - :cantidad 
                            WHERE producto_id = :producto_id";
        $stmt_inventario = $conn->prepare($query_inventario);
        $stmt_inventario->bindParam(':cantidad', $producto['cantidad']);
        $stmt_inventario->bindParam(':producto_id', $producto['id']);
        $stmt_inventario->execute();
    }

    // Confirmar transacción
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Venta procesada correctamente',
        'venta_id' => $venta_id
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Error al procesar la venta: ' . $e->getMessage()
    ]);
}
?> 