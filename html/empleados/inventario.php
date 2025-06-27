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
              JOIN productos p ON i.producto_id = p.id";
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
    <link rel="stylesheet" href="../../css/styles-inventario.css?v=20">
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

            <div class="suppliers-table-container">
                <table class="suppliers-table">
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
                    <tbody id="inventarioBody">
                        <!-- Las filas serán generadas dinámicamente por JavaScript -->
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
    // Botón cancelar cierra el modal
    const cancelBtn = document.querySelector('#inventoryForm .cancel-button');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            cerrarModal();
        });
    }
    // Cerrar modal cuando se hace clic en el botón de cerrar
    const closeBtn = document.querySelector('.close-button');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            cerrarModal();
        });
    }
    // Cerrar modal cuando se hace clic fuera del contenido
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('inventoryModal');
        if (event.target === modal) {
            cerrarModal();
        }
    });
    // Actualizar precio final cuando cambian los valores
    document.getElementById('precioBase').addEventListener('input', actualizarPrecioFinal);
    document.getElementById('descuento').addEventListener('input', actualizarPrecioFinal);
    // Configurar filtros - IGUAL QUE EN PROVEEDORES
    document.getElementById('searchInput').addEventListener('input', filtrarInventario);
    document.getElementById('filterCategory').addEventListener('change', filtrarInventario);
    document.getElementById('filterStock').addEventListener('change', filtrarInventario);
    document.getElementById('filterDiscount').addEventListener('change', filtrarInventario);
});

// Cargar inventario al iniciar
function cargarInventario() {
    fetch('../../php/empleados/listar_inventario.php')
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) {
                console.error('El inventario recibido no es un array:', data);
                inventarioData = [];
            } else {
                inventarioData = data;
            }
            renderInventarioTable(inventarioData);
        })
        .catch(error => {
            console.error('Error al cargar inventario:', error);
            const tbody = document.getElementById('inventarioBody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="no-data">Error al cargar el inventario</td></tr>';
        });
}

// Función para renderizar la tabla de inventario
function renderInventarioTable(data) {
    const tbody = document.getElementById('inventarioBody');
    if (!tbody) {
        console.error('No se encontró el tbody con id="inventarioBody"');
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
        let estado = '';
        let estadoClass = '';
        if (item.stock_actual <= item.stock_minimo) {
            estado = 'Bajo';
            estadoClass = 'bajo';
        } else if (item.stock_actual >= (item.stock_minimo * 2)) {
            estado = 'Alto';
            estadoClass = 'alto';
        } else {
            estado = 'Normal';
            estadoClass = 'normal';
        }
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.producto_nombre}</td>
            <td>${item.categoria}</td>
            <td>${item.stock_actual}</td>
            <td>${item.stock_minimo}</td>
            <td>$${parseFloat(item.precio_base).toFixed(2)}</td>
            <td><div class="discount-badge">${item.descuento}%</div></td>
            <td>$${precioFinal}</td>
            <td><span class="status ${estadoClass}">${estado}</span></td>
            <td>
                <button class="action-button edit" onclick="editarProducto(${item.id})">Editar</button>
                <button class="action-button delete" onclick="eliminarProducto(${item.id})">Eliminar</button>
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
            data.forEach(item => {
                select.innerHTML += `<option value="${item.id}">${item.nombre}</option>`;
            });
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
        });
}

// Función para mostrar el modal
function mostrarFormulario() {
    document.getElementById('modalTitle').textContent = 'Agregar Producto al Inventario';
    document.getElementById('inventoryForm').reset();
    document.getElementById('inventario_id').value = '';
    document.getElementById('inventoryModal').style.display = 'block';
}

// Función para cerrar el modal con animación
function cerrarModal() {
    const modal = document.getElementById('inventoryModal');
    if (modal) {
        modal.classList.add('fade-out');
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('fade-out');
        }, 300); // Duración de la animación
    }
}

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo = 'success') {
    let msgDiv = document.getElementById('msgInventario');
    if (!msgDiv) {
        msgDiv = document.createElement('div');
        msgDiv.id = 'msgInventario';
        msgDiv.style.position = 'fixed';
        msgDiv.style.top = '20px';
        msgDiv.style.right = '20px';
        msgDiv.style.zIndex = '9999';
        msgDiv.style.padding = '15px 25px';
        msgDiv.style.borderRadius = '8px';
        msgDiv.style.fontWeight = 'bold';
        msgDiv.style.transition = 'opacity 0.3s';
        document.body.appendChild(msgDiv);
    }
    msgDiv.textContent = mensaje;
    msgDiv.style.background = tipo === 'success' ? '#4caf50' : '#f44336';
    msgDiv.style.color = '#fff';
    msgDiv.style.opacity = '1';
    msgDiv.style.display = 'block';
    setTimeout(() => {
        msgDiv.style.opacity = '0';
        setTimeout(() => { msgDiv.style.display = 'none'; }, 300);
    }, 2000);
}

// Función para editar un producto
function editarProducto(id) {
    document.getElementById('modalTitle').textContent = "Editar Producto en Inventario";
    fetch(`../../php/obtener_inventario.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            const inventario = data.inventario || data; // Soporta ambos formatos
            document.getElementById('inventoryModal').style.display = 'block';
            document.getElementById('inventario_id').value = inventario.id || '';
            const productoSelect = document.getElementById('producto');
            productoSelect.value = inventario.producto_id || '';
            document.getElementById('stockActual').value = inventario.stock_actual || '';
            document.getElementById('stockMinimo').value = inventario.stock_minimo || '';
            document.getElementById('precioBase').value = inventario.precio_base || '';
            document.getElementById('descuento').value = inventario.descuento || '';
            document.getElementById('notas').value = inventario.notas || '';
            actualizarPrecioFinal();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al cargar los datos del inventario', 'error');
        });
}

// Función para eliminar un producto
function eliminarProducto(id) {
    if (confirm('¿Está seguro de que desea eliminar este producto del inventario?')) {
        fetch(`../../php/eliminar_inventario.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarMensaje('Producto eliminado del inventario correctamente', 'success');
                setTimeout(() => { location.reload(); }, 1000);
            } else {
                mostrarMensaje(data.error || 'Error al eliminar el producto', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al eliminar el producto', 'error');
        });
    }
}

// Función para actualizar el precio final cuando cambia el descuento
function actualizarPrecioFinal() {
    const precioBase = parseFloat(document.getElementById('precioBase').value) || 0;
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    const precioFinal = precioBase * (1 - (descuento / 100));
    document.getElementById('precioFinal').textContent = precioFinal.toFixed(2);
}

// Función para guardar inventario
function guardarInventario(event) {
    if (event) event.preventDefault();
    const form = document.getElementById('inventoryForm');
    if (!form) return false;
    const formData = new FormData(form);
    fetch('../../php/guardar_inventario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            alert('Respuesta inválida del servidor:\n' + text);
            data = { success: false, error: 'Respuesta inválida del servidor' };
        }
        if (data.success) {
            mostrarMensaje(data.message || 'Inventario guardado correctamente', 'success');
            cerrarModal();
            setTimeout(() => { location.reload(); }, 1000);
        } else {
            mostrarMensaje(data.error || 'Error al guardar el inventario', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al guardar el inventario', 'error');
    });
    return false;
}

// Función para filtrar el inventario - BASADA EN EL CÓDIGO DE PROVEEDORES
function filtrarInventario() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('filterCategory').value;
    const stockFilter = document.getElementById('filterStock').value;
    const discountFilter = document.getElementById('filterDiscount').value;
    // Filtrar los datos originales
    const filteredData = inventarioData.filter(item => {
        const nombre = (item.producto_nombre || '').toLowerCase();
        const categoria = (item.categoria || '').toLowerCase();
        // Determinar estado del stock
        let estado = 'normal';
        if (item.stock_actual <= item.stock_minimo) {
            estado = 'bajo';
        } else if (item.stock_actual >= (item.stock_minimo * 2)) {
            estado = 'alto';
        }
        const descuento = parseFloat(item.descuento) || 0;
        const matchesSearch = nombre.includes(searchTerm);
        const matchesCategory = !categoryFilter || categoria === categoryFilter.toLowerCase();
        const matchesStock = !stockFilter || estado === stockFilter.toLowerCase();
        const matchesDiscount = !discountFilter || 
            (discountFilter === 'con-descuento' && descuento > 0) ||
            (discountFilter === 'sin-descuento' && descuento === 0);
        return matchesSearch && matchesCategory && matchesStock && matchesDiscount;
    });
    // Re-renderizar la tabla con los datos filtrados
    renderInventarioTable(filteredData);
}
    </script>
</body>
</html>