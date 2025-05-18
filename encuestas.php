<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'egresado') {
    header("Location: login.php?error=1");
    exit;
}

require('db/conexion.php');

$curp = $_SESSION['curp'] ?? null;

if (!$curp) {
    header("Location: logout.php");
    exit;
}

// Obtener el ID del período activo actual
$periodo_actual = $conexion->query("
    SELECT ID_PERIODO 
    FROM PERIODO_ENCUESTA 
    WHERE ACTIVO = 1 
    AND CURDATE() BETWEEN FECHA_INICIO AND FECHA_FIN 
    LIMIT 1
")->fetch_assoc();
$_SESSION['id_periodo_actual'] = $periodo_actual ? $periodo_actual['ID_PERIODO'] : null;

// Verificar si ya respondió en el periodo ACTIVO
$consulta = $conexion->prepare("
    SELECT cr.ID_PERIODO 
    FROM CUESTIONARIO_RESPUESTA cr
    JOIN PERIODO_ENCUESTA pe ON cr.ID_PERIODO = pe.ID_PERIODO
    WHERE cr.CURP = ? AND pe.ACTIVO = TRUE
    LIMIT 1
");
$consulta->bind_param("s", $curp);
$consulta->execute();
$consulta->store_result();
$consulta->bind_result($id_periodo_respondido);

if ($consulta->fetch()) {
    $_SESSION['ya_respondio'] = true;
    $_SESSION['id_periodo_respondido'] = $id_periodo_respondido;
} else {
    $_SESSION['ya_respondio'] = false;
    $_SESSION['id_periodo_respondido'] = null;
}


// Procesar formulario al inicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'guardar-datos') {
    $fecha_egreso = (!empty($_POST['fecha_egreso']) && $_POST['fecha_egreso'] !== '0000-00-00') ? $_POST['fecha_egreso'] : null;

    $upd = $conexion->prepare("UPDATE EGRESADO SET
        NUM_CONTROL=?, NOMBRE=?, APELLIDO_PATERNO=?,
        APELLIDO_MATERNO=?, FECHA_NACIMIENTO=?, SEXO=?,
        ESTADO_CIVIL=?, EMAIL=?, TELEFONO=?,
        CARRERA=?, FECHA_EGRESO=?, TITULADO=?,
        CALLE=?, COLONIA=?, CODIGO_POSTAL=?,
        CIUDAD=?, MUNICIPIO=?, ESTADO=?
        WHERE CURP=?");


// "sssssssssssisssssss"
    $upd->bind_param("sssssssssssssssssss",
        $_POST['num_control'], $_POST['nombre'],
        $_POST['apellido_paterno'], $_POST['apellido_materno'],
        $_POST['fecha_nacimiento'], $_POST['sexo'],
        $_POST['estado_civil'], $_POST['email'],
        $_POST['telefono'], $_POST['carrera'],
        $fecha_egreso, $_POST['titulado'],
        $_POST['calle'], $_POST['colonia'], $_POST['codigo_postal'],
        $_POST['ciudad'], $_POST['municipio'], $_POST['estado'],
        $_POST['curp']
    );
    $upd->execute();

    // Determinar tipo de encuesta
    $_SESSION['tipo_encuesta'] = (stripos($_POST['carrera'], 'Química') !== false || stripos($_POST['carrera'], 'Bioquímica') !== false) ? 'QUIMICA' : 'GENERAL';

    header("Location: cuestionario.php");
    exit;
}

// Obtener datos del egresado
// Obtener datos del egresado
$curp = $_SESSION['curp'];
$stmt = $conexion->prepare("SELECT * FROM EGRESADO WHERE CURP = ?");
$stmt->bind_param("s", $curp);
$stmt->execute();
$egresado = $stmt->get_result()->fetch_assoc();

if (!$egresado) {
    header("Location: logout.php");
    exit;
}

// 💥 AQUÍ AGREGA ESTA LÍNEA
$_SESSION['id_egresado'] = $egresado['CURP'];


// Evaluar estado del período y participación
$yaRespondio = $_SESSION['ya_respondio'] ?? false;
$idPeriodoRespondido = $_SESSION['id_periodo_respondido'] ?? null;
$idPeriodoActual = $_SESSION['id_periodo_actual'] ?? null;

$puede_contestar = false;
$puede_modificar = false;

if ($idPeriodoActual) {
  if (!$yaRespondio) {
      $puede_contestar = true;
  } elseif ($yaRespondio && $idPeriodoRespondido != $idPeriodoActual) {
      $puede_modificar = true;
  }
}

include('includes/header.php');
include('includes/menu.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Encuesta para Egresados</title>
  <base href="/seguimientoEgresados/">
  <link rel="stylesheet" href="css/encuesta.css">
</head>
<body>

<?php if (!$puede_contestar && !$puede_modificar): ?>
  <main>
    <section>
      <h2 style="color: #004B82;">Encuesta ya completada</h2>
      <p style="color: #1f1f1f">Gracias por haber respondido la encuesta de seguimiento de egresados. Ya no puedes modificar tus respuestas en este período.</p>
      <a href="index.php" class="btn">Volver al inicio</a>
    </section>
  </main>
<?php else: ?>

  <header>
    <h1>Encuesta de Seguimiento de Egresados</h1>
    <p>Por favor completa tu información antes de continuar</p>
  </header>

  <?php include 'includes/modal_bienvenida.php'; ?>

  <main id="encuesta-content" class="encuesta-main" style="display:none;">
    <?php include 'includes/instrucciones.php'; ?>

    <section class="card datos-card" id="datos-section" style="display:none;">
      <h2>Información del Egresado</h2>
      <form method="POST" action="encuestas.php">
        <div class="form-grid">
          <label>Apellido paterno
            <input type="text" name="apellido_paterno" value="<?= htmlspecialchars($egresado['APELLIDO_PATERNO']) ?>" required>
          </label>
          <label>Apellido materno
            <input type="text" name="apellido_materno" value="<?= htmlspecialchars($egresado['APELLIDO_MATERNO']) ?>" required>
          </label>
          <label>Nombre
            <input type="text" name="nombre" value="<?= htmlspecialchars($egresado['NOMBRE']) ?>" required>
          </label>
          <label>Número de control
            <input type="text" name="num_control" value="<?= htmlspecialchars($egresado['NUM_CONTROL']) ?>" required>
          </label>
          <label>Fecha de nacimiento
            <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($egresado['FECHA_NACIMIENTO']) ?>" required>
          </label>
          <label>CURP
            <input type="text" name="curp" value="<?= htmlspecialchars($egresado['CURP']) ?>" readonly>
          </label>
          <label>Sexo
            <select name="sexo" required>
              <option value="" disabled>-- Seleccione la opción --</option>
              <option value="Hombre" <?= $egresado['SEXO'] === 'Hombre' ? 'selected' : '' ?>>Hombre</option>
              <option value="Mujer" <?= $egresado['SEXO'] === 'Mujer' ? 'selected' : '' ?>>Mujer</option>
            </select>
          </label>
          <label>Estado civil
            <select name="estado_civil" required>
              <option value="" disabled>-- Seleccione la opción --</option>
              <option value="Soltero(a)" <?= $egresado['ESTADO_CIVIL'] === 'Soltero(a)' ? 'selected' : '' ?>>Soltero(a)</option>
              <option value="Casado(a)" <?= $egresado['ESTADO_CIVIL'] === 'Casado(a)' ? 'selected' : '' ?>>Casado(a)</option>
              <option value="Otro" <?= $egresado['ESTADO_CIVIL'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
            </select>
          </label>
          <label>Domicilio (Calle y Numero)
            <input type="text" name="calle" value="<?= htmlspecialchars($egresado['CALLE']) ?>">
          </label>
          <label>Colonia 
            <input type="text" name="colonia" value="<?= htmlspecialchars($egresado['COLONIA']) ?>">
          </label>
          <label>Codigo Postal
            <input type="text" name="codigo_postal" value="<?= htmlspecialchars($egresado['CODIGO_POSTAL']) ?>">
          </label>
          <label>Ciudad
            <input type="text" name="ciudad" value="<?= htmlspecialchars($egresado['CIUDAD']) ?>">
          </label>
          <label>Municipio
            <input type="text" name="municipio" value="<?= htmlspecialchars($egresado['MUNICIPIO']) ?>">
          </label>
          <label>Estado
            <input type="text" name="estado" value="<?= htmlspecialchars($egresado['ESTADO']) ?>">
          </label>
          <label>Teléfono
            <input type="tel" name="telefono" value="<?= htmlspecialchars($egresado['TELEFONO']) ?>" required>
          </label>
          <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars($egresado['EMAIL']) ?>" required>
          </label>
          <label>Carrera
            <select name="carrera" required>
              <option value="" disabled>-- Seleccione la opción --</option>
              <?php 
                $opciones = [
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
                foreach($opciones as $opt): ?>
                  <option value="<?= $opt?>" <?= $egresado['CARRERA'] === $opt ? 'selected' : '' ?>>
                    <?= $opt ?>
                  </option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Fecha de egreso
            <input type="date" name="fecha_egreso" value="<?= htmlspecialchars(($egresado['FECHA_EGRESO'] === '0000-00-00' || empty($egresado['FECHA_EGRESO'])) ? '' : $egresado['FECHA_EGRESO']) ?>">
          </label>
          <label>Titulado
            <select name="titulado" required>
              <option value="0" <?= !$egresado['TITULADO'] ? 'selected' : '' ?>>No</option>
              <option value="1" <?= $egresado['TITULADO'] ? 'selected' : '' ?>>Sí</option>
            </select>
          </label>
        </div>
        <button type="submit" name="accion" value="guardar-datos" class="btn">Guardar y Continuar</button>
      </form>
    </section>
  </main>

<?php endif; ?>

<script src="js/encuesta.js"></script>
</body>
</html>
