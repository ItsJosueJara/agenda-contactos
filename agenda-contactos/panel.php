<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerirLogin();

// Filtros validados contra listas blancas (búsqueda, categoría, favoritos, orden)
$f = filtrosPanel($_GET);

/*
 * Consulta dinámica, pero segura:
 *  - Los valores del usuario viajan SIEMPRE como parámetros preparados (?).
 *  - Los nombres de columna y el ORDER BY salen de listas blancas del servidor.
 *  - La condición id_usuario = ? garantiza que solo se vean contactos propios.
 */
$where  = ['c.id_usuario = ?'];
$params = [usuarioId()];

// Mejora A: búsqueda por nombre, correo o ciudad
if ($f['q'] !== '') {
    $patron = '%' . escaparLike($f['q']) . '%';
    if ($f['campo'] === 'todos') {
        $where[] = '(c.nombre LIKE ? OR c.correo LIKE ? OR c.ciudad LIKE ?)';
        array_push($params, $patron, $patron, $patron);
    } else {
        $where[] = 'c.' . $f['campo'] . ' LIKE ?';
        $params[] = $patron;
    }
}

// Mejora C: filtrar por categoría
if ($f['categoria'] !== null) {
    $where[]  = 'c.id_categoria = ?';
    $params[] = $f['categoria'];
}

// Mejora D: mostrar únicamente los favoritos
if ($f['favoritos']) {
    $where[] = 'c.favorito = 1';
}

// Mejora B: ordenamiento
$orderBy = ORDENES[$f['orden']][1];

$sql = 'SELECT c.id, c.nombre, c.telefono, c.correo, c.ciudad, c.favorito,
               c.fecha_creacion, cat.nombre AS categoria
          FROM contactos c
          JOIN categorias cat ON cat.id = c.id_categoria
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY ' . $orderBy;

$stmt = db()->prepare($sql);
$stmt->execute($params);
$contactos = $stmt->fetchAll();

// Totales del usuario (sin filtros) para el resumen
$stmt = db()->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(favorito), 0) AS favoritos
                         FROM contactos WHERE id_usuario = ?');
$stmt->execute([usuarioId()]);
$totales = $stmt->fetch();

$hayFiltros = $f['q'] !== '' || $f['categoria'] !== null || $f['favoritos'];
$volver     = queryFiltros($f);

$titulo = 'Mi agenda';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta perfil">
    <div>
        <h1>Hola, <?= e($_SESSION['usuario_nombre']) ?></h1>
        <p class="subtitulo"><?= e($_SESSION['usuario_correo']) ?></p>
    </div>
    <div class="resumen">
        <div class="contador">
            <strong><?= (int) $totales['total'] ?></strong>
            <span>contacto<?= (int) $totales['total'] === 1 ? '' : 's' ?></span>
        </div>
        <div class="contador contador-fav">
            <strong><?= (int) $totales['favoritos'] ?></strong>
            <span>favorito<?= (int) $totales['favoritos'] === 1 ? '' : 's' ?></span>
        </div>
    </div>
</section>

<section class="tarjeta">
    <div class="cabecera-seccion">
        <h2>Mis contactos</h2>
        <a class="btn btn-primario" href="contacto_crear.php">+ Nuevo contacto</a>
    </div>

    <?php if ((int) $totales['total'] === 0): ?>
        <p class="vacio">Aún no tiene contactos registrados.</p>
    <?php else: ?>
        <!-- Filtros: se envían por GET para que la URL conserve la búsqueda -->
        <form method="get" class="filtros" id="form-filtros" role="search">
            <div class="filtro filtro-texto">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="<?= e($f['q']) ?>"
                       maxlength="100" placeholder="Escriba un nombre, correo o ciudad...">
            </div>
            <div class="filtro">
                <label for="campo">Buscar en</label>
                <select id="campo" name="campo" data-autoenviar>
                    <?php foreach (CAMPOS_BUSQUEDA as $clave => $etiqueta): ?>
                        <option value="<?= $clave ?>" <?= $f['campo'] === $clave ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtro">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria" data-autoenviar>
                    <option value="">Todas</option>
                    <?php foreach (categorias() as $idCat => $nombreCat): ?>
                        <option value="<?= (int) $idCat ?>" <?= $f['categoria'] === (int) $idCat ? 'selected' : '' ?>><?= e($nombreCat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtro">
                <label for="orden">Ordenar por</label>
                <select id="orden" name="orden" data-autoenviar>
                    <?php foreach (ORDENES as $clave => [$etiqueta]): ?>
                        <option value="<?= $clave ?>" <?= $f['orden'] === $clave ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <label class="check filtro-fav">
                <input type="checkbox" name="favoritos" value="1" data-autoenviar <?= $f['favoritos'] ? 'checked' : '' ?>>
                <span>★ Solo favoritos</span>
            </label>
            <div class="filtro-botones">
                <button type="submit" class="btn btn-primario">Buscar</button>
                <?php if ($hayFiltros || $f['orden'] !== 'nombre_asc' || $f['campo'] !== 'todos'): ?>
                    <a class="btn btn-claro" href="panel.php">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>

        <p class="resultado">
            <?= count($contactos) ?> resultado<?= count($contactos) === 1 ? '' : 's' ?>
            <?= $hayFiltros ? 'con los filtros aplicados' : '' ?>
        </p>

        <?php if (!$contactos): ?>
            <p class="vacio">No hay contactos que coincidan con la búsqueda.</p>
        <?php else: ?>
            <div class="tabla-scroll">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th class="col-fav" title="Favorito">★</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Ciudad</th>
                            <th>Categoría</th>
                            <th>Creado</th>
                            <th class="acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($contactos as $c): ?>
                        <tr>
                            <td class="col-fav">
                                <!-- Cambia el estado de favorito (POST + CSRF) -->
                                <form method="post" action="contacto_favorito.php" class="form-estrella">
                                    <?= csrfCampo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <input type="hidden" name="volver" value="<?= e($volver) ?>">
                                    <button type="submit" class="estrella <?= $c['favorito'] ? 'activa' : '' ?>"
                                            title="<?= $c['favorito'] ? 'Quitar de favoritos' : 'Marcar como favorito' ?>"
                                            aria-label="<?= $c['favorito'] ? 'Quitar de favoritos' : 'Marcar como favorito' ?>">
                                        <?= $c['favorito'] ? '★' : '☆' ?>
                                    </button>
                                </form>
                            </td>
                            <td><?= e($c['nombre']) ?></td>
                            <td><?= e($c['telefono']) ?></td>
                            <td><?= e($c['correo']) ?></td>
                            <td><?= e($c['ciudad']) ?></td>
                            <td><span class="etiqueta etiqueta-<?= e(mb_strtolower($c['categoria'])) ?>"><?= e($c['categoria']) ?></span></td>
                            <td class="fecha"><?= e(date('d/m/Y', strtotime($c['fecha_creacion']))) ?></td>
                            <td class="acciones">
                                <a class="btn btn-sm btn-secundario" href="contacto_editar.php?id=<?= (int) $c['id'] ?>">Editar</a>
                                <a class="btn btn-sm btn-peligro" href="contacto_eliminar.php?id=<?= (int) $c['id'] ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
