<?php
session_start();
if (isset($_SESSION['errores'])) {
    echo '<div class="error-message">';
    foreach ($_SESSION['errores'] as $error) {
        echo '<p>' . htmlspecialchars($error) . '</p>';
    }
    echo '</div>';
    unset($_SESSION['errores']);
}
if (isset($_SESSION['mensaje'])) {
    echo '<div class="success-message">' . htmlspecialchars($_SESSION['mensaje']) . '</div>';
    unset($_SESSION['mensaje']);
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Cafetería</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Registro de Usuario</h1>
            <form class="login-form" action="../../php/procesar_registro.php" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirmar_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirmar_password" name="confirmar_password" required>
                </div>
                <button type="submit" class="login-button">Registrarse</button>
            </form>
            <p class="register-link">¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a></p>
        </div>
    </div>
    <script src="../js/validacion_registro.js"></script>
</body>
</html> 