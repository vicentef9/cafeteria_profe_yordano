document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('supplierForm');
    form.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        fetch('../../php/guardar_proveedor.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Proveedor guardado correctamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    };
});

function editarProveedor(id) {
    fetch('../../php/obtener_proveedor.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('proveedor_id').value = data.proveedor.id;
                document.getElementById('nombre').value = data.proveedor.nombre;
                document.getElementById('contacto').value = data.proveedor.contacto;
                document.getElementById('telefono').value = data.proveedor.telefono;
                document.getElementById('email').value = data.proveedor.email;
                document.getElementById('categoria').value = data.proveedor.categoria;
                document.getElementById('direccion').value = data.proveedor.direccion;
                document.getElementById('calificacion').value = data.proveedor.calificacion;
                document.getElementById('estado').value = data.proveedor.estado;
                document.getElementById('modalTitle').innerText = 'Editar Proveedor';
                document.getElementById('supplierModal').style.display = 'block';
            } else {
                alert('No se pudo cargar el proveedor');
            }
        });
}

function eliminarProveedor(id) {
    if (confirm('¿Seguro que deseas eliminar este proveedor?')) {
        fetch('../../php/eliminar_proveedor.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Proveedor eliminado');
                location.reload();
            } else {
                alert('Error al eliminar');
            }
        });
    }
}

window.editarProveedor = editarProveedor;
window.eliminarProveedor = eliminarProveedor;