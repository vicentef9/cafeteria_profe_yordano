<?php
/**
 * Archivo session.php
 * 
 * Proporciona funciones para gestionar sesiones de usuario en ComicVerse
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario tiene una sesión iniciada
 * 
 * @return bool Verdadero si hay una sesión activa, falso en caso contrario
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Requiere que el usuario esté autenticado para acceder a una página
 * Si no está autenticado, redirige a la página de login
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Guardar la URL actual para redirigir después del login (opcional)
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirigir al usuario a la página de login
        header('Location: ../vistas/login.php');
        exit();
    }
}

/**
 * Cierra la sesión actual del usuario
 * 
 * @return void
 */
function logout() {
    // Destruir todas las variables de sesión
    $_SESSION = array();

    // Destruir la cookie de sesión si existe
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destruir la sesión
    session_destroy();
}
?>