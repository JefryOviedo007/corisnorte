<?php
$host = "localhost";
$user = "feceacbc_corisnorte";
$password = "%.@4(8fW?.}h";
$dbname = "feceacbc_corisnorte";

// Conexión MySQLi
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Mensaje de resultado
$mensaje = "";

// Procesar el formulario si se envió
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitizar datos
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];

    // Manejo de imagen de perfil
    $img_profile = "default.png";
    if (!empty($_FILES['img_profile']['name'])) {
        $nombre_img = time() . "_" . basename($_FILES['img_profile']['name']);
        $ruta_destino = "uploads/" . $nombre_img;

        // Crear carpeta si no existe
        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }

        if (move_uploaded_file($_FILES['img_profile']['tmp_name'], $ruta_destino)) {
            $img_profile = $nombre_img;
        }
    }

    // Verificar si el correo ya existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $mensaje = "<div class='alert alert-danger'>Este correo ya está registrado.</div>";
    } else {

        // Encriptar contraseña
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);

        // Insertar usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol, img_profile)
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $correo, $hash, $rol, $img_profile);

        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success'>Usuario registrado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al registrar: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }

    $check->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Registro de Usuario</h4>
        </div>
        <div class="card-body">
            <?php echo $mensaje; ?>
            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="correo" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="contrasena" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select" required>
                        <option value="">Seleccione un rol</option>
                        <option value="Admin">Administrador</option>
                        <option value="Secretaria">Secretaria</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Imagen de perfil (opcional)</label>
                    <input type="file" name="img_profile" class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn btn-success">Registrar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
