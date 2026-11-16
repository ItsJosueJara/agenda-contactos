<?php
require_once __DIR__ . '/includes/bootstrap.php';

cerrarSesion();

// Nueva sesión vacía solo para mostrar el mensaje de despedida
session_start();
flash('exito', 'Sesión cerrada correctamente.');
redirigir('login.php');
