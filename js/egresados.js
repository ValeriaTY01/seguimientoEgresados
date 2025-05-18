function abrirModal(curp) {
    const modal = document.getElementById('modal-overlay');
    const body = document.getElementById('modal-body');
    modal.classList.add('visible');
    body.innerHTML = 'Cargando...';

    fetch(`includes/ver_encuestas.php?curp=${encodeURIComponent(curp)}`)
        .then(response => {
            if (!response.ok) throw new Error('Error al cargar encuestas');
            return response.text();
        })
        .then(html => body.innerHTML = html)
        .catch(() => body.innerHTML = 'Error al cargar las encuestas.');
}

function cerrarModal() {
    const modal = document.getElementById('modal-overlay');
    modal.classList.remove('visible');
    document.getElementById('modal-body').innerHTML = '';
}

function verRespuestas(id) {
    const contenido = document.getElementById('contenidoRespuestas');
    const modalRespuestas = document.getElementById('modalVerRespuestas');

    contenido.innerHTML = 'Cargando...';
    modalRespuestas.style.display = 'block';

    fetch(`includes/ver_respuestas.php?id=${encodeURIComponent(id)}`)
        .then(res => {
            if (!res.ok) throw new Error('Error al cargar respuestas');
            return res.text();
        })
        .then(html => contenido.innerHTML = html)
        .catch(err => {
            console.error("Error al cargar respuestas:", err);
            contenido.innerHTML = 'No se pudieron cargar las respuestas.';
        });
}

function cerrarModalRespuestas() {
    const modalRespuestas = document.getElementById('modalVerRespuestas');
    modalRespuestas.style.display = 'none';
    document.getElementById('contenidoRespuestas').innerHTML = '';
}

function abrirModalEditar(curp) {
    const modal = document.getElementById('modal-editar');
    modal.classList.add('visible');

    document.getElementById('editar-curp').value = curp;

    // Limpio mensaje previo
    const mensajeDiv = document.getElementById('mensaje-editar-egresado');
    if (mensajeDiv) {
        mensajeDiv.textContent = '';
        mensajeDiv.style.display = 'none';
        mensajeDiv.style.color = 'green';
    }

    fetch(`includes/editar_egresado.php?curp=${encodeURIComponent(curp)}`)
        .then(res => {
            if (!res.ok) throw new Error('Egresado no encontrado');
            return res.json();
        })
        .then(data => {
            document.getElementById('editar-nombre').value = data.NOMBRE || '';
            document.getElementById('editar-apellido-paterno').value = data.APELLIDO_PATERNO || '';
            document.getElementById('editar-apellido-materno').value = data.APELLIDO_MATERNO || '';
            document.getElementById('editar-fecha-nacimiento').value = data.FECHA_NACIMIENTO || '';
            document.getElementById('editar-sexo').value = data.SEXO || '';
            document.getElementById('editar-estado-civil').value = data.ESTADO_CIVIL || '';
            document.getElementById('editar-email').value = data.EMAIL || '';
            document.getElementById('editar-telefono').value = data.TELEFONO || '';
            document.getElementById('editar-carrera').value = data.CARRERA || '';
            document.getElementById('editar-fecha-egreso').value = data.FECHA_EGRESO || '';
            document.getElementById('editar-titulado').checked = data.TITULADO == 1;
            document.getElementById('editar-calle').value = data.CALLE || '';
            document.getElementById('editar-colonia').value = data.COLONIA || '';
            document.getElementById('editar-codigo-postal').value = data.CODIGO_POSTAL || '';
            document.getElementById('editar-ciudad').value = data.CIUDAD || '';
            document.getElementById('editar-municipio').value = data.MUNICIPIO || '';
            document.getElementById('editar-estado').value = data.ESTADO || '';
                       // 🔁 Asegurarse de conectar el submit al editar
            const formEditar = document.getElementById('form-editar-egresado');
            if (formEditar) {
                formEditar.removeEventListener('submit', enviarEdicion); // Previene duplicados
                formEditar.addEventListener('submit', enviarEdicion);
            }
        })

        .catch(err => {
            console.error("Error al cargar datos de egresado:", err);
            alert('No se pudieron cargar los datos del egresado.');
            cerrarModalEditar();
        });
}

function cerrarModalEditar() {
    const modal = document.getElementById('modal-editar');
    modal.classList.remove('visible');
}

// Actualizo esta función para manejar JSON y mostrar mensaje en div
function enviarEdicion(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    const mensajeDiv = document.getElementById('mensaje-editar-egresado');
    mensajeDiv.textContent = '';
    mensajeDiv.style.display = 'none';
    mensajeDiv.style.color = 'green';

    fetch('includes/editar_egresado.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.message) {
            mensajeDiv.textContent = data.message;
            mensajeDiv.style.color = 'green';
            mensajeDiv.style.display = 'block';
            if (data.message.toLowerCase().includes('correctamente')) {
                // Cierra modal y refresca después de 1.5 segundos
                setTimeout(() => {
                    cerrarModalEditar();
                    location.reload();
                }, 1500);
            }
        } else if (data.error) {
            mensajeDiv.textContent = data.error;
            mensajeDiv.style.color = 'red';
            mensajeDiv.style.display = 'block';
        } else {
            mensajeDiv.textContent = 'Respuesta inesperada del servidor.';
            mensajeDiv.style.color = 'red';
            mensajeDiv.style.display = 'block';
        }
    })
    .catch(() => {
        mensajeDiv.textContent = 'Error al guardar los cambios.';
        mensajeDiv.style.color = 'red';
        mensajeDiv.style.display = 'block';
    });
}
