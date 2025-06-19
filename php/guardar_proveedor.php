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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener y sanitizar los datos del formulario
        $proveedor_id = isset($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : null;
        $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
        $contacto = filter_var($_POST['contacto'], FILTER_SANITIZE_STRING);
        $telefono = filter_var($_POST['telefono'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $categoria = filter_var($_POST['categoria'], FILTER_SANITIZE_STRING);
        $direccion = filter_var($_POST['direccion'], FILTER_SANITIZE_STRING);
        $calificacion = intval($_POST['calificacion']);
        $estado = filter_var($_POST['estado'], FILTER_SANITIZE_STRING);
        $notas = filter_var($_POST['notas'], FILTER_SANITIZE_STRING);

        if ($proveedor_id) {
            // Actualizar proveedor existente
            $query = "UPDATE proveedores SET 
                     nombre = :nombre,
                     contacto = :contacto,
                     telefono = :telefono,
                     email = :email,
                     categoria = :categoria,
                     direccion = :direccion,
                     calificacion = :calificacion,
                     estado = :estado,
                     notas = :notas
                     WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $proveedor_id);
        } else {
            // Insertar nuevo proveedor
            $query = "INSERT INTO proveedores 
                     (nombre, contacto, telefono, email, categoria, direccion, calificacion, estado, notas)
                     VALUES 
                     (:nombre, :contacto, :telefono, :email, :categoria, :direccion, :calificacion, :estado, :notas)";
            
            $stmt = $conn->prepare($query);
        }

        // Vincular parámetros
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':calificacion', $calificacion);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':notas', $notas);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Proveedor guardado exitosamente']);
        } else {
            throw new Exception('Error al guardar el proveedor');
        }

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido']);
}
?>