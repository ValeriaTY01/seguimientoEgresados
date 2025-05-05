<?php
// login.php con modal de registro
session_start();
if (isset($_SESSION['rol'])) {
    header("Location: index.php");
    exit;
}
$codigo = bin2hex(random_bytes(16));
$_SESSION['codigo_verificacion'] = $codigo;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Integral de Información</title>
    <base href="/seguimientoEgresados/">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header>
        <h1>Sistema Integral de Información</h1>
    </header>

    <main>
        <div class="contenido">
            <div class="fondo">
                <img src="img/fondo.jpg" alt="Fondo ITV" class="venus">
                <img src="img/logo_veracruz.png" alt="Logo ITV" class="logo-itv">
            </div>

            <div class="login-box">
                <div class="tabs">
                    <div class="tab activo" data-tipo="alumno">Alumnos</div>
                    <div class="tab" data-tipo="personal">Personal</div>
                </div>

                <div class="formulario">
                    <form id="form-login" action="auth.php" method="POST">
                        <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="alumno">

                        <div class="grupo-form activo" id="alumno-form">
                            <h2>Acceso a Alumnos</h2>
                            <input type="text" name="curp" placeholder="CURP" required>
                            <input type="password" name="contrasena" placeholder="Contraseña" required>
                        </div>

                        <div class="grupo-form" id="personal-form">
                            <h2>Acceso al Personal</h2>
                            <input type="text" name="rfc" placeholder="Usuario" required>
                            <input type="password" name="contrasena" placeholder="Contraseña" required>
                        </div>

                        <button type="submit">Ingresar</button>

                        <div class="registro-link" id="registro-enlace" style="display: none;">
                            <a href="#" onclick="abrirModal(); return false;">¿Eres egresado y no puedes ingresar? <strong>Regístrate aquí</strong></a>
                        </div>
                        <?php if (isset($_GET['error'])): ?>
                            <p id="mensaje-error" class="error-msg">
                                <?php
                                if ($_GET['error'] == 1) {
                                    echo "Credenciales incorrectas.";
                                } elseif ($_GET['error'] == 2) {
                                    echo "Tu cuenta aún no ha sido verificada. Revisa tu correo.";
                                }
                                ?>
                            </p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal de registro -->
        <div id="modal-registro" class="modal">
            <div class="modal-content">
                <span class="cerrar-modal" onclick="cerrarModal()">&times;</span>
                <?php include 'includes/form_registro.php'; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="logos">
            <img src="img/sep.jpeg" alt="Escudo" class="escudo">
        </div>
    </footer>

    <script src="js/login.js"></script>
</body>
</html>
