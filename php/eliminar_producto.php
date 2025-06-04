<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../html/autenticacion/login.php');
    exit();
}

// Verificar si es una petición POST y si se proporcionó un ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // Primero eliminar registros relacionados en inventario
        $query_inventario = "DELETE FROM inventario WHERE producto_id = ?";
        $stmt_inventario = $conn->prepare($query_inventario);
        $stmt_inventario->execute([$id]);

        // Luego eliminar el producto
        $query_producto = "DELETE FROM productos WHERE id = ?";
        $stmt_producto = $conn->prepare($query_producto);
        $stmt_producto->execute([$id]);

        // Confirmar transacción
        $conn->commit();

        $_SESSION['mensaje'] = "Producto eliminado exitosamente";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }
}

// Redirigir de vuelta a la página de productos
header('Location: ../html/empleados/productos.php');
exit();
?> 