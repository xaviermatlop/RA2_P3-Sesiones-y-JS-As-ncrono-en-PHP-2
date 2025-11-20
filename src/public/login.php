<?php
// Iniciamos sesión para verificar si ya está logueado
require_once __DIR__ . '/../auth.php';

// Si ya hay sesión, redirigimos al panel apropiado
if (check_session()) {
    $destino = $_SESSION['rol'] === 'admin' ? 'panel_admin.php' : 'sociograma.php';
    header("Location: {$destino}");
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Login - Mini CRUD AJAX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/css/styles.css" />
    <style>
        .contenedor-login { max-width: 400px; margin: 100px auto; }
        .bloque-formulario { padding: 40px; }
        .titulo-login { text-align: center; color: var(--color-primario); }
    </style>
</head>
<body>
    <div class="contenedor-login">
        <header>
            <h1 class="titulo-login">Acceso al Sistema</h1>
        </header>
        <main class="bloque-formulario">
            <div id="msg" class="mensajes-estado" role="status" aria-live="polite"></div>
            
            <form id="formLogin" class="formulario-alta-usuario">
                <div class="form-row">
                    <label for="campo-email" class="form-label">Email</label>
                    <input id="campo-email" name="email" class="form-input" type="email" required placeholder="tu@correo.com" autocomplete="email">
                </div>
                <div class="form-row">
                    <label for="campo-password" class="form-label">Contraseña</label>
                    <input id="campo-password" name="password" class="form-input" type="password" required placeholder="Contraseña" autocomplete="current-password">
                </div>
                <div class="form-actions">
                    <button id="boton-login" type="submit" class="boton-primario">
                        Iniciar Sesión
                    </button>
                    <span id="indicador-cargando" class="indicador-cargando" hidden>Cargando...</span>
                </div>
            </form>
        </main>
    </div>

    <script>
        const formLogin = document.getElementById('formLogin');
        const nodoZonaMensajesEstado = document.getElementById('msg');
        const nodoBotonLogin = document.getElementById('boton-login');
        const nodoIndicadorCargando = document.getElementById('indicador-cargando');

        function mostrarMensajeDeEstado(tipoEstado, textoMensaje) {
            nodoZonaMensajesEstado.className = 'mensajes-estado ' + tipoEstado; // .ok | .error | ''
            nodoZonaMensajesEstado.textContent = textoMensaje;
            if (tipoEstado !== '') {
                setTimeout(() => {
                    nodoZonaMensajesEstado.className = 'mensajes-estado';
                    nodoZonaMensajesEstado.textContent = '';
                }, 3000);
            }
        }
        
        function activarEstadoCargando(activar) {
            nodoBotonLogin.disabled = activar;
            nodoIndicadorCargando.hidden = !activar;
        }

        formLogin.addEventListener('submit', async (evento) => {
            evento.preventDefault();
            activarEstadoCargando(true);

            const datosFormulario = new FormData(formLogin);
            const datosLogin = new URLSearchParams(datosFormulario);

            try {
                // Usamos el front-controller público
                const respuestaHttp = await fetch('auth.php?action=login', {
                    method: 'POST',
                    body: datosLogin,
                });

                const cuerpoJson = await respuestaHttp.json();

                if (cuerpoJson.ok) {
                    mostrarMensajeDeEstado('ok', cuerpoJson.data.mensaje);
                    // Redirección al panel del usuario/admin
                    window.location.href = cuerpoJson.data.destino; 
                } else {
                    throw new Error(cuerpoJson.error || 'Error al iniciar sesión.');
                }
            } catch (error) {
                mostrarMensajeDeEstado('error', error.message);
            } finally {
                activarEstadoCargando(false);
            }
        });
    </script>
</body>
</html>