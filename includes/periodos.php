<?php
session_start();

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['Administrador', 'Jefe vinculación'])) {
    die('Acceso denegado');
}

require_once 'conexion.php'; // Asegúrate de tener tu conexión en este archivo

// Insertar nuevo periodo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['fecha_inicio'], $_POST['fecha_fin'])) {
    $nombre = $_POST['nombre'];
    $inicio = $_POST['fecha_inicio'];
    $fin = $_POST['fecha_fin'];

    $stmt = $conexion->prepare("INSERT INTO PERIODO_ENCUESTA (NOMBRE, FECHA_INICIO, FECHA_FIN) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $inicio, $fin);
    $stmt->execute();
    $stmt->close();
}

// Activar periodo
if (isset($_GET['activar'])) {
    $idActivar = intval($_GET['activar']);
    $conexion->query("UPDATE PERIODO_ENCUESTA SET ACTIVO = 0");
    $conexion->query("UPDATE PERIODO_ENCUESTA SET ACTIVO = 1 WHERE ID_PERIODO = $idActivar");
}

// Obtener todos los periodos
$periodos = $conexion->query("SELECT * FROM PERIODO_ENCUESTA ORDER BY FECHA_INICIO DESC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Periodos de Encuesta</title>
    <link rel="stylesheet" href="estilos.css"> <!-- Tu CSS -->
</head>
<body>
    <h1>Gestión de Periodos de Levantamiento</h1>

    <form method="post">
        <label>Nombre del Periodo:</label>
        <input type="text" name="nombre" required>
        <label>Fecha de Inicio:</label>
        <input type="date" name="fecha_inicio" required>
        <label>Fecha de Fin:</label>
        <input type="date" name="fecha_fin" required>
        <button type="submit">Crear Periodo</button>
    </form>

    <h2>Periodos Existentes</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $periodos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['NOMBRE']) ?></td>
                    <td><?= htmlspecialchars($row['FECHA_INICIO']) ?></td>
                    <td><?= htmlspecialchars($row['FECHA_FIN']) ?></td>
                    <td><?= $row['ACTIVO'] ? '✅' : '' ?></td>
                    <td>
                        <?php if (!$row['ACTIVO']): ?>
                            <a href="?activar=<?= $row['ID_PERIODO'] ?>" onclick="return confirm('¿Activar este periodo?')">Activar</a>
                        <?php else: ?>
                            Activo
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
