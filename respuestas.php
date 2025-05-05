<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'egresado') {
    header("Location: login.php?error=1");
    exit;
}

require('db/conexion.php');

$curp = $_SESSION['curp'] ?? null;

if (!$curp) {
    echo "CURP no encontrada en sesión.";
    exit;
}

// Obtener el cuestionario más reciente respondido por este egresado
$sqlCuestionario = "
    SELECT ID_CUESTIONARIO 
    FROM CUESTIONARIO_RESPUESTA 
    WHERE CURP = ? AND COMPLETO = 1 
    ORDER BY FECHA_APLICACION DESC 
    LIMIT 1
";

$stmtCuest = $conexion->prepare($sqlCuestionario);
$stmtCuest->bind_param("s", $curp);
$stmtCuest->execute();
$resultCuest = $stmtCuest->get_result();

if ($resultCuest->num_rows === 0) {
    echo "<p>No has completado ningún cuestionario aún.</p>";
    exit;
}

$cuestionario = $resultCuest->fetch_assoc();
$idCuestionario = $cuestionario['ID_CUESTIONARIO'];

// Obtener las preguntas con sus respuestas
$sql = "
SELECT 
    S.NOMBRE AS SECCION,
    P.TEXTO AS PREGUNTA,
    P.TIPO,
    R.RESPUESTA_TEXTO,
    O.TEXTO AS OPCION
FROM RESPUESTA R
JOIN PREGUNTA P ON R.ID_PREGUNTA = P.ID_PREGUNTA
JOIN SECCION S ON P.ID_SECCION = S.ID_SECCION
LEFT JOIN OPCION_RESPUESTA O ON R.ID_OPCION = O.ID_OPCION
WHERE R.ID_CUESTIONARIO = ?
ORDER BY S.ORDEN, P.ORDEN
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idCuestionario);
$stmt->execute();
$result = $stmt->get_result();

$seccionActual = '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Respuestas</title>
    <link rel="stylesheet" href="../css/estilos.css"> <!-- Asegúrate de tener estilos -->
</head>
<body>
    <h1>Respuestas del Cuestionario</h1>

    <div class="respuestas">
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php if ($seccionActual !== $row['SECCION']): ?>
                <?php $seccionActual = $row['SECCION']; ?>
                <h2><?= htmlspecialchars($seccionActual) ?></h2>
            <?php endif; ?>

            <div class="pregunta">
                <strong><?= htmlspecialchars($row['PREGUNTA']) ?></strong><br>
                <p>
                    <?php
                        if ($row['TIPO'] === 'texto' || $row['TIPO'] === 'escala' || $row['TIPO'] === 'boolean') {
                            echo htmlspecialchars($row['RESPUESTA_TEXTO']);
                        } else {
                            echo htmlspecialchars($row['OPCION']);
                        }
                    ?>
                </p>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>