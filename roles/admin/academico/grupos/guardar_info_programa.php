<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| VALIDACIONES BÁSICAS
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode([
    'success' => false,
    'message' => 'Método no permitido'
  ]);
  exit;
}

$programa_id = $_POST['programa_id'] ?? null;
$perfil_profesional = trim($_POST['perfil_profesional'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$funciones = $_POST['funciones'] ?? [];

if (!$programa_id || !$perfil_profesional) {
  echo json_encode([
    'success' => false,
    'message' => 'Datos obligatorios incompletos'
  ]);
  exit;
}

/*
|--------------------------------------------------------------------------
| LIMPIAR Y VALIDAR FUNCIONES
|--------------------------------------------------------------------------
*/
$funciones_limpias = [];

if (is_array($funciones)) {
  foreach ($funciones as $f) {
    $f = trim($f);
    if ($f !== '') {
      $funciones_limpias[] = $f;
    }
  }
}

$funciones_json = $funciones_limpias
  ? json_encode($funciones_limpias, JSON_UNESCAPED_UNICODE)
  : null;

try {

  /*
  |--------------------------------------------------------------------------
  | ¿YA EXISTE INFO DEL PROGRAMA?
  |--------------------------------------------------------------------------
  */
  $stmt = $pdo->prepare("
    SELECT id
    FROM programas_info
    WHERE programa_id = ?
    LIMIT 1
  ");
  $stmt->execute([$programa_id]);
  $existe = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existe) {

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    $stmt = $pdo->prepare("
      UPDATE programas_info
      SET
        perfil_profesional = ?,
        descripcion = ?,
        funciones = ?,
        fecha_actualizacion = CURRENT_TIMESTAMP
      WHERE programa_id = ?
    ");

    $stmt->execute([
      $perfil_profesional,
      $descripcion ?: null,
      $funciones_json,
      $programa_id
    ]);

  } else {

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */
    $stmt = $pdo->prepare("
      INSERT INTO programas_info (
        programa_id,
        perfil_profesional,
        descripcion,
        funciones
      ) VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
      $programa_id,
      $perfil_profesional,
      $descripcion ?: null,
      $funciones_json
    ]);
  }

  echo json_encode([
    'success' => true
  ]);

} catch (Exception $e) {

  echo json_encode([
    'success' => false,
    'message' => 'Error al guardar la información'
    // 👉 Para debug:
    // 'error' => $e->getMessage()
  ]);
}
