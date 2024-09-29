<?php

define('APP_NAME', 'Jornada Académica');
define('APP_URL', 'http://localhost/RegistroDeUsuarios');


define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'registrodeusuarios');

// Verificar si no hay sesin activa p
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 86400); 
    ini_set('session.gc_maxlifetime', 86400);  
}

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores (
error_reporting(E_ALL);
ini_set('display_errors', 1);

//define('SALT', bin2hex(random_bytes(16)));

define('SALT', 'una_cadena_aleatoria_para_salt');