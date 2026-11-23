<?php
require_once __DIR__ . '/includes/bootstrap.php';
redirigirSiAutenticado();

$errores = [];
$datos   = ['nombre' => '', 'correo' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $datos['nombre'] = campo('nombre');
    $datos['correo'] = mb_strtolower(campo('correo'));
    $password        = $_POST['password'] ?? '';
    $confirmar       = $_POST['confirmar'] ?? '';

    // ---- Validación en el servidor ----
    if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 100) {
        $errores['nombre'] = 'El nombre completo es obligatorio (máximo 100 caracteres).';
    }

    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['correo']) > 150) {
        $errores['correo'] = 'Ingrese un correo electrónico válido.';
    }

    if (!is_string($password) || strlen($password) < 8) {
        $errores['password'] = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errores['password'] = 'La contraseña debe incluir letras y números.';
    }

    if ($password !== $confirmar) {
        $errores['confirmar'] = 'Las contraseñas no coinciden.';
    }

    // ---- Correo único ----
    if (!$errores) {
        $stmt = db()->prepare('SELECT id FROM usuarios WHERE correo = ?');
        $stmt->execute([$datos['correo']]);
        if ($stmt->fetch()) {
            $errores['correo'] = 'Este correo ya está registrado.';
        }
    }

    // ---- Guardar con contraseña cifrada ----
    if (!$errores) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = db()->prepare('INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)');
        $stmt->execute([$datos['nombre'], $datos['correo'], $hash]);

        flash('exito', 'Cuenta creada correctamente. Ya puede iniciar sesión.');
        redirigir('login.php');
    }
}

$titulo = 'Crear cuenta';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-form">
    <h1>Crear cuenta</h1>
    <p class="subtitulo">Regístrese para administrar su agenda personal.</p>

    <form method="post" novalidate data-validar>
        <?= csrfCampo() ?>

        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre" maxlength="100" required value="<?= e($datos['nombre']) ?>">
        <?php if (isset($errores['nombre'])): ?><small class="error"><?= e($errores['nombre']) ?></small><?php endif; ?>

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" maxlength="150" required value="<?= e($datos['correo']) ?>">
        <?php if (isset($errores['correo'])): ?><small class="error"><?= e($errores['correo']) ?></small><?php endif; ?>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" minlength="8" required>
        <small class="ayuda">Mínimo 8 caracteres, con letras y números.</small>
        <?php if (isset($errores['password'])): ?><small class="error"><?= e($errores['password']) ?></small><?php endif; ?>

        <label for="confirmar">Confirmar contraseña</label>
        <input type="password" id="confirmar" name="confirmar" minlength="8" required>
        <?php if (isset($errores['confirmar'])): ?><small class="error"><?= e($errores['confirmar']) ?></small><?php endif; ?>

        <button type="submit" class="btn btn-primario btn-bloque">Registrarme</button>
    </form>

    <p class="pie-form">¿Ya tiene cuenta? <a href="login.php">Iniciar sesión</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
