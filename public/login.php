<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
    $contrasena = $_POST['contrasena'] ?? '';
    
    if (empty($correo) || empty($contrasena)) {
        $error = "Por favor, completa todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, introduce un correo electrónico válido.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT Id_Usu, Nombre_Usu, Contrasena_Usu, status FROM Usuario WHERE Correo_Usu = ?");
            $stmt->execute([$correo]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($contrasena, $user['Contrasena_Usu'])) {
                if ($user['status'] == 'activo') {
                    session_regenerate_id(true);
                    
                    login($user['Id_Usu'], $user['Nombre_Usu']);
                    
                    // registrar el registro
                    logLoginAttempt($correo, true);
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = "Tu cuenta aún no ha sido verificada o está inactiva.";
                }
            } else {
                $error = "Credenciales incorrectas.";
                // Registrar el intento de inicio de sesión fallido
                logLoginAttempt($correo, false);
            }
        } catch (PDOException $e) {
            $error = "Error en el servidor. Por favor, inténtalo de nuevo más tarde.";
            error_log("Error en la base de datos durante el inicio de sesión: " . $e->getMessage());
        }
    }
}

// Función para registrar intentos de inicio de sesión
function logLoginAttempt($email, $success) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (email, success, ip_address, attempt_time) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$email, $success ? 1 : 0, $_SERVER['REMOTE_ADDR']]);
    } catch (PDOException $e) {
        error_log("Error al registrar intento de inicio de sesión: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Iniciar Sesión</h1>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="post" action="">
        <input type="email" name="correo" placeholder="Correo electrónico" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <button type="submit">Iniciar Sesión</button>
    </form>
    <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
    <p><a href="forgot_password.php">¿Olvidaste tu contraseña?</a></p>
    <script src="js/script.js"></script>
</body>
</html>