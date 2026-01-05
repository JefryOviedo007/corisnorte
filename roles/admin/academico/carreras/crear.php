<?php
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

// ===============================
// ✅ Validar método
// ===============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido']);
  exit;
}

// ===============================
// ✅ Recibir datos
// ===============================
$nivel_id           = $_POST['nivel_id'] ?? null;
$nombre             = trim($_POST['nombre'] ?? '');
$descripcion        = trim($_POST['descripcion'] ?? null);
$costo_inscripcion  = $_POST['costo_inscripcion'] ?? 0;
$costo_matricula    = $_POST['costo_matricula'] ?? 0;
$mensualidad        = $_POST['mensualidad'] ?? null;
$tipo_duracion      = $_POST['tipo_duracion'] ?? '';
$duracion           = $_POST['duracion'] ?? null;
$estado             = $_POST['estado'] ?? 'Activo';

// ===============================
// ✅ Validaciones
// ===============================
if (
  !$nivel_id ||
  $nombre === '' ||
  !$tipo_duracion ||
  !$duracion
) {
  echo json_encode(['error' => 'Datos obligatorios incompletos']);
  exit;
}

// Si mensualidad viene vacía, guardarla como NULL
if ($mensualidad === '' || $mensualidad === null) {
  $mensualidad = null;
}

// ===============================
// ✅ Insertar programa
// ===============================
$sql = "
  INSERT INTO programas
  (nivel_id, nombre, descripcion, costo_inscripcion, costo_matricula, mensualidad, tipo_duracion, duracion, estado)
  VALUES
  (?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  $nivel_id,
  $nombre,
  $descripcion,
  $costo_inscripcion,
  $costo_matricula,
  $mensualidad,
  $tipo_duracion,
  $duracion,
  $estado
]);

echo json_encode(['success' => true]);
