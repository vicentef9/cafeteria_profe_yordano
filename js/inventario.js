// Cargar inventario al iniciar
function cargarInventario() {
    fetch('../../php/empleados/listar_inventario.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('inventarioBody');
            tbody.innerHTML = ''; // Limpiar tabla existente
            data.forEach(item => {
                const precioFinal = (item.precio_base * (1 - item.descuento / 100)).toFixed(2);
                let estado = '';
                if (item.stock_actual <= item.stock_minimo) estado = '<span class="status bajo">Bajo</span>';
                else if (item.stock_actual >= (item.stock_minimo * 2)) estado = '<span class="status alto">Alto</span>';
                else estado = '<span class="status normal">Normal</span>';

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
                    <td>${estado}</td>
                    <td>
                        <button class="action-button edit" data-id="${item.id}">Editar</button>
                        <button class="action-button delete" data-id="${item.id}">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(row);
            });

            // Adjuntar event listeners a los botones de editar y eliminar
            document.querySelectorAll('.action-button.edit').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    editarProducto(id);
                });
            });

            document.querySelectorAll('.action-button.delete').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    eliminarProducto(id);
                });
            });
        })
        .catch(error => {
            console.error('Error al cargar inventario:', error);
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
        });
}

// Función para mostrar el modal
function mostrarFormulario() {
    document.getElementById('inventoryModal').style.display = 'block';
    document.getElementById('inventoryForm').reset();
    document.getElementById('inventario_id').value = '';
}

// Función para cerrar el modal
function cerrarModal() {
    document.getElementById('inventoryModal').style.display = 'none';
}

// Función para editar un producto
function editarProducto(id) {
    document.getElementById('modalTitle').innerText = "Editar Producto en Inventario";
    fetch(`../../php/obtener_inventario.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('inventoryModal').style.display = 'block';
            document.getElementById('inventario_id').value = data.id || '';
            const productoSelect = document.getElementById('producto');
            productoSelect.value = data.producto_id || '';
            document.getElementById('stockActual').value = data.stock_actual || '';
            document.getElementById('stockMinimo').value = data.stock_minimo || '';
            document.getElementById('precioBase').value = data.precio_base || '';
            document.getElementById('descuento').value = data.descuento || '';
            document.getElementById('notas').value = data.notas || '';
            actualizarPrecioFinal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del inventario');
        });
}

// Función para eliminar un producto
function eliminarProducto(id) {
    if (confirm('¿Está seguro de que desea eliminar este producto del inventario?')) {
        const formData = new FormData();
        formData.append('id', id);

        fetch('../../php/eliminar_inventario.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el producto');
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
function guardarInventario(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('inventoryForm'));
    fetch('../../php/guardar_inventario.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success === true) {
            alert(data.message || 'Guardado con éxito');
            cerrarModal();
            window.location.reload();
        } else {
            alert((data && data.error) ? data.error : 'Error al guardar inventario');
        }
    })
    .catch(error => {
        alert('Error de red o de servidor: ' + error);
    });
}

// Eventos
document.addEventListener('DOMContentLoaded', function() {
    cargarInventario();
    cargarProductos();
    document.getElementById('inventoryForm').onsubmit = guardarInventario;
    // Cerrar modal cuando se hace clic en el botón de cerrar
    document.querySelector('.close-button').addEventListener('click', cerrarModal);
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

    // Filtrar inventario
    document.getElementById('searchInput').addEventListener('input', filtrarInventario);
    document.getElementById('filterCategory').addEventListener('change', filtrarInventario);
    document.getElementById('filterStock').addEventListener('change', filtrarInventario);
    document.getElementById('filterDiscount').addEventListener('change', filtrarInventario);
});

// Función para filtrar el inventario
function filtrarInventario() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('filterCategory').value;
    const stock = document.getElementById('filterStock').value;
    const discount = document.getElementById('filterDiscount').value;

    const rows = document.querySelectorAll('.inventory-table tbody tr');

    rows.forEach(row => {
        const producto = row.cells[1].textContent.toLowerCase();
        const categoria = row.cells[2].textContent.toLowerCase();
        const estado = row.cells[8].querySelector('.status').textContent.toLowerCase();
        const descuento = row.cells[6].querySelector('.discount-badge').textContent;

        const matchesSearch = producto.includes(searchTerm);
        const matchesCategory = !category || categoria === category.toLowerCase();
        const matchesStock = !stock || estado === stock.toLowerCase();
        const matchesDiscount = !discount || 
            (discount === 'con-descuento' && descuento !== '0%') ||
            (discount === 'sin-descuento' && descuento === '0%');

        row.style.display = matchesSearch && matchesCategory && matchesStock && matchesDiscount ? '' : 'none';
    });
}