<?php
// Evitar que errores de PHP se mezclen con el JSON
error_reporting(0); 
ini_set('display_errors', 0);

require_once __DIR__ . "/../../../../config.php";

// Limpiar cualquier salida accidental (espacios en blanco, warnings)
if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

// Funci¨®n para convertir a formato "Nombre Propio"
function formatearTexto($texto) {
    return mb_convert_case(mb_strtolower(trim($texto), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'];
        $foto_nombre = null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto_nombre = "foto_" . $id . "_" . time() . "." . $ext;
            $directorio = "../../../../assets/img/perfiles/";
            
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            move_uploaded_file($_FILES['foto']['tmp_name'], $directorio . $foto_nombre);
        }

        $sql = "UPDATE personas SET 
                nombres_completos = ?, 
                tipo_documento = ?, 
                numero_documento = ?, 
                correo = ?, 
                telefono = ?,
                direccion = ?,
                sisben = ?,
                estado_civil = ?,
                eps = ?,
                contacto_emergencia_nombre = ?,
                contacto_emergencia_parentesco = ?,
                contacto_emergencia_telefono = ?,
                contacto_emergencia_direccion = ?";
        
        $params = [
            formatearTexto($_POST['nombres_completos']), // Juan Perez
            $_POST['tipo_documento'], // Las siglas (CC, TI) se dejan igual
            $_POST['numero_documento'],
            mb_strtolower(trim($_POST['correo']), 'UTF-8'), // El correo siempre en min¨²scula
            $_POST['telefono'],
            formatearTexto($_POST['direccion']), // Calle 123 #45-67 -> Calle 123 #45-67
            mb_strtoupper(trim($_POST['sisben']), 'UTF-8'), // El Sisb¨¦n suele ir en May¨²scula (A1)
            $_POST['estado_civil'],
            formatearTexto($_POST['eps']), // Salud Total
            formatearTexto($_POST['contacto_nombre']), // Maria Lopez
            formatearTexto($_POST['contacto_parentesco']), // Padre / Madre
            $_POST['contacto_celular'],
            formatearTexto($_POST['contacto_direccion']) // Avenida Siempre Viva
        ];

        if ($foto_nombre) {
            $sql .= ", foto = ?";
            $params[] = $foto_nombre;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Datos actualizados correctamente']);
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
exit;