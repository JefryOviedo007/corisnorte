<?php
require_once __DIR__ . "/../../../../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método no permitido');
}

$ids = $_POST['ids'] ?? '';

if ($ids === '') {
  exit('No se recibieron IDs');
}

$idsArray = array_filter(array_map('intval', explode(',', $ids)));

if (count($idsArray) === 0) {
  exit('IDs inválidos');
}

// placeholders dinámicos (?, ?, ?)
$placeholders = implode(',', array_fill(0, count($idsArray), '?'));

$sql = "DELETE FROM programas WHERE id IN ($placeholders)";
$stmt = $pdo->prepare($sql);
$stmt->execute($idsArray);

echo "OK";
