<?php
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

$grupo_id = $_POST['grupo_id'] ?? null;
$estudiantes_ids = json_decode($_POST['estudiantes_ids'] ?? '[]', true);

if (!$grupo_id || empty($estudiantes_ids)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

try {
    $pdo->beginTransaction();

    $insertados = 0;
    $omitidos = 0;

    foreach ($estudiantes_ids as $p_id) {
        // 1. Verificar si ya existe en inscripciones y su estado
        $stmtCheck = $pdo->prepare("SELECT estado FROM inscripciones WHERE persona_id = ? AND grupo_id = ? LIMIT 1");
        $stmtCheck->execute([$p_id, $grupo_id]);
        $existe = $stmtCheck->fetch();

        // 2. Si no existe, o existe pero su estado NO es 'En formación' (según tu regla)
        // Nota: También verificamos el estado general del estudiante en la tabla personas si fuera necesario
        if (!$existe || $existe['estado'] !== 'En formación') {
            
            if ($existe) {
                // Si existe pero no está en formación, actualizamos el registro actual a 'Inscrito'
                $stmtInscribir = $pdo->prepare("UPDATE inscripciones SET estado = 'Inscrito' WHERE persona_id = ? AND grupo_id = ?");
                $stmtInscribir->execute([$p_id, $grupo_id]);
            } else {
                // Si no existe, insertamos nuevo registro
                $stmtInscribir = $pdo->prepare("INSERT INTO inscripciones (persona_id, grupo_id, estado, codigo) VALUES (?, ?, 'Inscrito', ?)");
                $codigo = "INS-" . date('Y') . "-" . str_pad($p_id, 4, "0", STR_PAD_LEFT);
                $stmtInscribir->execute([$p_id, $grupo_id, $codigo]);
            }
            $insertados++;
        } else {
            $omitidos++;
        }
    }

    // 3. Opcional: Actualizar cupos disponibles en la tabla grupos
    $stmtUpdateCupos = $pdo->prepare("UPDATE grupos SET cupos_disponibles = cupos - (SELECT COUNT(*) FROM inscripciones WHERE grupo_id = ? AND estado != 'Anulado') WHERE id = ?");
    $stmtUpdateCupos->execute([$grupo_id, $grupo_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'insertados' => $insertados, 'omitidos' => $omitidos]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}