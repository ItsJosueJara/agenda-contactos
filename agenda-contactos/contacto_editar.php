<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerirLogin();

$id = idValido($_GET['id'] ?? null);

/*
 * Control de acceso: el contacto se busca por su id Y por el id del
 * usuario en sesión. Si alguien cambia el id en la URL por el de un
 * contacto ajeno, la consulta no devuelve nada.
 */
$contacto = null;
if ($id !== null) {
    $stmt = db()->prepare(
        'SELECT id, nombre, telefono, correo, ciudad, id_categoria, favorito
           FROM contactos
          WHERE id = ? AND id_usuario = ?'
    );
    $stmt->execute([$id, usuarioId()]);
    $contacto = $stmt->fetch();
}

if (!$contacto) {
    flash('error', 'El contacto solicitado no existe o no tiene permiso para verlo.');
    redirigir('panel.php');
}

$datos   = $contacto;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $datos   = datosContactoPost();
    $errores = validarContacto($datos);

    if (!$errores) {
        $stmt = db()->prepare(
            'UPDATE contactos
                SET nombre = ?, telefono = ?, correo = ?, ciudad = ?, id_categoria = ?, favorito = ?
              WHERE id = ? AND id_usuario = ?'
        );
        $stmt->execute([
            $datos['nombre'], $datos['telefono'], $datos['correo'], $datos['ciudad'],
            $datos['id_categoria'], $datos['favorito'], $id, usuarioId(),
        ]);

        flash('exito', 'Contacto actualizado correctamente.');
        redirigir('panel.php');
    }
}

$titulo     = 'Editar contacto';
$textoBoton = 'Guardar cambios';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-form">
    <h1>Editar contacto</h1>
    <?php require __DIR__ . '/includes/contacto_form.php'; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
