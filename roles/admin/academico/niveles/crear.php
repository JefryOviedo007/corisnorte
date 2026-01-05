<?php
require_once __DIR__ . "/../../../../config.php";

if ($_POST) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $estado = $_POST['estado'];

    $stmt = $pdo->prepare("INSERT INTO niveles_formacion (nombre, descripcion, estado) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $estado]);
}
