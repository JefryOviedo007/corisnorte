<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

$id  = $_POST['id'] ?? null;
$rol = $_SESSION['rol'] ?? '';

if (!$id) {
  echo "ID inválido";
  exit;
}

/*
|------------------------------------------------------------
| OBTENER GRUPO ACTUAL
|------------------------------------------------------------
*/
$stmt = $pdo->prepare("
  SELECT cupos, cupos_disponibles
  FROM grupos
  WHERE id = ?
");
$stmt->execute([$id]);
$grupo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$grupo) {
  echo "Grupo no encontrado";
  exit;
}

$cupos_actuales      = (int)$grupo['cupos'];
$disponibles_actual  = (int)$grupo['cupos_disponibles'];
$inscritos           = $cupos_actuales - $disponibles_actual;

$nuevos_cupos = (int)$_POST['cupos'];

if ($nuevos_cupos < $inscritos) {
  echo "No puedes asignar menos cupos que los inscritos ($inscritos)";
  exit;
}

$nuevos_disponibles = $nuevos_cupos - $inscritos;

/*
|------------------------------------------------------------
| ARMAR SQL DINÁMICO
|------------------------------------------------------------
*/
$campos = [
  'nombre = ?',
  'jornada = ?',
  'cupos = ?',
  'cupos_disponibles = ?',
  'estado = ?',
  'fecha_inicio = ?',
  'fecha_fin = ?'
];

$params = [
  $_POST['nombre'],
  $_POST['jornada'],
  $nuevos_cupos,
  $nuevos_disponibles,
  $_POST['estado'],
  $_POST['fecha_inicio'] ?: null,
  $_POST['fecha_fin'] ?: null
];

// ✅ Solo Admin puede cambiar sede
if ($rol === 'Admin' && isset($_POST['sede_id'])) {
  $campos[] = 'sede_id = ?';
  $params[] = $_POST['sede_id'];
}

$params[] = $id;

$sql = "
  UPDATE grupos SET
    " . implode(", ", $campos) . "
  WHERE id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo "OK";
