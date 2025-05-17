document.addEventListener('DOMContentLoaded', function () {
    const tipoInforme = document.querySelector('select[name="tipo_informe"]');
    const formato = document.querySelector('select[name="formato"]');
    const seccionesContainer = document.getElementById('secciones_container');
    const seccionSelect = seccionesContainer.querySelector('select[name="seccion"]');
    const busquedaContainer = document.getElementById('busqueda_egresado_container');
    const inputBusqueda = document.getElementById('busqueda_egresado');
    const resultadosBusqueda = document.getElementById('resultados_busqueda');
    const curpSeleccionado = document.getElementById('curp_seleccionado');
    const form = document.querySelector('form');
    const btnGenerar = form.querySelector('button[type="submit"]');

    // Mostrar/ocultar select de secciones según tipo_informe
    function toggleSecciones() {
        if (tipoInforme.value === 'por_seccion') {
            seccionesContainer.style.display = 'block';
        } else {
            seccionesContainer.style.display = 'none';
        }
    }

    // Mostrar/ocultar barra de búsqueda según tipo_informe
    function toggleBusquedaEgresado() {
        if (tipoInforme.value === 'detallado') {
            busquedaContainer.style.display = 'block';
            alert('Para obtener información específica de un egresado, utilice el buscador.');
        } else {
            busquedaContainer.style.display = 'none';
            inputBusqueda.value = '';
            curpSeleccionado.value = '';
            resultadosBusqueda.style.display = 'none';
        }
    }

    // Cambiar visibilidad al cambiar tipo de informe
    tipoInforme.addEventListener('change', () => {
        toggleSecciones();
        toggleBusquedaEgresado();
    });

    // Ejecutar en carga por si ya está seleccionado
    toggleSecciones();
    toggleBusquedaEgresado();

    // Validar formulario antes de enviar
    form.addEventListener('submit', function(e) {
        // Evita múltiples clics
        btnGenerar.disabled = true;

        // Validación de sección
        if (tipoInforme.value === 'por_seccion' && !seccionSelect.value) {
            e.preventDefault();
            alert('Por favor, seleccione una sección para generar el informe por sección.');
            seccionSelect.focus();
            btnGenerar.disabled = false; // Rehabilitar botón
            return false;
        }

        // Advertencia de generación lenta para ciertos informes en PDF
        const tipo = tipoInforme.value;
        const formatoSeleccionado = formato ? formato.value : '';

        if ((tipo === 'estadistico' || tipo === 'historico') && formatoSeleccionado === 'pdf') {
            alert('Este tipo de informe puede tardar en generarse, por favor espere sin cerrar esta ventana.');
        }

        // Envío continúa y el botón queda desactivado
    });

    // Autocomplete para búsqueda egresado
    inputBusqueda.addEventListener('input', function () {
        const texto = inputBusqueda.value.trim();
        curpSeleccionado.value = ''; // Resetear selección si cambia texto

        if (texto.length < 3) {
            resultadosBusqueda.style.display = 'none';
            return;
        }

        fetch('jefe/buscar_egresados.php?q=' + encodeURIComponent(texto))
            .then(response => response.json())
            .then(data => {
                resultadosBusqueda.innerHTML = '';
                if (data.length === 0) {
                    resultadosBusqueda.style.display = 'none';
                    return;
                }
                data.forEach(egresado => {
                    const div = document.createElement('div');
                    div.textContent = `${egresado.NOMBRE} ${egresado.APELLIDO_PATERNO} ${egresado.APELLIDO_MATERNO} - ${egresado.CURP} - ${egresado.NUM_CONTROL}`;
                    div.style.padding = '5px';
                    div.style.cursor = 'pointer';
                    div.style.borderBottom = '1px solid #ddd';
                    div.addEventListener('click', () => {
                        inputBusqueda.value = `${egresado.NOMBRE} ${egresado.APELLIDO_PATERNO} ${egresado.APELLIDO_MATERNO} - ${egresado.CURP} - ${egresado.NUM_CONTROL}`;
                        curpSeleccionado.value = egresado.CURP;
                        resultadosBusqueda.style.display = 'none';
                    });
                    resultadosBusqueda.appendChild(div);
                });
                resultadosBusqueda.style.display = 'block';
            })
            .catch(() => {
                resultadosBusqueda.style.display = 'none';
            });
    });

    // Ocultar lista si clic fuera de la barra de búsqueda y resultados
    document.addEventListener('click', (e) => {
        if (!busquedaContainer.contains(e.target)) {
            resultadosBusqueda.style.display = 'none';
        }
    });

    // Opcional: Si el usuario borra el input manualmente, limpiar curp seleccionado
    inputBusqueda.addEventListener('blur', () => {
        if (!curpSeleccionado.value || !inputBusqueda.value.includes(curpSeleccionado.value)) {
            curpSeleccionado.value = '';
        }
    });
});
