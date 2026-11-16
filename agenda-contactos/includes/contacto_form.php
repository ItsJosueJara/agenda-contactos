<?php
/**
 * Formulario reutilizable para crear y editar contactos.
 * Variables esperadas: $datos, $errores, $textoBoton
 */
$campos = [
    'nombre'   => ['Nombre',             'text',  100],
    'telefono' => ['Teléfono',           'tel',   20],
    'correo'   => ['Correo electrónico', 'email', 150],
    'ciudad'   => ['Ciudad',             'text',  80],
];
?>
<form method="post" novalidate data-validar>
    <?= csrfCampo() ?>

    <?php foreach ($campos as $nombre => [$etiqueta, $tipo, $max]): ?>
        <label for="<?= $nombre ?>"><?= $etiqueta ?></label>
        <input type="<?= $tipo ?>" id="<?= $nombre ?>" name="<?= $nombre ?>"
               maxlength="<?= $max ?>" required value="<?= e($datos[$nombre]) ?>">
        <?php if (isset($errores[$nombre])): ?>
            <small class="error"><?= e($errores[$nombre]) ?></small>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="botones">
        <a class="btn btn-claro" href="panel.php">Cancelar</a>
        <button type="submit" class="btn btn-primario"><?= e($textoBoton) ?></button>
    </div>
</form>
