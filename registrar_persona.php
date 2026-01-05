<?php
require_once 'config.php';
header('Content-Type: application/json');

/* INSERT PERSONA */
$stmt = $pdo->prepare("
  INSERT INTO personas
  (tipo_documento, numero_documento, nombres_completos, telefono, correo)
  VALUES (?,?,?,?,?)
");

$stmt->execute([
  $_POST['tipo_documento'],
  $_POST['numero_documento'],
  $_POST['nombres_completos'],
  $_POST['telefono'] ?? null,
  $_POST['correo'] ?? null
]);

$persona_id = $pdo->lastInsertId();

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
$stmt->execute([$_POST['grupo_id']]);
$programa = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
  'persona' => [
    'id' => $persona_id,
    'nombres_completos' => $_POST['nombres_completos'],
    'tipo_documento' => $_POST['tipo_documento'],
    'numero_documento' => $_POST['numero_documento']
  ],
  'programa' => $programa
]);
