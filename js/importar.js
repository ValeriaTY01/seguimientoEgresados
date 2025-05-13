document.addEventListener("DOMContentLoaded", function () {
    const inputArchivo = document.getElementById("archivoExcel");
    const botonVistaPrevia = document.getElementById("btnPrevisualizar");
    const contenedorVistaPrevia = document.getElementById("treeviewPreview");
    const botonConfirmar = document.getElementById("btnConfirmarImportacion");
    const mensaje = document.getElementById("mensajeImportacion");

    const modalPeriodo = document.getElementById("modalPeriodo");
    const selectPeriodo = document.getElementById("selectPeriodo");
    const btnAceptarPeriodo = document.getElementById("btnAceptarPeriodo");
    const btnCancelarPeriodo = document.getElementById("btnCancelarPeriodo");

    let datosCargados = null;
    let periodoSeleccionado = null;

    if (!contenedorVistaPrevia) {
        console.error("Error: No se encontró el elemento con id 'treeviewPreview'.");
        return;
    }

    botonVistaPrevia.addEventListener("click", function () {
        const archivo = inputArchivo.files[0];
        if (!archivo) {
            alert("Por favor selecciona un archivo Excel.");
            return;
        }

        mensaje.style.display = "block";
        mensaje.innerText = "⏳ Procesando archivo...";

        const formData = new FormData();
        formData.append("archivoExcel", archivo);

        fetch("jefe/procesar_excel.php", {
            method: "POST",
            body: formData
        })
        .then(resp => resp.json())
        .then(data => {
            datosCargados = data;
            contenedorVistaPrevia.innerHTML = "";

            Object.keys(data).forEach(seccion => {
                const seccionDatos = data[seccion];
                if (Array.isArray(seccionDatos) && seccionDatos.length > 0) {
                    const table = document.createElement("table");
                    table.classList.add("tablaVistaPrevia");

                    const encabezados = Object.keys(seccionDatos[0]);
                    const thead = document.createElement("thead");
                    const encabezadoFila = document.createElement("tr");
                    encabezados.forEach(encabezado => {
                        const th = document.createElement("th");
                        th.textContent = encabezado;
                        encabezadoFila.appendChild(th);
                    });
                    thead.appendChild(encabezadoFila);
                    table.appendChild(thead);

                    const tbody = document.createElement("tbody");
                    seccionDatos.forEach(fila => {
                        const tr = document.createElement("tr");
                        encabezados.forEach(encabezado => {
                            const td = document.createElement("td");
                            td.textContent = fila[encabezado] ?? "-";
                            tr.appendChild(td);
                        });
                        tbody.appendChild(tr);
                    });
                    table.appendChild(tbody);

                    const sectionDiv = document.createElement("div");
                    sectionDiv.classList.add("sectionPreview");
                    const sectionTitle = document.createElement("h3");
                    sectionTitle.textContent = seccion;
                    sectionDiv.appendChild(sectionTitle);
                    sectionDiv.appendChild(table);
                    contenedorVistaPrevia.appendChild(sectionDiv);
                }
            });

            mensaje.innerText = "✅ Vista previa generada.";
            botonConfirmar.style.display = "inline-block";
        })
        .catch(error => {
            console.error("Error al procesar archivo:", error);
            mensaje.innerText = "❌ Error al procesar el archivo.";
        });
    });

    botonConfirmar.addEventListener("click", function () {
        if (!datosCargados) {
            alert("No hay datos cargados para importar.");
            return;
        }
        // Mostrar modal de período
        modalPeriodo.style.display = "block";
    });

    btnCancelarPeriodo.addEventListener("click", function () {
        modalPeriodo.style.display = "none";
    });

    btnAceptarPeriodo.addEventListener("click", function () {
        periodoSeleccionado = selectPeriodo.value;

        if (!periodoSeleccionado) {
            alert("⚠️ Por favor selecciona un período válido.");
            return;
        }

        modalPeriodo.style.display = "none";
        mensaje.innerText = "⏳ Importando datos...";

        fetch("jefe/guardar_importacion.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                periodo: periodoSeleccionado,
                datos: datosCargados
            })
        })
        .then(resp => resp.json())
        .then(respuesta => {
            if (respuesta.exito) {
                mensaje.innerText = "✅ Importación realizada con éxito.";
            } else {
                mensaje.innerText = "⚠️ Error en la importación: " + respuesta.mensaje;
            }
        })
        .catch(error => {
            console.error("Error al guardar:", error);
            mensaje.innerText = "❌ Error al guardar los datos.";
        });
    });
});
