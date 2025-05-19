<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once 'db/conexion.php';
include('includes/header.php');
include('includes/menu.php');

$curp = $_SESSION['curp'] ?? null;
if (!$curp) {
    echo "<div class='alerta-error'>Acceso denegado.</div>";
    exit;
}

$sql = "SELECT * FROM EGRESADO WHERE CURP = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $curp);
$stmt->execute();
$result = $stmt->get_result();
$egresado = $result->fetch_assoc();

if (!$egresado) {
    echo "<div class='alerta-error'>Egresado no encontrado.</div>";
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rfc = $_SESSION['rfc'] ?? null;
    foreach ($egresado as $campo => $valorAnterior) {
        if (isset($_POST[$campo])) {
            $valorNuevo = $_POST[$campo];
            if ($valorNuevo != $valorAnterior) {
                $sqlLog = "INSERT INTO MODIFICACION_EGRESADO (RFC, CURP, CAMPO_MODIFICADO, VALOR_ANTERIOR, VALOR_NUEVO)
                           VALUES (?, ?, ?, ?, ?)";
                $stmtLog = $conexion->prepare($sqlLog);
                $stmtLog->bind_param("sssss", $rfc, $curp, $campo, $valorAnterior, $valorNuevo);
                $stmtLog->execute();

                $sqlUpdate = "UPDATE EGRESADO SET $campo = ? WHERE CURP = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param("ss", $valorNuevo, $curp);
                $stmtUpdate->execute();
            }
        }
    }
    header("Location: resultados.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Datos - Egresado</title>
    <base href="/seguimientoEgresados/">
    <link rel="stylesheet" href="css/respuesta.css">
</head>
<body>
<div class="contenedor">
    <div class="columna columna-izquierda">
        <div class="card">
            <h2>Cuestionario Respondido Recientemente</h2>
            <?php
            $sqlCuest = "
                SELECT CR.ID_CUESTIONARIO, CR.ID_EMPRESA, CR.FECHA_APLICACION, E.RAZON_SOCIAL
                FROM CUESTIONARIO_RESPUESTA CR
                LEFT JOIN EMPRESA E ON CR.ID_EMPRESA = E.ID_EMPRESA
                WHERE CR.CURP = ?
                ORDER BY CR.FECHA_APLICACION DESC
                LIMIT 1
            ";
            $stmtCuest = $conexion->prepare($sqlCuest);
            $stmtCuest->bind_param("s", $curp);
            $stmtCuest->execute();
            $resultCuest = $stmtCuest->get_result();

            if ($cuest = $resultCuest->fetch_assoc()) {
                $idCuestionario = $cuest['ID_CUESTIONARIO'];
                echo "<p><strong>Empresa:</strong> " . htmlspecialchars($cuest['RAZON_SOCIAL']) . "</p>";
                echo "<p><strong>Fecha de aplicación:</strong> " . htmlspecialchars($cuest['FECHA_APLICACION']) . "</p>";

                $sqlRespuestas = "
                    SELECT 
                        S.NOMBRE AS SECCION,
                        S.ORDEN AS ORDEN_SECCION,
                        P.TEXTO AS PREGUNTA,
                        P.TIPO,
                        R.RESPUESTA_TEXTO,
                        O.TEXTO AS OPCION_TEXTO
                    FROM RESPUESTA R
                    INNER JOIN PREGUNTA P ON R.ID_PREGUNTA = P.ID_PREGUNTA
                    INNER JOIN SECCION S ON P.ID_SECCION = S.ID_SECCION
                    LEFT JOIN OPCION_RESPUESTA O ON R.ID_OPCION = O.ID_OPCION
                    WHERE R.ID_CUESTIONARIO = ?
                    ORDER BY S.ORDEN, P.ORDEN
                ";
                $stmtResp = $conexion->prepare($sqlRespuestas);
                $stmtResp->bind_param("i", $idCuestionario);
                $stmtResp->execute();
                $resultResp = $stmtResp->get_result();

                $secciones = [];
                while ($row = $resultResp->fetch_assoc()) {
                    $seccion = $row['SECCION'];
                    $pregunta = htmlspecialchars($row['PREGUNTA']);
                    $respuesta = ($row['TIPO'] === 'texto' || $row['TIPO'] === 'escala' || $row['TIPO'] === 'boolean')
                        ? htmlspecialchars($row['RESPUESTA_TEXTO'])
                        : htmlspecialchars($row['OPCION_TEXTO']);

                    $secciones[$seccion][] = "
                        <div class='pregunta-respuesta'>
                            <p class='pregunta'><strong>$pregunta</strong></p>
                            <p class='respuesta'>$respuesta</p>
                        </div>
                    ";
                }

                // Generar pestañas
                if (!empty($secciones)) {
                    echo '<div class="tabs">';
                    echo '<div class="tab-buttons">';
                    $i = 0;
                    foreach ($secciones as $nombre => $contenido) {
                        $active = $i === 0 ? 'active' : '';
                        echo "<button class='tab-btn $active' data-tab='tab-$i'>" . htmlspecialchars($nombre) . "</button>";
                        $i++;
                    }
                    echo '</div><div class="tab-contents">';
                    $i = 0;
                    foreach ($secciones as $nombre => $contenido) {
                        $active = $i === 0 ? 'active' : '';
                        echo "<div class='tab-content $active' id='tab-$i'>" . implode('', $contenido) . "</div>";
                        $i++;
                    }
                    echo '</div></div>';
                }
            } else {
                echo "<p><em>No se encontró ningún cuestionario respondido.</em></p>";
            }
            ?>
        </div>
    </div>

    <div class="columna columna-derecha">
        <div class="card">
            <h2>Datos Personales del Egresado</h2>
            <form method="POST" class="formulario">
                <?php foreach ($egresado as $campo => $valor): ?>
                    <?php if (in_array($campo, ['CONTRASENA', 'VERIFICADO', 'CODIGO_VERIFICACION'])) continue; ?>
                    <div class="form-grupo">
                        <label for="<?= $campo ?>"><?= ucwords(str_replace("_", " ", $campo)) ?></label>
                        <?php if ($campo == 'CURP' || $campo == 'NUM_CONTROL'): ?>
                            <input type="text" name="<?= $campo ?>" id="<?= $campo ?>" value="<?= htmlspecialchars($valor) ?>" readonly>
                        <?php elseif ($campo == 'TITULADO'): ?>
                            <select name="<?= $campo ?>" id="<?= $campo ?>">
                                <option value="1" <?= $valor ? 'selected' : '' ?>>Sí</option>
                                <option value="0" <?= !$valor ? 'selected' : '' ?>>No</option>
                            </select>
                        <?php elseif ($campo == 'SEXO'): ?>
                            <select name="<?= $campo ?>" id="<?= $campo ?>">
                                <option <?= $valor == 'Hombre' ? 'selected' : '' ?>>Hombre</option>
                                <option <?= $valor == 'Mujer' ? 'selected' : '' ?>>Mujer</option>
                            </select>
                        <?php elseif ($campo == 'ESTADO_CIVIL'): ?>
                            <select name="<?= $campo ?>" id="<?= $campo ?>">
                                <option <?= $valor == 'Soltero(a)' ? 'selected' : '' ?>>Soltero(a)</option>
                                <option <?= $valor == 'Casado(a)' ? 'selected' : '' ?>>Casado(a)</option>
                                <option <?= $valor == 'Otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        <?php elseif ($campo == 'CARRERA'): ?>
                            <select name="<?= $campo ?>" id="<?= $campo ?>">
                                <?php
                                 $carreras = [
                                'Licenciatura en Administración', 'Ingeniería Bioquímica', 'Ingeniería Eléctrica',
                                'Ingeniería Electrónica', 'Ingeniería Industrial', 'Ingeniería Mecatrónica',
                                'Ingeniería Mecánica', 'Ingeniería en Sistemas Computacionales', 'Ingeniería Química',
                                'Ingeniería en Energías Renovables', 'Ingeniería en Gestión Empresarial'
                                ];
                                foreach ($carreras as $carrera) {
                                    $selected = $valor == $carrera ? 'selected' : '';
                                    echo "<option $selected>$carrera</option>";
                                }
                                ?>
                            </select>
                        <?php else: ?>
                            <input type="<?= ($campo == 'FECHA_EGRESO' || $campo == 'FECHA_NACIMIENTO') ? 'date' : 'text' ?>"
                                   name="<?= $campo ?>" id="<?= $campo ?>" value="<?= htmlspecialchars($valor) ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div class="form-boton">
                    <button type="submit">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});
</script>
</body>
</html>