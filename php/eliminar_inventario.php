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
        $query = "DELETE FROM inventario WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Producto eliminado del inventario exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            throw new Exception("Error al eliminar el producto del inventario");
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }
}

// Redirigir de vuelta a la página de inventario
header('Location: ../html/empleados/inventario.html');
exit();
?> 