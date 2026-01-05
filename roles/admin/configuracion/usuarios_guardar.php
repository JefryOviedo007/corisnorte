<?php
session_start();
require_once __DIR__ . "/../../../config.php";

header('Content-Type: application/json');

// ===============================
// ✅ Validación básica
// ===============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido']);
  exit;
}

// ===============================
// ✅ Recibir datos
// ===============================
$id            = $_POST['id'] ?? null;
$sede_id       = $_POST['sede_id'] ?? null; // SOLO se usa al CREAR
$nombre        = trim($_POST['nombre'] ?? '');
$correo        = trim($_POST['correo'] ?? '');
$telefono      = trim($_POST['telefono'] ?? null);
$contrasena    = $_POST['contrasena'] ?? '';
$imagenActual  = $_POST['imagen_actual'] ?? 'default.png';

// Rol SOLO se usa al CREAR
$rol = $_POST['rol'] ?? 'Secretaria';

// ===============================
// ✅ Validaciones obligatorias
// ===============================
if ($nombre === '' || $correo === '') {
  echo json_encode(['error' => 'Nombre y correo son obligatorios']);
  exit;
}

// ===============================
// ✅ Validar correo duplicado
// ===============================
$sqlCorreo = "
  SELECT id FROM usuarios 
  WHERE correo = ? " . ($id ? "AND id != ?" : "");

$stmt = $pdo->prepare($sqlCorreo);
$stmt->execute($id ? [$correo, $id] : [$correo]);

if ($stmt->fetch()) {
  echo json_encode(['error' => 'El correo ya está registrado']);
  exit;
}

// ===============================
// ✅ Upload de imagen (opcional)
// ===============================
$nombreImagen = $imagenActual;

if (!empty($_FILES['imagen']['name'])) {

  $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
  $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

  if (!in_array($ext, $permitidas)) {
    echo json_encode(['error' => 'Formato de imagen no permitido']);
    exit;
  }

  $nombreImagen = uniqid('user_') . '.' . $ext;

  // 📁 Carpeta uploads/usuarios
  $directorioUploads = __DIR__ . "/uploads/usuarios/";

  if (!is_dir($directorioUploads)) {
    if (!mkdir($directorioUploads, 0755, true)) {
      echo json_encode(['error' => 'No se pudo crear carpeta de imágenes']);
      exit;
    }
  }

  $rutaDestino = $directorioUploads . $nombreImagen;

  if (!is_uploaded_file($_FILES['imagen']['tmp_name'])) {
    echo json_encode(['error' => 'Archivo no válido']);
    exit;
  }

  if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
    error_log('Error moviendo imagen a: ' . $rutaDestino);
    echo json_encode(['error' => 'Error al guardar la imagen']);
    exit;
  }
}

// ===============================
// ✅ CREAR USUARIO
// ===============================
if (empty($id)) {

  if ($contrasena === '') {
    echo json_encode(['error' => 'La contraseña es obligatoria']);
    exit;
  }

  if (!$sede_id || !$rol) {
    echo json_encode(['error' => 'Sede y rol son obligatorios']);
    exit;
  }

  $hash = password_hash($contrasena, PASSWORD_BCRYPT);

  $sql = "
    INSERT INTO usuarios
      (sede_id, nombre, correo, telefono, contrasena, rol, img_profile)
    VALUES
      (?, ?, ?, ?, ?, ?, ?)
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $sede_id,
    $nombre,
    $correo,
    $telefono,
    $hash,
    $rol,
    $nombreImagen
  ]);

  echo json_encode(['success' => true]);
  exit;
}

// ===============================
// ✅ EDITAR USUARIO (SIN CAMBIAR SEDE NI ROL)
// ===============================
$campos = [];
$params = [];

$campos[] = "nombre = ?";
$params[] = $nombre;

$campos[] = "correo = ?";
$params[] = $correo;

$campos[] = "telefono = ?";
$params[] = $telefono;

$campos[] = "img_profile = ?";
$params[] = $nombreImagen;

// Cambiar contraseña solo si se envía
if (!empty($contrasena)) {
  $campos[] = "contrasena = ?";
  $params[] = password_hash($contrasena, PASSWORD_BCRYPT);
}

$params[] = $id;

$sql = "
  UPDATE usuarios
  SET " . implode(', ', $campos) . "
  WHERE id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['success' => true]);
