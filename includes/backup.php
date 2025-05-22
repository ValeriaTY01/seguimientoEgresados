<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$rol = $_SESSION['rol'] ?? 'Invitado';
if (strtolower($rol) !== 'dba') {
    header('Location: ../index.php');
    exit;
}

include("header.php"); 
include("menu.php");
?>

<link rel="stylesheet" href="css/backup.css">
<h2>Respaldos del Sistema</h2>

<div class="backup-container">

    <!-- Sección: Descargar respaldo -->
    <section class="backup-section">
        <h3>Descargar respaldo de la base de datos</h3>
        <form action="includes/procesar_respaldo.php" method="post">
            <input type="hidden" name="accion" value="respaldar">
            <button type="submit" class="btn">Generar respaldo</button>
        </form>
    </section>

    <!-- Sección: Restaurar respaldo -->
    <section class="backup-section">
        <h3>Restaurar base de datos desde archivo</h3>
        <form action="includes/procesar_respaldo.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="restaurar">
            <input type="file" name="archivo_sql" accept=".sql" required>
            <button type="submit" class="btn btn-restore">Restaurar respaldo</button>
        </form>
    </section>

    <!-- Sección: Historial de respaldos guardados -->
    <section class="backup-section">
        <h3>Historial de respaldos guardados</h3>
        <?php
        $directorio = __DIR__ . '/../respaldo_bd/';
        $archivos = [];

        if (is_dir($directorio)) {
            $archivos = array_diff(scandir($directorio, SCANDIR_SORT_DESCENDING), ['.', '..']);
        }

        if (empty($archivos)) {
            echo "<p>No hay respaldos almacenados.</p>";
        } else {
            echo "<table class='tabla-respaldos'>
                    <thead>
                        <tr>
                            <th>Nombre del archivo</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>";
            foreach ($archivos as $archivo) {
                $ruta = $directorio . $archivo;
                $fecha = date("Y-m-d H:i:s", filemtime($ruta));
                echo "<tr>
                        <td>$archivo</td>
                        <td>$fecha</td>
                        <td>
                            <a href='respaldo_bd/$archivo' class='btn' download>Descargar</a>
                            <form action='includes/procesar_respaldo.php' method='post' style='display:inline-block;'>
                                <input type='hidden' name='accion' value='restaurar_archivo'>
                                <input type='hidden' name='nombre_archivo' value='$archivo'>
                                <button type='submit' class='btn btn-restore' onclick='return confirm(\"¿Restaurar este respaldo? Esta acción es irreversible.\")'>Restaurar</button>
                            </form>
                        </td>
                      </tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </section>

</div>