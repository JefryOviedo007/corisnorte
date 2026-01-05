<?php
require_once __DIR__ . "/../../../config.php";

$id = $_POST['id'] ?? null;

if ($id) {

    // Obtener imagen
    $stmt = $pdo->prepare("SELECT imagen FROM sedes WHERE id = ?");
    $stmt->execute([$id]);
    $sede = $stmt->fetch();

    // Eliminar imagen física
    if ($sede && $sede['imagen']) {
        $ruta = __DIR__ . "/../../../" . $sede['imagen'];
        if (file_exists($ruta)) unlink($ruta);
    }

    // Eliminar registro
    $stmt = $pdo->prepare("DELETE FROM sedes WHERE id = ?");
    $stmt->execute([$id]);
}
