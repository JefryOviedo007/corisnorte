<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

header('Content-Type: application/json');

if (!isset($_POST['ids'])) {
  echo json_encode(['success' => false, 'message' => 'No se recibieron IDs']);
  exit;
}

$ids = explode(',', $_POST['ids']);

// Seguridad
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = "
  UPDATE grupos
  SET estado = 'Inscripciones Abiertas'
  WHERE id IN ($placeholders)
";

$stmt = $pdo->prepare($sql);

if ($stmt->execute($ids)) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}
