<?php
session_start();
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        try {
            // Buscar el usuario en la base de datos
            $stmt = $conn->prepare("SELECT id, nombre, apellido, email, password, rol FROM empleados WHERE email = ? AND estado = 'activo'");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre_usuario'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
                $_SESSION['rol'] = $usuario['rol'];

                // Redirigir según el rol
                if ($usuario['rol'] === 'admin') {
                    header("Location: ../html/empleados/productos.php");
                } else {
                    header("Location: ../html/empleados/productos.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Credenciales inválidas o usuario inactivo";
                header("Location: ../html/autenticacion/login.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al procesar el login: " . $e->getMessage();
            header("Location: ../html/autenticacion/login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Por favor, complete todos los campos";
        header("Location: ../html/autenticacion/login.php");
        exit();
    }
} else {
    header("Location: ../html/autenticacion/login.php");
    exit();
}
?> 