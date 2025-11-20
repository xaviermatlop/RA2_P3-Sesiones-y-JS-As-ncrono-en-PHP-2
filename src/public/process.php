<?php
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$input = [
    'nombre_completo'        => trim($_POST['nombre_completo'] ?? ''),
    'email'                  => trim($_POST['email'] ?? ''),
    'telefono'               => trim($_POST['telefono'] ?? ''),
    'fecha_nacimiento'       => trim($_POST['fecha_nacimiento'] ?? ''),
    'edad'                   => trim($_POST['edad'] ?? ''),
    'genero'                 => trim($_POST['genero'] ?? ''),
    'puesto_actual'          => trim($_POST['puesto_actual'] ?? ''),
    'departamento'           => trim($_POST['departamento'] ?? ''),
    'fecha_incorporacion'    => trim($_POST['fecha_incorporacion'] ?? ''),
    'experiencia_anios'      => trim($_POST['experiencia_anios'] ?? ''),
    'eleccion_positiva_1'    => trim($_POST['eleccion_positiva_1'] ?? ''),
    'eleccion_positiva_2'    => trim($_POST['eleccion_positiva_2'] ?? ''),
    'eleccion_negativa_1'    => trim($_POST['eleccion_negativa_1'] ?? ''),
    'motivo_eleccion'        => trim($_POST['motivo_eleccion'] ?? ''),
    'hora_reunion_preferida' => trim($_POST['hora_reunion_preferida'] ?? ''),
    'color_equipo'           => trim($_POST['color_equipo'] ?? ''),
    'tecnologias'            => $_POST['tecnologias'] ?? [],
    'satisfaccion_general'   => trim($_POST['satisfaccion_general'] ?? ''),
    'carga_trabajo'          => trim($_POST['carga_trabajo'] ?? ''),
];

$errors = [];

if (strlen($input['nombre_completo']) < 3) {
    $errors['nombre_completo'] = 'El nombre debe tener al menos 3 caracteres.';
}
if ($input['eleccion_positiva_1'] === '') {
    $errors['eleccion_positiva_1'] = 'Debes indicar al menos una persona con la que te gusta trabajar.';
}
if ($input['eleccion_negativa_1'] === '') {
    $errors['eleccion_negativa_1'] = 'Debes indicar una persona con la que prefieres no trabajar.';
}

if ($errors) {
    $old_field = $input;
    include __DIR__ . '/includes/header.php';
?>
    <main class="container">
        <h1>Formulario Sociométrico</h1>
        <p>Corrige los errores e inténtalo de nuevo.</p>
        <?php include 'index.php'; ?>
    </main>
<?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$file = __DIR__ . '/data/sociograma.json';
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}
$todo = load_json($file);

$registro = $input;
$registro['fecha_envio'] = date('Y-m-d H:i:s');
$todo[] = $registro;

if (!save_json($file, $todo)) {
    http_response_code(500);
    echo ' Error al guardar los datos. Inténtalo más tarde.';
    exit;
}

include __DIR__ . '/includes/header.php';
?>
<main class="container">
    <h2>Gracias, <?= htmlspecialchars($input['nombre_completo']) ?>. Tu respuesta se ha guardado correctamente.</h2>
    <p>Total de respuestas guardadas: <strong><?= count($todo) ?></strong></p>
    <p><a href="index.php">Volver al formulario</a></p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
