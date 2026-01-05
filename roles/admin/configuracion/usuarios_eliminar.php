<?php
session_start();
require_once __DIR__ . "/../../../config.php";

// ===============================
// ✅ Validar POST
// ===============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit;
}

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
  http_response_code(400);
  exit;
}

// ===============================
// ✅ Obtener datos del usuario
// ===============================
$stmt = $pdo->prepare("
  SELECT rol, img_profile
  FROM usuarios
  WHERE id = ?
");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
  http_response_code(404);
  exit;
}

// ❌ No eliminar admins
if ($usuario['rol'] === 'Admin') {
  http_response_code(403);
  exit;
}

// ===============================
// ✅ Eliminar imagen si no es default
// ===============================
if (!empty($usuario['img_profile']) && $usuario['img_profile'] !== 'default.png') {

  $rutaImg = __DIR__ . "/uploads/usuarios/" . $usuario['img_profile'];

  if (file_exists($rutaImg)) {
    unlink($rutaImg);
  }
}

// ===============================
// ✅ Eliminar usuario
// ===============================
$stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->execute([$id]);

echo 'OK';
