// Función para validar el formulario de registro
function validateRegisterForm() {
    var nombre = document.getElementById('nombre').value;
    var apellidos = document.getElementById('apellidos').value;
    var correo = document.getElementById('correo').value;
    var contrasena = document.getElementById('contrasena').value;

    if (nombre == "" || apellidos == "" || correo == "" || contrasena == "") {
        alert("Por favor, completa todos los campos obligatorios.");
        return false;
    }

    // Validación simple de correo electrónico
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(correo)) {
        alert("Por favor, introduce un correo electrónico válido.");
        return false;
    }

    // Validación de contraseña (mínimo 8 caracteres)
    if (contrasena.length < 8) {
        alert("La contraseña debe tener al menos 8 caracteres.");
        return false;
    }

    return true;
}

// Función para validar el formulario de login
function validateLoginForm() {
    var correo = document.getElementById('correo').value;
    var contrasena = document.getElementById('contrasena').value;

    if (correo == "" || contrasena == "") {
        alert("Por favor, completa todos los campos.");
        return false;
    }

    return true;
}

// Agregar eventos de escucha a los formularios cuando el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    var registerForm = document.getElementById('registerForm');
    var loginForm = document.getElementById('loginForm');

    if (registerForm) {
        registerForm.onsubmit = validateRegisterForm;
    }

    if (loginForm) {
        loginForm.onsubmit = validateLoginForm;
    }
});