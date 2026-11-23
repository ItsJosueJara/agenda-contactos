<?php
/** Encabezado común. Variable opcional: $titulo */
$flash = obtenerFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? 'Agenda de Contactos') ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<header class="topbar">
    <a class="marca" href="<?= estaAutenticado() ? 'panel.php' : 'login.php' ?>">📇 Agenda de Contactos</a>
    <?php if (estaAutenticado()): ?>
        <nav>
            <span class="usuario"><?= e($_SESSION['usuario_nombre']) ?></span>
            <a class="btn btn-sm btn-claro" href="logout.php">Cerrar sesión</a>
        </nav>
    <?php endif; ?>
</header>
<main class="contenedor">
    <?php if ($flash): ?>
        <div class="alerta alerta-<?= e($flash['tipo']) ?>" role="alert"><?= e($flash['mensaje']) ?></div>
    <?php endif; ?>
