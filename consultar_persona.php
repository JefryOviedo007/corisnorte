<?php
require_once 'config.php';
header('Content-Type: application/json');

$tipo = $_POST['tipo_documento'] ?? null;
$numero = $_POST['numero_documento'] ?? null;
$grupo_id = $_POST['grupo_id'] ?? null;

if (!$tipo || !$numero || !$grupo_id) {
  echo json_encode(['error' => true]);
  exit;
}

/* CONSULTA PERSONA */
$stmt = $pdo->prepare("
  SELECT *
  FROM personas
  WHERE tipo_documento = ?
  AND numero_documento = ?
  LIMIT 1
");
$stmt->execute([$tipo, $numero]);
$persona = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$persona) {
  echo json_encode(['existe' => false]);
  exit;
}

/* CONSULTA PROGRAMA + GRUPO */
$stmt = $pdo->prepare("
  SELECT
    g.id AS grupo_id,
    g.nombre AS grupo,
    g.jornada,
    s.nombre AS sede,
    s.ciudad,
    n.nombre AS nivel,
    p.nombre AS programa,
    p.duracion,
    p.tipo_duracion
  FROM grupos g
  INNER JOIN sedes s ON s.id = g.sede_id
  INNER JOIN niveles_formacion n ON n.id = g.nivel_id
  INNER JOIN programas p ON p.id = g.programa_id
  WHERE g.id = ?
");
$stmt->execute([$grupo_id]);
$programa = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
  'existe' => true,
  'persona' => [
    'id' => $persona['id'],
    'nombres_completos' => $persona['nombres_completos'],
    'tipo_documento' => $persona['tipo_documento'],
    'numero_documento' => $persona['numero_documento']
  ],
  'programa' => $programa
]);
