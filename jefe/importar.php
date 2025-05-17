<?php
include ('../includes/header_jefe.php');
include ('../includes/menu.php');
?>

<link rel="stylesheet" href="css/importar.css">

<div class="contenido-principal">
    <div class="card-exportacion">
        <h2>📥 Importar Encuestas desde Excel</h2>

        <form id="form-importar" class="formulario-exportacion" enctype="multipart/form-data">
            <label for="archivoExcel">Selecciona archivo Excel (.xlsx):</label>
            <input type="file" name="archivoExcel" id="archivoExcel" accept=".xlsx" required>

            <button type="button" class="boton-amarillo" id="btnPrevisualizar">🔍 Previsualizar</button>
        </form>

        <div id="mensajeImportacion" style="display:none;">⏳ Procesando archivo...</div>

        <div id="treeviewPreview" class="treeview-container">
            <!-- Aquí se mostrará la vista tipo TreeView -->
        </div>

        <div style="text-align:center; margin-top: 20px;">
            <button type="button" class="boton-verde" id="btnConfirmarImportacion" style="display:none;">✅ Confirmar Importación</button>
        </div>
    </div>
</div>

<!-- Modal de selección de período -->
<div id="modalPeriodo" class="modal">
    <div class="modal-contenido">
        <h3>📅 Selecciona el período de encuesta</h3>
        <select id="selectPeriodo">
            <option value="">-- Selecciona un período --</option>
            <?php
            require('../db/conexion.php');
            $query = "SELECT ID_PERIODO, CONCAT(NOMBRE, ' (', FECHA_INICIO, ' - ', FECHA_FIN, ')') AS PERIODO FROM PERIODO_ENCUESTA ORDER BY FECHA_INICIO DESC";
            $result = $conexion->query($query);
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['ID_PERIODO']}'>{$row['PERIODO']}</option>";
            }
            ?>
        </select>

        <div style="text-align:right; margin-top: 20px;">
            <button class="boton-verde" id="btnAceptarPeriodo">✅ Aceptar</button>
            <button class="boton-rojo" id="btnCancelarPeriodo">❌ Cancelar</button>
        </div>
    </div>
</div>

<script src="js/importar.js"></script>
