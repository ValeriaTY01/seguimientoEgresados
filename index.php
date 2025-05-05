<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php?error=1"); // Si no está logueado, redirigir al login
    exit;
}
include 'includes/header.php';
include 'includes/menu.php';
?>


<div class="contenido-principal">
    <div class="botones-flotantes">
        <button  id="btn-encuesta" class="boton-amarillo">Encuesta para Egresados</button>
        <button class="boton-blanco">Reiniciar contraseña.</button>
    </div>
<script src="js/script.js"></script>
<?php include 'includes/footer.php'; ?>

