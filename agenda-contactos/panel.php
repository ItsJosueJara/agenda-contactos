<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerirLogin();

// Solo se consultan los contactos del usuario autenticado
$stmt = db()->prepare(
    'SELECT id, nombre, telefono, correo, ciudad
       FROM contactos
      WHERE id_usuario = ?
      ORDER BY nombre ASC'
);
$stmt->execute([usuarioId()]);
$contactos = $stmt->fetchAll();

$titulo = 'Mi agenda';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta perfil">
    <div>
        <h1>Hola, <?= e($_SESSION['usuario_nombre']) ?></h1>
        <p class="subtitulo"><?= e($_SESSION['usuario_correo']) ?></p>
    </div>
    <div class="contador">
        <strong><?= count($contactos) ?></strong>
        <span>contacto<?= count($contactos) === 1 ? '' : 's' ?></span>
    </div>
</section>

<section class="tarjeta">
    <div class="cabecera-seccion">
        <h2>Mis contactos</h2>
        <a class="btn btn-primario" href="contacto_crear.php">+ Nuevo contacto</a>
    </div>

    <?php if (!$contactos): ?>
        <p class="vacio">Aún no tiene contactos registrados.</p>
    <?php else: ?>
        <input type="search" id="buscador" class="buscador" placeholder="Buscar por nombre, correo o ciudad...">
        <div class="tabla-scroll">
            <table class="tabla" id="tabla-contactos">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Ciudad</th>
                        <th class="acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($contactos as $c): ?>
                    <tr>
                        <td><?= e($c['nombre']) ?></td>
                        <td><?= e($c['telefono']) ?></td>
                        <td><?= e($c['correo']) ?></td>
                        <td><?= e($c['ciudad']) ?></td>
                        <td class="acciones">
                            <a class="btn btn-sm btn-secundario" href="contacto_editar.php?id=<?= (int) $c['id'] ?>">Editar</a>
                            <a class="btn btn-sm btn-peligro" href="contacto_eliminar.php?id=<?= (int) $c['id'] ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="vacio oculto" id="sin-resultados">No hay coincidencias.</p>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
