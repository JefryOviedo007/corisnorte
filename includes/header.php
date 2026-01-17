<?php
// header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corisnorte</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/x-icon" href="favicon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #0D09A4;
            --secondary-blue: #1E5AA8;
            --light-blue: #8FBCE6;
            --accent-red: #FC0000;
            --white: #ffffff;
            --light-gray: #f8f9fa;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #EAF3FF, #DCEBFF);
            margin: 0;
        }

        /* BARRA SUPERIOR DECORATIVA */
        .top-bar {
            height: 3px;
            background: linear-gradient(
                90deg,
                var(--secondary-blue),
                var(--accent-red),
                var(--primary-blue)
            );
        }

        /* NAVBAR */
        .navbar {
            position: relative;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            z-index: 9;
        }

        .navbar-brand img {
            max-height: 55px;
            width: auto;
        }

        .nav-link {
            font-weight: 500;
            color: var(--primary-blue);
            margin-left: 0.75rem;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--accent-red);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-red);
            transition: width 0.3s ease;
        }

        /* Evitar que la línea aparezca en el botón de login */
        .nav-link.btn-login::after {
            display: none;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* ESTILO BOTÓN LOGIN */
        .btn-login {
            border: 2px solid var(--primary-blue) !important;
            border-radius: 50px;
            padding: 8px 25px !important;
            margin-left: 1.5rem !important;
            transition: all 0.3s ease !important;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-login:hover {
            background-color: var(--primary-blue) !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 9, 164, 0.2);
        }

        /* BOTÓN TOGGLER */
        .navbar-toggler {
            border: none;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="/../assets/img/logo-corisnorte-text.png" alt="Logo Corisnorte">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <i class="bi bi-list fs-1 text-primary"></i>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="menuPrincipal">
            <ul class="navbar-nav mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house-door"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="nosotros.php">
                        <i class="bi bi-compass"></i> ¡Nosotros damos el Norte a tu Vida!
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contacto.php">
                        <i class="bi bi-envelope"></i> Contacto
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">
                        <i class="bi bi-journal-text"></i> Blog
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link btn-login" href="auth.php">
                        <i class="bi bi-person-circle"></i> Iniciar Sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>
</nav>

<div class="top-bar"></div>