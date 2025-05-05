document.addEventListener("DOMContentLoaded", () => {
    // Función para recargar el captcha
    const captcha = document.getElementById("captcha-img");
    if (captcha) {
        captcha.addEventListener("click", () => {
            captcha.src = "captcha.php?" + Date.now();
        });
    }

    // Función para mostrar/ocultar contraseña
    const togglePassword = document.getElementById('togglePassword');
    const inputPassword = document.getElementById('contrasena');

    if (togglePassword && inputPassword) {
        togglePassword.addEventListener('click', () => {
            const isPassword = inputPassword.type === 'password';
            inputPassword.type = isPassword ? 'text' : 'password';
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });
    }

    // Función para manejar la respuesta del registro
    const formRegistro = document.getElementById("form-registro");
    if (formRegistro) {
        formRegistro.addEventListener("submit", (e) => {
            e.preventDefault();

            const formData = new FormData(formRegistro);

            fetch("procesar_registro.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const modal = document.createElement("div");
                modal.classList.add("modal-verificacion");
                const modalContent = document.createElement("div");
                modalContent.classList.add("modal-content-verificacion");

                if (data.success) {
                    modalContent.innerHTML = `
                        <h2>✅ ¡${data.message}!</h2>
                        <p>Por favor, revisa tu correo para verificar tu cuenta.</p>
                        <button onclick="window.location.href='login.php'">Aceptar</button>
                    `;
                } else {
                    modalContent.innerHTML = `
                        <h2>❌ Error</h2>
                        <p class="error">${data.message}</p>
                        <button id="cerrar-modal-error">Aceptar</button>
                    `;

                    // Recargar el CAPTCHA automáticamente cuando haya un error
                    const captcha = document.getElementById("captcha-img");
                    if (captcha) {
                        captcha.src = "captcha.php?" + Date.now();
                    }
                }

                modal.appendChild(modalContent);
                document.body.appendChild(modal);

                modal.style.display = "flex";  // Mostrar el modal

                // Cerrar el modal cuando se haga clic en cualquier parte del fondo
                modal.addEventListener("click", (e) => {
                    if (e.target === modal) {
                        modal.style.display = "none";
                    }
                });

                // Si el registro falla, cerrar el modal al hacer clic en "Aceptar"
                if (!data.success) {
                    const cerrarBtn = modalContent.querySelector("#cerrar-modal-error");
                    cerrarBtn.addEventListener("click", () => {
                        modal.remove(); // Elimina el modal del DOM sin redirigir
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }

    // Mostrar modal de verificación en la página de verificación
    const params = new URLSearchParams(window.location.search);
    if (params.has('codigo')) {
        const codigo = params.get('codigo');
        
        // Mostrar el modal de verificación al cargar la página
        const modal = document.createElement("div");
        modal.classList.add("modal");
        const modalContent = document.createElement("div");
        modalContent.classList.add("modal-content");

        fetch(`verificar_cuenta.php?codigo=${codigo}`)
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
            modal.style.display = "flex";  // Mostrar el modal
        })
        .catch(error => {
            console.error('Error al verificar la cuenta:', error);
        });

        // Cerrar el modal cuando se haga clic en cualquier parte del fondo
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });
    }
});
