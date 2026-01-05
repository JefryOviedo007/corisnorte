<?php
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$id = $_POST['id'] ?? null;
if (!$id) exit;

$sql = "
  UPDATE programas SET
    nivel_id = ?,
    nombre = ?,
    descripcion = ?,
    costo_inscripcion = ?,
    costo_matricula = ?,
    mensualidad = ?,
    tipo_duracion = ?,
    duracion = ?,
    estado = ?
  WHERE id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  $_POST['nivel_id'],
  $_POST['nombre'],
  $_POST['descripcion'] ?: null,
  $_POST['costo_inscripcion'],
  $_POST['costo_matricula'],
  $_POST['mensualidad'] ?: null,
  $_POST['tipo_duracion'],
  $_POST['duracion'],
  $_POST['estado'],
  $id
]);

echo json_encode(['success'=>true]);
