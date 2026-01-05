<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Corisnorte</title>
    
    <link rel="icon" type="image/x-icon" href="favicon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #0B3C89;       /* Azul principal del logo */
            --secondary-blue: #1E5AA8;     /* Azul secundario del logo */
            --light-blue: #8FBCE6;         /* Azul claro del fondo */
            --accent-red: #D62828;         /* Rojo institucional */
            --accent-yellow: #1E5AA8;      /* Reemplazado por azul corporativo */
            --accent-green: #0B3C89;       /* Reemplazado por azul principal */
            --secondary-color: #1E5AA8;    /* Color secundario de botones */
            --white: #ffffff;
            --light-gray: #f8f9fa;
        }

        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #EAF3FF, #DCEBFF);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
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
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, 
                var(--accent-yellow) 0%, 
                var(--accent-red) 33%, 
                var(--accent-green) 66%, 
                var(--secondary-color) 100%);
        }
        
        .login-logo {
            max-height: 80px;
            width: auto;
            margin-bottom: 1rem;
        }
        
        .login-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--accent-red);
        }
        
        .login-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            color: var(--accent-red);
        }
        
        .login-body {
            padding: 2.5rem 2rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-label i {
            color: var(--accent-yellow);
            font-size: 1.1rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--light-gray);
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25);
            background: white;
        }
        
        .input-group .form-control {
            border-right: none;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        
        .password-toggle {
            background: var(--light-gray);
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 12px 12px 0;
            color: var(--secondary-blue);
            transition: all 0.3s ease;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            background: #e9ecef;
            color: var(--primary-blue);
        }
        
        .input-group:focus-within .password-toggle {
            border-color: var(--secondary-color);
            background: white;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--accent-red) 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 159, 67, 0.4);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 159, 67, 0.6);
            background: var(--accent-red);
            color: white;
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
            cursor: not-allowed;
        }
        
        .login-message {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
            display: none;
        }
        
        .login-message.error {
            background: rgba(230, 57, 70, 0.1);
            color: var(--accent-red);
            border-left: 4px solid var(--accent-red);
            display: block;
        }
        
        .login-message.success {
            background: rgba(42, 157, 143, 0.1);
            color: var(--accent-green);
            border-left: 4px solid var(--accent-green);
            display: block;
        }
        
        .recovery-link {
            color: var(--secondary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .recovery-link:hover {
            color: var(--accent-red);
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            margin: 1rem 0;
        }
        
        .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
            border-width: 0.2em;
            color: var(--secondary-color);
        }
        
        /* Animaciones */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            animation: fadeIn 0.6s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
            }
            
            .login-header {
                padding: 2rem 1.5rem;
            }
            
            .login-body {
                padding: 2rem 1.5rem;
            }
            
            .login-logo {
                max-height: 60px;
            }
        }

        /* Efecto de partículas decorativas */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            background: var(--accent-yellow);
            border-radius: 50%;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
    </style>
</head>
<body>
    <!-- Partículas decorativas -->
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="card login-card border-0">
            <div class="login-header text-center">

            <!-- ✅ LOGO -->
            <img src="assets/img/logo-corisnorte-text.png" alt="Logo Corisnorte" class="login-logo mb-3">
        
            <h4 class="login-title">Iniciar Sesión</h4>
            <p class="login-subtitle">Acceso exclusivo para Corisnorte</p>
        </div>

            <div class="login-body">
                <!-- Mensaje de error/success -->
                <div id="loginMensaje" class="login-message"></div>

                <!-- Campo de correo -->
                <div class="mb-4">
                    <label for="loginCorreo" class="form-label">
                        <i class="bi bi-envelope"></i>Correo electrónico
                    </label>
                    <input type="email" 
                           class="form-control" 
                           id="loginCorreo" 
                           placeholder="tu@correo.com"
                           required>
                </div>

                <!-- Campo de contraseña con ojito -->
                <div class="mb-4">
                    <label for="loginContrasena" class="form-label">
                        <i class="bi bi-lock"></i>Contraseña
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control" 
                               id="loginContrasena" 
                               placeholder="Ingresa tu contraseña"
                               required>
                        <button class="btn password-toggle" type="button" id="toggleLoginPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Loading spinner -->
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>

                <!-- Botón de ingresar -->
                <div class="d-grid">
                    <button class="btn btn-login" id="btnLogin">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Ingresar</span>
                    </button>
                </div>

                <!-- Link de recuperación -->
                <div class="text-center mt-4">
                    <a href="recuperar.php" class="recovery-link">
                        <i class="bi bi-key"></i>¿Olvidaste tu contraseña?
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Crear partículas decorativas
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 12;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Tamaño y posición aleatoria
                const size = Math.random() * 8 + 4;
                const left = Math.random() * 100;
                const top = Math.random() * 100;
                const delay = Math.random() * 5;
                const duration = Math.random() * 3 + 4;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${left}%`;
                particle.style.top = `${top}%`;
                particle.style.animationDelay = `${delay}s`;
                particle.style.animationDuration = `${duration}s`;
                
                // Color aleatorio de la paleta
                const colors = ['#f4a300', '#e63946', '#2a9d8f', '#457b9d'];
                const color = colors[Math.floor(Math.random() * colors.length)];
                particle.style.background = color;
                
                particlesContainer.appendChild(particle);
            }
        }

        // Mostrar / ocultar contraseña
        document.getElementById('toggleLoginPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('loginContrasena');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });

        // Acción de iniciar sesión
        document.getElementById('btnLogin').addEventListener('click', function (e) {
            e.preventDefault();

            const correo = document.getElementById('loginCorreo').value.trim();
            const contrasena = document.getElementById('loginContrasena').value;
            const mensajeDiv = document.getElementById('loginMensaje');
            const btnLogin = document.getElementById('btnLogin');
            const loadingSpinner = document.getElementById('loadingSpinner');

            // Reset mensaje
            mensajeDiv.textContent = "";
            mensajeDiv.className = "login-message";
            
            // Validación básica
            if (!correo || !contrasena) {
                mensajeDiv.textContent = "Por favor, completa todos los campos.";
                mensajeDiv.classList.add('error');
                return;
            }

            // Validación de formato de correo
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(correo)) {
                mensajeDiv.textContent = "Por favor, ingresa un correo electrónico válido.";
                mensajeDiv.classList.add('error');
                return;
            }

            // Mostrar loading
            btnLogin.disabled = true;
            loadingSpinner.style.display = 'block';

            const datos = new FormData();
            datos.append("correo", correo);
            datos.append("contrasena", contrasena);

            fetch("login.php", {
                method: "POST",
                body: datos
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "ok") {
                    mensajeDiv.textContent = "¡Inicio de sesión exitoso! Redirigiendo...";
                    mensajeDiv.classList.add('success');
                    
                    // Redirigir después de un breve delay
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    mensajeDiv.textContent = data.mensaje;
                    mensajeDiv.classList.add('error');
                }
            })
            .catch(error => {
                console.error("Error:", error);
                mensajeDiv.textContent = "Ocurrió un error al iniciar sesión. Intenta nuevamente.";
                mensajeDiv.classList.add('error');
            })
            .finally(() => {
                // Ocultar loading
                btnLogin.disabled = false;
                loadingSpinner.style.display = 'none';
            });
        });

        // Permitir enviar con Enter
        document.getElementById('loginContrasena').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('btnLogin').click();
            }
        });

        // Inicializar partículas cuando la página cargue
        document.addEventListener('DOMContentLoaded', createParticles);
    </script>
</body>
</html>