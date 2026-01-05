<?php
session_start();
require_once "config.php"; // crea $conn (MySQLi)

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($correo === '' || $contrasena === '') {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Debes completar todos los campos."
        ]);
        exit;
    }

    // 🔐 Buscar usuario (incluye sede_id)
    $stmt = $conn->prepare("
        SELECT id, nombre, correo, contrasena, rol, sede_id
        FROM usuarios
        WHERE correo = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {

        $usuario = $resultado->fetch_assoc();

        if (password_verify($contrasena, $usuario['contrasena'])) {

            // ✅ GUARDAR SESIÓN COMPLETA
            $_SESSION['id']      = $usuario['id'];
            $_SESSION['nombre']  = $usuario['nombre'];
            $_SESSION['rol']     = $usuario['rol'];
            $_SESSION['sede_id'] = $usuario['sede_id'];

            // ✅ REDIRECCIÓN CORRECTA
            $redirect = "/dashboard.php";

            echo json_encode([
                "status"   => "ok",
                "redirect" => $redirect
            ]);
            exit;

        } else {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Contraseña incorrecta."
            ]);
            exit;
        }

    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Correo no registrado."
        ]);
        exit;
    }

} else {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Método no permitido."
    ]);
}
