<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../../../../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

// Datos básicos
$tipo_documento = $_POST['tipo_documento']   ?? '';
$numero_doc     = trim($_POST['numero_documento'] ?? '');
$nombres        = strtoupper(trim($_POST['nombres_completos'] ?? ''));
$telefono       = trim($_POST['telefono']    ?? '');
$correo         = trim($_POST['correo']      ?? '');
$direccion      = trim($_POST['direccion']   ?? '');
$eps            = trim($_POST['eps']         ?? '');
$sisben         = trim($_POST['sisben']      ?? '');
$estado_civil   = trim($_POST['estado_civil'] ?? '');
$estado         = $_POST['estado']           ?? 'Prospecto';
$c_nombre       = trim($_POST['contacto_nombre']      ?? '');
$c_parentesco   = trim($_POST['contacto_parentesco']  ?? '');
$c_telefono     = trim($_POST['contacto_telefono']    ?? '');
$c_direccion    = trim($_POST['contacto_direccion']   ?? '');
$sede_id        = $_SESSION['sede_id']       ?? null;

// Validaciones obligatorias
if (empty($tipo_documento) || empty($numero_doc) || empty($nombres)) {
    echo json_encode(['status' => 'error', 'message' => 'Tipo doc, número y nombre son obligatorios.']);
    exit;
}

// Verificar documento duplicado
$check = $pdo->prepare("SELECT id FROM personas WHERE numero_documento = ? AND tipo_documento = ?");
$check->execute([$numero_doc, $tipo_documento]);
if ($check->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'Ya existe un estudiante registrado con ese documento.']);
    exit;
}

// Manejo de foto
$foto = null;
if (!empty($_FILES['foto']['name'])) {
    $ext        = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $permitidos)) {
        echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no permitido. Use JPG, PNG o WEBP.']);
        exit;
    }

    $dir_upload = __DIR__ . '/uploads/';
    if (!is_dir($dir_upload)) mkdir($dir_upload, 0755, true);

    $foto = uniqid('est_') . '.' . $ext;
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $dir_upload . $foto)) {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir la foto.']);
        exit;
    }
}

try {
    $sql = "INSERT INTO personas (
                foto,
                tipo_documento,
                numero_documento,
                nombres_completos,
                telefono,
                correo,
                direccion,
                eps,
                sisben,
                estado_civil,
                estado,
                sede_id,
                contacto_emergencia_nombre,
                contacto_emergencia_parentesco,
                contacto_emergencia_telefono,
                contacto_emergencia_direccion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $foto,
        $tipo_documento,
        $numero_doc,
        $nombres,
        $telefono     ?: null,
        $correo       ?: null,
        $direccion    ?: null,
        $eps          ?: null,
        $sisben       ?: null,
        $estado_civil ?: null,
        $estado,
        $sede_id,             // ← desde $_SESSION['sede_id']
        $c_nombre     ?: null,
        $c_parentesco ?: null,
        $c_telefono   ?: null,
        $c_direccion  ?: null,
    ]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Estudiante registrado correctamente.'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al guardar: ' . $e->getMessage()
    ]);
}