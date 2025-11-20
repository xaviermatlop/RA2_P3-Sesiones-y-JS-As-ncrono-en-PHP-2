<?php
// Necesitamos la lógica de autenticación para comprobar la sesión
require_once __DIR__ . '/../auth.php';
require __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php';
// Si no hay sesión, redirigir al login
if (!check_session()) {
    header('Location: login.php');
    exit;
}

// Si venimos “de cero” o de una recarga, que existan $old_field y $errors vacíos.
// Esto es para la rehidratación en caso de que la validación PHP falle.
$old_field = isset($old_field) ? $old_field : [];
$errors = isset($errors) ? $errors : [];
?>
<main class="container">
     <header class="encabezado-aplicacion">
        <h1 class="encabezado-aplicacion__titulo">
            Vista de Usuario Estándar
        </h1>
        <p class="encabezado-aplicacion__descripcion">
            Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>. Este sería el sociograma o vista restringida.
        </p>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <p>¡Eres administrador! Vuelve al <a href="panel_admin.php">Panel de Administración</a>.</p>
        <?php endif; ?>
        <p><a href="logout.php">Cerrar Sesión</a></p>
    </header>
    <main class="zona-principal" tabindex="-1">
        <section class="bloque-listado">
            <p>Aquí se cargaría el contenido para los usuarios sin rol de administrador.</p>
        </section>
    </main>
    <h1>Formulario Sociométrico</h1>
    <p>Por favor, completa la siguiente información con la mayor sinceridad posible. Tus respuestas son confidenciales.</p>

    <form method="POST" action="process.php">

        <fieldset>
            <legend>1. Datos Personales</legend>

            <label for="nombre_completo">Nombre Completo:</label>
            <input type="text" id="nombre_completo" name="nombre_completo" value="<?= old_field('nombre_completo', $old_field) ?>" required minlength="3" maxlength="100">
            <?= field_error('nombre_completo', $errors) ?>

            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" value="<?= old_field('email', $old_field) ?>" required>
            <?= field_error('email', $errors) ?>

            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" value="<?= old_field('telefono', $old_field) ?>" pattern="[0-9]{9}" title="Introduce 9 dígitos sin espacios">
            <?= field_error('telefono', $errors) ?>

            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= old_field('fecha_nacimiento', $old_field) ?>" required>
            <?= field_error('fecha_nacimiento', $errors) ?>

            <label for="edad">Edad:</label>
            <input type="number" id="edad" name="edad" value="<?= old_field('edad', $old_field) ?>" required min="18" max="99">
            <?= field_error('edad', $errors) ?>

            <label>Género:</label>
            <div>
                <input type="radio" id="genero_masculino" name="genero" value="masculino"> <label for="genero_masculino">Masculino</label>
                <input type="radio" id="genero_femenino" name="genero" value="femenino"> <label for="genero_femenino">Femenino</label>

            </div>
        </fieldset>

        <fieldset>
            <legend>2. Perfil Profesional</legend>

            <label for="puesto_actual">Puesto Actual:</label>
            <input type="text" id="puesto_actual" name="puesto_actual" value="<?= old_field('puesto_actual', $old_field) ?>" required>
            <?= field_error('puesto_actual', $errors) ?>

            <label for="departamento">Departamento:</label>
            <select id="departamento" name="departamento" required>
                <option value="desarrollo">Desarrollo</option>
                <option value="diseno">Diseño</option>
                <option value="marketing">Marketing</option>
                <option value="rrhh">Recursos Humanos</option>
                <option value="direccion">Dirección</option>
            </select>

            <label for="fecha_incorporacion">Fecha de Incorporación:</label>
            <input type="date" id="fecha_incorporacion" name="fecha_incorporacion" value="<?= old_field('fecha_incorporacion', $old_field) ?>">

            <label for="experiencia_anios">Años de experiencia en el sector:</label>
            <input type="number" id="experiencia_anios" name="experiencia_anios" value="<?= old_field('experiencia_anios', $old_field) ?>" min="0" max="50">

        </fieldset>

        <fieldset>
            <legend>3. Elecciones Sociométricas</legend>

            <label for="eleccion_positiva_1">¿Con qué compañero/a prefieres trabajar en un proyecto?</label>
            <input type="text" id="eleccion_positiva_1" name="eleccion_positiva_1" value="<?= old_field('eleccion_positiva_1', $old_field) ?>" required placeholder="Nombre y Apellido">
            <?= field_error('eleccion_positiva_1', $errors) ?>

            <label for="eleccion_positiva_2">¿Con qué otro/a compañero/a te gustaría colaborar?</label>
            <input type="text" id="eleccion_positiva_2" name="eleccion_positiva_2" value="<?= old_field('eleccion_positiva_2', $old_field) ?>" placeholder="Nombre y Apellido">

            <label for="eleccion_negativa_1">¿Con qué compañero/a prefieres no trabajar directamente?</label>
            <input type="text" id="eleccion_negativa_1" name="eleccion_negativa_1" value="<?= old_field('eleccion_negativa_1', $old_field) ?>" required placeholder="Nombre y Apellido">
            <?= field_error('eleccion_negativa_1', $errors) ?>

            <label for="motivo_eleccion">Motivos de tus elecciones (opcional):</label>
            <textarea id="motivo_eleccion" name="motivo_eleccion" rows="4" maxlength="500"><?= old_field('motivo_eleccion', $old_field) ?></textarea>
        </fieldset>

        <fieldset>
            <legend>4. Autoevaluación y Preferencias</legend>

            <label>¿Qué horario prefieres para reuniones importantes?</label>
            <input type="time" id="hora_reunion_preferida" name="hora_reunion_preferida" value="<?= old_field('hora_reunion_preferida', $old_field) ?>">

            <label for="color_equipo">Si tu equipo fuera un color ¿cuál sería?</label>
            <input type="color" id="color_equipo" name="color_equipo" value="<?= old_field('color_equipo', $old_field) ?: '#007bff' ?>">

            <label>¿Qué tecnologías o herramientas dominas?</label>
            <div>
                <input type="checkbox" id="tec_php" name="tecnologias[]" value="php"> <label for="tec_php">PHP</label>
                <input type="checkbox" id="tec_js" name="tecnologias[]" value="js"> <label for="tec_js">JavaScript</label>
                <input type="checkbox" id="tec_css" name="tecnologias[]" value="css"> <label for="tec_css">CSS</label>
                <input type="checkbox" id="tec_figma" name="tecnologias[]" value="figma"> <label for="tec_figma">Figma</label>
            </div>

            <label for="satisfaccion_general">En una escala de 1 a 10 ¿cómo valoras el ambiente de trabajo actual?</label>
            <input type="range" id="satisfaccion_general" name="satisfaccion_general" min="1" max="10" value="<?= old_field('satisfaccion_general', $old_field) ?: '5' ?>">

            <label for="carga_trabajo">¿Cómo consideras la carga de trabajo actual?</label>
            <select id="carga_trabajo" name="carga_trabajo">
                <option value="baja">Baja</option>
                <option value="adecuada" selected>Adecuada</option>
                <option value="alta">Alta</option>
            </select>

        </fieldset>

        <div class="submit-container">
            <button type="submit">Enviar</button>
        </div>
    </form>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
