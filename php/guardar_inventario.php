<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../html/autenticacion/login.php');
    exit();
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inventario_id = isset($_POST['inventario_id']) ? $_POST['inventario_id'] : null;
    $producto_id = $_POST['producto_id'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $precio_base = $_POST['precio_base'];
    $descuento = $_POST['descuento'];
    $notas = $_POST['notas'];

    try {
        if ($inventario_id) {
            // Actualizar inventario existente
            $query = "UPDATE inventario SET 
                     producto_id = ?, 
                     stock_actual = ?, 
                     stock_minimo = ?, 
                     precio_base = ?, 
                     descuento = ?, 
                     notas = ? 
                     WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$producto_id, $stock_actual, $stock_minimo, $precio_base, $descuento, $notas, $inventario_id]);
        } else {
            // Insertar nuevo inventario
            $query = "INSERT INTO inventario (producto_id, stock_actual, stock_minimo, precio_base, descuento, notas) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$producto_id, $stock_actual, $stock_minimo, $precio_base, $descuento, $notas]);
        }
        echo "ok";
        exit();
    } catch (Exception $e) {
        echo "error: " . $e->getMessage();
        exit();
    }
} else {
    // Si no es POST, redirigir a la página de inventario
    header('Location: ../html/empleados/inventario.php');
    exit();
}
?> 