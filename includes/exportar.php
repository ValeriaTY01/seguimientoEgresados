<?php
include ('header.php');
include ('menu.php');
require ('../db/conexion.php');

// Validación de rol
$rolUsuario = strtolower($_SESSION['rol'] ?? '');
$isAdmin = in_array($rolUsuario, ['administrador', 'jefe vinculación']);
$isJefe = $rolUsuario === 'jefe departamento';

$carreraJefe = $_SESSION['carrera'] ?? '';

// Obtener periodos
$queryPeriodos = "SELECT NOMBRE FROM PERIODO_ENCUESTA ORDER BY FECHA_INICIO DESC";
$resultPeriodos = $conexion->query($queryPeriodos);

$periodos = [];
while ($row = $resultPeriodos->fetch_assoc()) {
    $periodos[] = $row['NOMBRE'];
}

// Lista de carreras
$carreras = [
    "Licenciatura en Administración",
    "Ingeniería Bioquímica",
    "Ingeniería Eléctrica",
    "Ingeniería Electrónica",
    "Ingeniería Industrial",
    "Ingeniería Mecatrónica",
    "Ingeniería Mecánica",
    "Ingeniería en Sistemas Computacionales",
    "Ingeniería Química",
    "Ingeniería en Energías Renovables",
    "Ingeniería en Gestión Empresarial"
];
?>
<link rel="stylesheet" href="css/exportar.css">
<div class="contenido-principal">
    <div class="card-exportacion">
        <h2>📤 Exportar Encuestas por Sección</h2>
        <form id="form-exportar" class="formulario-exportacion">
            <label for="carrera">Carrera:</label>
            <select name="carrera" id="carrera">
                <?php if ($isAdmin): ?>
                    <option value="">-- Seleccione una carrera --</option>
                    <?php foreach ($carreras as $carrera): ?>
                        <option value="<?= htmlspecialchars($carrera) ?>"><?= htmlspecialchars($carrera) ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="<?= htmlspecialchars($carreraJefe) ?>"><?= htmlspecialchars($carreraJefe) ?></option>
                <?php endif; ?>
            </select>

            <label for="periodo">Periodo:</label>
            <select name="periodo" id="periodo">
                <option value="">-- Seleccione un periodo --</option>
                <?php foreach ($periodos as $nombrePeriodo): ?>
                    <option value="<?= htmlspecialchars($nombrePeriodo) ?>"><?= htmlspecialchars($nombrePeriodo) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="button" class="boton-amarillo" id="btnExportarExcel">📄 Generar Excel</button>
        </form>
        <div id="mensajeExportacion" style="display:none;">⏳ Generando archivo...</div>
    </div>
</div>

<script src="js/exportar.js"></script>
