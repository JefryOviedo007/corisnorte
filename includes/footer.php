<?php
$anio = date('Y');
?>

<div class="top-bar"></div>
<footer class="bg-white border-top">
    <div class="container py-4">

        <div class="row align-items-center gy-3">

            <!-- COPYRIGHT -->
            <div class="col-md-6 text-center text-md-start">
            
                <!-- Línea 1: Copyright -->
                <div class="text-muted small">
                    © <?= $anio ?> 
                    <strong class="text-primary">Corisnorte</strong>. 
                    Todos los derechos reservados.
                </div>
            
                <!-- Línea 2: Desarrollado por -->
                <div class="small mt-1">
                    Desarrollado por 
                    <a href="https://orbiitech.com"
                       target="_blank"
                       class="fw-semibold text-decoration-none text-primary developer-link">
                        Orbitech Dynamics
                    </a>
                </div>
            
            </div>


            <!-- REDES SOCIALES -->
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="social-icon facebook" title="Facebook" target="_blank">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="#" class="social-icon instagram" title="Instagram" target="_blank">
                    <i class="bi bi-instagram"></i>
                </a>
                <a href="#" class="social-icon whatsapp" title="WhatsApp" target="_blank">
                    <i class="bi bi-whatsapp"></i>
                </a>
                <a href="#" class="social-icon youtube" title="YouTube" target="_blank">
                    <i class="bi bi-youtube"></i>
                </a>
            </div>

        </div>
    </div>
</footer>

<!-- ESTILOS DEL FOOTER -->
<style>
    footer {
        font-family: 'Poppins', sans-serif;
    }

    .developer-link {
        transition: color 0.3s ease;
    }

    .developer-link:hover {
        color: #D62828; /* rojo institucional */
    }

    .social-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        margin-left: 8px;
        font-size: 1.2rem;
        color: #fff;
        text-decoration: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .social-icon:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        color: #fff;
    }

    /* COLORES OFICIALES */
    .social-icon.facebook { background: #1877F2; }
    .social-icon.instagram {
        background: linear-gradient(45deg,#F58529,#DD2A7B,#8134AF,#515BD4);
    }
    .social-icon.whatsapp { background: #25D366; }
    .social-icon.youtube { background: #FF0000; }

    @media (max-width: 576px) {
        .social-icon {
            margin: 0 5px;
        }
    }
</style>
