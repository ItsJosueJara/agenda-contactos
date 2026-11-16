<?php
/**
 * Manejo de autenticación y sesiones.
 */

const TIEMPO_INACTIVIDAD = 1800; // 30 minutos

/** Inicia la sesión del usuario después de validar credenciales. */
function iniciarSesion(array $usuario): void
{
    // Nuevo id de sesión para evitar fijación de sesión
    session_regenerate_id(true);

    $_SESSION['usuario_id']     = (int) $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_correo'] = $usuario['correo'];
    $_SESSION['ultima_actividad'] = time();
}

/** Indica si hay un usuario autenticado y con sesión vigente. */
function estaAutenticado(): bool
{
    if (empty($_SESSION['usuario_id'])) {
        return false;
    }

    // Expira la sesión por inactividad
    if (time() - ($_SESSION['ultima_actividad'] ?? 0) > TIEMPO_INACTIVIDAD) {
        cerrarSesion();
        return false;
    }

    $_SESSION['ultima_actividad'] = time();
    return true;
}

/**
 * Debe llamarse al inicio de TODAS las páginas protegidas.
 * Si no hay sesión activa, redirige al login.
 */
function requerirLogin(): void
{
    if (!estaAutenticado()) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(); // la sesión pudo destruirse por inactividad
        }
        flash('error', 'Debe iniciar sesión para acceder.');
        redirigir('login.php');
    }
}

/** Para login/registro: si ya hay sesión, envía al panel. */
function redirigirSiAutenticado(): void
{
    if (estaAutenticado()) {
        redirigir('panel.php');
    }
}

/** Id del usuario autenticado. */
function usuarioId(): int
{
    return (int) $_SESSION['usuario_id'];
}

/** Destruye completamente la sesión. */
function cerrarSesion(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();
}
