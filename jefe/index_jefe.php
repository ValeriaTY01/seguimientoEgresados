<?php
session_start();
include '../includes/header_admin.php';
include '../includes/menu.php'; // el menú ya usa $_SESSION['rol']
?>

<div class="contenido-principal">
    <h2>Bienvenido, Jefe de Departamento</h2>
    <p>Utilice las herramientas del menú para gestionar información de su carrera.</p>
</div>

<?php include '../includes/footer.php'; ?>
