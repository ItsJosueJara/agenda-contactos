<?php
/**
 * Configuración y conexión a la base de datos mediante PDO.
 *
 * Los valores por defecto sirven para XAMPP (root sin contraseña).
 * Si tu MySQL tiene contraseña, crea el archivo config/local.php
 * (no se sube al repositorio) con este contenido:
 *
 *   <?php
 *   return ['db_pass' => 'tu_contraseña'];
 */

$config = [
    'db_host'    => '127.0.0.1',
    'db_port'    => '3306',
    'db_name'    => 'agenda_contactos',
    'db_user'    => 'root',
    'db_pass'    => '',
    'db_charset' => 'utf8mb4',
];

$localFile = __DIR__ . '/local.php';
if (is_file($localFile)) {
    $config = array_merge($config, require $localFile);
}

/**
 * Devuelve una única instancia de PDO (patrón singleton simple).
 */
function db(): PDO
{
    static $pdo = null;
    global $config;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['db_host'],
            $config['db_port'],
            $config['db_name'],
            $config['db_charset']
        );

        try {
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Se registra el detalle técnico, pero al usuario no se le revela.
            error_log('Error de conexión a BD: ' . $e->getMessage());
            http_response_code(500);
            exit('No se pudo conectar con el servicio. Intente más tarde.');
        }
    }

    return $pdo;
}
