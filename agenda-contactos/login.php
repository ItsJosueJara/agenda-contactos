<?php
require_once __DIR__ . '/includes/bootstrap.php';
redirigirSiAutenticado();

$error  = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $correo   = mb_strtolower(campo('correo'));
    $password = $_POST['password'] ?? '';

    if ($correo === '' || !is_string($password) || $password === '') {
        $error = 'Ingrese su correo y contraseña.';
    } else {
        $stmt = db()->prepare('SELECT id, nombre, correo, password FROM usuarios WHERE correo = ?');
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        // password_verify() compara la contraseña con el hash almacenado
        if ($usuario && password_verify($password, $usuario['password'])) {

            // Actualiza el hash si el algoritmo por defecto cambió
            if (password_needs_rehash($usuario['password'], PASSWORD_DEFAULT)) {
                $nuevo = password_hash($password, PASSWORD_DEFAULT);
                db()->prepare('UPDATE usuarios SET password = ? WHERE id = ?')->execute([$nuevo, $usuario['id']]);
            }

            iniciarSesion($usuario);
            flash('exito', 'Bienvenido, ' . $usuario['nombre'] . '.');
            redirigir('panel.php');
        }

        // Mensaje genérico: no revela si el correo existe o no
        $error = 'Correo o contraseña incorrectos.';
    }
}

$titulo = 'Iniciar sesión';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-form">
    <h1>Iniciar sesión</h1>
    <p class="subtitulo">Acceda a su agenda personal de contactos.</p>

    <?php if ($error): ?>
        <div class="alerta alerta-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate data-validar>
        <?= csrfCampo() ?>

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" required value="<?= e($correo) ?>" autofocus>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn btn-primario btn-bloque">Ingresar</button>
    </form>

    <p class="pie-form">¿No tiene cuenta? <a href="registro.php">Regístrese aquí</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
