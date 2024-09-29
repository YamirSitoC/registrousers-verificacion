<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // buscar el token 
        $stmt = $pdo->prepare("SELECT email, created_at FROM verification_tokens WHERE token = ?");
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $email = $result['email'];
            $created_at = strtotime($result['created_at']);
            
            if (!tokenExpirado($created_at)) {
                // cambia de status
                $updateStmt = $pdo->prepare("UPDATE Usuario SET status = 'activo' WHERE Correo_Usu = ?");
                
                if ($updateStmt->execute([$email])) {
                    // elimina token
                    deleteTokenFromDatabase($email);
                    $message = "Tu cuenta ha sido verificada. Ahora puedes <a href='login.php'>iniciar sesión</a>.";
                } else {
                    $message = "Hubo un problema al verificar tu cuenta.";
                }
            } else {
                $message = "El token de verificación ha expirado. Por favor, solicita uno nuevo.";
            }
        } else {
            $message = "Token de verificación inválido.";
        }
    } catch (PDOException $e) {
        $message = "Error en la base de datos: " . $e->getMessage();
        error_log("Error en la base de datos durante la verificación: " . $e->getMessage());
    }
} else {
    $message = "Token de verificación no proporcionado.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Cuenta</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Verificación de Cuenta</h1>
    <div class="message">
        <?php echo $message; ?>
    </div>
    <?php if (strpos($message, "expirado") !== false): ?>
    <p><a href="resend_verification.php">Solicitar un nuevo token de verificación</a></p>
    <?php endif; ?>
</body>
</html>