<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include('header.php');
include('menu.php');
require_once('../db/conexion.php');

$rol = $_SESSION['rol'] ?? '';
$carreraUsuario = $_SESSION['carrera'] ?? '';

// Lista de carreras para el select filtro
$carreras = [
    'Licenciatura en Administración',
    'Ingeniería Bioquímica',
    'Ingeniería Eléctrica',
    'Ingeniería Electrónica',
    'Ingeniería Industrial',
    'Ingeniería Mecatrónica',
    'Ingeniería Mecánica',
    'Ingeniería en Sistemas Computacionales',
    'Ingeniería Química',
    'Ingeniería en Energías Renovables',
    'Ingeniería en Gestión Empresarial'
];

// Procesar POST para guardar edición (tu código igual)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_egresado'])) {
    // ... sin cambios ...
}

// Obtener valores de filtros (GET o POST)
$nombreBuscar = $_GET['nombre'] ?? '';
$sexoFiltro = $_GET['sexo'] ?? '';
$estadoCivilFiltro = $_GET['estado_civil'] ?? '';
$anioEgresoFiltro = $_GET['anio_egreso'] ?? '';
$tituladoFiltro = $_GET['titulado'] ?? '';
$curpOControl = $_GET['curp_control'] ?? '';
$filtroCarrera = $_GET['filtro_carrera'] ?? '';

// Construir consulta con filtros dinámicos
$sql = "SELECT CURP, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, CARRERA, SEXO, TITULADO, ESTADO_CIVIL, YEAR(FECHA_EGRESO) AS ANIO_EGRESO
        FROM EGRESADO";

$conditions = [];
$params = [];
$types = "";

// Rol jefe departamento ve solo su carrera
if ($rol === 'jefe departamento') {
    $conditions[] = "CARRERA = ?";
    $params[] = $carreraUsuario;
    $types .= "s";
}

// Rol admin o jefe vinculación pueden filtrar por carrera válida
if (($rol === 'jefe vinculación' || $rol === 'administrador') && in_array($filtroCarrera, $carreras)) {
    $conditions[] = "CARRERA = ?";
    $params[] = $filtroCarrera;
    $types .= "s";
}

// Filtros comunes
if (!empty($nombreBuscar)) {
    $conditions[] = "(NOMBRE LIKE ? OR APELLIDO_PATERNO LIKE ? OR APELLIDO_MATERNO LIKE ?)";
    $buscar = "%$nombreBuscar%";
    $params[] = $buscar; $params[] = $buscar; $params[] = $buscar;
    $types .= "sss";
}

if (!empty($curpOControl)) {
    $conditions[] = "(CURP LIKE ? OR NUM_CONTROL LIKE ?)";
    $buscarCC = "%$curpOControl%";
    $params[] = $buscarCC; $params[] = $buscarCC;
    $types .= "ss";
}

if (!empty($sexoFiltro)) {
    $conditions[] = "SEXO = ?";
    $params[] = $sexoFiltro;
    $types .= "s";
}

if (!empty($estadoCivilFiltro)) {
    $conditions[] = "ESTADO_CIVIL = ?";
    $params[] = $estadoCivilFiltro;
    $types .= "s";
}

if (!empty($anioEgresoFiltro)) {
    $conditions[] = "YEAR(FECHA_EGRESO) = ?";
    $params[] = $anioEgresoFiltro;
    $types .= "i";
}

if ($tituladoFiltro !== '') {
    $conditions[] = "TITULADO = ?";
    $params[] = ($tituladoFiltro == '1') ? 1 : 0;
    $types .= "i";
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY APELLIDO_PATERNO, APELLIDO_MATERNO, NOMBRE";

$stmt = $conexion->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Egresados</title>
    <link rel="stylesheet" href="css/egresados.css">
</head>
<body>
    <h2>Listado de Egresados</h2>

    <?php if (!empty($mensajeGuardado)): ?>
        <div class="mensaje-exito"><?= htmlspecialchars($mensajeGuardado) ?></div>
    <?php endif; ?>

    <form method="GET" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
        <input type="text" name="nombre" placeholder="Nombre o apellidos" value="<?= htmlspecialchars($nombreBuscar) ?>">

        <input type="text" name="curp_control" placeholder="CURP o No. Control" value="<?= htmlspecialchars($curpOControl) ?>">

        <select name="sexo">
            <option value="">Sexo</option>
            <option value="Hombre" <?= $sexoFiltro == 'Hombre' ? 'selected' : '' ?>>Hombre</option>
            <option value="Mujer" <?= $sexoFiltro == 'Mujer' ? 'selected' : '' ?>>Mujer</option>
        </select>

        <select name="estado_civil">
            <option value="">Estado Civil</option>
            <option value="Soltero(a)" <?= $estadoCivilFiltro == 'Soltero(a)' ? 'selected' : '' ?>>Soltero(a)</option>
            <option value="Casado(a)" <?= $estadoCivilFiltro == 'Casado(a)' ? 'selected' : '' ?>>Casado(a)</option>
            <option value="Otro" <?= $estadoCivilFiltro == 'Otro' ? 'selected' : '' ?>>Otro</option>
        </select>

        <input type="number" name="anio_egreso" placeholder="Año de egreso" min="1900" max="<?= date('Y') ?>" value="<?= htmlspecialchars($anioEgresoFiltro) ?>">

        <select name="titulado">
            <option value="">¿Titulado?</option>
            <option value="1" <?= $tituladoFiltro == '1' ? 'selected' : '' ?>>Sí</option>
            <option value="0" <?= $tituladoFiltro == '0' ? 'selected' : '' ?>>No</option>
        </select>

        <?php if ($rol === 'jefe vinculación' || $rol === 'administrador'): ?>
            <select name="filtro_carrera">
                <option value="">Todas las carreras</option>
                <?php foreach ($carreras as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= ($filtroCarrera === $c) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button type="submit">Aplicar filtros</button>
    </form>

    <div class="directorio-container">
        <table>
            <thead>
                <tr>
                    <th>CURP</th>
                    <th>Nombre Completo</th>
                    <th>Carrera</th>
                    <th>Sexo</th>
                    <th>Estado Civil</th>
                    <th>Titulado</th>
                    <th>Año de Egreso</th>
                    <th>Acciones</th>
                    <th>Encuestas Respondidas</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($e = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['CURP']) ?></td>
                        <td><?= htmlspecialchars($e['NOMBRE'] . ' ' . $e['APELLIDO_PATERNO'] . ' ' . $e['APELLIDO_MATERNO']) ?></td>
                        <td><?= htmlspecialchars($e['CARRERA']) ?></td>
                        <td><?= htmlspecialchars($e['SEXO']) ?></td>
                        <td><?= htmlspecialchars($e['ESTADO_CIVIL']) ?></td>
                        <td><?= $e['TITULADO'] ? 'Sí' : 'No' ?></td>
                        <td><?= htmlspecialchars($e['ANIO_EGRESO']) ?></td>
                        <td>
                            <button class="btn-editar" onclick="abrirModalEditar('<?= $e['CURP'] ?>')">Editar</button>
                        </td>
                        <td>
                            <button onclick="abrirModal('<?= $e['CURP'] ?>')">Ver encuestas</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal edición egresado -->
    <div id="modal-editar" class="modal-editar-container">
        <div class="modal-editar-box">
            <span class="modal-editar-close" onclick="cerrarModalEditar()">×</span>
            <h3 class="modal-editar-title">Editar datos del egresado</h3>

            <form id="form-editar-egresado" class="modal-editar-form" method="POST" action="includes/editar_egresado.php">
                <input type="hidden" name="curp" id="editar-curp" readonly>
                <input type="hidden" name="editar_egresado" value="1">

                <div class="modal-editar-grid">
                    <!-- Columna izquierda: Datos personales -->
                    <div class="modal-editar-col">
                        <label for="editar-nombre">Nombre:</label>
                        <input type="text" name="NOMBRE" id="editar-nombre" required>

                        <label for="editar-apellido-paterno">Apellido Paterno:</label>
                        <input type="text" name="APELLIDO_PATERNO" id="editar-apellido-paterno" required>

                        <label for="editar-apellido-materno">Apellido Materno:</label>
                        <input type="text" name="APELLIDO_MATERNO" id="editar-apellido-materno" required>

                        <label for="editar-fecha-nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" name="FECHA_NACIMIENTO" id="editar-fecha-nacimiento" required>

                        <label for="editar-sexo">Sexo:</label>
                        <select name="SEXO" id="editar-sexo" required>
                            <option value="Hombre">Hombre</option>
                            <option value="Mujer">Mujer</option>
                        </select>

                        <label for="editar-estado-civil">Estado Civil:</label>
                        <select name="ESTADO_CIVIL" id="editar-estado-civil" required>
                            <option value="Soltero(a)">Soltero(a)</option>
                            <option value="Casado(a)">Casado(a)</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <!-- Columna derecha: Datos académicos y dirección -->
                    <div class="modal-editar-col">
                        <label for="editar-email">Email:</label>
                        <input type="email" name="EMAIL" id="editar-email" required>

                        <label for="editar-telefono">Teléfono:</label>
                        <input type="text" name="TELEFONO" id="editar-telefono">

                        <label for="editar-carrera">Carrera:</label>
                        <select name="CARRERA" id="editar-carrera" required>
                            <?php foreach ($carreras as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="editar-fecha-egreso">Fecha de Egreso:</label>
                        <input type="date" name="FECHA_EGRESO" id="editar-fecha-egreso">

                        <label for="editar-titulado">Titulado:</label>
                        <select name="TITULADO" id="editar-titulado" required>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>

                        <fieldset class="modal-editar-direccion">
                            <legend>Dirección</legend>

                            <label for="editar-calle">Calle:</label>
                            <input type="text" name="CALLE" id="editar-calle">

                            <label for="editar-colonia">Colonia:</label>
                            <input type="text" name="COLONIA" id="editar-colonia">

                            <label for="editar-codigo-postal">Código Postal:</label>
                            <input type="text" name="CODIGO_POSTAL" id="editar-codigo-postal">

                            <label for="editar-ciudad">Ciudad:</label>
                            <input type="text" name="CIUDAD" id="editar-ciudad">

                            <label for="editar-municipio">Municipio:</label>
                            <input type="text" name="MUNICIPIO" id="editar-municipio">

                            <label for="editar-estado">Estado:</label>
                            <input type="text" name="ESTADO" id="editar-estado">
                        </fieldset>
                    </div>
                </div>
                <div id="mensaje-editar-egresado" class="modal-editar-mensaje"></div>
                <button type="submit" class="modal-editar-submit">Guardar cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal encuestas -->
    <div id="modal-overlay" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModal()">×</span>
            <div id="modal-body">Cargando...</div>
        </div>
    </div>

    <div id="modalVerRespuestas" class="modal-ver-respuestas">
        <button class="cerrar-btn" onclick="cerrarModalRespuestas()">Cerrar</button>
        <div id="contenidoRespuestas" class="contenido-respuestas"></div>
    </div>

    <script src="js/egresados.js"></script>
</body>
</html>
