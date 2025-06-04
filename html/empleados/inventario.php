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

// Obtener datos del inventario
$query = "SELECT i.*, p.nombre as producto_nombre, p.categoria 
          FROM inventario i 
          JOIN productos p ON i.producto_id = p.id";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - Sistema de Cafetería</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <h2>Sistema de Cafetería</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="productos.php" class="nav-item">Productos</a></li>
                <li><a href="inventario.php" class="nav-item active">Inventario</a></li>
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
                <h1>Gestión de Inventario</h1>
                <button class="add-button" onclick="mostrarFormulario()">Agregar Producto al Inventario</button>
            </header>
            
            <div class="search-filters">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Buscar producto...">
                    <button class="search-button" onclick="filtrarInventario()">
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
                    
                    <div class="filter-group">
                        <label for="filterStock">Estado de Stock</label>
                        <select id="filterStock">
                            <option value="">Todos</option>
                            <option value="bajo">Stock Bajo</option>
                            <option value="normal">Stock Normal</option>
                            <option value="alto">Stock Alto</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filterDiscount">Descuentos</label>
                        <select id="filterDiscount">
                            <option value="">Todos</option>
                            <option value="con-descuento">Con Descuento</option>
                            <option value="sin-descuento">Sin Descuento</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="inventory-table-container">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Precio Base</th>
                            <th>Descuento</th>
                            <th>Precio Final</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($result) > 0) {
                            foreach($result as $row) {
                                $precio_final = $row['precio_base'] * (1 - ($row['descuento'] / 100));
                                $estado = 'normal';
                                if ($row['stock_actual'] <= $row['stock_minimo']) {
                                    $estado = 'bajo';
                                } elseif ($row['stock_actual'] >= ($row['stock_minimo'] * 2)) {
                                    $estado = 'alto';
                                }
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['producto_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                            <td><?php echo $row['stock_actual']; ?></td>
                            <td><?php echo $row['stock_minimo']; ?></td>
                            <td>$<?php echo number_format($row['precio_base'], 2); ?></td>
                            <td>
                                <div class="discount-badge"><?php echo $row['descuento']; ?>%</div>
                            </td>
                            <td>$<?php echo number_format($precio_final, 2); ?></td>
                            <td><span class="status <?php echo $estado; ?>"><?php echo ucfirst($estado); ?></span></td>
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
                            <td colspan="10" class="no-data">No hay productos en el inventario</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal para agregar/editar producto en inventario -->
            <div id="inventoryModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModal()">&times;</span>
                    <h2 id="modalTitle">Agregar Producto en Inventario</h2>
                    <form id="inventoryForm" action="../../php/guardar_inventario.php" method="POST">
                        <input type="hidden" id="inventario_id" name="inventario_id">
                        <div class="form-group">
                            <label for="producto">Producto</label>
                            <select id="producto" name="producto_id" required>
                                <option value="">Seleccionar producto...</option>
                                <?php
                                $query_productos = "SELECT id, nombre, categoria FROM productos ORDER BY nombre";
                                $stmt_productos = $conn->prepare($query_productos);
                                $stmt_productos->execute();
                                $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);
                                foreach($productos as $producto) {
                                    echo "<option value='" . $producto['id'] . "'>" . 
                                         htmlspecialchars($producto['nombre']) . " (" . 
                                         htmlspecialchars($producto['categoria']) . ")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stockActual">Stock Actual</label>
                            <input type="number" id="stockActual" name="stock_actual" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="stockMinimo">Stock Mínimo</label>
                            <input type="number" id="stockMinimo" name="stock_minimo" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="precioBase">Precio Base</label>
                            <input type="number" id="precioBase" name="precio_base" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="descuento">Descuento (%)</label>
                            <input type="number" id="descuento" name="descuento" min="0" max="100" value="0">
                            <div class="discount-preview">
                                <span>Precio Final: $</span>
                                <span id="precioFinal">0.00</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notas">Notas</label>
                            <textarea id="notas" name="notas" rows="3"></textarea>
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
    <script src="../../js/inventario.js"></script>
</body>
</html> 