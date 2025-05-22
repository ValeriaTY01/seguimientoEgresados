<?php
session_start();

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['administrador', 'jefe vinculación'])) {
    die('Acceso denegado');
}

require_once '../db/conexion.php';
include('header.php');
include('menu.php');

$errores = [];

// Función para validar años
function esFechaValida($fecha) {
    $anioActual = date('Y');
    $anioFecha = date('Y', strtotime($fecha));
    return $anioFecha <= $anioActual;
}

// Crear nuevo periodo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $nombre = $_POST['nombre'];
    $inicio = $_POST['fecha_inicio'];
    $fin = $_POST['fecha_fin'];

    // Validar que las fechas sean válidas y que fecha_fin no sea menor que fecha_inicio
    if (!esFechaValida($inicio) || !esFechaValida($fin)) {
        $errores[] = "Las fechas deben ser válidas y no en un año futuro.";
    } elseif ($fin < $inicio) {
        $errores[] = "La fecha fin no puede ser menor que la fecha inicio.";
    }

    if (empty($errores)) {
        $stmt = $conexion->prepare("INSERT INTO PERIODO_ENCUESTA (NOMBRE, FECHA_INICIO, FECHA_FIN) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $inicio, $fin);
        $stmt->execute();
        $stmt->close();
        header('Location: periodos.php');
        exit;
    }
}

// Editar periodo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $nombre = $_POST['nombre'];
    $inicio = $_POST['fecha_inicio'];
    $fin = $_POST['fecha_fin'];
    $id = $_POST['id'];

    // Validar que las fechas sean válidas y que fecha_fin no sea menor que fecha_inicio
    if (!esFechaValida($inicio) || !esFechaValida($fin)) {
        $errores[] = "Las fechas deben ser válidas y no en un año futuro.";
    } elseif ($fin < $inicio) {
        $errores[] = "La fecha fin no puede ser menor que la fecha inicio.";
    }

    if (empty($errores)) {
        $stmt = $conexion->prepare("UPDATE PERIODO_ENCUESTA SET NOMBRE=?, FECHA_INICIO=?, FECHA_FIN=? WHERE ID_PERIODO=?");
        $stmt->bind_param("sssi", $nombre, $inicio, $fin, $id);
        $stmt->execute();
        $stmt->close();
        header('Location: periodos.php');
        exit;
    }
}

// Eliminar periodo (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $idEliminar = intval($_POST['id']);
    if ($idEliminar > 0) {
        $stmt = $conexion->prepare("DELETE FROM PERIODO_ENCUESTA WHERE ID_PERIODO=?");
        $stmt->bind_param("i", $idEliminar);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: periodos.php');
            exit;
        } else {
            $stmt->close();
            $errores[] = 'Error al eliminar el periodo.';
        }
    } else {
        $errores[] = 'ID inválido para eliminar.';
    }
}

$result = $conexion->query("SELECT * FROM PERIODO_ENCUESTA ORDER BY FECHA_INICIO DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Periodos</title>
    <link rel="stylesheet" href="css/periodos.css">
</head>
<body>
    <div class="container">
        <h1>Gestión de Periodos de Encuesta</h1>

        <?php if (!empty($errores)): ?>
            <div class="errores">
                <?php foreach ($errores as $error): ?>
                    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2>Crear Nuevo Periodo</h2>
        <form method="post">
            <input type="hidden" name="accion" value="crear">
            <div>
                <label for="nombre">Nombre:
                    <input type="text" name="nombre" id="nombre" required>
                </label>
            </div>
            <div>
                <label for="fecha_inicio">Inicio:
                    <input type="date" name="fecha_inicio" id="fecha_inicio" required max="<?= date('Y') ?>-12-31">
                </label>
            </div>
            <div>
                <label for="fecha_fin">Fin:
                    <input type="date" name="fecha_fin" id="fecha_fin" required max="<?= date('Y') ?>-12-31">
                </label>
            </div>
            <div style="grid-column: 1 / -1; text-align: center;">
                <button type="submit">Guardar</button>
            </div>
        </form>

        <h2>Periodos Existentes</h2>
        <table>
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
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="<?= $row['ACTIVO'] ? 'activo' : '' ?>">
                    <td><?= htmlspecialchars($row['NOMBRE']) ?></td>
                    <td><?= $row['FECHA_INICIO'] ?></td>
                    <td><?= $row['FECHA_FIN'] ?></td>
                    <td><?= $row['ACTIVO'] ? '✅' : '❌' ?></td>
                    <td>
                        <button class="btn toggle-btn" data-id="<?= $row['ID_PERIODO'] ?>">
                            <?= $row['ACTIVO'] ? 'Desactivar' : 'Activar' ?>
                        </button>
                        <button class="btn" onclick='editarPeriodo(<?= json_encode($row) ?>)'>Editar</button>
                        <form method="post" style="display:inline;" onsubmit="return confirm('¿Eliminar este periodo?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= $row['ID_PERIODO'] ?>">
                            <button type="submit" class="btn">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="modalEditar" style="display:none;" class="modal">
        <h3>Editar Periodo</h3>
        <form method="post">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label for="edit_nombre">Nombre:
                    <input type="text" name="nombre" id="edit_nombre" required>
                </label>
            </div>
            <div>
                <label for="edit_inicio">Inicio:
                    <input type="date" name="fecha_inicio" id="edit_inicio" required max="<?= date('Y') ?>-12-31">
                </label>
            </div>
            <div>
                <label for="edit_fin">Fin:
                    <input type="date" name="fecha_fin" id="edit_fin" required max="<?= date('Y') ?>-12-31">
                </label>
            </div>
            <div style="grid-column: 1 / -1; text-align: center;">
                <button type="submit">Actualizar</button>
                <button type="button" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>


    <script src="js/periodos.js"></script>
</body>
</html>
