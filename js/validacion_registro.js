document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registroForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Limpiar mensajes de error previos
        clearErrors();
        
        // Obtener valores del formulario
        const nombre = document.getElementById('nombre').value.trim();
        const apellido = document.getElementById('apellido').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmarPassword = document.getElementById('confirmar_password').value;
        
        // Validar campos
        let isValid = true;
        
        if (nombre.length < 2) {
            showError('nombre', 'El nombre debe tener al menos 2 caracteres');
            isValid = false;
        }
        
        if (apellido.length < 2) {
            showError('apellido', 'El apellido debe tener al menos 2 caracteres');
            isValid = false;
        }
        
        if (!isValidEmail(email)) {
            showError('email', 'Por favor, ingrese un correo electrónico válido');
            isValid = false;
        }
        
        if (password.length < 6) {
            showError('password', 'La contraseña debe tener al menos 6 caracteres');
            isValid = false;
        }
        
        if (password !== confirmarPassword) {
            showError('confirmar_password', 'Las contraseñas no coinciden');
            isValid = false;
        }
        
        if (isValid) {
            form.submit();
        }
    });
    
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearErrors() {
        const errors = document.querySelectorAll('.error');
        errors.forEach(error => error.remove());
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}); 