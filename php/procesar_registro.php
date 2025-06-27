<?php
session_start();
require_once 'conexion.php';

// Habilitar mostrar errores para debugging (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errores = [];
    
    // Obtener y validar datos
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    // Validaciones
    if (empty($nombre)) $errores[] = 'El nombre es requerido';
    if (empty($apellido)) $errores[] = 'El apellido es requerido';
    if (empty($email)) $errores[] = 'El email es requerido';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email inválido';
    if (empty($password)) $errores[] = 'La contraseña es requerida';
    if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    if ($password !== $confirmar_password) $errores[] = 'Las contraseñas no coinciden';
    
    if (empty($errores)) {
        try {
            // Verificar conexión a BD
            if (!$conn) {
                throw new Exception('Error de conexión a la base de datos');
            }
            
            // Verificar si el email ya existe
            $stmt = $conn->prepare("SELECT id FROM empleados WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errores[] = 'El email ya está registrado';
            } else {
                // Crear nuevo usuario
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO empleados (nombre, apellido, email, password, rol, estado) VALUES (?, ?, ?, ?, 'empleado', 'activo')");
                
                if ($stmt->execute([$nombre, $apellido, $email, $password_hash])) {
                    $_SESSION['mensaje'] = 'Usuario registrado exitosamente. Puedes iniciar sesión.';
                    header('Location: ../html/autenticacion/login.php');
                    exit();
                } else {
                    $errores[] = 'Error al ejecutar la consulta de inserción';
                }
            }
        } catch (PDOException $e) {
            $errores[] = 'Error de base de datos: ' . $e->getMessage();
        } catch (Exception $e) {
            $errores[] = 'Error: ' . $e->getMessage();
        }
    }
    
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header('Location: ../html/autenticacion/registro.php');
        exit();
    }
} else {
    // Si acceden directamente al archivo sin POST
    header('Location: ../html/autenticacion/registro.php');
    exit();
}
?>