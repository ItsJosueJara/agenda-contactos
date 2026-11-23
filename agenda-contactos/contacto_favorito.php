<?php
/**
 * Mejora D: marca o desmarca un contacto como favorito.
 * Solo acepta POST con token CSRF y solo afecta contactos del usuario en sesión.
 */
require_once __DIR__ . '/includes/bootstrap.php';
requerirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('panel.php');
}

verificarCsrf();

$id = idValido($_POST['id'] ?? null);

if ($id !== null) {
    // Invierte el valor (0 -> 1, 1 -> 0) solo si el contacto es del usuario
    $stmt = db()->prepare(
        'UPDATE contactos SET favorito = 1 - favorito WHERE id = ? AND id_usuario = ?'
    );
    $stmt->execute([$id, usuarioId()]);

    if ($stmt->rowCount() === 0) {
        flash('error', 'El contacto solicitado no existe o no tiene permiso para modificarlo.');
    }
}

// Regresa al panel conservando los filtros, reconstruidos y validados de nuevo
$volver = [];
$cadena = $_POST['volver'] ?? '';
parse_str(ltrim(is_string($cadena) ? $cadena : '', '?'), $volver);
redirigir('panel.php' . queryFiltros(filtrosPanel($volver)));
