<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Configuración de límites básica
set_time_limit(0);
ini_set('memory_limit', '512M');

require_once __DIR__ . "/../../../../config.php"; 
header('Content-Type: application/json');

$sede_id_usuario = $_SESSION['sede_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_excel'])) {
    $file = $_FILES['archivo_excel']['tmp_name'];
    
    // 1. Detección de delimitador más robusta
    $handle = fopen($file, "r");
    $first_line = fgets($handle);
    $delimitador = (strpos($first_line, ';') !== false) ? ';' : ',';
    rewind($handle); // Regresamos al inicio para leer con fgetcsv

    if ($handle !== FALSE) {
        $insertados = 0;
        $fila_numero = 0;
        $errores = [];

        // 2. Leer y limpiar encabezados (Quitamos BOM y espacios)
        $headers = fgetcsv($handle, 1000, $delimitador);
        if ($headers) {
            foreach ($headers as $key => $value) {
                // Elimina caracteres no imprimibles y espacios
                $headers[$key] = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value));
            }

            $idx_nombres   = array_search('APELLIDOS Y NOMBRES', $headers);
            $idx_tipo_doc  = array_search('TD', $headers);
            $idx_documento = array_search('DOCUMENTO', $headers);
            $idx_telefono  = array_search('TELEFONO', $headers);
            $idx_correo    = array_search('CORREO', $headers);
            $idx_direccion = array_search('DIRECCION', $headers);
        }

        // 3. Procesamiento fila por fila
        // Usamos INSERT IGNORE para que si un documento ya existe, simplemente salte a la siguiente fila sin romper todo
        $sql = "INSERT IGNORE INTO personas (
                    tipo_documento, 
                    numero_documento, 
                    nombres_completos, 
                    telefono, 
                    correo, 
                    direccion, 
                    sede_id,
                    estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Prospecto')";
        
        $stmt = $pdo->prepare($sql);

        while (($data = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
            $fila_numero++;
            
            // Validar que la fila no sea solo una línea vacía de Excel
            if (empty(array_filter($data))) continue;

            $num_doc = ($idx_documento !== false) ? trim($data[$idx_documento] ?? '') : '';
            if (empty($num_doc)) continue;

            try {
                $stmt->execute([
                    ($idx_tipo_doc !== false)  ? trim($data[$idx_tipo_doc] ?? 'CC') : 'CC',
                    $num_doc,
                    ($idx_nombres !== false)   ? mb_strtoupper(trim($data[$idx_nombres] ?? ''), 'UTF-8') : '',
                    ($idx_telefono !== false)  ? trim($data[$idx_telefono] ?? '') : '',
                    ($idx_correo !== false)    ? strtolower(trim($data[$idx_correo] ?? '')) : '',
                    ($idx_direccion !== false) ? trim($data[$idx_direccion] ?? '') : '',
                    $sede_id_usuario
                ]);
                
                // Si rowCount es > 0 significa que se insertó (no era duplicado)
                if ($stmt->rowCount() > 0) {
                    $insertados++;
                }
            } catch (Exception $e) {
                // Guardamos el error pero permitimos que el bucle siga con la siguiente fila
                $errores[] = "Fila $fila_numero: " . $e->getMessage();
            }
        }

        fclose($handle);

        echo json_encode([
            'status' => 'success',
            'insertados' => $insertados,
            'total_filas' => $fila_numero,
            'errores' => $errores,
            'message' => "Se procesaron $fila_numero filas. $insertados registros nuevos agregados."
        ]);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo abrir el archivo temporal.']);
    }
}