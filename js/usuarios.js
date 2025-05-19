document.addEventListener("DOMContentLoaded", () => {
    cargarUsuarios();
    document.getElementById("btnAgregarUsuario").addEventListener("click", abrirModalAgregar);
    document.getElementById("formUsuario").addEventListener("submit", guardarUsuario);
    document.getElementById("filtroRol").addEventListener("change", cargarUsuarios);
    document.getElementById("rol").addEventListener("change", function() {
    const campoCarrera = document.getElementById("carrera"); // El div que contiene el select
    if (this.value === "jefe departamento") {
        campoCarrera.style.display = "block";
    } else {
        campoCarrera.style.display = "none";
        document.getElementById("carrera").value = ""; // limpiar carrera si no aplica
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
            tbody.innerHTML = "";

            usuarios.forEach(u => {
                let fila = `
                    <tr>
                        <td>${u.rfc}</td>
                        <td>${u.nombre}</td>
                        <td>${u.email}</td>
                        <td>${u.rol}</td>
                        <td>${u.carrera || ''}</td>
                        <td>
                            <button onclick="editarUsuario('${u.rfc}')">Editar</button>
                            <button onclick="eliminarUsuario('${u.rfc}')">Eliminar</button>
                        </td>
                    </tr>`;
                tbody.innerHTML += fila;
            });
        })
        .catch(error => console.error("Error fetch usuarios:", error));
}

function abrirModalAgregar() {
    document.getElementById("formUsuario").reset();
    document.getElementById("modo").value = "agregar";
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
            document.getElementById("modo").value = "editar";
            document.getElementById("rfc_original").value = data.rfc;
            document.getElementById("rfc").value = data.rfc;
            document.getElementById("nombre").value = data.nombre;
            document.getElementById("apellido_paterno").value = data.apellido_paterno || "";
            document.getElementById("apellido_materno").value = data.apellido_materno || "";
            document.getElementById("email").value = data.email;
            document.getElementById("rol").value = data.rol;
            document.getElementById("carrera").value = data.carrera;
            document.getElementById("contrasena").value = ""; // no mostrar la actual
            document.getElementById("modalUsuario").style.display = "block";
        });
}

function guardarUsuario(e) {
    e.preventDefault();
    const form = e.target;
    const datos = new FormData(form);

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
