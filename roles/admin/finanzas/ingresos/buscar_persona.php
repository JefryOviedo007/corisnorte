<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../../../../config.php";

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, nombres_completos, numero_documento, tipo_documento
        FROM personas
        WHERE nombres_completos LIKE ? OR numero_documento LIKE ?
        LIMIT 8
    ");
    $like = "%$q%";
    $stmt->execute([$like, $like]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode([]);
}