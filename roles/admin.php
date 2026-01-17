<?php
session_start();
include 'config.php'; 

// ✅ 1. Identificar Datos de Sesión
$usuario_id = $_SESSION['id'] ?? null;
$rol        = $_SESSION['rol'] ?? 'Invitado';
$nombre     = $_SESSION['nombre'] ?? 'Usuario';
$sede_id    = $_SESSION['sede_id'] ?? null;

// ✅ 2. CONSULTA DE IMAGEN DE PERFIL
$img_profile = null;
if ($usuario_id) {
    $stmt = $conn->prepare("SELECT img_profile FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res_u = $stmt->get_result();
    if ($user_data = $res_u->fetch_assoc()) {
        $img_profile = $user_data['img_profile'];
    }
    $stmt->close();
}

// ✅ 2. CONSULTA REAL DE LA SEDE
$nombre_sede = "N/A";
if ($sede_id) {
    $stmt = $conn->prepare("SELECT nombre FROM sedes WHERE id = ?");
    $stmt->bind_param("i", $sede_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($datos_sede = $result->fetch_assoc()) {
        $nombre_sede = $datos_sede['nombre'];
    }
    $stmt->close();
}

// ✅ 3. Configuración de Estilos según Rol
$text_color = "white"; 
switch ($rol) {
    case 'Admin':
        $sidebar_style = "background: linear-gradient(180deg, #1f3c88, #3f6ad8);";
        break;
    case 'Coordinador':
        $sidebar_style = "background: linear-gradient(180deg, #e63946, #b91d2a);";
        break;
    case 'Secretaria':
        $sidebar_style = "background: #ffffff; border-right: 1px solid #ddd;";
        $text_color = "#333"; 
        break;
    default:
        $sidebar_style = "background: #333;";
}

$page = $_GET['page'] ?? 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Corisnorte - <?= $rol ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --azul: #1f3c88; --rojo: #e63946; --fondo: #f4f6f9; --sidebar-width: 260px; }
        body { font-family: 'Montserrat', sans-serif; background-color: var(--fondo); margin-top: 70px; }
        
        .sidebar { 
            position: fixed; margin-top: 0px; left: 0; width: var(--sidebar-width); height: 90vh; 
            <?= $sidebar_style ?> 
            padding-top: 20px; box-shadow: 3px 0 12px rgba(0,0,0,.1); z-index: 100; overflow-y: auto; 
        }

        .sidebar h5, .sidebar .nav-link { color: <?= $text_color ?> !important; }
        .sidebar img { max-width: 120px; margin-bottom: 10px; }
        
        .sidebar .nav-link { 
            padding: 11px 20px; margin: 3px 10px; border-radius: 10px; transition: all .3s ease; display: flex; align-items: center; 
            opacity: 0.85;
        }

        .sidebar .nav-link:hover { background: rgba(0,0,0,0.1); transform: translateX(5px); opacity: 1; }
        
        /* Estilo para el link activo */
        .sidebar .nav-link.active { 
            background: <?= ($rol == 'Secretaria') ? '#f0f0f0' : 'rgba(255,255,255,0.2)' ?> !important; 
            font-weight: 700; opacity: 1;
            border-left: 4px solid <?= ($rol == 'Coordinador') ? '#fff' : 'var(--azul)' ?>;
        }

        .main-content { margin-left: var(--sidebar-width); padding: 5px; min-height: 100vh; }
        @media (max-width: 768px) { .sidebar { width: 75px; } .sidebar span, .sidebar h5, .sidebar .ms-auto { display: none; } .main-content { margin-left: 75px; } }
        
        /* TOPBAR (Ahora ocupa todo el ancho) */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 70px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 10; /* Por encima de todo */
        }

        .topbar .brand-area { display: flex; align-items: center; gap: 15px; width: var(--sidebar-width); }
        .topbar .brand-area img { max-height: 45px; }
        .topbar .brand-area span { font-weight: 700; color: var(--azul); font-size: 1.1rem; }
        
        .topbar .sede-badge { background: #f8f9fa; border: 1px solid #e9ecef; padding: 6px 15px; border-radius: 20px; color: var(--azul); font-weight: 600; font-size: 0.85rem; }
        .topbar .user-info { display: flex; align-items: center; gap: 12px; }
        .topbar .user-info img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #eee; }
        .topbar .user-name { font-weight: 700; font-size: 0.9rem; color: #333; }

        @media (max-width: 768px) {
            .sidebar { width: 75px; }
            .sidebar span { display: none; }
            .main-content { margin-left: 75px; }
            .topbar .brand-area span { display: none; }
            .topbar .brand-area { width: 75px; }
        }
    </style>
</head>
<body>
    
<header class="topbar">
    <div class="brand-area">
        <img src="../assets/img/logo-corisnorte-text.png" alt="Logo">
    </div>

    <div class="sede-badge d-none d-md-block">
        <i class="bi bi-geo-alt-fill me-1"></i> Sede: <?= htmlspecialchars($nombre_sede) ?>
    </div>
    
    <div class="user-info">
    <div class="text-end d-none d-md-block">
        <span class="user-name d-block"><?= htmlspecialchars($nombre) ?></span>
        <small class="text-muted text-uppercase" style="font-size: 0.65rem;"><?= htmlspecialchars($rol) ?></small>
    </div>

    <?php 
    // 1. Ruta para el NAVEGADOR (HTML/SRC)
    // Si admin.php y la carpeta admin/ están en el mismo lugar, esta ruta es correcta
    $url_img = "roles/admin/configuracion/uploads/usuarios/" . $img_profile;

    // 2. Ruta para el SERVIDOR (PHP / file_exists)
    // __DIR__ obtiene la carpeta actual donde está admin.php
    $path_fisico = __DIR__ . "/admin/configuracion/uploads/usuarios/" . $img_profile;

    // Depuración actualizada
    echo "<script>
        console.group('Prueba de Ruta Absoluta');
        console.log('Nombre archivo: " . $img_profile . "');
        console.log('Ruta Servidor: " . $path_fisico . "');
        console.log('¿Encontrado?: " . (file_exists($path_fisico) ? 'SÍ' : 'NO') . "');
        console.groupEnd();
    </script>";

    if (!empty($img_profile) && file_exists($path_fisico)): ?>
        <img src="<?= $url_img ?>?v=<?= time() ?>" alt="Perfil" class="user-avatar">
    <?php else: ?>
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=random&color=fff" alt="User" class="user-avatar">
    <?php endif; ?>
</div>
</header>

<nav class="sidebar">
    <div class="text-center px-3">
        <img src="../assets/img/logo.png" alt="Logo" class="img-fluid">
        <h5>Corisnorte</h5>
        <small style="color: <?= $text_color ?>; opacity: 0.7;"><?= $_SESSION['nombre_usuario'] ?? '' ?></small>
    </div>

    <ul class="nav flex-column mt-4">
        
        <li class="nav-item">
            <a class="nav-link <?= $page == 'inicio' ? 'active' : '' ?>" href="?page=inicio">
                <i class="bi bi-speedometer2 me-2"></i><span>Dashboard</span>
            </a>
        </li>

        <?php if(in_array($rol, ['Admin', 'Coordinador', 'Secretaria'])): ?>
        <li class="nav-item">
            <a class="nav-link <?= in_array($page, ['niveles','carreras','grupos','estudiantes']) ? 'active' : '' ?>"
               data-bs-toggle="collapse" href="#menuAcademico">
                <i class="bi bi-mortarboard me-2"></i><span>Académico</span>
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= in_array($page, ['niveles','carreras','grupos','estudiantes']) ? 'show' : '' ?> ps-3" id="menuAcademico">
                <a class="nav-link <?= $page == 'niveles' ? 'active' : '' ?>" href="?page=niveles">Niveles</a>
                <a class="nav-link <?= $page == 'carreras' ? 'active' : '' ?>" href="?page=carreras">Carreras</a>
                <a class="nav-link <?= $page == 'grupos' ? 'active' : '' ?>" href="?page=grupos">Grupos</a>
                <a class="nav-link <?= $page == 'estudiantes' ? 'active' : '' ?>" href="?page=estudiantes">Estudiantes</a>
            </div>
        </li>
        <?php endif; ?>

        <li class="nav-item">
            <a class="nav-link <?= in_array($page, ['inscripciones','matriculas']) ? 'active' : '' ?>"
               data-bs-toggle="collapse" href="#menuAdmisiones">
                <i class="bi bi-person-check me-2"></i><span>Admisiones</span>
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= in_array($page, ['inscripciones','matriculas']) ? 'show' : '' ?> ps-3" id="menuAdmisiones">
                <a class="nav-link <?= $page == 'inscripciones' ? 'active' : '' ?>" href="?page=inscripciones">Inscripciones</a>
                <a class="nav-link <?= $page == 'matriculas' ? 'active' : '' ?>" href="?page=matriculas">Matrículas</a>
            </div>
        </li>

        <?php if(in_array($rol, ['Admin', 'Coordinador', 'Secretaria'])): ?>
        <li class="nav-item">
            <a class="nav-link <?= in_array($page, ['pagos','ingresos','egresos','caja_diaria']) ? 'active' : '' ?>"
               data-bs-toggle="collapse" href="#menuFinanzas">
                <i class="bi bi-cash-stack me-2"></i><span>Finanzas</span>
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= in_array($page, ['pagos','ingresos','egresos','caja_diaria']) ? 'show' : '' ?> ps-3" id="menuFinanzas">
                <a class="nav-link <?= $page == 'pagos' ? 'active' : '' ?>" href="?page=pagos">Pagos</a>
                <?php if($rol == 'Admin'): ?>
                    <a class="nav-link <?= $page == 'ingresos' ? 'active' : '' ?>" href="?page=ingresos">Ingresos</a>
                    <a class="nav-link <?= $page == 'egresos' ? 'active' : '' ?>" href="?page=egresos">Egresos</a>
                <?php endif; ?>
                <a class="nav-link <?= $page == 'caja_diaria' ? 'active' : '' ?>" href="?page=caja_diaria">Caja Diaria</a>
            </div>
        </li>
        <?php endif; ?>

        <?php if($rol == 'Admin'): ?>
        <li class="nav-item">
            <a class="nav-link <?= $page == 'configuracion' ? 'active' : '' ?>" href="?page=configuracion">
                <i class="bi bi-gear me-2"></i><span>Ajustes</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right me-2 text-danger"></i><span>Cerrar sesión</span>
            </a>
        </li>
    </ul>
</nav>

<div class="main-content">
    <div class="container-fluid">
        <?php
        try {
            // Se mantiene el switch original de rutas
            switch ($page) {
                case 'inicio': include 'admin/inicio.php'; break;
                case 'niveles': include 'admin/academico/niveles/listar.php'; break;
                case 'carreras': include 'admin/academico/carreras/listar.php'; break;
                case 'grupos': include 'admin/academico/grupos/listar.php'; break;
                case 'estudiantes': include 'admin/academico/estudiantes/listar.php'; break;
                case 'inscripciones': include 'admin/admisiones/inscripciones/listar.php'; break;
                case 'matriculas': include 'admin/admisiones/matriculas/listar.php'; break;
                case 'pagos': include 'admin/finanzas/pagos/listar.php'; break;
                case 'ingresos': include 'admin/finanzas/ingresos/listar.php'; break;
                case 'egresos': include 'admin/finanzas/egresos/listar.php'; break;
                case 'caja_diaria': include 'admin/finanzas/caja_diaria.php'; break;
                case 'configuracion': include 'admin/configuracion/configuracion.php'; break;
                case 'usuarios': include 'admin/usuarios/listar.php'; break;
                default: include 'admin/inicio.php';
            }
        } catch (Throwable $e) {
            echo '<div class="alert alert-danger">Error al cargar el módulo</div>';
        }
        ?>
    </div>
</div>

</body>
</html>