document.addEventListener("DOMContentLoaded", () => {
    cargarUsuarios();

    document.getElementById("btnAgregarUsuario").addEventListener("click", abrirModalAgregar);
    document.getElementById("formUsuario").addEventListener("submit", guardarUsuario);
    document.getElementById("filtroRol").addEventListener("change", cargarUsuarios);
    document.getElementById("filtroCarrera").addEventListener("change", cargarUsuarios);

    document.getElementById("rol").addEventListener("change", function () {
        const contenedorCarrera = document.getElementById("contenedorCarrera");
        const selectCarrera = document.getElementById("carrera");
        if (this.value.toLowerCase() === "jefe departamento") {
            contenedorCarrera.style.display = "block";
        } else {
            contenedorCarrera.style.display = "none";
            selectCarrera.value = "";
        }
    });
});

function cargarUsuarios() {
    const rol = document.getElementById("filtroRol").value;
    const carrera = document.getElementById("filtroCarrera").value;

    fetch(`includes/usuarios_api.php?accion=listar&rol=${rol}&carrera=${carrera}`)
        .then(res => res.json())
        .then(json => {
            if (!json.ok) {
                console.error("Error API:", json.mensaje);
                return;
            }
            const usuarios = json.data;
            const tbody = document.querySelector("#tablaUsuarios tbody");
            tbody.innerHTML = usuarios.map(u => `
                <tr>
                    <td>${u.rfc}</td>
                    <td>${u.nombre}</td>
                    <td>${u.email}</td>
                    <td>${u.rol}</td>
                    <td>${u.carrera || ''}</td>
                    <td>
                        <button class="boton-editar" data-rfc="${u.rfc}">Editar</button>
                        <button class="boton-eliminar" data-rfc="${u.rfc}">Eliminar</button>
                    </td>
                </tr>
            `).join("");

            // Asignar eventos a los botones de editar y eliminar
            document.querySelectorAll(".boton-editar").forEach(btn => {
                btn.addEventListener("click", () => editarUsuario(btn.dataset.rfc));
            });
            document.querySelectorAll(".boton-eliminar").forEach(btn => {
                btn.addEventListener("click", () => eliminarUsuario(btn.dataset.rfc));
            });
        })
        .catch(error => console.error("Error fetch usuarios:", error));
}

function abrirModalAgregar() {
    document.getElementById("formUsuario").reset();
    document.getElementById("accion").value = "agregar";
    // Ocultar contenedor de carrera al agregar
    document.getElementById("contenedorCarrera").style.display = "none";
    document.getElementById("modalUsuario").style.display = "block";
}

function cerrarModal() {
    document.getElementById("modalUsuario").style.display = "none";
}

function editarUsuario(rfc) {
    fetch(`includes/usuarios_api.php?accion=obtener&rfc=${rfc}`)
        .then(res => res.json())
        .then(u => {
            if (!u.ok) {
                alert("Error: " + u.mensaje);
                return;
            }
            const data = u.data;
            document.getElementById("accion").value = "actualizar";
            document.getElementById("rfc_original").value = data.rfc;
            document.getElementById("rfc").value = data.rfc;
            document.getElementById("nombre").value = data.nombre;
            document.getElementById("apellido_paterno").value = data.apellido_paterno || "";
            document.getElementById("apellido_materno").value = data.apellido_materno || "";
            document.getElementById("email").value = data.email;
            document.getElementById("rol").value = data.rol;
            document.getElementById("carrera").value = data.carrera || "";
            document.getElementById("contrasena").value = ""; // no mostrar la actual

            // Mostrar u ocultar carrera según el rol
            const contenedorCarrera = document.getElementById("contenedorCarrera");
            if (data.rol.toLowerCase() === "jefe departamento") {
                contenedorCarrera.style.display = "block";
            } else {
                contenedorCarrera.style.display = "none";
            }

            document.getElementById("modalUsuario").style.display = "block";
        });
}

function guardarUsuario(e) {
    e.preventDefault();
    const form = e.target;
    const datos = new FormData(form);

    if (!datos.get("rfc") || !datos.get("nombre") || !datos.get("email") || !datos.get("rol")) {
        alert("Por favor completa todos los campos obligatorios.");
        return;
    }

    fetch("includes/usuarios_api.php", {
        method: "POST",
        body: datos
    })
    .then(res => res.json())
    .then(resp => {
        alert(resp.mensaje);
        if (resp.ok) {
            cerrarModal();
            cargarUsuarios();
        }
    });
}

function eliminarUsuario(rfc) {
    if (confirm("¿Estás seguro de eliminar este usuario?")) {
        fetch("includes/usuarios_api.php", {
            method: "POST",
            body: new URLSearchParams({
                accion: "eliminar",
                rfc: rfc
            })
        })
        .then(res => res.json())
        .then(resp => {
            alert(resp.mensaje);
            if (resp.ok) cargarUsuarios();
        });
    }
}
