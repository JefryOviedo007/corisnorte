<?php 
// auth.php
include __DIR__ . '/includes/header.php'; 
?>

<style>
    /* CSS ORIGINAL RESTAURADO */
    :root {
        --primary-blue: #0B3C89;
        --secondary-blue: #1E5AA8;
        --light-blue: #8FBCE6;
        --accent-red: #D62828;
        --accent-yellow: #1E5AA8;
        --accent-green: #0B3C89;
        --secondary-color: #1E5AA8;
        --white: #ffffff;
        --light-gray: #f8f9fa;
    }

    .login-container {
        width: 100%;
        max-width: 420px;
        margin: 50px auto; /* Centrado manual para convivir con el header */
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: none;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        animation: fadeIn 0.6s ease-out;
    }

    .login-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .login-header {
        background: linear-gradient(135deg, var(--light-blue) 0%, #b7d3ff 100%);
        padding: 2.5rem 2rem;
        text-align: center;
        color: white;
        position: relative;
    }

    .login-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, 
            var(--accent-yellow) 0%, 
            var(--accent-red) 33%, 
            var(--accent-green) 66%, 
            var(--secondary-color) 100%);
    }

    .login-logo { max-height: 80px; width: auto; margin-bottom: 1rem; }

    .login-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600; font-size: 1.5rem;
        margin-bottom: 0.5rem; color: var(--accent-red);
    }

    .login-subtitle {
        opacity: 0.9; font-size: 0.9rem;
        margin: 0; color: var(--accent-red);
    }

    .login-body { padding: 2.5rem 2rem; }

    .form-label {
        font-weight: 500; color: var(--primary-blue);
        margin-bottom: 0.5rem; display: flex;
        align-items: center; gap: 0.5rem;
    }

    .form-label i { color: var(--accent-yellow); font-size: 1.1rem; }

    .form-control {
        border: 2px solid #e9ecef; border-radius: 12px;
        padding: 0.75rem 1rem; font-size: 1rem;
        transition: all 0.3s ease; background: var(--light-gray);
    }

    .form-control:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.2rem rgba(30, 90, 168, 0.25);
        background: white;
    }

    .password-toggle {
        background: var(--light-gray); border: 2px solid #e9ecef;
        border-left: none; border-radius: 0 12px 12px 0;
        color: var(--secondary-blue); padding: 0.75rem 1rem;
    }

    /* BOTÓN ACTUALIZADO */
    .btn-login-auth {
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--accent-red) 100%);
        border: none; color: white; font-weight: 600;
        padding: 0.75rem 2rem; border-radius: 50px;
        width: 100%; transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(214, 40, 40, 0.4);
        display: flex; align-items: center;
        justify-content: center; gap: 0.5rem;
    }

    .btn-login-auth:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(214, 40, 40, 0.6);
        background: var(--accent-red); color: white;
    }

    .login-message {
        border-radius: 12px; padding: 1rem 1.25rem;
        margin-bottom: 1rem; text-align: center;
        font-weight: 500; display: none;
    }

    .login-message.error {
        background: rgba(230, 57, 70, 0.1);
        color: var(--accent-red); border-left: 4px solid var(--accent-red);
        display: block;
    }

    .recovery-link {
        color: var(--secondary-blue); text-decoration: none;
        font-weight: 500; transition: color 0.3s ease;
        display: inline-flex; align-items: center; gap: 0.5rem;
    }

    .recovery-link:hover { color: var(--accent-red); }

    .particles {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        pointer-events: none; z-index: -1;
    }

    .particle {
        position: absolute; border-radius: 50%;
        opacity: 0.1; animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="particles" id="particles"></div>

<div class="login-container">
    <div class="card login-card border-0">
        <div class="login-header text-center">
            <img src="assets/img/logo-corisnorte-text.png" alt="Logo Corisnorte" class="login-logo mb-3">
            <h4 class="login-title">Iniciar Sesión</h4>
            <p class="login-subtitle">Acceso exclusivo para Corisnorte</p>
        </div>

        <div class="login-body">
            <div id="loginMensaje" class="login-message"></div>

            <div class="mb-4">
                <label for="loginCorreo" class="form-label">
                    <i class="bi bi-envelope"></i>Correo electrónico
                </label>
                <input type="email" class="form-control" id="loginCorreo" placeholder="tu@correo.com" required>
            </div>

            <div class="mb-4">
                <label for="loginContrasena" class="form-label">
                    <i class="bi bi-lock"></i>Contraseña
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="loginContrasena" placeholder="Ingresa tu contraseña" required>
                    <button class="btn password-toggle" type="button" id="toggleLoginPassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="loading-spinner text-center mb-3" id="loadingSpinner" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            <div class="d-grid">
                <button class="btn btn-login-auth" id="btnIngresarAuth">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Ingresar</span>
                </button>
            </div>

            <div class="text-center mt-4">
                <a href="recuperar.php" class="recovery-link">
                    <i class="bi bi-key"></i>¿Olvidaste tu contraseña?
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Partículas
    function createParticles() {
        const container = document.getElementById('particles');
        const colors = ['#f4a300', '#e63946', '#2a9d8f', '#457b9d'];
        for (let i = 0; i < 12; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            const size = Math.random() * 8 + 4;
            Object.assign(p.style, {
                width: `${size}px`, height: `${size}px`,
                left: `${Math.random() * 100}%`, top: `${Math.random() * 100}%`,
                background: colors[Math.floor(Math.random() * colors.length)],
                animationDelay: `${Math.random() * 5}s`,
                animationDuration: `${Math.random() * 3 + 4}s`
            });
            container.appendChild(p);
        }
    }

    // Toggle Password
    document.getElementById('toggleLoginPassword').addEventListener('click', function () {
        const pass = document.getElementById('loginContrasena');
        const icon = this.querySelector('i');
        pass.type = pass.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });

    // Login logic
    document.getElementById('btnIngresarAuth').addEventListener('click', function (e) {
        e.preventDefault();
        const correo = document.getElementById('loginCorreo').value.trim();
        const pass = document.getElementById('loginContrasena').value;
        const msg = document.getElementById('loginMensaje');
        const btn = this;

        if (!correo || !pass) {
            msg.textContent = "Por favor, completa todos los campos.";
            msg.className = "login-message error";
            return;
        }

        btn.disabled = true;
        document.getElementById('loadingSpinner').style.display = 'block';

        const datos = new FormData();
        datos.append("correo", correo);
        datos.append("contrasena", pass);

        fetch("login.php", { method: "POST", body: datos })
        .then(res => res.json())
        .then(data => {
            if (data.status === "ok") {
                msg.textContent = "Éxito. Redirigiendo...";
                msg.className = "login-message success text-success p-2 mb-2 bg-light border-start border-success border-4";
                setTimeout(() => window.location.href = data.redirect, 1000);
            } else {
                msg.textContent = data.mensaje;
                msg.className = "login-message error";
                btn.disabled = false;
                document.getElementById('loadingSpinner').style.display = 'none';
            }
        })
        .catch(() => {
            msg.textContent = "Error de conexión.";
            msg.className = "login-message error";
            btn.disabled = false;
            document.getElementById('loadingSpinner').style.display = 'none';
        });
    });

    document.addEventListener('DOMContentLoaded', createParticles);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>