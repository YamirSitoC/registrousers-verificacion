<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$error = '';
$success = '';

// definimos el rol del usuario (1 es el admin, 2 es el usur)
define('ROL_USUARIO', 2);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoge y sanitiza los datos del formulario
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $apellidos = filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_STRING);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
    $matricula = filter_input(INPUT_POST, 'matricula', FILTER_SANITIZE_STRING);
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
    $contrasena = $_POST['contrasena'] ?? '';
    $carrera = filter_input(INPUT_POST, 'carrera', FILTER_SANITIZE_STRING);
    $semestre = filter_input(INPUT_POST, 'semestre', FILTER_SANITIZE_STRING);
    $grupo = filter_input(INPUT_POST, 'grupo', FILTER_SANITIZE_STRING);
    
    // Valida los datos
    if (empty($nombre) || empty($apellidos) || empty($correo) || empty($contrasena)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, introduce un correo electrónico válido.";
    } elseif (strlen($contrasena) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        // Verifica si el correo ya está registrado
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE Correo_Usu = ?");
        $stmt->execute([$correo]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Este correo electrónico ya está registrado.";
        } else {
            // hashea la contraseña
            $hashed_password = hashPassword($contrasena);
        
            $token = generarToken();
            
            try {
         
                $pdo->beginTransaction();       
              
                $stmt = $pdo->prepare("INSERT INTO Usuario (Nombre_Usu, Apellidos_Usu, Telefono_Usu, Matricula_Usu, Correo_Usu, Contrasena_Usu, Carrera, Semestre, Grupo, Rol_Usu, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')");
                
        
                if ($stmt->execute([$nombre, $apellidos, $telefono, $matricula, $correo, $hashed_password, $carrera, $semestre, $grupo, ROL_USUARIO])) {
                    // almacena el token
                    if (storeTokenInDatabase($correo, $token)) {
                      
                        if (enviarCorreoVerificacion($correo, $token)) {
                            $pdo->commit();
                            $success = "Registro exitoso. Por favor, verifica tu correo electrónico.";
                        } else {
                            $pdo->rollBack();
                            $error = "Registro fallido. No se pudo enviar el correo de verificación.";
                        }
                    } else {
                        $pdo->rollBack();
                        $error = "Error al almacenar el token de verificación.";
                    }
                } else {
                    $pdo->rollBack();
                    $error = "Error al registrar el usuario.";
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Error en la base de datos: " . $e->getMessage();
                error_log("Error en la base de datos durante el registro: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Registro de Usuario</h1>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="post" action="">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="apellidos" placeholder="Apellidos" required>
        <input type="tel" name="telefono" placeholder="Teléfono" required>
        <input type="text" name="matricula" placeholder="Matrícula">
        <input type="email" name="correo" placeholder="Correo electrónico" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required minlength="8">
        <input type="text" name="carrera" placeholder="Carrera" required>
        <input type="text" name="semestre" placeholder="Semestre">
        <input type="text" name="grupo" placeholder="Grupo">
        <button type="submit">Registrarse</button>
    </form>
    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    <script src="js/script.js"></script>
</body>
</html>