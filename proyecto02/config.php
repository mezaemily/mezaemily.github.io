<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'dulce_encanto');
define('DB_USER', 'root');
define('DB_PASS', '');

// Crear conexión
try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si el usuario está logueado
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

// Función para verificar si el usuario es admin
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

// Función para proteger páginas
function protegerPagina() {
    if (!estaLogueado()) {
        header("Location: login.php");
        exit();
    }
}

// Función para proteger páginas de admin
function protegerAdmin() {
    if (!esAdmin()) {
        header("Location: dashboard.php");
        exit();
    }
}
?>