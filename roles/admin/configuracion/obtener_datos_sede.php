<?php
require_once __DIR__ . "/../../../config.php";
header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

if (!$id) {
  echo json_encode([]);
  exit;
}

// 🔎 Buscar sede
$sede = $pdo->prepare("
  SELECT 
    coordinador_nombre,
    coordinador_correo,
    coordinador_telefono
  FROM sedes
  WHERE id = ?
");
$sede->execute([$id]);
$sedeData = $sede->fetch(PDO::FETCH_ASSOC);

// 🔎 Verificar si ya existe usuario coordinador
$coordinadorExiste = $pdo->prepare("
  SELECT COUNT(*) 
  FROM usuarios 
  WHERE sede_id = ? AND rol = 'Coordinador'
");
$coordinadorExiste->execute([$id]);

echo json_encode([
  'tiene_coordinador' => $coordinadorExiste->fetchColumn() > 0,
  'coordinador' => [
    'nombre'   => $sedeData['coordinador_nombre'] ?? '',
    'correo'   => $sedeData['coordinador_correo'] ?? '',
    'telefono' => $sedeData['coordinador_telefono'] ?? ''
  ]
]);
