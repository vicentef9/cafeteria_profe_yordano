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
    $descuento = isset($_POST['descuento']) ? $_POST['descuento'] : 0;
    $notas = isset($_POST['notas']) ? $_POST['notas'] : '';

    // Verificar si es una actualización o una inserción
    if (isset($_POST['inventario_id']) && !empty($_POST['inventario_id'])) {
        // Actualizar registro existente
        $query = "UPDATE inventario SET 
                  producto_id = :producto_id,
                  stock_actual = :stock_actual,
                  stock_minimo = :stock_minimo,
                  precio_base = :precio_base,
                  descuento = :descuento,
                  notas = :notas
                  WHERE id = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $_POST['inventario_id'], PDO::PARAM_INT);
    } else {
        // Insertar nuevo registro
        $query = "INSERT INTO inventario 
                  (producto_id, stock_actual, stock_minimo, precio_base, descuento, notas) 
                  VALUES 
                  (:producto_id, :stock_actual, :stock_minimo, :precio_base, :descuento, :notas)";
        
        $stmt = $conn->prepare($query);
    }

    // Vincular parámetros
    $stmt->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
    $stmt->bindParam(':stock_actual', $stock_actual, PDO::PARAM_INT);
    $stmt->bindParam(':stock_minimo', $stock_minimo, PDO::PARAM_INT);
    $stmt->bindParam(':precio_base', $precio_base, PDO::PARAM_STR);
    $stmt->bindParam(':descuento', $descuento, PDO::PARAM_INT);
    $stmt->bindParam(':notas', $notas, PDO::PARAM_STR);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "Error al guardar los datos";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?> 