<?php

// Inicia la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Inicia la sesión del usuario y establece una cookie si se solicita recordar
 * 
 * @param int $user_id ID del usuario
 * @param string $email Correo electrónico del usuario (usado como identificador único)
 * @param int $rol_id ID del rol del usuario
 * @param bool $remember Si se debe recordar al usuario
 */
function login($user_id, $email, $rol_id, $remember = false) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['rol_id'] = $rol_id;
    $_SESSION['last_activity'] = time();
    
    if ($remember) {
        $token = $user_id . ':' . hash('sha256', $user_id . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
        setcookie('remember_me', $token, time() + (86400 * 30), "/", "", true, true);
    }
}

/**
 * Cierra la sesión del usuario y elimina las cookies
 */
function logout() {
    $_SESSION = array();
    session_destroy();
    
    setcookie('remember_me', '', time() - 3600, '/');
}

/**
 * Verifica si el usuario ha iniciado sesión
 * 
 * @return bool True si el usuario ha iniciado sesión, False en caso contrario
 */
function is_logged_in() {
    if (isset($_SESSION['user_id'])) {
        // Si han pasado más de 30 minutos desde la última actividad, cierra la sesión
        if (time() - $_SESSION['last_activity'] > 1800) {
            logout();
            return false;
        }
        // Actualiza la última actividad del usuario
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Si la sesión no está activa, verificamos si hay una cookie de "remember_me"
    if (isset($_COOKIE['remember_me'])) {
        list($user_id, $token) = explode(':', $_COOKIE['remember_me']);
        $check_token = hash('sha256', $user_id . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
        
        // Compara el token con el hash almacenado
        if ($token === $check_token) {
            // Obtener datos del usuario desde la base de datos
            $user_data = getUserDataFromDatabase($user_id);
            if ($user_data && $user_data['status'] == 'activo') {
                // Si todo está correcto, vuelve a iniciar la sesión
                login($user_data['Id_Usu'], $user_data['Correo_Usu'], $user_data['Rol_Usu'], true);
                return true;
            }
        }
        
        // Si el token no es válido, elimina la cookie
        setcookie('remember_me', '', time() - 3600, '/');
    }
    
    return false;
}

/**
 * Regenera el ID de sesión para prevenir ataques de fijación de sesión
 */
function regenerate_session() {
    $old_session_id = session_id();
    session_regenerate_id(true);
    $new_session_id = session_id();
}

/**
 * Limpia los datos de entrada para prevenir XSS
 * 
 * @param string $data Los datos a limpiar
 * @return string Los datos limpios
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Guarda los datos del usuario en la sesión, con la opción de excluir campos específicos
 * 
 * @param array $user_data Datos del usuario
 * @param array $exclude_fields Campos a excluir (opcional)
 */
function save_user_data($user_data, $exclude_fields = []) {
    $prohibited_fields = ['Contrasena_Usu'];
    $exclude_fields = array_merge($exclude_fields, $prohibited_fields);
    
    foreach ($user_data as $key => $value) {
        if (!in_array($key, $exclude_fields)) {
            $_SESSION['user_' . $key] = $value;
        }
    }
}

/**
 * Obtiene los datos del usuario de la sesión
 * 
 * @param array $fields Campos específicos a obtener (opcional)
 * @return array Datos del usuario
 */
function get_user_data($fields = []) {
    $user_data = [];
    if (empty($fields)) {
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'user_') === 0) {
                $user_data[substr($key, 5)] = $value;
            }
        }
    } else {
        foreach ($fields as $field) {
            if (isset($_SESSION['user_' . $field])) {
                $user_data[$field] = $_SESSION['user_' . $field];
            }
        }
    }
    return $user_data;
}

/**
 * Función para obtener los datos del usuario de la base de datos
 * Debes implementar esta función según tu estructura de base de datos
 * 
 * @param int $user_id ID del usuario
 * @return array|false Datos del usuario o false si no se encuentra
 */
function getUserDataFromDatabase($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT Id_Usu, Nombre_Usu, Apellidos_Usu, Correo_Usu, Rol_Usu, status FROM Usuario WHERE Id_Usu = ? AND status = 'activo'");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Verifica si el usuario tiene un rol específico
 * 
 * @param int $required_role_id ID del rol requerido
 * @return bool True si el usuario tiene el rol requerido, False en caso contrario
 */
function has_role($required_role_id) {
    return isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == $required_role_id;
}

/**
 * Genera un token de verificación
 * 
 * @return string Token de verificación
 */
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Almacena el token de verificación en la sesión
 * 
 * @param string $token Token de verificación
 * @param string $email Correo electrónico del usuario
 */
function storeVerificationToken($token, $email) {
    $_SESSION['verification_token'] = $token;
    $_SESSION['user_email'] = $email;
    $_SESSION['token_time'] = time();
}

/**
 * Verifica si el token ha expirado
 * 
 * @param int $tokenTime Tiempo de generación del token
 * @return bool True si el token ha expirado, False en caso contrario
 */
function isTokenExpired($tokenTime) {
    $expirationTime = 24 * 60 * 60; // 24 horas en segundos
    return (time() - $tokenTime) > $expirationTime;
}

/**
 * Verifica el token de verificación
 * 
 * @param string $token Token a verificar
 * @return bool True si el token es válido y no ha expirado, False en caso contrario
 */
function verifyToken($token) {
    if (isset($_SESSION['verification_token']) && 
        $token === $_SESSION['verification_token'] && 
        !isTokenExpired($_SESSION['token_time'])) {
        return true;
    }
    return false;
}

/**
 * Limpia las variables de sesión relacionadas con la verificación
 */
function clearVerificationSession() {
    unset($_SESSION['verification_token']);
    unset($_SESSION['user_email']);
    unset($_SESSION['token_time']);
}