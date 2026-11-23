<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Punto de entrada: envía al panel o al login según la sesión.
redirigir(estaAutenticado() ? 'panel.php' : 'login.php');
