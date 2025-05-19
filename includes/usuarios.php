<?php 
include("header.php"); 
include("menu.php");
?>
<link rel="stylesheet" href="css/usuarios.css">

<div class="container">
    <h2>Gestión de Usuarios</h2>

    <div class="acciones">
        <button id="btnAgregarUsuario">Agregar Usuario</button>
        <div class="filtros">
            <select id="filtroRol">
                <option value="">Filtrar por Rol</option>
                <option value="DBA">DBA</option>
                <option value="Administrador">Administrador</option>
                <option value="Jefe Departamento">Jefe Departamento</option>
                <option value="Jefe Vinculación">Jefe Vinculación</option>
            </select>
            <select id="filtroCarrera">
                <option value="">Filtrar por Carrera</option>
                <!-- Se carga dinámicamente -->
            </select>
        </div>
    </div>

    <table id="tablaUsuarios">
        <thead>
            <tr>
                <th>RFC</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Carrera</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- JS llenará esta tabla -->
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="modalUsuario" class="modal" style="display: none;">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal()">&times;</span>
        <h3 id="tituloModal">Agregar Usuario</h3>
        <form id="formUsuario">
            <input type="hidden" name="modo" id="modo">
            <input type="hidden" name="rfc_original" id="rfc_original">

            <label>RFC:</label>
            <input type="text" name="rfc" id="rfc" required>

            <label>Nombre:</label>
            <input type="text" name="nombre" id="nombre" required>

            <label>Apellido Paterno:</label>
            <input type="text" name="apellido_paterno" id="apellido_paterno" required>

            <label>Apellido Materno:</label>
            <input type="text" name="apellido_materno" id="apellido_materno">

            <label>Email:</label>
            <input type="email" name="email" id="email" required>

            <label>Contraseña:</label>
            <input type="password" name="contrasena" id="contrasena">

            <label>Rol:</label>
            <select name="rol" id="rol" required>
                <option value="DBA">DBA</option>
                <option value="Administrador">Administrador</option>
                <option value="Jefe Departamento">Jefe Departamento</option>
                <option value="Jefe Vinculación">Jefe Vinculación</option>
            </select>

            <label>Carrera:</label>
            <select name="carrera" id="carrera">
                <option value="">Ninguna</option>
                <option value="Licenciatura en Administración">Licenciatura en Administración</option>
                <option value="Ingeniería Bioquímica">Ingeniería Bioquímica</option>
                <option value="Ingeniería Eléctrica">Ingeniería Eléctrica</option>
                <option value="Ingeniería Electrónica">Ingeniería Electrónica</option>
                <option value="Ingeniería Industrial">Ingeniería Industrial</option>
                <option value="Ingeniería Mecatrónica">Ingeniería Mecatrónica</option>
                <option value="Ingeniería Mecánica">Ingeniería Mecánica</option>
                <option value="Ingeniería en Sistemas Computacionales">Ingeniería en Sistemas Computacionales</option>
                <option value="Ingeniería Química">Ingeniería Química</option>
                <option value="Ingeniería en Energías Renovables">Ingeniería en Energías Renovables</option>
                <option value="Ingeniería en Gestión Empresarial">Ingeniería en Gestión Empresarial</option>
            </select>

            <div class="acciones-modal">
                <button type="submit">Guardar</button>
                <button type="button" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script src="js/usuarios.js"></script>
