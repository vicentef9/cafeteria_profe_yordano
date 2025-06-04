<?php
session_start();
require_once '../../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../autenticacion/login.php');
    exit();
}

// Obtener el rol del usuario
$rol = $_SESSION['rol'];

// Verificar si el usuario tiene permisos para acceder a esta página
if ($rol !== 'empleado' && $rol !== 'admin') {
    header('Location: ../autenticacion/login.php');
    exit();
}

// Obtener datos de productos
$query = "SELECT * FROM productos ORDER BY id";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Sistema de Cafetería</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <h2>Sistema de Cafetería</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="productos.php" class="nav-item active">Productos</a></li>
                <li><a href="inventario.php" class="nav-item">Inventario</a></li>
                <li><a href="proveedores.php" class="nav-item">Proveedores</a></li>
                <li><a href="ventas.php" class="nav-item">Ventas</a></li>
            </ul>
            <div class="user-info">
                <span class="user-name">Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Empleado'); ?></span>
                <a href="../autenticacion/login.php" class="logout-button">Cerrar Sesión</a>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Gestión de Productos</h1>
                <button class="add-button" onclick="mostrarFormulario()">Agregar Producto</button>
            </header>
            
            <div class="search-filters">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Buscar producto...">
                    <button class="search-button" onclick="filtrarProductos()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                
                <div class="filters-container">
                    <div class="filter-group">
                        <label for="filterCategory">Categoría</label>
                        <select id="filterCategory">
                            <option value="">Todas las categorías</option>
                            <option value="cafe">Café</option>
                            <option value="postres">Postres</option>
                            <option value="bebidas">Bebidas</option>
                            <option value="insumos">Insumos</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="products-table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($result) > 0) {
                            foreach($result as $row) {
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                            <td><?php echo htmlspecialchars($row['descripcion'] ?? 'Sin descripción'); ?></td>
                            <td><?php echo isset($row['fecha_vencimiento']) ? date('d/m/Y', strtotime($row['fecha_vencimiento'])) : 'N/A'; ?></td>
                            <td>
                                <button class="action-button edit" onclick="editarProducto(<?php echo $row['id']; ?>)">Editar</button>
                                <button class="action-button delete" onclick="eliminarProducto(<?php echo $row['id']; ?>)">Eliminar</button>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="6" class="no-data">No hay productos registrados</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal para agregar/editar producto -->
            <div id="productModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModal()">&times;</span>
                    <h2 id="modalTitle">Editar Producto</h2>
                    <form id="productForm" action="../../php/guardar_producto.php" method="POST" onsubmit="return guardarProducto(event)">
                        <input type="hidden" id="producto_id" name="producto_id">
                        <div class="form-group">
                            <label for="nombre">Nombre del Producto</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="categoria">Categoría</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Seleccionar categoría...</option>
                                <option value="cafe">Café</option>
                                <option value="postres">Postres</option>
                                <option value="bebidas">Bebidas</option>
                                <option value="insumos">Insumos</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                            <input type="date" id="fecha_vencimiento" name="fecha_vencimiento">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="submit-button">Guardar</button>
                            <button type="button" class="cancel-button" onclick="cerrarModal()">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="../../js/productos.js"></script>
</body>
</html> 