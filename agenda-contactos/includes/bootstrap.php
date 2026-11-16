<?php
/**
 * Archivo de arranque: se incluye al inicio de cada página.
 * Configura la sesión de forma segura y carga las dependencias.
 */

declare(strict_types=1);

// Cookies de sesión más seguras
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,      // JavaScript no puede leer la cookie
    'samesite' => 'Strict',  // Mitiga CSRF
    'secure'   => !empty($_SERVER['HTTPS']),
]);

session_name('AGENDASESSID');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
