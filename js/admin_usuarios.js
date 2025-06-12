// Cargar usuarios al iniciar la página
document.addEventListener('DOMContentLoaded', cargarUsuarios);
document.getElementById('userForm').addEventListener('submit', function(event) {
    event.preventDefault();
    guardarUsuario();
});

function mostrarFormulario() {
    document.getElementById('modalTitle').textContent = 'Agregar Usuario';
    document.getElementById('userForm').reset();
    document.getElementById('usuario_id').value = ''; // Limpiar ID para nuevo usuario
    document.getElementById('password').setAttribute('required', 'required'); // Contraseña requerida al crear
    document.getElementById('userModal').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Función para cargar la lista de usuarios
async function cargarUsuarios() {
    try {
        const response = await fetch('../../php/usuarios.php?accion=listar');
        
        // Debug the raw response
        const rawText = await response.text();
        console.log('Raw response:', rawText);
        
        // Try parsing the response
        let result;
        try {
            result = JSON.parse(rawText);
        } catch (e) {
            console.error('JSON parse error:', e);
            console.log('Response that failed to parse:', rawText);
            throw new Error('Invalid JSON response from server');
        }

        if (!result.success) {
            throw new Error(result.message || 'Error desconocido');
        }

        const empleadosTableBody = document.getElementById('empleadosTableBody');
        empleadosTableBody.innerHTML = '';

        result.data.forEach(usuario => {
            const estadoClass = usuario.estado === 'activo' ? 'bg-success' : 'bg-danger';
            empleadosTableBody.innerHTML += `
                <tr>
                    <td>${usuario.id}</td>
                    <td>${usuario.nombre}</td>
                    <td>${usuario.apellido}</td>
                    <td>${usuario.email}</td>
                    <td>${usuario.rol}</td>
                    <td><span class="badge ${estadoClass}">${usuario.estado}</span></td>
                    <td>
                        <button class="btn btn-primary" onclick="editarUsuario(${usuario.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger" onclick="eliminarUsuario(${usuario.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
        alert('Error al cargar la lista de usuarios: ' + error.message);
    }
}

// Función para guardar o actualizar un usuario
async function guardarUsuario() {
    try {
        const form = document.getElementById('userForm');
        const formData = new FormData(form);
        const usuarioId = document.getElementById('usuario_id').value;

        const url = `../../php/usuarios.php?accion=${usuarioId ? 'actualizar' : 'crear'}`;
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Error al guardar usuario');
        }

        alert(usuarioId ? 'Usuario actualizado exitosamente' : 'Usuario creado exitosamente');
        cerrarModal();
        form.reset();
        await cargarUsuarios();
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar usuario: ' + error.message);
    }
}

// Función para cargar datos de usuario en el modal de edición
async function editarUsuario(id) {
    try {
        const response = await fetch(`../../php/usuarios.php?accion=obtener&id=${id}`);
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Error al obtener usuario');
        }

        const usuario = result.data;
        
        document.getElementById('modalTitle').textContent = 'Editar Usuario';
        document.getElementById('usuario_id').value = usuario.id;
        document.getElementById('nombre').value = usuario.nombre;
        document.getElementById('apellido').value = usuario.apellido;
        document.getElementById('email').value = usuario.email;
        document.getElementById('password').removeAttribute('required'); // Contraseña opcional al editar
        document.getElementById('rol').value = usuario.rol;
        document.getElementById('estado').value = usuario.estado;
        
        document.getElementById('userModal').style.display = 'block';
    } catch (error) {
        console.error('Error al cargar datos del usuario:', error);
        alert('Error al cargar los datos del usuario: ' + error.message);
    }
}

// Función para eliminar un usuario
async function eliminarUsuario(id) {
    if (!confirm('¿Está seguro de que desea eliminar este usuario?')) {
        return;
    }
    
    try {
        const response = await fetch(`../php/usuarios.php?accion=eliminar&id=${id}`, {
            method: 'DELETE'
        });
        
        const resultado = await response.json();
        
        if (resultado.exito) {
            alert('Usuario eliminado exitosamente');
            cargarUsuarios();
        } else {
            alert('Error al eliminar usuario: ' + resultado.mensaje);
        }
    } catch (error) {
        console.error('Error al eliminar usuario:', error);
        alert('Error al eliminar el usuario');
    }
}