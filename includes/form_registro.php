<?php
// form_registro.php: Formulario de registro mostrado dentro del modal
?>
<div class="modal-verificacion" id="modal-registro">
  <div class="modal-content-verificacion">
    <button class="btn-cerrar-modal" onclick="cerrarModal()" title="Cerrar">×</button>
    <h2 class="modal-title">Registro de Egresado</h2>
    <form action="procesar_registro.php" method="POST" class="formulario-registro" id="form-registro" autocomplete="off">
        <div class="grid-campos">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellido_paterno" placeholder="Apellido Paterno" required>
            <input type="text" name="apellido_materno" placeholder="Apellido Materno" required>
            <input type="text" name="curp" placeholder="CURP" required>
            <input type="text" name="num_control" placeholder="Número de Control" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
        </div>

        <div class="campo-contrasena">
            <input type="password" name="contrasena" id="contrasena" placeholder="Contraseña" required>
            <i class="fa fa-eye" id="togglePassword" title="Mostrar / Ocultar contraseña"></i>
        </div>

        <div class="captcha-container">
            <img src="captcha.php" alt="CAPTCHA" id="captcha-img" title="Haz clic para actualizar" onclick="this.src='captcha.php?'+Date.now()">
            <input type="text" name="captcha" placeholder="Ingresa el texto" required>
        </div>

        <input type="hidden" name="codigo_verificacion" value="<?php echo $_SESSION['codigo_verificacion'] ?? ''; ?>">
        <button type="submit" class="btn-submit">Registrarse</button>
    </form>
  </div>
</div>

<base href="/seguimientoEgresados/">
<link rel="stylesheet" href="css/modal.css">
<script src="js/registro.js"></script>
