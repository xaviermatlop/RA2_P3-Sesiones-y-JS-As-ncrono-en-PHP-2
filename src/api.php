<?php
declare(strict_types=1);

// NOTA: El front-controller público (public/api.php) debe definir $is_api_endpoint = true; antes de incluirnos.
require_once __DIR__ . '/auth.php'; 

// -----------------------------------------------------------------------------
// Lógica Principal de CRUD (SOLO se ejecuta si se llama como API)
// -----------------------------------------------------------------------------

if (isset($is_api_endpoint) && $is_api_endpoint === true) {

    // 1. Verificar sesión de ADMIN antes de cualquier acción CRUD
    if (!check_session('admin')) {
        responder_json(false, 'Acceso denegado. Se requiere ser Administrador.', 403);
    }

    // 2. Cargar datos
    $listaUsuarios = cargar_usuarios($rutaArchivoDatosJson); // $rutaArchivoDatosJson viene de auth.php

    // --- FUNCIONES AUXILIARES ESPECÍFICAS DE CRUD ---

    function persistir_y_responder(array $usuarios, int $codigoHttp = 200): void
    {
        file_put_contents(
            $GLOBALS['rutaArchivoDatosJson'],
            json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
        );
        responder_json(true, $usuarios, $codigoHttp);
    }

    function generar_id(array $usuarios): int
    {
        $max_id = 0;
        foreach ($usuarios as $u) {
            $max_id = max($max_id, $u['id'] ?? 0);
        }
        return $max_id + 1;
    }

    // -----------------------------------------------------------------------------
    // Lógica de Acciones CRUD
    // -----------------------------------------------------------------------------
    
    $metodo = $_SERVER['REQUEST_METHOD'];
    $accion = $_GET['action'] ?? '';
    $input = json_decode((string) file_get_contents('php://input'), true) ?? [];

    // LISTAR usuarios: GET /api.php?action=list
    if ($metodo === 'GET' && $accion === 'list') {
        $datos_seguros = array_map(function ($u) {
            unset($u['password']);
            return $u;
        }, $listaUsuarios);
        responder_json(true, $datos_seguros, 200);
    }

    // CREAR usuario: POST /api.php?action=create
    if ($metodo === 'POST' && $accion === 'create') {
        $nombre = trim($input['nombre'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $rol = trim($input['rol'] ?? 'usuario');

        if (empty($nombre) || empty($email) || empty($password)) {
            responder_json(false, 'Nombre, Email y Contraseña son obligatorios.', 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            responder_json(false, 'El email no tiene un formato válido.', 422);
        }
        
        foreach ($listaUsuarios as $u) {
            if (mb_strtolower($u['email']) === mb_strtolower($email)) {
                responder_json(false, 'Ya existe un usuario con ese email.', 409);
            }
        }
        
        $nuevoUsuario = [
            'id' => generar_id($listaUsuarios),
            'nombre' => $nombre,
            'email' => mb_strtolower($email),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'rol' => $rol,
        ];
        
        $listaUsuarios[] = $nuevoUsuario;
        persistir_y_responder($listaUsuarios, 201);
    }

    // ELIMINAR usuario: POST /api.php?action=delete
    if ($metodo === 'POST' && $accion === 'delete') {
        $idAEliminar = $input['id'] ?? null;
        $indiceAEliminar = -1;
        
        foreach ($listaUsuarios as $indice => $u) {
            if (isset($u['id']) && (int)$u['id'] === (int)$idAEliminar) {
                $indiceAEliminar = $indice;
                break;
            }
        }
        
        if ($indiceAEliminar < 0) {
            responder_json(false, "El usuario con ID {$idAEliminar} no existe.", 404);
        }
        
        array_splice($listaUsuarios, $indiceAEliminar, 1);
        $listaUsuarios = array_values($listaUsuarios); 
        
        persistir_y_responder($listaUsuarios, 200);
    }

    // EDITAR/ACTUALIZAR usuario: POST /api.php?action=update
    if ($metodo === 'POST' && $accion === 'update') {
        $idAActualizar = $input['id'] ?? null;
        $indiceAActualizar = -1;

        foreach ($listaUsuarios as $indice => $u) {
            if (isset($u['id']) && (int)$u['id'] === (int)$idAActualizar) {
                $indiceAActualizar = $indice;
                break;
            }
        }
        
        if ($indiceAActualizar < 0) {
            responder_json(false, "El usuario con ID {$idAActualizar} no existe.", 404);
        }
        
        $usuario = $listaUsuarios[$indiceAActualizar];
        
        $usuario['nombre'] = trim($input['nombre'] ?? $usuario['nombre']);
        $usuario['email'] = trim($input['email'] ?? $usuario['email']);
        $usuario['rol'] = trim($input['rol'] ?? $usuario['rol']);

        if (!empty($input['password'])) {
            $usuario['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        if (!filter_var($usuario['email'], FILTER_VALIDATE_EMAIL)) {
            responder_json(false, 'El email de actualización no tiene un formato válido.', 422);
        }
        
        $listaUsuarios[$indiceAActualizar] = $usuario;
        
        persistir_y_responder($listaUsuarios, 200);
    }

    // Si la acción no existe o método incorrecto
    responder_json(false, "Acción '{$accion}' no implementada o método '{$metodo}' no permitido.", 405);
}