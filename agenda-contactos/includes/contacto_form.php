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

    <!-- Mejora C: cada contacto pertenece a una categoría -->
    <label for="id_categoria">Categoría</label>
    <select id="id_categoria" name="id_categoria" required>
        <option value="">Seleccione una categoría...</option>
        <?php foreach (categorias() as $idCat => $nombreCat): ?>
            <option value="<?= (int) $idCat ?>" <?= (int) $datos['id_categoria'] === (int) $idCat ? 'selected' : '' ?>>
                <?= e($nombreCat) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errores['id_categoria'])): ?>
        <small class="error"><?= e($errores['id_categoria']) ?></small>
    <?php endif; ?>

    <!-- Mejora D: marcar como favorito -->
    <label class="check">
        <input type="checkbox" name="favorito" value="1" <?= (int) $datos['favorito'] === 1 ? 'checked' : '' ?>>
        <span>★ Marcar como contacto favorito</span>
    </label>

    <div class="botones">
        <a class="btn btn-claro" href="panel.php">Cancelar</a>
        <button type="submit" class="btn btn-primario"><?= e($textoBoton) ?></button>
    </div>
</form>
