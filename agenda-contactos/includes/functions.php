<?php
/**
 * Funciones de apoyo: escape de salida, mensajes flash, CSRF y validación.
 */

/** Escapa texto para mostrarlo en HTML (evita XSS). */
function e(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirige y termina la ejecución. */
function redirigir(string $ruta): never
{
    header('Location: ' . $ruta);
    exit;
}

/** Guarda un mensaje para mostrarlo en la siguiente página. */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/** Obtiene y elimina el mensaje flash. */
function obtenerFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/* ---------------- Protección CSRF ---------------- */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCampo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verificarCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(400);
        exit('Solicitud no válida. Recargue la página e intente de nuevo.');
    }
}

/* ---------------- Validación en el servidor ---------------- */

/** Obtiene un campo POST como texto limpio. */
function campo(string $nombre): string
{
    $valor = $_POST[$nombre] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

/**
 * Valida los datos de un contacto.
 * Devuelve un arreglo de errores (vacío si todo es válido).
 */
function validarContacto(array $datos): array
{
    $errores = [];

    if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 100) {
        $errores['nombre'] = 'El nombre es obligatorio (máximo 100 caracteres).';
    }

    if (!preg_match('/^\+?[0-9\s\-]{7,20}$/', $datos['telefono'])) {
        $errores['telefono'] = 'Ingrese un teléfono válido (7 a 20 dígitos, puede iniciar con +).';
    }

    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['correo']) > 150) {
        $errores['correo'] = 'Ingrese un correo electrónico válido.';
    }

    if ($datos['ciudad'] === '' || mb_strlen($datos['ciudad']) > 80) {
        $errores['ciudad'] = 'La ciudad es obligatoria (máximo 80 caracteres).';
    }

    return $errores;
}

/** Valida y convierte un id recibido por URL o formulario. */
function idValido(mixed $valor): ?int
{
    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}
