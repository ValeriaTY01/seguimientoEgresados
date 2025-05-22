document.addEventListener('DOMContentLoaded', () => {
    // Manejo de botones de activar/desactivar periodo
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            fetch('includes/ajax_periodos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=toggle&id=${encodeURIComponent(id)}`
            })
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Error al cambiar el estado del periodo'));
                    }
                } catch (e) {
                    alert('Respuesta inesperada del servidor: ' + text);
                }
            })
            .catch(() => alert('Error en la comunicación con el servidor'));
        });
    });

    // Asignación de eventos a los botones de cerrar modal (si existen)
    const cerrarBtn = document.getElementById('cerrarModalBtn');
    if (cerrarBtn) {
        cerrarBtn.addEventListener('click', cerrarModal);
    }
});

// Función para abrir el modal con datos del periodo
function editarPeriodo(periodo) {
    document.getElementById('edit_id').value = periodo.ID_PERIODO;
    document.getElementById('edit_nombre').value = periodo.NOMBRE;
    document.getElementById('edit_inicio').value = periodo.FECHA_INICIO;
    document.getElementById('edit_fin').value = periodo.FECHA_FIN;
    document.getElementById('modalEditar').style.display = 'block';
}

// Función para cerrar el modal y limpiar el formulario
function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
    document.getElementById('edit_id').value = '';
    document.getElementById('edit_nombre').value = '';
    document.getElementById('edit_inicio').value = '';
    document.getElementById('edit_fin').value = '';
}
