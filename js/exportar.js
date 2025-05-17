document.addEventListener('DOMContentLoaded', function () {
  const boton = document.getElementById('btnExportarExcel');
  const mensaje = document.getElementById('mensajeExportacion');

  boton.addEventListener('click', async function () {
      const form = document.getElementById('form-exportar');
      const formData = new FormData(form);

      mensaje.style.display = 'block';

      try {
          const response = await fetch('includes/generar_excel.php', {
              method: 'POST',
              body: formData
          });

          if (!response.ok) throw new Error('Error al generar el Excel');

          const blob = await response.blob();
          const url = window.URL.createObjectURL(blob);

          const a = document.createElement('a');
          a.href = url;
          a.download = 'reporte_encuestas.xlsx';
          document.body.appendChild(a);
          a.click();
          a.remove();
          window.URL.revokeObjectURL(url);
      } catch (error) {
          alert('❌ Hubo un error: ' + error.message);
      } finally {
          setTimeout(() => mensaje.style.display = 'none', 5000);
      }
  });
});
