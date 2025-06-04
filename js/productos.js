// Función para mostrar el modal
function mostrarFormulario() {
    document.getElementById('modalTitle').textContent = 'Agregar Producto';
    document.getElementById('productForm').reset();
    document.getElementById('producto_id').value = '';
    document.getElementById('productModal').style.display = 'block';
}

// Función para cerrar el modal
function cerrarModal() {
    document.getElementById('productModal').style.display = 'none';
}

// Función para editar un producto
function editarProducto(id) {
    document.getElementById('modalTitle').innerText = "Editar Producto";
    fetch(`../../php/obtener_producto.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            // Mostrar el modal antes de llenar los campos
            document.getElementById('productModal').style.display = 'block';
            // Llenar los campos
            document.getElementById('producto_id').value = data.id || '';
            document.getElementById('nombre').value = data.nombre || '';
            document.getElementById('categoria').value = data.categoria || '';
            document.getElementById('descripcion').value = data.descripcion || '';
            document.getElementById('fecha_vencimiento').value = data.fecha_vencimiento || '';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del producto');
        });
}

// Función para eliminar un producto
function eliminarProducto(id) {
    if (confirm('¿Está seguro de que desea eliminar este producto?')) {
        fetch(`../../php/eliminar_producto.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Producto eliminado correctamente');
                location.reload();
            } else {
                alert(data.error || 'Error al eliminar el producto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el producto');
        });
    }
}

// Función para filtrar productos
function filtrarProductos() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('filterCategory').value;
    const rows = document.querySelectorAll('.products-table tbody tr');

    rows.forEach(row => {
        const nombre = row.cells[1].textContent.toLowerCase();
        const categoria = row.cells[2].textContent.toLowerCase();
        
        const matchesSearch = nombre.includes(searchTerm);
        const matchesCategory = !categoryFilter || categoria === categoryFilter.toLowerCase();

        row.style.display = matchesSearch && matchesCategory ? '' : 'none';
    });
}

// Cerrar el modal si se hace clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('productModal');
    if (event.target == modal) {
        cerrarModal();
    }
}

// Eventos
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar modal cuando se hace clic en el botón de cerrar
    document.querySelector('.close-button').addEventListener('click', cerrarModal);

    // Filtrar productos
    document.getElementById('searchInput').addEventListener('input', filtrarProductos);
    document.getElementById('filterCategory').addEventListener('change', filtrarProductos);
});

// Función para guardar el producto
function guardarProducto(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('productForm'));
    
    fetch('../../php/guardar_producto.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            cerrarModal();
            location.reload();
        } else {
            alert(data.error || 'Error al guardar el producto');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar el producto');
    });

    return false;
} 