<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerirLogin();

$datos = [
    'nombre' => '', 'telefono' => '', 'correo' => '', 'ciudad' => '',
    'id_categoria' => 0, 'favorito' => 0,
];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $datos   = datosContactoPost();
    $errores = validarContacto($datos);

    if (!$errores) {
        // El propietario se toma de la SESIÓN, nunca del formulario
        $stmt = db()->prepare(
            'INSERT INTO contactos (id_usuario, id_categoria, nombre, telefono, correo, ciudad, favorito)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            usuarioId(), $datos['id_categoria'], $datos['nombre'], $datos['telefono'],
            $datos['correo'], $datos['ciudad'], $datos['favorito'],
        ]);

        flash('exito', 'Contacto registrado correctamente.');
        redirigir('panel.php');
    }
}

$titulo     = 'Nuevo contacto';
$textoBoton = 'Guardar contacto';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-form">
    <h1>Nuevo contacto</h1>
    <?php require __DIR__ . '/includes/contacto_form.php'; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
