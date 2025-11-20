// -----------------------------------------------------------------------------
// Mini CRUD AJAX — Lado cliente (COMPLETO con ID, Edit y Sesión)
// Archivo: src/public/assets/js/main.js
// -----------------------------------------------------------------------------
const URL_API_SERVIDOR = 'api.php';
const nodoCuerpoTablaUsuarios = document.getElementById('tbody');
const nodoFilaEstadoVacio = document.getElementById('fila-estado-vacio');
const formularioAltaUsuario = document.getElementById('formCreate');
const nodoZonaMensajesEstado = document.getElementById('msg');
const nodoBotonAgregarUsuario = document.getElementById('boton-agregar-usuario');
const nodoIndicadorCargando = document.getElementById('indicador-cargando');

// --- Helpers ---
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
function activarEstadoCargando() {
    if (nodoBotonAgregarUsuario) nodoBotonAgregarUsuario.disabled = true;
    if (nodoIndicadorCargando) nodoIndicadorCargando.hidden = false;
}
function desactivarEstadoCargando() {
    if (nodoBotonAgregarUsuario) nodoBotonAgregarUsuario.disabled = false;
    if (nodoIndicadorCargando) nodoIndicadorCargando.hidden = true;
}
function convertirATextoSeguro(entrada) {
    return String(entrada).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;');
}

// -----------------------------------------------------------------------------
// BLOQUE: Renderizado (con soporte para modo Edición)
// -----------------------------------------------------------------------------
function renderizarTablaDeUsuarios(arrayUsuarios) {
    nodoCuerpoTablaUsuarios.innerHTML = '';
    
    if (Array.isArray(arrayUsuarios) && arrayUsuarios.length > 0) {
        if (nodoFilaEstadoVacio) nodoFilaEstadoVacio.hidden = true;
    } else {
        if (nodoFilaEstadoVacio) nodoFilaEstadoVacio.hidden = false;
        return;
    }

    arrayUsuarios.forEach((usuario) => {
        const id = usuario?.id ?? 'N/A';
        const nombre = convertirATextoSeguro(usuario?.nombre ?? '');
        const email = convertirATextoSeguro(usuario?.email ?? '');
        const rol = convertirATextoSeguro(usuario?.rol ?? 'usuario');

        const nodoFila = document.createElement('tr');
        nodoFila.dataset.id = id;

        // Renderizado normal
        nodoFila.innerHTML = `
            <td>${id}</td>
            <td>${nombre}</td>
            <td>${email}</td>
            <td>${rol}</td>
            <td>
                <button type="button" data-action="edit" data-id="${id}">Editar</button>
                <button type="button" data-action="delete" data-id="${id}" style="background-color: #f44336; border: none;">Eliminar</button>
            </td>
        `;

        nodoCuerpoTablaUsuarios.appendChild(nodoFila);
    });
}

// -----------------------------------------------------------------------------
// BLOQUE: Carga inicial y Manejo de Seguridad
// -----------------------------------------------------------------------------
async function obtenerYMostrarListadoDeUsuarios() {
    try {
        const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=list`);
        
        // ⭐ CORRECCIÓN CLAVE: Manejar 401/403 antes de intentar parsear JSON
        if (respuestaHttp.status === 401 || respuestaHttp.status === 403) {
            // El servidor nos niega el acceso. Forzamos la redirección al login
            window.location.href = 'login.php';
            // Lanzamos un error para detener la ejecución
            throw new Error("Sesión expirada o acceso denegado."); 
        }

        const cuerpoJson = await respuestaHttp.json();
        
        // Si el servidor nos devuelve un JSON (ej. de error de validación)
        if (!cuerpoJson.ok) {
            throw new Error(cuerpoJson.error || 'No fue posible obtener el listado.');
        }
        
        // Todo OK
        renderizarTablaDeUsuarios(cuerpoJson.data);
    } catch (error) {
        mostrarMensajeDeEstado('error', error.message);
    }
}

// -----------------------------------------------------------------------------
// BLOQUE: Alta de usuario (CREATE)
// -----------------------------------------------------------------------------
formularioAltaUsuario?.addEventListener('submit', async (evento) => {
    evento.preventDefault(); 
    const datosFormulario = new FormData(formularioAltaUsuario);
    const datosUsuarioNuevo = {
        nombre: datosFormulario.get('nombre').trim(),
        email: datosFormulario.get('email').trim(),
        password: datosFormulario.get('password').trim(),
        rol: datosFormulario.get('rol'),
    };
    
    if (!datosUsuarioNuevo.nombre || !datosUsuarioNuevo.email || !datosUsuarioNuevo.password) {
        mostrarMensajeDeEstado('error', 'Todos los campos son obligatorios.');
        return;
    }
    
    try {
        activarEstadoCargando();
        const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosUsuarioNuevo),
        });
        
        // La API devuelve un 401/403 JSON en caso de fallo de sesión.
        if (respuestaHttp.status === 401 || respuestaHttp.status === 403) {
            window.location.href = 'login.php';
            return;
        }

        const cuerpoJson = await respuestaHttp.json();
        if (!cuerpoJson.ok) {
            throw new Error(cuerpoJson.error || 'No fue posible crear el usuario.');
        }
        renderizarTablaDeUsuarios(cuerpoJson.data);
        formularioAltaUsuario.reset();
        mostrarMensajeDeEstado('ok', 'Usuario agregado correctamente.');
    } catch (error) {
        mostrarMensajeDeEstado('error', error.message);
    } finally {
        desactivarEstadoCargando();
    }
});

// -----------------------------------------------------------------------------
// BLOQUE: Eliminación (DELETE) y Edición (UPDATE)
// -----------------------------------------------------------------------------
nodoCuerpoTablaUsuarios?.addEventListener('click', async (evento) => {
    const boton = evento.target.closest('button[data-action]');
    if (!boton) return;
    
    const action = boton.dataset.action;
    const id = parseInt(boton.dataset.id, 10);
    
    if (!Number.isInteger(id)) return;

    // --- ACCIÓN ELIMINAR ---
    if (action === 'delete') {
        if (!window.confirm(`¿Deseas eliminar el usuario con ID ${id}?`)) return;
        try {
            const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id }), // DELETE por ID
            });
            
            if (respuestaHttp.status === 401 || respuestaHttp.status === 403) {
                window.location.href = 'login.php';
                return;
            }

            const cuerpoJson = await respuestaHttp.json();
            if (!cuerpoJson.ok) {
                throw new Error(cuerpoJson.error || 'No fue posible eliminar el usuario.');
            }
            obtenerYMostrarListadoDeUsuarios(); // Refrescar la tabla
            mostrarMensajeDeEstado('ok', 'Usuario eliminado correctamente.');
        } catch (error) {
            mostrarMensajeDeEstado('error', error.message);
        }
    } 
    
    // --- ACCIÓN EDITAR (Muestra Formulario en Fila) ---
    else if (action === 'edit') {
        // Opción simple: re-renderizar la tabla para tener los datos originales
        await obtenerYMostrarListadoDeUsuarios(); 
        
        const fila = nodoCuerpoTablaUsuarios.querySelector(`tr[data-id="${id}"]`);
        if (fila) {
            const nombreActual = fila.querySelector('td:nth-child(2)').textContent;
            const emailActual = fila.querySelector('td:nth-child(3)').textContent;
            const rolActual = fila.querySelector('td:nth-child(4)').textContent;

            // Transforma la fila en un formulario de edición
            fila.innerHTML = `
                <td>${id}</td>
                <td><input type="text" value="${convertirATextoSeguro(nombreActual)}" data-field="nombre" class="form-input" required></td>
                <td><input type="email" value="${convertirATextoSeguro(emailActual)}" data-field="email" class="form-input" required></td>
                <td>
                    <select data-field="rol" class="form-input" required>
                        <option value="usuario" ${rolActual.toLowerCase() === 'usuario' ? 'selected' : ''}>Usuario</option>
                        <option value="admin" ${rolActual.toLowerCase() === 'admin' ? 'selected' : ''}>Admin</option>
                    </select>
                </td>
                <td>
                    <input type="password" placeholder="Nueva Contraseña (vacío para no cambiar)" data-field="password" class="form-input" style="width:100px; margin-bottom: 5px;">
                    <button type="button" data-action="save" data-id="${id}">Guardar</button>
                    <button type="button" data-action="cancel" data-id="${id}">Cancelar</button>
                </td>
            `;
        }

    } 
    
    // --- ACCIÓN GUARDAR (Envía UPDATE) ---
    else if (action === 'save') {
        const fila = boton.closest('tr');
        const inputs = fila.querySelectorAll('[data-field]');
        const datosActualizados = { id: id };
        
        inputs.forEach(input => {
            datosActualizados[input.dataset.field] = input.value.trim();
        });

        if (!datosActualizados.nombre || !datosActualizados.email) {
            mostrarMensajeDeEstado('error', 'Nombre y Email no pueden estar vacíos.');
            return;
        }

        try {
            const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosActualizados),
            });
            
            if (respuestaHttp.status === 401 || respuestaHttp.status === 403) {
                window.location.href = 'login.php';
                return;
            }

            const cuerpoJson = await respuestaHttp.json();
            if (!cuerpoJson.ok) {
                throw new Error(cuerpoJson.error || 'No fue posible actualizar el usuario.');
            }
            obtenerYMostrarListadoDeUsuarios(); // Refrescar la tabla
            mostrarMensajeDeEstado('ok', 'Usuario actualizado correctamente.');
        } catch (error) {
            mostrarMensajeDeEstado('error', error.message);
        }
    } 
    
    // --- ACCIÓN CANCELAR ---
    else if (action === 'cancel') {
        obtenerYMostrarListadoDeUsuarios();
        mostrarMensajeDeEstado('', 'Edición cancelada.');
    }
});

// --- Inicialización ---
obtenerYMostrarListadoDeUsuarios();