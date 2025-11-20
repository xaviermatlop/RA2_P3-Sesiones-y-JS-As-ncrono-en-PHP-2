<?php
// ¡Línea 1, columna 1!
ob_start(); // Inicia la captura de salida

$is_api_endpoint = true; 
require_once __DIR__ . '/../auth.php'; // Incluye funciones, inicia sesión
require_once __DIR__ . '/../api.php';  // Ejecuta la lógica CRUD y llama a responder_json
// Si responder_json no hizo exit, limpiamos el buffer y terminamos
ob_end_clean();
exit;