<?php
$servidor = "localhost";
$usuario = "root";
$clave = "";
$bs = "comicverse";

// Configurar la zona horaria de Chile
date_default_timezone_set('America/Santiago');

$enlace = mysqli_connect($servidor, $usuario, $clave, $bs);
if (!$enlace) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Obtener hora actual de Chile
    $chile_datetime = date('Y-m-d H:i:s');

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Hashear la contraseña antes de guardarla
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $enlace->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $password_hash, $chile_datetime, $chile_datetime);

        if ($stmt->execute()) {
            header("Location: ../vistas/index.php");
            exit();
        } else {
            echo "<div style='color: red; font-weight: bold;'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } else {
        echo "<div style='color: red; font-weight: bold;'>Por favor, complete todos los campos requeridos.</div>";
    }
}

mysqli_close($enlace);
?>