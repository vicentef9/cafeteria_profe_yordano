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
try {
    $query = "SELECT i.*, p.nombre as producto_nombre, p.categoria 
              FROM inventario i 
              JOIN productos p ON i.producto_id = p.id
              ORDER BY p.nombre";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div style='color:red; font-weight:bold;'>No se pudo conectar a la base de datos. Verifique que el servidor MySQL esté en ejecución y la configuración sea correcta.<br>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $result = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - Sistema de Cafetería</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="../../css/styles-inventario.css?v=2.0">
</head>
<body>
   <div class="dashboard-container">
        <div class="sidebar">
            <div class="logo">
                <h2>Sistema de <br> Cafetería</h2>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="interfase_administrador.html" class="nav-item active">Inicio</a></li>
                    <li><a href="catalogo.html" class="nav-item">Catálogo</a></li>
                    <li><a href="admin_usuarios.php" class="nav-item active">Usuarios</a></li>
                    <li><a href="productos.php" class="nav-item">Productos</a></li>
                    <li><a href="inventario.php" class="nav-item">Inventario</a></li>
                    <li><a href="proveedores.php" class="nav-item">Proveedores</a></li>
                    <li><a href="ventas.php" class="nav-item">Ventas</a></li>
                    <li><a href="soporte.html" class="nav-item">Soporte</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <div class="inventory-header">
                <h1>Gestión de Inventario</h1>
                <button class="add-product-btn" onclick="openInventoryModal()">Agregar Producto al Inventario</button>
            </div>
            
            <div class="search-filters">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar producto..." id="searchInput">
                    <button class="search-button" onclick="searchProducts()">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>
                </div>
                
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="categoryFilter">Categoría</label>
                        <select id="categoryFilter">
                            <option value="">Todas las categorías</option>
                            <option value="cafe">Café</option>
                            <option value="postres">Postres</option>
                            <option value="bebidas">Bebidas</option>
                            <option value="alimentos">Alimentos</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="stockFilter">Estado de Stock</label>
                        <select id="stockFilter">
                            <option value="">Todos</option>
                            <option value="normal">Normal</option>
                            <option value="low">Bajo</option>
                            <option value="critical">Crítico</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="discountFilter">Descuentos</label>
                        <select id="discountFilter">
                            <option value="">Todos</option>
                            <option value="with-discount">Con descuento</option>
                            <option value="no-discount">Sin descuento</option>
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
                    <tbody id="inventoryTableBody">
                        <tr>
                            <td colspan="10" class="no-data">No hay productos en el inventario</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar inventario -->
    <div id="inventoryModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Agregar Producto al Inventario</h3>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            <form id="inventoryForm" onsubmit="guardarInventario(event)">
                <input type="hidden" id="inventario_id" name="inventario_id">
                
                <div class="form-group">
                    <label for="producto">Producto *</label>
                    <select id="producto" name="producto_id" required>
                        <option value="">Seleccionar producto...</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="stockActual">Stock Actual *</label>
                        <input type="number" id="stockActual" name="stock_actual" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="stockMinimo">Stock Mínimo *</label>
                        <input type="number" id="stockMinimo" name="stock_minimo" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="precioBase">Precio Base *</label>
                        <input type="number" id="precioBase" name="precio_base" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="descuento">Descuento (%)</label>
                        <input type="number" id="descuento" name="descuento" min="0" max="100" step="0.01" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Precio Final: $<span id="precioFinal">0.00</span></label>
                </div>
                
                <div class="form-group">
                    <label for="notas">Notas</label>
                    <textarea id="notas" name="notas" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="cerrarModal()" class="btn-cancel">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
// Variable global para almacenar los datos originales del inventario  
let inventarioData = [];

document.addEventListener('DOMContentLoaded', function() {
    cargarInventario();
    cargarProductos();
    
    // Configurar el formulario
    const form = document.getElementById('inventoryForm');
    if (form) {
        form.onsubmit = guardarInventario;
    }
    
    // Configurar filtros
    document.getElementById('searchInput').addEventListener('input', filtrarInventario);
    document.getElementById('categoryFilter').addEventListener('change', filtrarInventario);
    document.getElementById('stockFilter').addEventListener('change', filtrarInventario);
    document.getElementById('discountFilter').addEventListener('change', filtrarInventario);
    
    // Actualizar precio final cuando cambian los valores
    document.getElementById('precioBase').addEventListener('input', actualizarPrecioFinal);
    document.getElementById('descuento').addEventListener('input', actualizarPrecioFinal);
});

// Función para abrir el modal (llamada desde el botón)
function openInventoryModal() {
    document.getElementById('modalTitle').textContent = 'Agregar Producto al Inventario';
    document.getElementById('inventoryForm').reset();
    document.getElementById('inventario_id').value = '';
    document.getElementById('precioFinal').textContent = '0.00';
    cargarProductos(); // Recargar productos cada vez que se abre el modal
    document.getElementById('inventoryModal').style.display = 'block';
}

// Cargar inventario al iniciar
function cargarInventario() {
    // Usar los datos PHP directamente
    const inventarioFromPHP = <?php echo json_encode($result); ?>;
    inventarioData = inventarioFromPHP;
    renderInventarioTable(inventarioData);
}

// Función para renderizar la tabla de inventario
function renderInventarioTable(data) {
    const tbody = document.getElementById('inventoryTableBody');
    if (!tbody) {
        console.error('No se encontró el tbody con id="inventoryTableBody"');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!data || data.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="10" class="no-data">No hay productos en el inventario</td>';
        tbody.appendChild(row);
        return;
    }
    
    data.forEach(item => {
        const precioFinal = (item.precio_base * (1 - item.descuento / 100)).toFixed(2);
        
        let estado = 'Normal';
        let estadoClass = 'normal';
        
        if (item.stock_actual <= 0) {
            estado = 'Sin Stock';
            estadoClass = 'critical';
        } else if (item.stock_actual <= item.stock_minimo) {
            estado = 'Bajo';
            estadoClass = 'low';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.producto_nombre}</td>
            <td>${item.categoria}</td>
            <td>${item.stock_actual}</td>
            <td>${item.stock_minimo}</td>
            <td>$${parseFloat(item.precio_base).toFixed(2)}</td>
            <td>${item.descuento}%</td>
            <td>$${precioFinal}</td>
            <td><span class="stock-alert ${estadoClass}">${estado}</span></td>
            <td>
                <button class="action-button edit" onclick="editarProducto(${item.id})">Editar</button>
                <button class="action-button delete" onclick="eliminarProducto(${item.id})">Eliminar</button>
                <button class="action-button restock" onclick="reabastecer(${item.id})">Reabastecer</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Cargar productos en el select
function cargarProductos() {
    fetch('../../php/empleados/listar_productos.php')
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('producto');
            select.innerHTML = '<option value="">Seleccionar producto...</option>';
            
            // Obtener IDs de productos que ya están en inventario
            const productosEnInventario = inventarioData.map(item => parseInt(item.producto_id));
            
            data.forEach(item => {
                // Solo mostrar productos que no están en inventario al agregar nuevo
                const inventarioId = document.getElementById('inventario_id').value;
                if (!inventarioId && productosEnInventario.includes(item.id)) {
                    // No agregar productos que ya están en inventario
                    return;
                }
                select.innerHTML += `<option value="${item.id}">${item.nombre}</option>`;
            });
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
        });
}

// Función para filtrar el inventario
function filtrarInventario() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const stockFilter = document.getElementById('stockFilter').value;
    const discountFilter = document.getElementById('discountFilter').value;
    
    const filteredData = inventarioData.filter(item => {
        const nombre = (item.producto_nombre || '').toLowerCase();
        const categoria = (item.categoria || '').toLowerCase();
        
        // Determinar estado del stock
        let estado = 'normal';
        if (item.stock_actual <= 0) {
            estado = 'critical';
        } else if (item.stock_actual <= item.stock_minimo) {
            estado = 'low';
        }
        
        const descuento = parseFloat(item.descuento) || 0;
        
        const matchesSearch = nombre.includes(searchTerm);
        const matchesCategory = !categoryFilter || categoria === categoryFilter.toLowerCase();
        const matchesStock = !stockFilter || estado === stockFilter;
        const matchesDiscount = !discountFilter || 
            (discountFilter === 'with-discount' && descuento > 0) ||
            (discountFilter === 'no-discount' && descuento === 0);
        
        return matchesSearch && matchesCategory && matchesStock && matchesDiscount;
    });
    
    renderInventarioTable(filteredData);
}

// Función para cerrar el modal
function cerrarModal() {
    document.getElementById('inventoryModal').style.display = 'none';
}

// Función para actualizar precio final
function actualizarPrecioFinal() {
    const precioBase = parseFloat(document.getElementById('precioBase').value) || 0;
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    const precioFinal = precioBase * (1 - (descuento / 100));
    document.getElementById('precioFinal').textContent = precioFinal.toFixed(2);
}

// Función para guardar inventario
function guardarInventario(event) {
    if (event) event.preventDefault();
    
    const formData = new FormData(document.getElementById('inventoryForm'));
    
    fetch('../../php/guardar_inventario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Producto guardado en inventario correctamente');
            cerrarModal();
            // Recargar la página para mostrar los cambios
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'No se pudo guardar el producto'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar el producto en inventario');
    });
    
    return false;
}

// Función para editar producto
function editarProducto(id) {
    // Buscar el producto en los datos
    const producto = inventarioData.find(item => item.id == id);
    if (!producto) {
        alert('Producto no encontrado');
        return;
    }
    
    // Llenar el formulario con los datos del producto
    document.getElementById('modalTitle').textContent = 'Editar Producto en Inventario';
    document.getElementById('inventario_id').value = producto.id;
    document.getElementById('stockActual').value = producto.stock_actual;
    document.getElementById('stockMinimo').value = producto.stock_minimo;
    document.getElementById('precioBase').value = producto.precio_base;
    document.getElementById('descuento').value = producto.descuento || 0;
    document.getElementById('notas').value = producto.notas || '';
    
    // Cargar productos y seleccionar el actual
    fetch('../../php/empleados/listar_productos.php')
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('producto');
            select.innerHTML = '<option value="">Seleccionar producto...</option>';
            data.forEach(item => {
                const selected = item.id == producto.producto_id ? 'selected' : '';
                select.innerHTML += `<option value="${item.id}" ${selected}>${item.nombre}</option>`;
            });
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
        });
    
    // Actualizar precio final
    actualizarPrecioFinal();
    
    // Mostrar el modal
    document.getElementById('inventoryModal').style.display = 'block';
}

// Función para eliminar producto
function eliminarProducto(id) {
    if (confirm('¿Está seguro de eliminar este producto del inventario?')) {
        fetch('../../php/eliminar_inventario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'inventario_id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Producto eliminado del inventario correctamente');
                // Recargar la página para mostrar los cambios
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'No se pudo eliminar el producto'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el producto del inventario');
        });
    }
}

// Función para reabastecer
function reabastecer(id) {
    const cantidad = prompt('Ingrese la cantidad a reabastecer:');
    if (cantidad && !isNaN(cantidad) && parseInt(cantidad) > 0) {
        fetch('../../php/reabastecer_inventario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'inventario_id=' + id + '&cantidad=' + cantidad
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Stock reabastecido correctamente');
                // Recargar la página para mostrar los cambios
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'No se pudo reabastecer el producto'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al reabastecer el producto');
        });
    }
}
    </script>
</body>
</html>