<?php
require_once '../includes/session.php';
require_once '../includes/config.php'; 


if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}


$app_name = APP_NAME ?? 'Jornada academica';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a <?php echo htmlspecialchars($app_name); ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
        <header>
            <h1>Bienvenido a <?php echo htmlspecialchars($app_name); ?></h1>
        </header>
        <main>
            <p>Por favor, elige una opción:</p>
            <nav>
                <ul>
                    <li><a href="login.php" class="btn btn-primary">Iniciar sesión</a></li>
                    <li><a href="register.php" class="btn btn-secondary">Registrarse</a></li>
                </ul>
            </nav>
        </main>
        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($app_name); ?>. Todos los derechos reservados.</p>
        </footer>
    </div>
    <script src="js/script.js"></script>
</body>
</html>