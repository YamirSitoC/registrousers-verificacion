<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function enviarCorreoVerificacion($email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'isoftuadeo@gmail.com';
        $mail->Password   = 'vkdx rogk nhee odwk'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('isoftuadeo@gmail.com', 'Sistema de Verificacion');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Verificación de su cuenta';
        $verifyUrl = "http://localhost/RegistroDeUsuarios/public/verify.php?token=$token";
        $mail->Body    = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2>Estimado(a) usuario,</h2>
            <p>Gracias por registrarse en nuestro sistema. Para completar el proceso de verificación de su cuenta, por favor haga clic en el siguiente enlace:</p>
            <p><a href='$verifyUrl' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; border-radius: 5px;'>Verificar mi cuenta</a></p>
            <p>Si el botón no funciona, puede copiar y pegar el siguiente enlace en su navegador:</p>
            <p>$verifyUrl</p>
            <p>Este enlace expirará en 24 horas.</p>
            <p>Si usted no ha solicitado esta verificación, por favor ignore este correo.</p>
            <p>Atentamente,<br>El equipo de soporte</p>
        </body>
        </html>";

        $mail->AltBody = "Estimado(a) usuario,\n\nGracias por registrarse en nuestro sistema. Para completar el proceso de verificación de su cuenta, por favor visite el siguiente enlace:\n\n$verifyUrl\n\nEste enlace expirará en 24 horas.\n\nSi usted no ha solicitado esta verificación, por favor ignore este correo.\n\nAtentamente,\nEl equipo de soporte";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}

function generarToken() {
    return bin2hex(random_bytes(32));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function tokenExpirado($token_tiempo, $expiracion = 86400) {
    return (time() - $token_tiempo) > $expiracion;
}

function storeTokenInDatabase($email, $token) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO verification_tokens (email, token, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE token = ?, created_at = NOW()");
        $stmt->execute([$email, $token, $token]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al almacenar el token en la base de datos: " . $e->getMessage());
        return false;
    }
}

function getTokenFromDatabase($email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT token, created_at FROM verification_tokens WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener el token de la base de datos: " . $e->getMessage());
        return false;
    }
}

function deleteTokenFromDatabase($email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM verification_tokens WHERE email = ?");
        $stmt->execute([$email]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al eliminar el token de la base de datos: " . $e->getMessage());
        return false;
    }
}