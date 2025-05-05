document.addEventListener("DOMContentLoaded", () => {
    const secciones = document.querySelectorAll(".seccion");
    const btnAnterior = document.getElementById("anterior");
    const btnSiguiente = document.getElementById("siguiente");
    const btnEnviar = document.getElementById("enviar");
    const progreso = document.getElementById("progreso");
    const modalExito = document.getElementById("modalExito");
    const formulario = document.getElementById("formularioCuestionario");
    const btnVolverInicio = document.getElementById("volverInicio");

    let indiceActual = 0;

    function mostrarSeccion(index) {
        secciones.forEach((sec, i) => {
            sec.style.display = i === index ? "block" : "none";
        });

        btnAnterior.style.display = index === 0 ? "none" : "inline-block";
        btnSiguiente.style.display = index === secciones.length - 1 ? "none" : "inline-block";
        btnEnviar.style.display = index === secciones.length - 1 ? "inline-block" : "none";

        progreso.style.width = ((index + 1) / secciones.length) * 100 + "%";
    }

    btnAnterior.addEventListener("click", () => {
        if (indiceActual > 0) {
            indiceActual--;
            mostrarSeccion(indiceActual);
        }
    });

    btnSiguiente.addEventListener("click", () => {
        if (indiceActual < secciones.length - 1) {
            const seccionActual = secciones[indiceActual];
            const preguntas = seccionActual.querySelectorAll(".pregunta");

            for (const pregunta of preguntas) {
                const tipo = pregunta.dataset.tipo;
                const inputs = pregunta.querySelectorAll("input, textarea");
                const esObligatoria = Array.from(inputs).some(input => input.hasAttribute("required"));
                if (!esObligatoria) continue;

                let respondido = false;
                if (tipo === "multiple") {
                    respondido = Array.from(inputs).some(input => input.checked);
                } else if (tipo === "texto") {
                    respondido = Array.from(inputs).some(input => input.value.trim() !== "");
                } else {
                    respondido = Array.from(inputs).some(input => input.checked);
                }

                if (!respondido) {
                    alert("Por favor, responde todas las preguntas obligatorias antes de continuar.");
                    return;
                }
            }

            indiceActual++;
            mostrarSeccion(indiceActual);
        }
    });

    formulario.addEventListener("submit", async (e) => {
        e.preventDefault();

        const respuestas = [];

        secciones.forEach(seccion => {
            const preguntas = seccion.querySelectorAll(".pregunta");

            preguntas.forEach(pregunta => {
                const tipo = pregunta.dataset.tipo;
                const inputs = pregunta.querySelectorAll("input, textarea");

                if (inputs.length === 0) return;

                const name = inputs[0].name;
                const match = name.match(/\[(\d+)\]/);
                const id_pregunta = match ? parseInt(match[1]) : null;
                if (!id_pregunta) return;

                let respuesta = null;

                if (tipo === "multiple") {
                    respuesta = [];
                    inputs.forEach(input => {
                        if (input.checked) respuesta.push(parseInt(input.value));
                    });
                } else if (tipo === "texto") {
                    respuesta = inputs[0].value.trim();
                } else {
                    inputs.forEach(input => {
                        if (input.checked) {
                            respuesta = parseInt(input.value);
                        }
                    });
                }

                if (
                    (Array.isArray(respuesta) && respuesta.length > 0) ||
                    (!Array.isArray(respuesta) && respuesta !== null && respuesta !== "")
                ) {
                    respuestas.push({ id_pregunta, respuesta });
                }
            });
        });

        const empresa = {};
        const camposEmpresa = formulario.querySelectorAll("[name^='empresa[']");
        camposEmpresa.forEach(campo => {
            const key = campo.name.match(/empresa\[(.+?)\]/);
            if (key && key[1]) {
                empresa[key[1]] = campo.value.trim();
            }
        });

        const opcionEmpresa = respuestas.find(r => r.id_pregunta === 10 && r.respuesta === 25);
        if (opcionEmpresa) {
            const camposRequeridos = [
                "tipo_organismo", "giro", "razon_social", "calle", "numero", "colonia",
                "codigo_postal", "ciudad", "municipio", "estado", "telefono", "email"
            ];

            const faltantes = camposRequeridos.filter(campo => {
                const input = formulario.querySelector(`[name="empresa[${campo}]"]`);
                return !input || input.value.trim() === "";
            });

            if (faltantes.length > 0) {
                alert("Por favor, completa todos los datos de la empresa antes de enviar.");
                return;
            }
        }

        try {
            const resp = await fetch("includes/guardar_respuestas.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ respuestas, empresa })
            });

            const data = await resp.json();

            if (data.success) {
                formulario.style.display = "none";
                modalExito.style.display = "flex";
            } else {
                alert("Error al enviar respuestas: " + (data.error || "Inténtalo nuevamente."));
            }
        } catch (err) {
            console.error("Error en la solicitud:", err);
            alert("Ocurrió un error al enviar el formulario.");
        }
    });

    if (btnVolverInicio) {
        btnVolverInicio.addEventListener("click", () => {
            window.location.href = "index.php";
        });
    }

    mostrarSeccion(indiceActual);

    if (typeof FORMULARIO_DESACTIVADO !== "undefined" && FORMULARIO_DESACTIVADO === true) {
        const elementos = formulario.querySelectorAll("input, textarea, button");
        elementos.forEach(el => {
            el.disabled = true;
        });
        if (btnEnviar) btnEnviar.style.display = "none";
    }

    if (typeof RESPUESTAS_PREVIAS !== "undefined" && Array.isArray(RESPUESTAS_PREVIAS)) {
        RESPUESTAS_PREVIAS.forEach(res => {
            const id = res.id_pregunta;
            const valor = res.respuesta;

            const contenedor = document.querySelector(`.pregunta[data-id='${id}']`);
            if (!contenedor) return;

            const tipo = contenedor.dataset.tipo;
            const inputs = contenedor.querySelectorAll("input, textarea");

            if (tipo === "multiple" && Array.isArray(valor)) {
                inputs.forEach(input => {
                    if (valor.includes(parseInt(input.value))) {
                        input.checked = true;
                    }
                });
            } else if (tipo === "texto") {
                inputs[0].value = valor;
            } else {
                inputs.forEach(input => {
                    if (parseInt(input.value) === valor) {
                        input.checked = true;
                    }
                });
            }
        });
    }
});

document.addEventListener("DOMContentLoaded", () => {
    // === LÓGICA SECCIÓN 3 ===
    const pregunta10 = document.querySelector('.pregunta input[name="respuesta[10]"]')?.closest('.pregunta');
    if (!pregunta10) return;

    const empresaFieldset = document.querySelector('fieldset.empresa');
    const secciones = document.querySelectorAll('.seccion');

    // Ocultar sección 3 (preguntas 11 a 28) por defecto
    secciones.forEach(seccion => {
        const contienePreguntas = [...seccion.querySelectorAll('.pregunta')];
        contienePreguntas.forEach(p => {
            const pid = parseInt(p.querySelector('[name^="respuesta["]')?.name.match(/\d+/)?.[0] || 0);
            if (pid >= 11 && pid <= 28) {
                p.style.display = 'none';
            }
        });
    });

    if (empresaFieldset) {
        empresaFieldset.style.display = 'none';
    }

    const manejarCambioEnPregunta10 = () => {
        const opcionSeleccionada = document.querySelector('input[name="respuesta[10]"]:checked');
        if (!opcionSeleccionada) return;

        const valor = parseInt(opcionSeleccionada.value);

        const mostrarEmpresa = [25, 27].includes(valor);
        const mostrar11y12 = valor === 26;
        const mostrarTodo = valor === 27;

        if (empresaFieldset) {
            empresaFieldset.style.display = mostrarEmpresa ? 'block' : 'none';
        }

        secciones.forEach(seccion => {
            const contienePreguntas = [...seccion.querySelectorAll('.pregunta')];
            contienePreguntas.forEach(p => {
                const pid = parseInt(p.querySelector('[name^="respuesta["]')?.name.match(/\d+/)?.[0] || 0);
                if (pid >= 11 && pid <= 28) {
                    if (mostrarTodo) {
                        p.style.display = 'block';
                    } else if (mostrarEmpresa && pid >= 13 && pid <= 28) {
                        p.style.display = 'block';
                    } else if (mostrar11y12 && (pid === 11 || pid === 12)) {
                        p.style.display = 'block';
                    } else {
                        p.style.display = 'none';
                    }
                }
            });
        });
    };

    manejarCambioEnPregunta10();
    document.querySelectorAll('input[name="respuesta[10]"]').forEach(radio => {
        radio.addEventListener('change', manejarCambioEnPregunta10);
    });
});