<!-- Modal de Bienvenida -->
<div id="modal-bienvenida" class="modal">
  <div class="modal-dialog bienvenida-dialog">
    <button class="modal-close" id="cerrar-bienvenida">&times;</button>
    <!-- Logo en esquina superior derecha -->
    <img src="img/logo_veracruz.png" alt="Logo ITV" class="bienvenida-logo">
    <div class="modal-body bienvenida-body">
      <h2>Instituto Tecnológico de Veracruz </h2>
      <p><em>Fecha: <?= date("d/m/Y") ?></em></p>
      <p>Estimado(a) Egresado(a)&nbsp;<strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>:</p>
      <p class="texto-modal">
        Los servicios educativos de este Instituto Tecnológico deben estar en mejora continua para
        asegurar la pertinencia de los conocimientos adquiridos y mejorar sistemáticamente, para
        colaborar en la formación integral de nuestros alumnos.<br><br>

        Para esto es indispensable tomarte en cuenta como factor de cambios y reformas, por lo que
        por este medio solicitamos tu valiosa participación y cooperación en esta investigación del
        Seguimiento de Egresados, que nos permitirá obtener información valiosa para analizar la
        problemática del mercado laboral y sus características, así como las competencias
        laborales de nuestros egresados.<br><br>

        Las respuestas del cuestionario anexo serán tratadas con absoluta confidencialidad y con
        fines meramente estadísticos.<br><br>

        Con nuestro agradecimiento por tu cooperación, recibe un cordial saludo.
      </p>
      <p>Atentamente,</p>
      <p>_________________________________<br>
         Director del Plantel</p>
      <button class="btn btn-primary" id="btn-comenzar">Comenzar</button>
    </div>
  </div>
</div>

  <!-- Contenido principal (oculto hasta cerrar el modal) -->
  