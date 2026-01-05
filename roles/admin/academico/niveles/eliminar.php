<?php
require_once __DIR__ . "/../../../../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['ids']) || empty($_POST['ids'])) {
        http_response_code(400);
        exit;
    }

    // Recibe: "1,3,5"
    $ids = explode(",", $_POST['ids']);

    // Limpieza de seguridad (solo números)
    $ids = array_map('intval', $ids);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "DELETE FROM niveles_formacion WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);

    echo "ok";
}
