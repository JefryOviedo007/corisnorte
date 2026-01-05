<?php
require_once __DIR__ . "/../../../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==========================
    // 📥 DATOS
    // ==========================
    $id        = $_POST['id'] ?? null;
    $imagenActual = $_POST['imagen_actual'] ?? null;

    $nombre    = $_POST['nombre'] ?? null;
    $ciudad    = $_POST['ciudad'] ?? null;
    $telefono  = $_POST['telefono'] ?? null;
    $correo    = $_POST['correo'] ?? null;
    $direccion = $_POST['direccion'] ?? null;

    $coord_nombre   = $_POST['coordinador_nombre'] ?? null;
    $coord_telefono = $_POST['coordinador_telefono'] ?? null;
    $coord_correo   = $_POST['coordinador_correo'] ?? null;

    // ==========================
    // 🖼️ IMAGEN
    // ==========================
    $imagenRuta = $imagenActual;

    if (!empty($_FILES['imagen']['name'])) {

        $directorio = __DIR__ . "/uploads/sedes/";
        $rutaBD     = "roles/admin/configuracion/uploads/sedes/";

        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $imagenNombre = "sede_" . time() . "." . $extension;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio . $imagenNombre)) {

            // ✅ Eliminar imagen anterior
            if ($imagenActual && file_exists(__DIR__ . "/../../../" . $imagenActual)) {
                unlink(__DIR__ . "/../../../" . $imagenActual);
            }

            $imagenRuta = $rutaBD . $imagenNombre;
        }
    }

    // ==========================
    // ✏️ ACTUALIZAR
    // ==========================
    if (!empty($id)) {

        $stmt = $pdo->prepare("
            UPDATE sedes SET
                nombre = ?,
                ciudad = ?,
                telefono = ?,
                correo = ?,
                direccion = ?,
                imagen = ?,
                coordinador_nombre = ?,
                coordinador_telefono = ?,
                coordinador_correo = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $nombre,
            $ciudad,
            $telefono,
            $correo,
            $direccion,
            $imagenRuta,
            $coord_nombre,
            $coord_telefono,
            $coord_correo,
            $id
        ]);

        echo json_encode(['status' => 'updated']);
        exit;
    }

    // ==========================
    // ➕ CREAR
    // ==========================
    $stmt = $pdo->prepare("
        INSERT INTO sedes (
            nombre, ciudad, telefono, correo, direccion, imagen,
            coordinador_nombre, coordinador_telefono, coordinador_correo
        ) VALUES (?,?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $nombre,
        $ciudad,
        $telefono,
        $correo,
        $direccion,
        $imagenRuta,
        $coord_nombre,
        $coord_telefono,
        $coord_correo
    ]);

    echo json_encode(['status' => 'created']);
}
