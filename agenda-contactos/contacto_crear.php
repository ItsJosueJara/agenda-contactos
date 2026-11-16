<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerirLogin();

$datos   = ['nombre' => '', 'telefono' => '', 'correo' => '', 'ciudad' => ''];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    foreach ($datos as $clave => $_) {
        $datos[$clave] = campo($clave);
    }
    $errores = validarContacto($datos);

    if (!$errores) {
        // El propietario se toma de la SESIÓN, nunca del formulario
        $stmt = db()->prepare(
            'INSERT INTO contactos (id_usuario, nombre, telefono, correo, ciudad)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([usuarioId(), $datos['nombre'], $datos['telefono'], $datos['correo'], $datos['ciudad']]);

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
