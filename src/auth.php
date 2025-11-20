<?php
declare(strict_types=1);

// 1. INICIO SEGURO DE SESIÓN: session_start() solo se ejecuta si no hay una sesión activa.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Ruta al almacenamiento
$rutaArchivoDatosJson = __DIR__ . '/storage/data.json';

// --- FUNCIONES AUXILIARES ---

/**
 * Carga los usuarios desde el JSON.
 */
function cargar_usuarios(string $ruta): array
{
    if (!file_exists($ruta)) {
        return [];
    }
    $contenido = file_get_contents($ruta);
    return json_decode($contenido, true) ?? [];
}

/**
 * Envía una respuesta de éxito/error en JSON.
 * Usa un buffer de salida para asegurar que el JSON se envíe correctamente.
 */
function responder_json(bool $ok, mixed $dataOrError, int $codigoHttp = 200): void
{
    // Limpia cualquier salida de buffer anterior que pudiera contener HTML
    if (ob_get_level() > 0) {
        ob_clean(); 
    }

    // Solo intentamos enviar headers si no se ha enviado contenido (output) antes
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($codigoHttp);
    }
    
    if ($ok) {
        echo json_encode(['ok' => true, 'data' => $dataOrError], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['ok' => false, 'error' => $dataOrError], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

/**
 * Comprueba si hay una sesión activa con el rol requerido.
 */
function check_session(string $rol_requerido = 'usuario'): bool
{
    if (!isset($_SESSION['rol'])) {
        return false;
    }
    
    if ($rol_requerido === 'admin') {
        return $_SESSION['rol'] === 'admin';
    }
    return true;
}

// -----------------------------------------------------------------------------
// Lógica Principal de Autenticación (SOLO se ejecuta si se llama como API)
// -----------------------------------------------------------------------------

// Si la variable $is_api_endpoint existe y es TRUE, ejecuta la lógica de API
if (isset($is_api_endpoint) && $is_api_endpoint === true) {

    $accion = $_GET['action'] ?? '';

    // Acción de LOGIN
    if ($accion === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            responder_json(false, 'Email y contraseña son obligatorios.', 400);
        }
        
        $usuarios = cargar_usuarios($rutaArchivoDatosJson);
        $usuario_encontrado = null;
        
        foreach ($usuarios as $u) {
            if (isset($u['email']) && mb_strtolower($u['email']) === mb_strtolower($email)) {
                $usuario_encontrado = $u;
                break;
            }
        }
        // en lugar de verify utilizamos la email porque es unica
        if ($usuario_encontrado && $usuario_encontrado['email'] === $email) {
            $_SESSION['id'] = $usuario_encontrado['id'];
            $_SESSION['nombre'] = $usuario_encontrado['nombre'];
            $_SESSION['email'] = $usuario_encontrado['email'];
            $_SESSION['rol'] = $usuario_encontrado['rol'];
            
            $destino = $usuario_encontrado['rol'] === 'admin' ? 'index_ajax.php' : 'sociograma.php';
            
            responder_json(true, ['mensaje' => 'Login exitoso', 'destino' => $destino]);
        } else {
            responder_json(false, 'Credenciales incorrectas.', 401);
        }
    }

    // Acción para obtener datos del usuario actual
    if ($accion === 'me') {
        if (check_session()) {
            responder_json(true, [
                'id' => $_SESSION['id'],
                'nombre' => $_SESSION['nombre'],
                'email' => $_SESSION['email'],
                'rol' => $_SESSION['rol']
            ]);
        } else {
            responder_json(false, 'No hay sesión activa.', 401);
        }
    }

    // Si la acción no existe y estamos en modo API, devolvemos un error.
    responder_json(false, 'Acción no válida o método no permitido.', 405);
}