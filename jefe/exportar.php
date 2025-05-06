<?php
include ('../includes/header_admin.php');
include ('../includes/menu.php');
require ('../db/conexion.php');

$isJefe = $_SESSION['rol'] === 'jefe departamento';
$carreraJefe = $_SESSION['carrera'] ?? '';

$queryPeriodos = "SELECT NOMBRE FROM PERIODO_ENCUESTA ORDER BY FECHA_INICIO DESC";
$resultPeriodos = $conexion->query($queryPeriodos);

$periodos = [];
while ($row = $resultPeriodos->fetch_assoc()) {
    $periodos[] = $row['NOMBRE'];
}
?>
<link rel="stylesheet" href="css/exportar.css">
<div class="contenido-principal">
    <div class="card-exportacion">
        <h2>📤 Exportar Encuestas por Sección</h2>
        <form id="form-exportar" class="formulario-exportacion">
            <label for="carrera">Carrera:</label>
            <select name="carrera" id="carrera">
                <?php if (!$isJefe): ?>
                    <option value="">-- Seleccione una carrera --</option>
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
                <?php else: ?>
                    <option value="">-- Seleccione una carrera --</option>
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