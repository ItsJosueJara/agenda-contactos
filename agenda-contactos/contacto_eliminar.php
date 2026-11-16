<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerirLogin();

$id = idValido($_GET['id'] ?? null);

// Se verifica que el contacto pertenezca al usuario en sesión
$contacto = null;
if ($id !== null) {
    $stmt = db()->prepare(
        'SELECT id, nombre, telefono, correo, ciudad
           FROM contactos
          WHERE id = ? AND id_usuario = ?'
    );
    $stmt->execute([$id, usuarioId()]);
    $contacto = $stmt->fetch();
}

if (!$contacto) {
    flash('error', 'El contacto solicitado no existe o no tiene permiso para eliminarlo.');
    redirigir('panel.php');
}

// La eliminación solo ocurre por POST, después de la confirmación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $stmt = db()->prepare('DELETE FROM contactos WHERE id = ? AND id_usuario = ?');
    $stmt->execute([$id, usuarioId()]);

    flash('exito', 'Contacto eliminado correctamente.');
    redirigir('panel.php');
}

$titulo = 'Eliminar contacto';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-form">
    <h1>Eliminar contacto</h1>
    <p class="advertencia">¿Está seguro de que desea eliminar este contacto? Esta acción no se puede deshacer.</p>

    <dl class="detalle">
        <dt>Nombre</dt><dd><?= e($contacto['nombre']) ?></dd>
        <dt>Teléfono</dt><dd><?= e($contacto['telefono']) ?></dd>
        <dt>Correo</dt><dd><?= e($contacto['correo']) ?></dd>
        <dt>Ciudad</dt><dd><?= e($contacto['ciudad']) ?></dd>
    </dl>

    <form method="post">
        <?= csrfCampo() ?>
        <div class="botones">
            <a class="btn btn-claro" href="panel.php">Cancelar</a>
            <button type="submit" class="btn btn-peligro">Sí, eliminar</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
