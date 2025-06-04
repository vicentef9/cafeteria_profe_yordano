<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    
    // Validar campos
    $errores = [];
    
    if (strlen($nombre) < 2) {
        $errores[] = "El nombre debe tener al menos 2 caracteres";
    }
    
    if (strlen($apellido) < 2) {
        $errores[] = "El apellido debe tener al menos 2 caracteres";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido";
    }
    
    if (strlen($password) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }

    if ($password !== $confirmar_password) {
        $errores[] = "Las contraseñas no coinciden";
    }
    
    // Verificar si el correo ya existe
    $stmt = $conn->prepare("SELECT id FROM empleados WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errores[] = "Este correo electrónico ya está registrado";
    }
    
    if (empty($errores)) {
        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            // Insertar nuevo usuario
            $stmt = $conn->prepare("INSERT INTO empleados (nombre, apellido, email, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nombre, $apellido, $email, $password_hash])) {
                $_SESSION['mensaje'] = "Registro exitoso. Por favor, inicia sesión.";
                header("Location: ../html/autenticacion/login.php");
                exit();
            } else {
                $errores[] = "Error al registrar el usuario. Por favor, intenta nuevamente.";
            }
        } catch (PDOException $e) {
            $errores[] = "Error de base de datos: " . $e->getMessage();
        }
    }
    
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: ../html/autenticacion/registro.php");
        exit();
    }
}
?> 