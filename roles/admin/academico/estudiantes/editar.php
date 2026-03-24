<?php
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

// Función para formatear texto: "JUAN PEREZ" -> "Juan Perez"
function formatText($text) {
    if (empty($text)) return null;
    return mb_convert_case(trim($text), MB_CASE_TITLE, "UTF-8");
}

// --- ACCIÓN: ACTUALIZAR ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    try {
        $id = $_POST['id'];
        
        // 1. Procesar la Foto (Carpeta uploads en la misma ruta que editar.php)
        $nombre_foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            // Nombre único para evitar caché del navegador
            $nombre_foto = "estudiante_" . $id . "_" . time() . "." . $ext;
            
            // Ruta: Misma carpeta que editar.php + /uploads/
            $directorio_subida = __DIR__ . "/uploads/";
            $ruta_destino = $directorio_subida . $nombre_foto;
            
            // Crear carpeta uploads si no existe
            if (!is_dir($directorio_subida)) {
                mkdir($directorio_subida, 0777, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                // Actualizar el nombre del archivo en la BD
                $stmtFoto = $pdo->prepare("UPDATE personas SET foto = ? WHERE id = ?");
                $stmtFoto->execute([$nombre_foto, $id]);
            }
        }

        // 2. Actualizar datos generales con formato Capitalizado
        $sql = "UPDATE personas SET 
                tipo_documento = ?, 
                numero_documento = ?, 
                nombres_completos = ?, 
                telefono = ?, 
                correo = ?, 
                direccion = ?, 
                estado = ?, 
                sisben = ?, 
                estado_civil = ?, 
                eps = ?, 
                contacto_emergencia_nombre = ?, 
                contacto_emergencia_parentesco = ?, 
                contacto_emergencia_telefono = ?, 
                contacto_emergencia_direccion = ?
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['tipo_documento'], 
            $_POST['numero_documento'], 
            formatText($_POST['nombres_completos']), 
            $_POST['telefono'], 
            mb_strtolower($_POST['correo']), 
            formatText($_POST['direccion']), 
            $_POST['estado'],
            formatText($_POST['sisben']),
            formatText($_POST['estado_civil']), 
            mb_strtoupper($_POST['eps'] ?? ''), 
            formatText($_POST['contacto_nombre']), 
            formatText($_POST['contacto_parentesco']),
            $_POST['contacto_telefono'], 
            formatText($_POST['contacto_direccion']),
            $id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Estudiante actualizado correctamente']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// --- ACCIÓN: OBTENER DATOS (GET) ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM personas WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $est = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($est) {
            echo json_encode(['status' => 'success', 'data' => $est]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Estudiante no encontrado']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}