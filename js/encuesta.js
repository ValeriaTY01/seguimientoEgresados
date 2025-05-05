document.addEventListener("DOMContentLoaded", () => {
    const modal      = document.getElementById("modal-bienvenida");
    const closeBtn   = document.getElementById("cerrar-bienvenida");
    const mainContent= document.getElementById("encuesta-content");
    const btnComenzar= document.getElementById("btn-comenzar");
    const instrSect  = document.getElementById("instrucciones-section");
    const datosSect  = document.getElementById("datos-section");
    const btnSaltar  = document.getElementById("btn-saltar-instrucciones");
  
    // Cerrar modal muestra instrucciones
    closeBtn.addEventListener("click", () => {
      modal.style.display = "none";
      mainContent.style.display = "block";
      instrSect.style.display = "block";
    });
  
    // “Comenzar” también cierra modal y muestra instrucciones
    btnComenzar.addEventListener("click", () => {
      modal.style.display = "none";
      mainContent.style.display = "block";
      instrSect.style.display = "block";
    });
  
    // Salta instrucciones y muestra formulario de datos
    btnSaltar.addEventListener("click", () => {
      instrSect.style.display = "none";
      datosSect.style.display = "block";
    });
  });
  