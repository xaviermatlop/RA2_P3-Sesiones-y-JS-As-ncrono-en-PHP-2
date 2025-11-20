<?php
// Necesitamos la lógica de autenticación para comprobar la sesión
require_once __DIR__ . '/../auth.php';

// Si no es admin, redirigir al login o a la vista de usuario
if (!check_session('admin')) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <title>Panel de Administración (CRUD AJAX)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/css/styles.css" />
  </head>
  <body>
    <header class="encabezado-aplicacion">
      <h1 class="encabezado-aplicacion__titulo">
        Panel de Administración (CRUD Completo)
      </h1>
      <p class="encabezado-aplicacion__descripcion">
        Bienvenido, Administrador (<?= htmlspecialchars($_SESSION['nombre']) ?>). Gestión de Usuarios vía AJAX.
      </p>
      <p><a href="logout.php">Cerrar Sesión</a></p>
    </header>
    <main class="zona-principal" id="zona-principal" tabindex="-1">
      <div
        id="msg"
        class="mensajes-estado"
        role="status"
        aria-live="polite"
        aria-atomic="true"
      ></div>
      
      <section class="bloque-formulario" aria-labelledby="titulo-formulario">
        <h2 id="titulo-formulario">Agregar nuevo usuario</h2>
        <form
          id="formCreate"
          class="formulario-alta-usuario"
          autocomplete="off"
          novalidate
        >
          <div class="form-row">
            <label for="campo-nombre" class="form-label">Nombre</label>
            <input
              id="campo-nombre"
              name="nombre"
              class="form-input"
              type="text"
              required
              maxlength="60"
              placeholder="Ej.: Ana Pérez"
              autocomplete="name"
            />
          </div>
          <div class="form-row">
            <label for="campo-email" class="form-label">Email</label>
            <input
              id="campo-email"
              name="email"
              class="form-input"
              type="email"
              required
              maxlength="120"
              placeholder="ejemplo@correo.com"
              autocomplete="email"
            />
          </div>
          <div class="form-row">
            <label for="campo-password" class="form-label">Contraseña</label>
            <input
              id="campo-password"
              name="password"
              class="form-input"
              type="password"
              required
              placeholder="Mínimo 8 caracteres"
            />
          </div>
          <div class="form-row">
            <label for="campo-rol" class="form-label">Rol</label>
            <select id="campo-rol" name="rol" class="form-input" required>
                <option value="usuario">Usuario</option>
                <option value="admin">Administrador</option>
            </select>
          </div>
          <div class="form-actions">
            <button
              id="boton-agregar-usuario"
              type="submit"
              class="boton-primario"
            >
              Agregar usuario
            </button>
            <span
              id="indicador-cargando"
              class="indicador-cargando"
              aria-hidden="true"
              hidden
            >
              Cargando...
            </span>
          </div>
        </form>
      </section>
      
      <section class="bloque-listado" aria-labelledby="titulo-listado">
        <h2 id="titulo-listado">Listado de usuarios</h2>
        <div class="tabla-contenedor" role="region" aria-labelledby="titulo-listado">
          <table class="tabla-usuarios">
            <thead>
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Nombre</th>
                <th scope="col">Email</th>
                <th scope="col">Rol</th>
                <th scope="col">Acción</th>
              </tr>
            </thead>
            <tbody id="tbody">
              <tr id="fila-estado-vacio" class="fila-estado-vacio" hidden>
                <td colspan="5">
                  <em>No hay usuarios registrados todavía.</em>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
      
    </main>
    <footer class="pie-aplicacion">
      <p>CRUD asíncrono implementado con fetch() y API PHP.</p>
    </footer>
    <script src="assets/js/main.js" defer></script>
  </body>
</html>