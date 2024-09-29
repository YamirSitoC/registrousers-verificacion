<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Por favor, introduce un correo electrónico válido.";
    } else {
        // Verificar si el usuario existe y su estado
        $stmt = $pdo->prepare("SELECT status FROM Usuario WHERE Correo_Usu = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['status'] == 'pendiente') {
                // crea un token
                $token = generarToken();
                
                // guarda el token
                if (storeTokenInDatabase($email, $token)) {
                    // Enviar el correo de verificación
                    if (enviarCorreoVerificacion($email, $token)) {
                        $message = "Se ha enviado un nuevo correo de verificación. Por favor, revisa tu bandeja de entrada.";
                    } else {
                        $message = "Hubo un problema al enviar el correo de verificación. Por favor, inténtalo de nuevo más tarde.";
                    }
                } else {
                    $message = "Error al generar el nuevo token de verificación. Por favor, inténtalo de nuevo más tarde.";
                }
            } elseif ($user['status'] == 'activo') {
                $message = "Esta cuenta ya ha sido verificada. Puedes iniciar sesión.";
            } else {
                $message = "El estado de tu cuenta es inválido. Por favor, contacta al soporte.";
            }
        } else {
            $message = "No se encontró ninguna cuenta con este correo electrónico.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reenviar Correo de Verificación</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Reenviar Correo de Verificación</h1>
    
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <form method="post" action="">
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <button type="submit">Reenviar Correo de Verificación</button>
    </form>
    
    <p><a href="login.php">Volver al inicio de sesión</a></p>
</body>
</html>