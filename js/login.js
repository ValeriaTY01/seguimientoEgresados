document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll('.tab');
    const forms = document.querySelectorAll('.grupo-form');
    const tipoInput = document.getElementById('tipo_usuario');
    const enlaceRegistro = document.getElementById('registro-enlace');
    const formLogin = document.getElementById('form-login'); // ← Asegúrate de tener este ID en el formulario
        // Asegurar que el modal de recuperación esté oculto al cargar
    const modalRecuperar = document.getElementById("modal-recuperar");
    if (modalRecuperar) {
        modalRecuperar.style.display = "none";
    }


    // 1. Inicializar: deshabilitar inputs de formularios no activos
    forms.forEach(f => {
        if (!f.classList.contains('activo')) {
            f.querySelectorAll('input').forEach(i => i.disabled = true);
        }
    });

    // 2. Manejar el cambio de pestañas
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Desactivar todas pestañas y limpiar formularios
            tabs.forEach(t => t.classList.remove('activo'));
            forms.forEach(f => {
                f.classList.remove('activo');
                f.querySelectorAll('input').forEach(i => {
                    i.disabled = true;
                    if (i.type !== 'hidden') i.value = '';
                });
            });

            // Resetear el formulario de login completamente
            if (formLogin) formLogin.reset();

            // Activar pestaña y formulario correspondiente
            const tipo = tab.dataset.tipo;
            tab.classList.add('activo');
            const formActivo = document.getElementById(`${tipo}-form`);
            formActivo.classList.add('activo');
            formActivo.querySelectorAll('input').forEach(i => i.disabled = false);

            // Actualizar tipo de usuario
            tipoInput.value = tipo;

            // Mostrar enlace de registro solo para alumno
            if (enlaceRegistro) {
                enlaceRegistro.style.display = tipo === 'alumno' ? 'block' : 'none';
            }
            const enlaceRecuperar = document.querySelector('.recuperar-link');
            if (enlaceRecuperar) {
                enlaceRecuperar.style.display = tipo === 'alumno' ? 'block' : 'none';
            }
        });
    });

    // 3. Ocultar mensaje de error después de unos segundos
    const mensajeError = document.getElementById('mensaje-error');
    if (mensajeError) {
        setTimeout(() => {
            mensajeError.style.display = 'none';
            if (history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('error');
                history.replaceState(null, '', url);
            }
        }, 3000);
    }

    // 4. Mostrar u ocultar enlace de registro al cargar
    if (enlaceRegistro && tipoInput.value === 'alumno') {
        enlaceRegistro.style.display = 'block';
    }

    window.abrirModalRecuperar = () => {
        const modal = document.getElementById("modal-recuperar");
        modal.style.display = "flex"; // o "block", según tu CSS
    };

    window.cerrarModalRecuperar = () => {
        const modal = document.getElementById("modal-recuperar");
        modal.style.display = "none";

        // Limpiar campo
        const input = document.getElementById("curp_recuperar");
        if (input) input.value = '';
    };

    // 5. Funciones para modal de registro
    window.abrirModal = () => {
        document.getElementById("modal-registro").style.display = "block";
    };
    window.cerrarModal = () => {
        const modal = document.getElementById("modal-registro");
        modal.style.display = "none";

        // Limpiar formulario del modal
        const form = document.getElementById("form-registro");
        if (form) form.reset();

        // Recargar CAPTCHA
        const captcha = document.getElementById("captcha-img");
        if (captcha) {
            captcha.src = "captcha.php?" + Date.now();
        }
    };
    window.addEventListener("click", event => {
        const modal = document.getElementById("modal-registro");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // 6. Recargar CAPTCHA al hacer clic en la imagen
    const captcha = document.getElementById("captcha-img");
    if (captcha) {
        captcha.addEventListener("click", () => {
            captcha.src = "captcha.php?" + Date.now();
        });
    }

    // 7. Toggle password (si usas íconos de ojo)
    const toggleIcons = document.querySelectorAll('.toggle-password');
    toggleIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            const input = icon.previousElementSibling;
            if (input && input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else if (input) {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
