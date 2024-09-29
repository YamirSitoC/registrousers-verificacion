<?php
require_once '../config/database.php';
require_once '../includes/session.php';

// Verifica si el usuario ha iniciado sesión
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Obtiene la información del usuario
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM Usuario WHERE Id_Usu = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Maneja la acción de cerrar sesión
if (isset($_POST['logout'])) {
    logout();
    header('Location: index.php');
    exit();
}

// Manejo de carga de archivos
$uploadMessage = '';
if (isset($_FILES['profile_picture'])) {
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["profile_picture"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));

    // Verifica si el archivo ya existe
    if (file_exists($targetFile)) {
        $uploadMessage = "Lo siento, el archivo ya existe.";
        $uploadOk = 0;
    }

    // Verifica el tamaño del archivo
    if ($_FILES["profile_picture"]["size"] > 500000) {
        $uploadMessage = "Lo siento, tu archivo es demasiado grande.";
        $uploadOk = 0;
    }

    // Permite solo ciertos formatos de archivo
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $uploadMessage = "Lo siento, solo se permiten archivos JPG, JPEG, PNG & GIF.";
        $uploadOk = 0;
    }

    // Intenta subir el archivo si todas las verificaciones pasaron
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
            $uploadMessage = "El archivo ". basename( $_FILES["profile_picture"]["name"]). " ha sido subido.";
        } else {
            $uploadMessage = "Lo siento, hubo un error al subir tu archivo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenido, <?php echo htmlspecialchars($user['Nombre_Usu']); ?>!</h1>
        
        <h2>Información del usuario</h2>
        <ul>
            <li>Nombre: <?php echo htmlspecialchars($user['Nombre_Usu'] . ' ' . $user['Apellidos_Usu']); ?></li>
            <li>Correo: <?php echo htmlspecialchars($user['Correo_Usu']); ?></li>
            <li>Carrera: <?php echo htmlspecialchars($user['Carrera']); ?></li>
            <li>Semestre: <?php echo htmlspecialchars($user['Semestre']); ?></li>
            <li>Grupo: <?php echo htmlspecialchars($user['Grupo']); ?></li>
        </ul>
        
        <h2>Subir foto de perfil</h2>
        <form action="" method="post" enctype="multipart/form-data">
            Selecciona una imagen:
            <input type="file" name="profile_picture" id="profile_picture">
            <input type="submit" value="Subir Imagen" name="submit">
        </form>
        
        <?php
        if (!empty($uploadMessage)) {
            echo "<p>$uploadMessage</p>";
        }
        ?>
        
        <form method="post" action="">
            <button type="submit" name="logout">Cerrar sesión</button>
        </form>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
Las