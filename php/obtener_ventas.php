<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

try {
    // Construir la consulta base
    $query = "SELECT v.*, e.nombre as empleado_nombre 
              FROM ventas v 
              JOIN empleados e ON v.empleado_id = e.id 
              WHERE 1=1";
    $params = [];

    // Aplicar filtros si existen
    if (isset($_GET['fecha']) && !empty($_GET['fecha'])) {
        $query .= " AND DATE(v.fecha_venta) = :fecha";
        $params[':fecha'] = $_GET['fecha'];
    }

    if (isset($_GET['metodo_pago']) && !empty($_GET['metodo_pago'])) {
        $query .= " AND v.metodo_pago = :metodo_pago";
        $params[':metodo_pago'] = $_GET['metodo_pago'];
    }

    if (isset($_GET['estado']) && !empty($_GET['estado'])) {
        $query .= " AND v.estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }

    if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
        $query .= " AND (v.id LIKE :busqueda OR e.nombre LIKE :busqueda)";
        $params[':busqueda'] = '%' . $_GET['busqueda'] . '%';
    }

    // Ordenar por fecha más reciente
    $query .= " ORDER BY v.fecha_venta DESC";

    // Ejecutar la consulta
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener estadísticas
    $query_stats = "SELECT 
                    COUNT(*) as total_ventas,
                    SUM(total) as total_ingresos,
                    AVG(total) as promedio_venta
                    FROM ventas 
                    WHERE DATE(fecha_venta) = CURDATE()";
    $stmt_stats = $conn->prepare($query_stats);
    $stmt_stats->execute();
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

    // Obtener productos más vendidos
    $query_populares = "SELECT p.nombre, SUM(dv.cantidad) as total_vendido
                       FROM detalles_venta dv
                       JOIN productos p ON dv.producto_id = p.id
                       JOIN ventas v ON dv.venta_id = v.id
                       WHERE DATE(v.fecha_venta) = CURDATE()
                       GROUP BY p.id
                       ORDER BY total_vendido DESC
                       LIMIT 5";
    $stmt_populares = $conn->prepare($query_populares);
    $stmt_populares->execute();
    $productos_populares = $stmt_populares->fetchAll(PDO::FETCH_ASSOC);

    // Combinar resultados
    $resultado = [
        'ventas' => $ventas,
        'estadisticas' => $stats,
        'productos_populares' => $productos_populares
    ];

    header('Content-Type: application/json');
    echo json_encode($resultado);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al obtener las ventas: ' . $e->getMessage()]);
}
?> 