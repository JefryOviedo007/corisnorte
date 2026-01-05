<?php
require_once __DIR__ . "/../../../config.php";

if ($_POST) {

    $nombre    = $_POST['nombre'] ?? null;
    $ciudad    = $_POST['ciudad'] ?? null;
    $telefono  = $_POST['telefono'] ?? null;
    $correo    = $_POST['correo'] ?? null;
    $direccion = $_POST['direccion'] ?? null;

    // Verificar si ya existe
    $existe = $pdo->query("SELECT id FROM institucion LIMIT 1")->fetch();

    if ($existe) {
        $stmt = $pdo->prepare("
            UPDATE institucion 
            SET nombre=?, ciudad=?, telefono=?, correo=?, direccion=? 
            WHERE id=?
        ");
        $stmt->execute([
            $nombre, $ciudad, $telefono, $correo, $direccion, $existe['id']
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO institucion (nombre, ciudad, telefono, correo, direccion)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nombre, $ciudad, $telefono, $correo, $direccion
        ]);
    }
}
