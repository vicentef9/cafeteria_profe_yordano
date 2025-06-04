<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit('No autorizado');
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID no proporcionado');
}

$id = $_GET['id'];

try {
    $query = "SELECT * FROM inventario WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Devolver los datos en formato JSON
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        header('HTTP/1.1 404 Not Found');
        exit('Producto no encontrado');
    }
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Error al obtener los datos del producto');
}
?> 