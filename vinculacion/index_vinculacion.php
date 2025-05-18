<?php
session_start();
include '../includes/header.php';
include '../includes/menu.php'; // el menú ya usa $_SESSION['rol']
?>

<div class="contenido-principal">
    <div class="bienvenida">
        <h2>BIENVENIDO(A)</h2>
        <p><?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
