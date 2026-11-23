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

    // La categoría debe existir en la tabla categorias
    if (!isset(categorias()[(int) $datos['id_categoria']])) {
        $errores['id_categoria'] = 'Seleccione una categoría válida.';
    }

    return $errores;
}

/** Valida y convierte un id recibido por URL o formulario. */
function idValido(mixed $valor): ?int
{
    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

/* ---------------- Categorías y favoritos (mejoras C y D) ---------------- */

/**
 * Devuelve las categorías como [id => nombre].
 * Se consulta una sola vez por petición.
 */
function categorias(): array
{
    static $lista = null;
    if ($lista === null) {
        $lista = db()->query('SELECT id, nombre FROM categorias ORDER BY id')
                     ->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $lista;
}

/** Lee los datos de un contacto enviados por POST. */
function datosContactoPost(): array
{
    return [
        'nombre'       => campo('nombre'),
        'telefono'     => campo('telefono'),
        'correo'       => campo('correo'),
        'ciudad'       => campo('ciudad'),
        'id_categoria' => (int) (idValido($_POST['id_categoria'] ?? null) ?? 0),
        'favorito'     => isset($_POST['favorito']) ? 1 : 0,
    ];
}

/* ---------------- Búsqueda y ordenamiento (mejoras A y B) ---------------- */

/** Columnas por las que se puede buscar (lista blanca). */
const CAMPOS_BUSQUEDA = [
    'todos'  => 'Todos los campos',
    'nombre' => 'Nombre',
    'correo' => 'Correo',
    'ciudad' => 'Ciudad',
];

/** Opciones de orden: clave => [etiqueta, expresión SQL]. */
const ORDENES = [
    'nombre_asc'  => ['Nombre (A-Z)',              'c.nombre ASC'],
    'nombre_desc' => ['Nombre (Z-A)',              'c.nombre DESC'],
    'ciudad_asc'  => ['Ciudad (A-Z)',              'c.ciudad ASC, c.nombre ASC'],
    'ciudad_desc' => ['Ciudad (Z-A)',              'c.ciudad DESC, c.nombre ASC'],
    'fecha_desc'  => ['Fecha de creación (recientes)', 'c.fecha_creacion DESC, c.id DESC'],
    'fecha_asc'   => ['Fecha de creación (antiguos)',  'c.fecha_creacion ASC, c.id ASC'],
];

/**
 * Lee y normaliza los filtros del panel desde la URL.
 * Cualquier valor fuera de la lista blanca se reemplaza por el predeterminado.
 */
function filtrosPanel(array $origen): array
{
    $texto = is_string($origen['q'] ?? null) ? trim($origen['q']) : '';
    $campo = $origen['campo'] ?? 'todos';
    $orden = $origen['orden'] ?? 'nombre_asc';
    $cat   = idValido($origen['categoria'] ?? null);

    return [
        'q'         => mb_substr($texto, 0, 100),
        'campo'     => is_string($campo) && isset(CAMPOS_BUSQUEDA[$campo]) ? $campo : 'todos',
        'orden'     => is_string($orden) && isset(ORDENES[$orden]) ? $orden : 'nombre_asc',
        'categoria' => $cat !== null && isset(categorias()[$cat]) ? $cat : null,
        'favoritos' => ($origen['favoritos'] ?? '') === '1',
    ];
}

/** Convierte los filtros activos en una cadena de consulta para la URL. */
function queryFiltros(array $f): string
{
    $params = array_filter([
        'q'         => $f['q'],
        'campo'     => $f['campo'] !== 'todos' ? $f['campo'] : '',
        'categoria' => $f['categoria'] ?? '',
        'favoritos' => $f['favoritos'] ? '1' : '',
        'orden'     => $f['orden'] !== 'nombre_asc' ? $f['orden'] : '',
    ], fn($v) => $v !== '' && $v !== null);

    return $params ? '?' . http_build_query($params) : '';
}

/** Escapa los comodines de LIKE (% y _) para buscar texto literal. */
function escaparLike(string $texto): string
{
    return addcslashes($texto, '\\%_');
}
