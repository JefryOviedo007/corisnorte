<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

$data = json_decode(file_get_contents("php://input"), true);
$ids  = $data['ids'] ?? [];

if (!is_array($ids) || count($ids) === 0) {
  echo "No hay registros seleccionados";
  exit;
}

/*
|------------------------------------------------------------
| VALIDAR QUE NO TENGAN INSCRITOS
|------------------------------------------------------------
*/
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("
  SELECT nombre
  FROM grupos
  WHERE id IN ($placeholders)
  AND cupos_disponibles < cupos
");

$stmt->execute($ids);
$bloqueados = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($bloqueados) {
  echo "No se pueden eliminar grupos con inscritos:\n- " . implode("\n- ", $bloqueados);
  exit;
}

/*
|------------------------------------------------------------
| ELIMINAR
|------------------------------------------------------------
*/
$stmt = $pdo->prepare("
  DELETE FROM grupos
  WHERE id IN ($placeholders)
");

$stmt->execute($ids);

echo "OK";
